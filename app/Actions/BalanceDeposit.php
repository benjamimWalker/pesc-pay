<?php

namespace App\Actions;

use App\Models\Wallet;

class BalanceDeposit
{
    public function handle(int $userId, float $amount)
    {
        Wallet::whereUserId($userId)
            ->increment('balance', $amount);
    }
}
