<?php

namespace App\Http\Controllers;

use App\Actions\MakePayment;
use App\Http\Requests\CreateTransactionRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function create(CreateTransactionRequest $request, MakePayment $makeTransaction): JsonResponse
    {
        $makeTransaction->handle($request);

        return response()->json(['message' => 'Transaction completed successfully.'], Response::HTTP_CREATED);
    }
}
