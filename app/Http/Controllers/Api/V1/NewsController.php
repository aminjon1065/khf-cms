<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsItemResource;
use App\Models\News;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class NewsController extends Controller
{
    private const EAGER = ['category', 'region', 'media', 'coverAsset.media'];

    public function index(Request $request): AnonymousResourceCollection
    {
        $news = News::query()
            ->published()
            ->with(self::EAGER)
            ->when($request->filled('category'), fn (Builder $query) => $query->whereHas(
                'category',
                fn (Builder $q) => $q->where('id', $request->query('category'))
                    ->orWhere('label->tj', $request->query('category'))
                    ->orWhere('label->ru', $request->query('category'))
                    ->orWhere('label->en', $request->query('category')),
            ))
            ->when($request->filled('search'), fn (Builder $query) => $query->where(fn (Builder $q) => $q
                ->where('title->tj', 'like', '%'.$request->query('search').'%')
                ->orWhere('title->ru', 'like', '%'.$request->query('search').'%')
                ->orWhere('title->en', 'like', '%'.$request->query('search').'%')))
            ->orderByDesc('published_at')
            ->paginate($this->perPage($request, 'per_page', 12));

        return NewsItemResource::collection($news);
    }

    public function show(Request $request): NewsItemResource
    {
        $news = $this->findPublished($this->idOrSlug($request))->with(self::EAGER)->firstOrFail();

        return new NewsItemResource($news);
    }

    public function related(Request $request): AnonymousResourceCollection
    {
        $news = $this->findPublished($this->idOrSlug($request))->firstOrFail();

        $related = News::query()
            ->published()
            ->with(self::EAGER)
            ->whereKeyNot($news->getKey())
            ->where(function (Builder $query) use ($news): void {
                $query->where('category_id', $news->category_id);

                if ($news->region_id !== null) {
                    $query->orWhere('region_id', $news->region_id);
                }
            })
            ->orderByDesc('published_at')
            ->paginate($this->perPage($request, 'limit', 3));

        return NewsItemResource::collection($related);
    }

    /**
     * Public view beacon. Counts one view per (IP + user agent) per hour so ISR
     * caching does not under-count. Always answers 204, even for repeats.
     */
    public function view(Request $request): Response
    {
        $news = $this->findPublished($this->idOrSlug($request))->firstOrFail();

        $key = sprintf('news-view:%d:%s', $news->getKey(), sha1($request->ip().'|'.$request->userAgent()));

        if (Cache::add($key, true, now()->addHour())) {
            // Query-builder increment: atomic and avoids firing model events
            // (no revision snapshot / revalidation for a view bump).
            News::query()->whereKey($news->getKey())->increment('views');
        }

        return response()->noContent();
    }

    /**
     * The {idOrSlug} route segment, read by name (positional binding would clash
     * with the optional {locale} prefix parameter).
     */
    private function idOrSlug(Request $request): string
    {
        return (string) $request->route('idOrSlug');
    }

    /**
     * Look up a published news item by slug first, then by numeric id.
     *
     * @return Builder<News>
     */
    private function findPublished(string $idOrSlug): Builder
    {
        return News::query()
            ->published()
            ->where(function (Builder $query) use ($idOrSlug): void {
                $query->where('slug', $idOrSlug);

                if (is_numeric($idOrSlug)) {
                    $query->orWhere('id', (int) $idOrSlug);
                }
            });
    }

    private function perPage(Request $request, string $key, int $default): int
    {
        $value = (int) $request->integer($key, $default);

        return $value >= 1 && $value <= 50 ? $value : $default;
    }
}
