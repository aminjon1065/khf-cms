<?php

namespace App\Filament\Resources\ForumTopics\Pages;

use App\Filament\Resources\ForumTopics\ForumTopicResource;
use Filament\Resources\Pages\CreateRecord;

class CreateForumTopic extends CreateRecord
{
    protected static string $resource = ForumTopicResource::class;
}
