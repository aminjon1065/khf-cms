<?php

namespace App\Filament\Pages;

use App\Services\ApiTokenService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageApiToken extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'API-токен';

    protected static ?string $title = 'API-токен фронта';

    protected static string|UnitEnum|null $navigationGroup = 'Администрирование';

    protected static ?int $navigationSort = 120;

    protected static ?string $slug = 'api-token';

    public ?string $generatedToken = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Сгенерировать новый токен')
                ->requiresConfirmation()
                ->modalDescription('Предыдущий токен будет отозван. Скопируйте новый токен в API_TOKEN на фронте.')
                ->action(function (ApiTokenService $apiTokenService): void {
                    $this->generatedToken = $apiTokenService->generateFrontendToken();
                }),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Text::make(fn (ApiTokenService $apiTokenService): string => $apiTokenService->hasFrontendToken()
                    ? 'Токен активен. При генерации нового предыдущий будет отозван.'
                    : 'Токен ещё не создан.'),
                Text::make(fn (): string => filled($this->generatedToken)
                    ? "Новый токен (показывается один раз): {$this->generatedToken}"
                    : '')
                    ->copyable(fn (): bool => filled($this->generatedToken))
                    ->visible(fn (): bool => filled($this->generatedToken)),
            ]);
    }
}
