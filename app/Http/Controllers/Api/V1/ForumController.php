<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ForumResource;
use App\Models\ForumCategory;
use App\Models\ForumStat;
use App\Models\ForumTopic;

class ForumController extends Controller
{
    /**
     * GET /forum — categories and topics (active, in manual order; pinned topics
     * first) plus the global community stats singleton. All stats values are
     * display strings.
     */
    public function index(): ForumResource
    {
        $stats = ForumStat::current();

        return new ForumResource([
            'categories' => ForumCategory::query()->active()->ordered()->get(),
            'topics' => ForumTopic::query()->active()->ordered()->get(),
            'stats' => [
                'members' => (string) $stats->members,
                'topics' => (string) $stats->topics,
                'posts' => (string) $stats->posts,
                'online' => (string) $stats->online,
            ],
        ]);
    }
}
