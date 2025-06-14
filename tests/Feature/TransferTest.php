<?php

use App\Actions\BalanceDeposit;
use App\Actions\MakePayment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Http;

const ENDPOINT = 'api/transfer';

beforeEach(function () {
    Http::preventStrayRequests();
});

function walletBalance(int $userId): int
{
    return Wallet::whereUserId($userId)->first()?->balance ?? 0;
}

it('creates a transaction from a common user to a merchant', function () {
    fakeSuccessfulCalls();

    $payer = User::factory()->create(['type' => 'common']);
    $payee = User::factory()->create(['type' => 'merchant']);

    Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 1000]);
    Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 200]);

    $transferAmount = 100;

    expect(walletBalance($payer->id))->toBe(1000)
        ->and(walletBalance($payee->id))->toBe(200);

    $payload = [
        'payer' => $payer->id,
        'payee' => $payee->id,
        'amount' => $transferAmount,
    ];

    $this->postJson(ENDPOINT, $payload)
        ->assertCreated()
        ->assertJson(['message' => 'Transaction completed successfully']);

    expect(walletBalance($payer->id))->toBe(900)
        ->and(walletBalance($payee->id))->toBe(300);

    $transaction = Transaction::latest()->first();
    expect($transaction->payer_id)->toBe($payer->id)
        ->and($transaction->payee_id)->toBe($payee->id)
        ->and($transaction->amount)->toBe($transferAmount)
        ->and($transaction->status)->toBe('completed');

    Http::assertSent(function ($request) {
        return $request->url() === 'http://fake-notify-url.test'
            && $request->method() === 'POST'
            && $request->data()['message'] === 'Transaction completed successfully';
    });
});

it('creates a transaction from a common user to another common user', function () {
    fakeSuccessfulCalls();

    $payer = User::factory()->create(['type' => 'common']);
    $payee = User::factory()->create(['type' => 'common']);

    Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 500]);
    Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 100]);

    $transferAmount = 50;

    expect(walletBalance($payer->id))->toBe(500)
        ->and(walletBalance($payee->id))->toBe(100);

    $payload = [
        'payer' => $payer->id,
        'payee' => $payee->id,
        'amount' => $transferAmount,
    ];

    $this->postJson(ENDPOINT, $payload)
        ->assertCreated()
        ->assertJson(['message' => 'Transaction completed successfully']);

    expect(walletBalance($payer->id))->toBe(450)
        ->and(walletBalance($payee->id))->toBe(150);

    $transaction = Transaction::latest()->first();
    expect($transaction->payer_id)->toBe($payer->id)
        ->and($transaction->payee_id)->toBe($payee->id)
        ->and($transaction->amount)->toBe($transferAmount)
        ->and($transaction->status)->toBe('completed');

    Http::assertSent(function ($request) {
        return $request->url() === 'http://fake-notify-url.test'
            && $request->method() === 'POST'
            && $request->data()['message'] === 'Transaction completed successfully';
    });
});

it('fails if a merchant tries to send money', function () {
    fakeSuccessfulCalls();

    $payer = User::factory()->create(['type' => 'merchant']);
    $payee = User::factory()->create(['type' => 'common']);

    Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 1000]);
    Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 100]);

    $payload = [
        'payer' => $payer->id,
        'payee' => $payee->id,
        'amount' => 50,
    ];

    $this->postJson(ENDPOINT, $payload)
        ->assertUnprocessable()
        ->assertJson([
            'errors' => [
                'payer' => ['The payer must be an existing user with a "common" account type and sufficient balance to cover the transaction.'],
            ],
        ]);

    expect(walletBalance($payer->id))->toBe(1000)
        ->and(walletBalance($payee->id))->toBe(100);
});

it('fails if payer does not have enough balance', function () {
    fakeSuccessfulCalls();

    $payer = User::factory()->create(['type' => 'common']);
    $payee = User::factory()->create(['type' => 'common']);

    Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 10]);
    Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 50]);

    $payload = [
        'payer' => $payer->id,
        'payee' => $payee->id,
        'amount' => 100,
    ];

    $this->postJson(ENDPOINT, $payload)
        ->assertUnprocessable()
        ->assertJson([
            'errors' => [
                'payer' => ['The payer must be an existing user with a "common" account type and sufficient balance to cover the transaction.'],
            ],
        ]);

    expect(walletBalance($payer->id))->toBe(10)
        ->and(walletBalance($payee->id))->toBe(50);
});

it('fails if payee and payer are the same', function () {
    fakeSuccessfulCalls();

    $user = User::factory()->create(['type' => 'common']);
    Wallet::factory()->create(['user_id' => $user->id, 'balance' => 100]);

    $payload = [
        'payer' => $user->id,
        'payee' => $user->id,
        'amount' => 10,
    ];

    $this->postJson(ENDPOINT, $payload)
        ->assertUnprocessable()
        ->assertJson([
            'errors' => [
                'payee' => ['The payer and payee cannot be the same user.'],
            ],
        ]);

    expect(walletBalance($user->id))->toBe(100);
});

it('fails if payer wallet is missing', function () {
    fakeSuccessfulCalls();

    $payer = User::factory()->create(['type' => 'common']);
    $payee = User::factory()->create(['type' => 'common']);

    Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 100]);

    $payload = [
        'payer' => $payer->id,
        'payee' => $payee->id,
        'amount' => 20,
    ];

    $this->postJson(ENDPOINT, $payload)
        ->assertUnprocessable()
        ->assertJson([
            'errors' => [
                'payer' => ['The payer must be an existing user with a "common" account type and sufficient balance to cover the transaction.'],
            ],
        ]);

    expect(Wallet::whereUserId($payer->id)->exists())->toBeFalse()
        ->and(walletBalance($payee->id))->toBe(100);
});

it('fails if payee does not exist', function () {
    fakeSuccessfulCalls();

    $payer = User::factory()->create(['type' => 'common']);
    Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 500]);

    $payload = [
        'payer' => $payer->id,
        'payee' => 9999,
        'amount' => 10,
    ];

    $this->postJson(ENDPOINT, $payload)
        ->assertUnprocessable()
        ->assertJson([
            'errors' => [
                'payee' => ['The selected payee does not exist.'],
            ],
        ]);

    expect(walletBalance($payer->id))->toBe(500);
});

it('fails if authorization service denies', function () {
    fakeFailedCalls();

    $payer = User::factory()->create(['type' => 'common']);
    $payee = User::factory()->create(['type' => 'common']);

    Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 1000]);
    Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 100]);

    $payload = [
        'payer' => $payer->id,
        'payee' => $payee->id,
        'amount' => 100,
    ];

    $this->postJson(ENDPOINT, $payload)
        ->assertForbidden();

    expect(walletBalance($payer->id))->toBe(1000)
        ->and(walletBalance($payee->id))->toBe(100);
});

it('rolls back if deposit fails', function () {
    $payer = User::factory()->create(['type' => 'common']);
    $payee = User::factory()->create(['type' => 'common']);

    Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 1000]);
    Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 200]);

    $amount = 100;

    $depositMock = mock(BalanceDeposit::class)
        ->expects('handle')
        ->andThrow(new Exception('Deposit failure'))
        ->getMock();

    app()->instance(BalanceDeposit::class, $depositMock);

    $transaction = app(MakePayment::class);

    $payload = (object) [
        'payer' => $payer->id,
        'payee' => $payee->id,
        'amount' => $amount,
    ];

    try {
        $transaction->handle($payload);
    } catch (Exception $e) {
        expect($e->getMessage())->toBe('Deposit failure');
    }

    expect(walletBalance($payer->id))->toBe(1000)
        ->and(walletBalance($payee->id))->toBe(200);
});

it('creates a transaction with failed status if deposit fails', function () {
    $payer = User::factory()->create(['type' => 'common']);
    $payee = User::factory()->create(['type' => 'common']);

    Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 1000]);
    Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 200]);

    $amount = 100;

    $depositMock = mock(BalanceDeposit::class)
        ->expects('handle')
        ->andThrow(new Exception('Deposit failure'))
        ->getMock();

    app()->instance(BalanceDeposit::class, $depositMock);

    $transactionService = app(MakePayment::class);

    $payload = (object) [
        'payer' => $payer->id,
        'payee' => $payee->id,
        'amount' => $amount,
    ];

    try {
        $transactionService->handle($payload);
    } catch (Exception $e) {
        expect($e->getMessage())->toBe('Deposit failure');
    }

    $failedTransaction = Transaction::where('payer_id', $payer->id)
        ->where('payee_id', $payee->id)
        ->where('amount', $amount)
        ->latest()
        ->first();

    expect($failedTransaction)->not()->toBeNull()
        ->and($failedTransaction->status)->toBe('failed')
        ->and(walletBalance($payer->id))->toBe(1000)
        ->and(walletBalance($payee->id))->toBe(200);
});
