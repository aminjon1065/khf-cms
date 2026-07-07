<?php

namespace App\Filament\Resources\ForumTopics\Schemas;

use App\Filament\Support\LocaleTabs;
use App\Models\ForumCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ForumTopicForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->label('Идентификатор (slug)')
                    ->required()
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Латиница, напр. t1, sel-pamyatka.'),
                LocaleTabs::text('title', 'Заголовок'),
                Select::make('category')
                    ->label('Раздел')
                    ->options(fn (): array => ForumCategory::query()
                        ->orderBy('sort')
                        ->get()
                        ->mapWithKeys(fn (ForumCategory $category): array => [
                            $category->slug => $category->getTranslation('title', 'tj').' ('.$category->slug.')',
                        ])
                        ->all())
                    ->searchable()
                    ->required(),
                TextInput::make('author')
                    ->label('Автор')
                    ->required()
                    ->maxLength(255),
                TextInput::make('replies')
                    ->label('Ответов')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                TextInput::make('views')
                    ->label('Просмотров')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                LocaleTabs::text('last_activity', 'Последняя активность'),
                Toggle::make('pinned')
                    ->label('Закреплено')
                    ->default(false),
                TextInput::make('sort')
                    ->label('Порядок')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('active')
                    ->label('Активно')
                    ->default(true),
            ]);
    }
}
