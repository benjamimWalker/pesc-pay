<?php

namespace App\Contracts;

interface TransactionNotification
{
    public function notify(): bool;
}
