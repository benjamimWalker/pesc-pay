<?php

namespace App\Contracts;

interface TransactionAuthorization
{
    public function authorize(): bool;
}
