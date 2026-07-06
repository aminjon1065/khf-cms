<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Notifies the Next.js frontend to revalidate ISR cache tags (ToR §8).
 * Queued with 3 retries and backoff; a no-op when the webhook is unconfigured.
 */
class SendRevalidationRequest implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  list<string>  $tags
     */
    public function __construct(public array $tags) {}

    /**
     * Backoff (seconds) between the 3 attempts.
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(): void
    {
        $url = config('khf.revalidate.frontend_url');
        $secret = config('khf.revalidate.secret');

        if (blank($url) || blank($secret)) {
            Log::warning('Revalidation skipped: FRONTEND_URL or REVALIDATE_SECRET is not configured.', [
                'tags' => $this->tags,
            ]);

            return;
        }

        $endpoint = rtrim((string) $url, '/').config('khf.revalidate.path');

        $response = Http::withHeaders(['x-revalidate-secret' => $secret])
            ->acceptJson()
            ->post($endpoint, ['tags' => $this->tags, 'paths' => []]);

        if ($response->failed()) {
            Log::warning('Revalidation webhook failed.', [
                'status' => $response->status(),
                'tags' => $this->tags,
            ]);

            // Non-2xx: throw so the queue retries with backoff.
            $response->throw();
        }

        Log::info('Revalidation webhook sent.', ['tags' => $this->tags]);
    }
}
