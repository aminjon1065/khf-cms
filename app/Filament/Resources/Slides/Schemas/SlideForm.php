<?php

namespace App\Filament\Resources\Slides\Schemas;

use App\Filament\Support\LocaleTabs;
use App\Models\News;
use App\Models\Slide;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Содержание')
                    ->schema([
                        LocaleTabs::text('title', 'Заголовок'),
                        LocaleTabs::text('category', 'Категория (бейдж)'),
                        LocaleTabs::make('source', 'Источник', fn (string $statePath, string $locale, bool $required): TextInput => TextInput::make($statePath)
                            ->label('Источник')
                            ->maxLength(255), requiredDefault: false),
                        SpatieMediaLibraryFileUpload::make('image')
                            ->label('Изображение')
                            ->collection(Slide::IMAGE_COLLECTION)
                            ->image()
                            ->imageEditor()
                            ->maxSize(10240)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                    ]),
                Section::make('Параметры')
                    ->columns(2)
                    ->schema([
                        TextInput::make('date')
                            ->label('Дата')
                            ->placeholder('дд.мм.гггг')
                            ->helperText('Формат: ДД.ММ.ГГГГ')
                            ->rule('date_format:d.m.Y')
                            ->maxLength(10),
                        Select::make('news_id')
                            ->label('Связанная новость')
                            ->helperText('Опционально — фронт получит слаг для ссылки.')
                            ->options(fn (): array => News::query()
                                ->orderByDesc('published_at')
                                ->limit(100)
                                ->get()
                                ->pluck('title', 'id')
                                ->all())
                            ->searchable()
                            ->native(false),
                        Toggle::make('active')
                            ->label('Активен')
                            ->default(true),
                    ]),
            ]);
    }
}
