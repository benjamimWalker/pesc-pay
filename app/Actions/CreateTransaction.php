<?php

namespace App\Actions;

use App\Enums\TransactionStatusEnum;
use App\Models\Transaction;

class CreateTransaction
{
    public function handle(object $transactionPayload, TransactionStatusEnum $status, ?int $originalTransaction = null): void
    {
        Transaction::create([
            'payer_id' => $transactionPayload->payer,
            'payee_id' => $transactionPayload->payee,
            'original_transaction_id' => $originalTransaction,
            'amount' => $transactionPayload->amount,
            'status' => $status->name
        ]);
    }
}
