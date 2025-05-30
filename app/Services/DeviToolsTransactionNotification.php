<?php

namespace App\Services;

use App\Contracts\TransactionNotification;
use Illuminate\Support\Facades\Http;

class DeviToolsTransactionNotification implements TransactionNotification
{
    private $url;

    public function __construct()
    {
        $this->url = config('services.devi_tools_notifications_url');
    }

    public function notify(): bool
    {
        return Http::post($this->url, [
            'message' => 'Transaction completed successfully.',
        ])->successful();
    }
}
