<?php

namespace App\Services;

use App\Enums\NewsStatus;
use App\Models\News;
use App\Models\NewsRevision;

/**
 * Keeps a rolling history of the last {@see self::LIMIT} versions of a news
 * item and can restore any of them (ToR §5.2 «ревизии: 15 версий, откат»).
 */
class NewsRevisionService
{
    private const LIMIT = 15;

    /**
     * @var list<string>
     */
    private const TRANSLATABLE = ['title', 'excerpt', 'body', 'seo_title', 'seo_description'];

    public function record(News $news): void
    {
        $data = $this->snapshot($news);

        $latest = $news->revisions()->latest('id')->first();

        if ($latest !== null && $latest->data == $data) {
            return; // nothing changed since the last revision (e.g. a no-op autosave)
        }

        $news->revisions()->create([
            'user_id' => auth()->id(),
            'data' => $data,
        ]);

        $this->prune($news);
    }

    public function rollback(NewsRevision $revision): void
    {
        $news = $revision->news;
        $data = $revision->data;

        foreach (self::TRANSLATABLE as $attribute) {
            $news->setTranslations($attribute, $data[$attribute] ?? []);
        }

        $news->slug = $data['slug'];
        $news->category_id = $data['category_id'];
        $news->region_id = $data['region_id'];
        $news->author = $data['author'];
        $news->status = NewsStatus::from($data['status']);
        $news->published_at = $data['published_at'];

        $news->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(News $news): array
    {
        $data = [
            'slug' => $news->slug,
            'category_id' => $news->category_id,
            'region_id' => $news->region_id,
            'author' => $news->author,
            'status' => $news->status?->value,
            'published_at' => $news->published_at?->toISOString(),
        ];

        foreach (self::TRANSLATABLE as $attribute) {
            $data[$attribute] = $news->getTranslations($attribute);
        }

        return $data;
    }

    private function prune(News $news): void
    {
        $stale = $news->revisions()->latest('id')->pluck('id')->slice(self::LIMIT);

        if ($stale->isNotEmpty()) {
            NewsRevision::query()->whereIn('id', $stale)->delete();
        }
    }
}
