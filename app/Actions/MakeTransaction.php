<?php

namespace App\Actions;

use App\Contracts\TransactionNotification;
use Illuminate\Support\Facades\DB;
use function Illuminate\Support\defer;

readonly class MakeTransaction
{
    public function __construct(
        private BalanceDeposit $deposit,
        private BalanceWithdraw $withdraw,
        private TransactionNotification $notification
    ) {
    }

    public function handle(object $transactionPayload): void
    {
        DB::transaction(function () use ($transactionPayload) {
            $this->withdraw->handle(
                $transactionPayload->payer,
                $transactionPayload->value
            );

            $this->deposit->handle(
                $transactionPayload->payee,
                $transactionPayload->value
            );
        });

        defer(fn() => $this->notification->notify());
    }
}
