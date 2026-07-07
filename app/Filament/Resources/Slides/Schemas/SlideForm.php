<?php

namespace App\Filament\Resources\Slides\Schemas;

use App\Filament\Support\LocaleTabs;
use App\Filament\Support\MediaPicker\ImageAssetPickerTable;
use App\Filament\Support\MediaPicker\UploadAssetAction;
use App\Models\News;
use App\Rules\MediaAssetKind;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Section::make('Содержание')
                    ->columnSpan(['default' => 1, 'lg' => 2])
                    ->schema([
                        LocaleTabs::text('title', 'Заголовок'),
                        LocaleTabs::text('category', 'Категория (бейдж)'),
                        LocaleTabs::make('source', 'Источник', fn (string $statePath, string $locale, bool $required): TextInput => TextInput::make($statePath)
                            ->label('Источник')
                            ->maxLength(255), requiredDefault: false),
                        ModalTableSelect::make('image_media_asset_id')
                            ->label('Изображение (из медиатеки)')
                            ->relationship('imageAsset', 'name')
                            ->tableConfiguration(ImageAssetPickerTable::class)
                            ->rule(MediaAssetKind::image())
                            ->hintAction(UploadAssetAction::make(image: true))
                            ->helperText('Выберите изображение из медиатеки или загрузите новое.'),
                    ]),
                Section::make('Параметры')
                    ->columnSpan(['default' => 1, 'lg' => 1])
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
