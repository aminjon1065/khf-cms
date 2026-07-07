<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivitiesResource;
use App\Models\Direction;
use App\Models\Program;

class ActivityController extends Controller
{
    /**
     * GET /activities — activity directions and state programmes, each in
     * manual order (active only).
     */
    public function index(): ActivitiesResource
    {
        return new ActivitiesResource([
            'directions' => Direction::query()->active()->ordered()->get(),
            'programs' => Program::query()->active()->ordered()->get(),
        ]);
    }
}
