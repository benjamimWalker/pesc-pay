<?php

namespace App\Http\Controllers;

use App\Actions\MakeTransaction;
use App\Http\Requests\CreateTransactionRequest;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function create(
        CreateTransactionRequest $request,
        MakeTransaction $makeTransaction
    )
    {
        $makeTransaction->handle($request);

        return response()->json(['message' => 'Transaction completed successfully.'], Response::HTTP_CREATED);
    }
}
