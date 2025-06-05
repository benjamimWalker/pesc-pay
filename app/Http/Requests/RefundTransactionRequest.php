<?php

namespace App\Http\Requests;

use App\Contracts\TransactionAuthorization;
use Illuminate\Foundation\Http\FormRequest;

class RefundTransactionRequest extends FormRequest
{
    public function authorize(TransactionAuthorization $authorization): bool
    {
        return $authorization->authorize();
    }
}
