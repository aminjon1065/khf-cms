<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegionsResource;
use App\Models\MapRegion;
use App\Models\MapSetting;

class RegionController extends Controller
{
    /**
     * GET /regions — operational map regions plus a global stats block. The
     * region/station/incident counts are computed from the active regions;
     * `monitoring` is the editable singleton value.
     */
    public function index(): RegionsResource
    {
        $regions = MapRegion::query()->active()->ordered()->get();

        return new RegionsResource([
            'regions' => $regions,
            'stats' => [
                'regions' => $regions->count(),
                'stations' => (int) $regions->sum('stations'),
                'activeIncidents' => (int) $regions->sum('active_incidents'),
                'monitoring' => (string) MapSetting::current()->monitoring,
            ],
        ]);
    }
}
