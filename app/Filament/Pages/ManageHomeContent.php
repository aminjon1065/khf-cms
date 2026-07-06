<?php

namespace App\Filament\Pages;

use App\Filament\Support\LocaleTabs;
use App\Models\HomeSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Singleton editor for the home President quote and site stats (ToR §6.9).
 *
 * @property-read Schema $form
 */
class ManageHomeContent extends Page
{
    protected string $view = 'filament.pages.manage-home-content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Главная: президент и статистика';

    protected static ?string $title = 'Главная: президент и статистика';

    protected static string|UnitEnum|null $navigationGroup = 'Глобальные блоки';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'home-content';

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
        $record = HomeSetting::current();

        $this->form->fill([
            'president_name' => $record->president_name,
            'president_role' => $record->getTranslations('president_role'),
            'president_quote' => $record->getTranslations('president_quote'),
            'president_href' => $record->president_href,
            'stats_today' => $record->stats_today,
            'stats_month' => $record->stats_month,
            'stats_rescued' => $record->stats_rescued,
            'stats_reaction' => $record->stats_reaction,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Цитата Президента')
                    ->schema([
                        TextInput::make('president_name')
                            ->label('Имя')
                            ->maxLength(255),
                        LocaleTabs::text('president_role', 'Должность'),
                        LocaleTabs::make('president_quote', 'Цитата', fn (string $statePath, string $locale, bool $required): Textarea => Textarea::make($statePath)
                            ->label('Цитата')
                            ->rows(3)
                            ->maxLength(1000), requiredDefault: false),
                        TextInput::make('president_href')
                            ->label('Ссылка')
                            ->url()
                            ->maxLength(255),
                    ]),
                Section::make('Статистика сайта')
                    ->columns(2)
                    ->schema([
                        TextInput::make('stats_today')->label('Сегодня')->maxLength(50),
                        TextInput::make('stats_month')->label('За месяц')->maxLength(50),
                        TextInput::make('stats_rescued')->label('Спасено')->maxLength(50),
                        TextInput::make('stats_reaction')->label('Время реакции')->maxLength(50),
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
        $record = HomeSetting::current();
        $record->fill($this->form->getState());
        $record->save();

        Notification::make()
            ->title('Сохранено')
            ->success()
            ->send();
    }
}
