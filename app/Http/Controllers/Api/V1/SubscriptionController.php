<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    /**
     * POST /subscriptions — register an alert subscription. Success is returned
     * WITHOUT a `data` wrapper per the contract (docs/API-CONTRACT.md §4).
     */
    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        $subscription = Subscription::create($request->safe()->only([
            'channel', 'region', 'categories', 'contact',
        ]));

        return response()->json([
            'ok' => true,
            'reference' => $subscription->reference,
        ]);
    }
}
