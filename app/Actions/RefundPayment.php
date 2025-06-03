<?php

namespace App\Actions;

use App\Contracts\TransactionNotification;
use App\Enums\TransactionStatusEnum;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

use function Illuminate\Support\defer;

readonly class RefundPayment
{
    public function __construct(
        private BalanceDeposit          $deposit,
        private BalanceWithdraw         $withdraw,
        private TransactionNotification $notification,
        private CreateTransaction       $createTransaction,
        private RefundTransaction       $refundTransaction,
    ) {
    }

    public function handle(Transaction $transaction): void
    {
        try {
            DB::transaction(function () use ($transaction) {
                $this->withdraw->handle($transaction->payee_id, $transaction->amount);
                $this->deposit->handle($transaction->payer_id, $transaction->amount);

                $this->createTransaction->handle(
                    (object) [
                        'payer' => $transaction->payee_id,
                        'payee' => $transaction->payer_id,
                        'amount' => $transaction->amount
                    ],
                    TransactionStatusEnum::completed,
                    $transaction->id
                );
                $this->refundTransaction->handle($transaction);
            });
        } catch (Throwable $e) {
            $this->createTransaction->handle(
                (object)[
                    'payer' => $transaction->payee_id,
                    'payee' => $transaction->payer_id,
                    'amount' => $transaction->amount
                ],
                TransactionStatusEnum::failed,
                $transaction->id
            );

            throw $e;
        }

        defer(fn () => $this->notification->notify());
    }
}
