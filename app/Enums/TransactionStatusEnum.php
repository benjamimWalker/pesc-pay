<?php

namespace App\Enums;

enum TransactionStatusEnum
{
    case pending;
    case completed;
    case failed;
    case refunded;
}
