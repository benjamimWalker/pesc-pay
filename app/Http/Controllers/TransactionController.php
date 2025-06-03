<?php

namespace App\Http\Controllers;

use App\Actions\MakePayment;
use App\Actions\RefundPayment;
use App\Http\Requests\CreateTransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function create(CreateTransactionRequest $request, MakePayment $makePayment): JsonResponse
    {
        $makePayment->handle($request);

        return response()->json(['message' => 'Transaction completed successfully'], Response::HTTP_CREATED);
    }

    public function refund(Transaction $transaction, RefundPayment $refundPayment): JsonResponse
    {
        $refundPayment->handle($transaction);

        return response()->json(['message' => 'Transaction refunded successfully'], Response::HTTP_OK);
    }
}
