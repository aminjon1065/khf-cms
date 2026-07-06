<?php

namespace App\Filament\Resources\News\RelationManagers;

use App\Enums\NewsStatus;
use App\Models\NewsRevision;
use App\Services\NewsRevisionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'Ревизии';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Автор')
                    ->placeholder('—'),
                TextColumn::make('title')
                    ->label('Заголовок')
                    ->state(fn (NewsRevision $record): string => (string) data_get($record->data, 'title.tj'))
                    ->limit(40),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->state(fn (NewsRevision $record): ?NewsStatus => NewsStatus::tryFrom(data_get($record->data, 'status', ''))),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                Action::make('view')
                    ->label('Просмотр')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Версия записи')
                    ->modalSubmitAction(false)
                    ->modalContent(fn (NewsRevision $record) => view('filament.news-revision-view', [
                        'data' => $record->data,
                    ])),
                Action::make('rollback')
                    ->label('Откатить')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->requiresConfirmation()
                    ->modalDescription('Восстановить запись из этой версии? Текущее состояние будет сохранено как новая версия.')
                    ->action(function (NewsRevision $record): void {
                        app(NewsRevisionService::class)->rollback($record);

                        Notification::make()
                            ->title('Запись восстановлена из выбранной версии')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
