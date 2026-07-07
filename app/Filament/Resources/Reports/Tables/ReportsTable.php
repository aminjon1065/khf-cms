<?php

namespace App\Filament\Resources\Reports\Tables;

use App\Enums\SubmissionStatus;
use App\Models\Report;
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

class ReportsTable
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
                    ->label('Получено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge(),
                TextColumn::make('region')
                    ->label('Регион'),
                TextColumn::make('location')
                    ->label('Место')
                    ->limit(40)
                    ->tooltip(fn (Report $record): string => $record->location),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Описание')
                    ->limit(60)
                    ->wrap()
                    ->tooltip(fn (Report $record): string => $record->description)
                    ->toggleable(isToggledHiddenByDefault: true),
                SelectColumn::make('status')
                    ->label('Статус')
                    ->options(SubmissionStatus::options())
                    ->selectablePlaceholder(false),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(SubmissionStatus::options()),
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
                'reports.csv',
                ['Номер', 'Получено', 'Тип', 'Регион', 'Место', 'Телефон', 'Описание', 'Статус'],
                $records->map(fn (Report $record): array => [
                    $record->reference,
                    $record->created_at?->format('d.m.Y H:i'),
                    $record->type,
                    $record->region,
                    $record->location,
                    $record->phone,
                    $record->description,
                    $record->status->getLabel(),
                ])->all(),
            ));
    }
}
