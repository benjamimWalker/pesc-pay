<?php

namespace App\Actions;

use App\Contracts\TransactionNotification;
use App\Enums\TransactionStatusEnum;
use Illuminate\Support\Facades\DB;

use Throwable;
use function Illuminate\Support\defer;

readonly class MakePayment
{
    public function __construct(
        private BalanceDeposit $deposit,
        private BalanceWithdraw $withdraw,
        private TransactionNotification $notification,
        private CreateTransaction $createTransaction
    ) {
    }

    public function handle(object $transactionPayload): void
    {
        try {
            DB::transaction(function () use ($transactionPayload) {
                $this->withdraw->handle(
                    $transactionPayload->payer,
                    $transactionPayload->value
                );

                $this->deposit->handle(
                    $transactionPayload->payee,
                    $transactionPayload->value
                );

                $this->createTransaction->handle($transactionPayload, TransactionStatusEnum::completed);
            });
        } catch (Throwable $e) {
            $this->createTransaction->handle($transactionPayload, TransactionStatusEnum::failed);
            throw $e;
        }

        defer(fn () => $this->notification->notify());
    }
}
