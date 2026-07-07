<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\HomeResource;
use App\Http\Resources\SlideResource;
use App\Models\HomeSetting;
use App\Models\Slide;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HomeController extends Controller
{
    /**
     * GET /home — services, president quote and site stats (global blocks).
     */
    public function index(): HomeResource
    {
        return new HomeResource(HomeSetting::current());
    }

    /**
     * GET /home/slides — active home slides in manual order.
     * Returns a plain `{ "data": Slide[] }` collection (not paginated).
     */
    public function slides(): AnonymousResourceCollection
    {
        $slides = Slide::query()
            ->activeOrdered()
            ->with(['news', 'media', 'imageAsset.media'])
            ->get();

        return SlideResource::collection($slides);
    }
}
