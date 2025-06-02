<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'payer_id',
        'payee_id',
        'original_transaction_id',
        'amount',
        'status',
        'type'
    ];
}
