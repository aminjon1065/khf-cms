<?php

namespace App\Filament\Resources\ForumCategories\Schemas;

use App\Filament\Support\LocaleTabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ForumCategoryForm
{
    /**
     * lucide-react icons the frontend forum page recognises.
     *
     * @var array<string, string>
     */
    public const ICONS = [
        'MessagesSquare' => 'MessagesSquare',
        'ShieldAlert' => 'ShieldAlert',
        'HeartHandshake' => 'HeartHandshake',
        'HelpCircle' => 'HelpCircle',
    ];

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
                    ->helperText('Латиница, напр. general, alerts.'),
                LocaleTabs::text('title', 'Название'),
                LocaleTabs::textarea('description', 'Описание'),
                TextInput::make('topics')
                    ->label('Тем (счётчик)')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                TextInput::make('posts')
                    ->label('Сообщений (счётчик)')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                Select::make('icon')
                    ->label('Иконка (lucide-react)')
                    ->options(self::ICONS)
                    ->default('MessagesSquare')
                    ->required(),
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
