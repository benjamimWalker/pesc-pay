<?php

namespace App\Services;

use App\Contracts\TransactionAuthorization;
use Illuminate\Support\Facades\Http;

class DeviToolsTransactionAuthorization implements TransactionAuthorization
{
    private string $url;

    public function __construct()
    {
        $this->url = config('services.devi_tools_authorization_url');
    }

    public function authorize(): bool
    {
        return Http::get($this->url)->successful();
    }
}
