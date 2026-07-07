<?php

namespace App\Filament\Pages;

use App\Models\MapSetting;
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
 * Singleton editor for the map's global "monitoring" stat (docs/API-CONTRACT.md
 * §GET /regions). The other map stats are computed from the regions.
 *
 * @property-read Schema $form
 */
class ManageMapStats extends Page
{
    protected string $view = 'filament.pages.manage-map-stats';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = 'Карта: статистика';

    protected static ?string $title = 'Карта: статистика';

    protected static string|UnitEnum|null $navigationGroup = 'Глобальные блоки';

    protected static ?int $navigationSort = 30;

    protected static ?string $slug = 'map-stats';

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
        $this->form->fill([
            'monitoring' => MapSetting::current()->monitoring,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Статистика карты')
                    ->description('Регионы, станции и инциденты считаются автоматически. Здесь задаётся только строка «мониторинг».')
                    ->schema([
                        TextInput::make('monitoring')
                            ->label('Пункты мониторинга')
                            ->maxLength(50)
                            ->helperText('Строка, напр. «320+».'),
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
        $record = MapSetting::current();
        $record->fill($this->form->getState());
        $record->save();

        Notification::make()
            ->title('Сохранено')
            ->success()
            ->send();
    }
}
