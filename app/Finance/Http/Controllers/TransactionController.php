<?php

declare(strict_types=1);

namespace App\Finance\Http\Controllers;

use App\Finance\Http\Requests\ListTransactionsRequest;
use App\Finance\Http\Requests\TransferRequest;
use App\Finance\Http\Resources\TransactionResource;
use Domain\Finance\Services\TransactionQueryService;
use Domain\Finance\Services\TransferService;
use Illuminate\Http\JsonResponse;

final readonly class TransactionController
{
    public function __construct(
        private TransferService $transferService,
        private TransactionQueryService $queryService
    ) {}

    public function index(ListTransactionsRequest $request): JsonResponse
    {
        $user = $request->user();
        
        // Refresh user to get latest balance from database
        $user->refresh();

        $transactions = $this->queryService->getTransactionsForUser($user);

        return response()->json([
            'balance' => $user->balance,
            'transactions' => TransactionResource::collection($transactions),
        ]);
    }

    public function store(TransferRequest $request): JsonResponse
    {
        $transaction = $this->transferService->transfer(
            sender: $request->user(),
            receiverId: $request->integer('receiver_id'),
            amount: $request->string('amount')->toString(),
            description: $request->string('description')->toString()
        );

        return TransactionResource::make($transaction)
            ->response()
            ->setStatusCode(201);
    }
}
