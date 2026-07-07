<?php

namespace App\Filament\Pages;

use App\Models\ForumStat;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Singleton editor for the forum's global community stats (docs/API-CONTRACT.md
 * §GET /forum). All four values are display strings.
 *
 * @property-read Schema $form
 */
class ManageForumStats extends Page
{
    protected string $view = 'filament.pages.manage-forum-stats';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Форум: статистика';

    protected static ?string $title = 'Форум: статистика';

    protected static string|UnitEnum|null $navigationGroup = 'Форум';

    protected static ?int $navigationSort = 52;

    protected static ?string $slug = 'forum-stats';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function mount(): void
    {
        $stats = ForumStat::current();

        $this->form->fill([
            'members' => $stats->members,
            'topics' => $stats->topics,
            'posts' => $stats->posts,
            'online' => $stats->online,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Статистика форума')
                    ->description('Отображаемые счётчики сообщества. Все значения — строки, напр. «8 420».')
                    ->columns(2)
                    ->schema([
                        TextInput::make('members')
                            ->label('Участников')
                            ->maxLength(50),
                        TextInput::make('topics')
                            ->label('Тем')
                            ->maxLength(50),
                        TextInput::make('posts')
                            ->label('Сообщений')
                            ->maxLength(50),
                        TextInput::make('online')
                            ->label('Онлайн')
                            ->maxLength(50),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить')
                ->keyBindings(['mod+s'])
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $record = ForumStat::current();
        $record->fill($this->form->getState());
        $record->save();

        Notification::make()
            ->title('Сохранено')
            ->success()
            ->send();
    }
}
