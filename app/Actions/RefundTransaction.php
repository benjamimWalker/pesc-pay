<?php

namespace App\Actions;

use App\Enums\TransactionStatusEnum;
use App\Models\Transaction;

class RefundTransaction
{
    public function handle(Transaction $transaction): void
    {
        $transaction->update([
            'status' => TransactionStatusEnum::refunded,
        ]);
    }
}
