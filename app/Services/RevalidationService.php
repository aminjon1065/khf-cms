<?php

namespace App\Services;

use App\Enums\RevalidationTag;
use App\Jobs\SendRevalidationRequest;
use App\Models\News;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Builds ISR revalidation tag sets (docs/API-CONTRACT.md §5) and hands them to
 * the queued webhook job. `ping()` is a synchronous connection check for the
 * CMS settings page.
 */
class RevalidationService
{
    /**
     * @param  list<string>  $tags
     */
    public function revalidate(array $tags): void
    {
        $tags = array_values(array_unique(array_filter($tags)));

        if ($tags === []) {
            return;
        }

        SendRevalidationRequest::dispatch($tags);
    }

    public function forNews(News $news): void
    {
        $this->revalidate([
            RevalidationTag::News->value,
            'news:'.$news->slug,
            RevalidationTag::Home->value,
        ]);
    }

    public function forSlides(): void
    {
        $this->revalidate([
            RevalidationTag::Home->value,
            RevalidationTag::News->value,
        ]);
    }

    public function forHome(): void
    {
        $this->revalidate([RevalidationTag::Home->value]);
    }

    public function forDocuments(): void
    {
        $this->revalidate([RevalidationTag::Documents->value]);
    }

    public function forRegions(): void
    {
        $this->revalidate([RevalidationTag::Regions->value]);
    }

    public function forForum(): void
    {
        $this->revalidate([RevalidationTag::Forum->value]);
    }

    /**
     * "Сбросить весь кеш": send every fixed tag.
     */
    public function flushAll(): void
    {
        $this->revalidate(RevalidationTag::all());
    }

    /**
     * Synchronous connection test for the "Проверить соединение" button.
     *
     * @return array{ok: bool, status: int|null, error: string|null}
     */
    public function ping(): array
    {
        $url = config('khf.revalidate.frontend_url');
        $secret = config('khf.revalidate.secret');

        if (blank($url) || blank($secret)) {
            return ['ok' => false, 'status' => null, 'error' => 'FRONTEND_URL или REVALIDATE_SECRET не заданы в .env'];
        }

        try {
            $response = Http::withHeaders(['x-revalidate-secret' => $secret])
                ->acceptJson()
                ->timeout(10)
                ->post(rtrim((string) $url, '/').config('khf.revalidate.path'), ['tags' => [], 'paths' => []]);

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'error' => $response->successful() ? null : $response->body(),
            ];
        } catch (Throwable $e) {
            return ['ok' => false, 'status' => null, 'error' => $e->getMessage()];
        }
    }
}
