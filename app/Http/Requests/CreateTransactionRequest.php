<?php

namespace App\Http\Requests;

use App\Contracts\TransactionAuthorization;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTransactionRequest extends FormRequest
{
    public function authorize(TransactionAuthorization $authorization): bool
    {
        return $authorization->authorize();
    }

    public function rules(): array
    {
        return [
            'value' => ['required', 'numeric', 'min:0.01', 'max:999999999'],
            'payer' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function (Builder $query) {
                    $query->where('type', 'common');
                }),
                Rule::exists('wallets', 'user_id')->where(function (Builder $query) {
                    $query->where('balance', '>=', $this?->value ?? 0);
                })
            ],
            'payee' => [
                'required','integer','exists:users,id', 'different:payer'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'payer.required' => 'Please specify the payer for this transaction.',
            'payer.integer' => 'The payer ID must be a valid integer.',
            'payer.exists' => 'The payer must be an existing user with a "common" account type and sufficient balance to cover the transaction.',
            'payee.required' => 'Please specify the payee for this transaction.',
            'payee.integer' => 'The payee ID must be a valid integer.',
            'payee.exists' => 'The selected payee does not exist.',
            'payee.different' => 'The payer and payee cannot be the same user.',
            'value.required' => 'Please enter the amount you want to transfer.',
            'value.numeric' => 'The transaction amount must be a valid number.',
            'value.min' => 'The amount must be at least :min to proceed.',
            'value.max' => 'The amount cannot exceed :max.',
            'payer.exists_wallet' => 'The payer does not have enough balance to complete this transaction.',
        ];
    }
}
