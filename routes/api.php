<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('transfer')->group(function () {
    Route::post('', [TransactionController::class, 'create']);
});
