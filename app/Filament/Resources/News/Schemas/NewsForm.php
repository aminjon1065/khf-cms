<?php

namespace App\Filament\Resources\News\Schemas;

use App\Enums\NewsStatus;
use App\Filament\Support\LocaleTabs;
use App\Filament\Support\MediaPicker\ImageAssetPickerTable;
use App\Filament\Support\MediaPicker\UploadAssetAction;
use App\Models\NewsCategory;
use App\Models\Region;
use App\Rules\MediaAssetKind;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class NewsForm
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
                        TextInput::make('slug')
                            ->label('Слаг')
                            ->helperText('Оставьте пустым — сгенерируется из заголовка (tj).')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        LocaleTabs::make('excerpt', 'Анонс', fn (string $statePath, string $locale, bool $required): Textarea => Textarea::make($statePath)
                            ->label('Анонс')
                            ->required($required)
                            ->rows(3)
                            ->maxLength(500)),
                        LocaleTabs::make('body', 'Текст', fn (string $statePath, string $locale, bool $required): RichEditor => RichEditor::make($statePath)
                            ->label('Текст')
                            ->required($required)
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('news/body')
                            ->fileAttachmentsVisibility('public')),
                        ModalTableSelect::make('cover_media_asset_id')
                            ->label('Обложка (из медиатеки)')
                            ->relationship('coverAsset', 'name')
                            ->tableConfiguration(ImageAssetPickerTable::class)
                            ->rule(MediaAssetKind::image())
                            ->hintAction(UploadAssetAction::make(image: true))
                            ->helperText('Выберите изображение из медиатеки или загрузите новое.'),
                        ModalTableSelect::make('galleryAssets')
                            ->label('Галерея')
                            ->relationship('galleryAssets', 'name')
                            ->tableConfiguration(ImageAssetPickerTable::class)
                            ->multiple()
                            ->rule(MediaAssetKind::image())
                            ->hintAction(UploadAssetAction::make(image: true))
                            ->helperText('Несколько изображений для слайдера; выбирайте из медиатеки или загружайте новые.'),
                    ]),
                Group::make()
                    ->columnSpan(['default' => 1, 'lg' => 1])
                    ->schema([
                        Section::make('Публикация')
                            ->schema([
                                Select::make('status')
                                    ->label('Статус')
                                    ->options(NewsStatus::class)
                                    ->default(NewsStatus::Draft->value)
                                    ->required()
                                    ->native(false),
                                DateTimePicker::make('published_at')
                                    ->label('Дата публикации')
                                    ->seconds(false),
                                Select::make('category_id')
                                    ->label('Категория')
                                    ->options(fn (): array => NewsCategory::query()->orderBy('sort')->get()->pluck('label', 'id')->all())
                                    ->searchable()
                                    ->native(false)
                                    ->required(),
                                Select::make('region_id')
                                    ->label('Регион')
                                    ->options(fn (): array => Region::query()->orderBy('sort')->get()->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->native(false),
                                TextInput::make('author')
                                    ->label('Автор')
                                    ->default('Пресс-центр КҲФ')
                                    ->maxLength(255),
                            ]),
                        Section::make('SEO')
                            ->collapsed()
                            ->schema([
                                LocaleTabs::make('seo_title', 'SEO заголовок', fn (string $statePath, string $locale, bool $required): TextInput => TextInput::make($statePath)
                                    ->label('SEO заголовок')
                                    ->maxLength(255), requiredDefault: false),
                                LocaleTabs::make('seo_description', 'SEO описание', fn (string $statePath, string $locale, bool $required): Textarea => Textarea::make($statePath)
                                    ->label('SEO описание')
                                    ->rows(2)
                                    ->maxLength(500), requiredDefault: false),
                            ]),
                    ]),
                View::make('filament.forms.draft-autosave')
                    ->columnSpanFull()
                    ->visible(fn (string $operation): bool => $operation === 'edit'),
            ]);
    }
}
