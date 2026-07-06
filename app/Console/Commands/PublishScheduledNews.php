<?php

namespace App\Console\Commands;

use App\Enums\NewsStatus;
use App\Models\News;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('news:publish-scheduled')]
#[Description('Publish scheduled news whose publication time has arrived')]
class PublishScheduledNews extends Command
{
    public function handle(): int
    {
        $due = News::query()
            ->where('status', NewsStatus::Scheduled)
            ->where('published_at', '<=', now())
            ->get();

        // Update each model individually so model events fire (revisions and,
        // later, the frontend revalidation webhook).
        $due->each(fn (News $news) => $news->update(['status' => NewsStatus::Published]));

        $this->info("Опубликовано записей: {$due->count()}");

        return self::SUCCESS;
    }
}
