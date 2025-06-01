<?php

namespace App\Actions;

use App\Models\Wallet;

class BalanceWithdraw
{
    public function handle(int $userId, float $amount): void
    {
        Wallet::whereUserId($userId)
            ->decrement('balance', $amount);
    }
}
