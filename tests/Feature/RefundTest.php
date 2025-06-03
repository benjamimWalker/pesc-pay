<?php

use App\Actions\RefundPayment;
use App\Actions\BalanceWithdraw;
use App\Actions\BalanceDeposit;
use App\Enums\TransactionStatusEnum;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;

it('refunds a completed transaction successfully', function () {
    $payer = User::factory()->create();
    $payee = User::factory()->create();

    Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 100]);
    Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 200]);

    $transaction = Transaction::factory()->create([
        'payer_id' => $payer->id,
        'payee_id' => $payee->id,
        'amount' => 50,
        'status' => TransactionStatusEnum::completed,
    ]);

    $refund = app(RefundPayment::class);

    $refund->handle($transaction);

    $transaction->refresh();

    expect(walletBalance($payer->id))->toBe(150)
        ->and(walletBalance($payee->id))->toBe(150);

    $refundRecord = Transaction::where('original_transaction_id', $transaction->id)->first();

    expect($refundRecord)->not()->toBeNull()
        ->and($refundRecord->status)->toBe(TransactionStatusEnum::completed->name)
        ->and($transaction->status)->toBe(TransactionStatusEnum::refunded->name);

});

it('creates a failed refund transaction if withdrawal fails', function () {
    $payer = User::factory()->create();
    $payee = User::factory()->create();

    Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 100]);
    Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 200]);

    $transaction = Transaction::factory()->create([
        'payer_id' => $payer->id,
        'payee_id' => $payee->id,
        'amount' => 50,
        'status' => TransactionStatusEnum::completed,
    ]);

    $withdrawMock = mock(BalanceWithdraw::class)
        ->expects('handle')
        ->andThrow(new Exception('withdraw fail'))
        ->getMock();

    app()->instance(BalanceWithdraw::class, $withdrawMock);

    $refund = app(RefundPayment::class);

    try {
        $refund->handle($transaction);
    } catch (Exception $e) {
        expect($e->getMessage())->toBe('withdraw fail');
    }

    $failedRefund = Transaction::where('original_transaction_id', $transaction->id)->first();

    expect($failedRefund)->not()->toBeNull()
        ->and($failedRefund->status)->toBe(TransactionStatusEnum::failed->name)
        ->and(walletBalance($payer->id))->toBe(100)
        ->and(walletBalance($payee->id))->toBe(200);

    $transaction->refresh();
    expect($transaction->status)->not()->toBe(TransactionStatusEnum::refunded->name);
});

it('creates a failed refund transaction if deposit fails', function () {
    $payer = User::factory()->create();
    $payee = User::factory()->create();

    Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 100]);
    Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 200]);

    $transaction = Transaction::factory()->create([
        'payer_id' => $payer->id,
        'payee_id' => $payee->id,
        'amount' => 50,
        'status' => TransactionStatusEnum::completed,
    ]);

    $depositMock = mock(BalanceDeposit::class)
        ->expects('handle')
        ->andThrow(new Exception('deposit fail'))
        ->getMock();

    app()->instance(BalanceDeposit::class, $depositMock);

    $refund = app(RefundPayment::class);

    try {
        $refund->handle($transaction);
    } catch (Exception $e) {
        expect($e->getMessage())->toBe('deposit fail');
    }

    $failedRefund = Transaction::where('original_transaction_id', $transaction->id)->first();

    expect($failedRefund)->not()->toBeNull()
        ->and($failedRefund->status)->toBe(TransactionStatusEnum::failed->name)
        ->and(walletBalance($payer->id))->toBe(100)
        ->and(walletBalance($payee->id))->toBe(200);

    $transaction->refresh();
    expect($transaction->status)->not()->toBe(TransactionStatusEnum::refunded->name);
});
