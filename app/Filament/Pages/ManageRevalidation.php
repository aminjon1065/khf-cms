<?php

namespace App\Filament\Pages;

use App\Services\RevalidationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageRevalidation extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Ревалидация';

    protected static ?string $title = 'Ревалидация фронта';

    protected static string|UnitEnum|null $navigationGroup = 'Администрирование';

    protected static ?int $navigationSort = 130;

    protected static ?string $slug = 'revalidation';

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
            Action::make('ping')
                ->label('Проверить соединение')
                ->action(function (RevalidationService $revalidationService): void {
                    $result = $revalidationService->ping();

                    if ($result['ok']) {
                        Notification::make()
                            ->title('Соединение успешно')
                            ->body('Фронт ответил статусом '.$result['status'].'.')
                            ->success()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Ошибка соединения')
                        ->body($result['error'] ?? 'Неизвестная ошибка')
                        ->danger()
                        ->send();
                }),
            Action::make('flush')
                ->label('Сбросить весь кеш')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('Фронту будут отправлены все теги ревалидации — все ISR-страницы пересоберутся.')
                ->action(function (RevalidationService $revalidationService): void {
                    $revalidationService->flushAll();

                    Notification::make()
                        ->title('Сброс кеша поставлен в очередь')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Text::make(fn (): string => 'FRONTEND_URL: '.(filled(config('khf.revalidate.frontend_url'))
                    ? (string) config('khf.revalidate.frontend_url')
                    : 'не задан (см. .env)')),
                Text::make(fn (): string => 'REVALIDATE_SECRET: '.(filled(config('khf.revalidate.secret'))
                    ? 'задан'
                    : 'не задан (см. .env)')),
            ]);
    }
}
