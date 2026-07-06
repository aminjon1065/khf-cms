<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StructureResource;
use App\Models\Department;
use App\Models\Leader;
use App\Models\RegionalOffice;

class StructureController extends Controller
{
    /**
     * GET /structure — leadership, departments and regional offices, each in
     * manual order (active only).
     */
    public function index(): StructureResource
    {
        return new StructureResource([
            'leadership' => Leader::query()->active()->ordered()->get(),
            'departments' => Department::query()->active()->ordered()->get(),
            'offices' => RegionalOffice::query()->active()->ordered()->get(),
        ]);
    }
}
