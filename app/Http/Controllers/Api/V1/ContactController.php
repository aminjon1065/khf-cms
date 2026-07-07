<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactsResource;
use App\Models\ContactOffice;
use App\Models\Hotline;

class ContactController extends Controller
{
    /**
     * GET /contacts — emergency hotlines plus the central office (headOffice)
     * and the regional offices, each active and in manual order.
     */
    public function index(): ContactsResource
    {
        $offices = ContactOffice::query()->active()->ordered()->get();

        return new ContactsResource([
            'hotlines' => Hotline::query()->active()->ordered()->get(),
            'headOffice' => $offices->firstWhere('is_head', true),
            'offices' => $offices->where('is_head', false)->values(),
        ]);
    }
}
