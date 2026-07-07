<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

class ContactMessageController extends Controller
{
    /**
     * POST /contact — store a contact-form message. Success is returned WITHOUT a
     * `data` wrapper and without a reference per the contract
     * (docs/API-CONTRACT.md §4).
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        ContactMessage::create($request->safe()->only([
            'name', 'email', 'subject', 'message',
        ]));

        return response()->json(['ok' => true]);
    }
}
