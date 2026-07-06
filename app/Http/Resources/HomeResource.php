<?php

namespace App\Http\Resources;

use App\Models\HomeSetting;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * GET /home — composed home blocks (docs/API-CONTRACT.md §3):
 * { data: { services, president, stats } }. Translatable fields resolve to the
 * current locale with a Tajik fallback.
 *
 * @mixin HomeSetting
 */
class HomeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'services' => ServiceResource::collection(Service::query()->activeOrdered()->get()),
            'president' => [
                'name' => $this->president_name,
                'role' => $this->president_role,
                'quote' => $this->president_quote,
                'href' => $this->president_href,
            ],
            'stats' => [
                'today' => $this->stats_today,
                'month' => $this->stats_month,
                'rescued' => $this->stats_rescued,
                'reaction' => $this->stats_reaction,
            ],
        ];
    }
}
