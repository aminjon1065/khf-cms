<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Enums\SubmissionStatus;
use App\Models\ContactMessage;
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

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Получено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Эл. почта')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('subject')
                    ->label('Тема')
                    ->limit(40)
                    ->tooltip(fn (ContactMessage $record): string => $record->subject),
                TextColumn::make('message')
                    ->label('Сообщение')
                    ->limit(60)
                    ->wrap()
                    ->tooltip(fn (ContactMessage $record): string => $record->message)
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
                'contact-messages.csv',
                ['Получено', 'Имя', 'Эл. почта', 'Тема', 'Сообщение', 'Статус'],
                $records->map(fn (ContactMessage $record): array => [
                    $record->created_at?->format('d.m.Y H:i'),
                    $record->name,
                    $record->email,
                    $record->subject,
                    $record->message,
                    $record->status->getLabel(),
                ])->all(),
            ));
    }
}
