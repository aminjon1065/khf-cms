<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use App\Enums\SubmissionStatus;
use App\Models\Subscription;
use App\Support\CsvExporter;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('Номер')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('created_at')
                    ->label('Оформлена')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('channel')
                    ->label('Канал')
                    ->badge(),
                TextColumn::make('region')
                    ->label('Регион'),
                TextColumn::make('categories')
                    ->label('Категории')
                    ->badge(),
                TextColumn::make('contact')
                    ->label('Контакт')
                    ->searchable()
                    ->copyable(),
                SelectColumn::make('status')
                    ->label('Статус')
                    ->options(SubmissionStatus::options())
                    ->selectablePlaceholder(false),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(SubmissionStatus::options()),
                SelectFilter::make('channel')
                    ->label('Канал')
                    ->options([
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'telegram' => 'Telegram',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::exportAction(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function exportAction(): BulkAction
    {
        return BulkAction::make('exportCsv')
            ->label('Экспорт в CSV')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records): StreamedResponse => CsvExporter::streamDownload(
                'subscriptions.csv',
                ['Номер', 'Оформлена', 'Канал', 'Регион', 'Категории', 'Контакт', 'Статус'],
                $records->map(fn (Subscription $record): array => [
                    $record->reference,
                    $record->created_at?->format('d.m.Y H:i'),
                    $record->channel,
                    $record->region,
                    implode(', ', $record->categories ?? []),
                    $record->contact,
                    $record->status->getLabel(),
                ])->all(),
            ));
    }
}
