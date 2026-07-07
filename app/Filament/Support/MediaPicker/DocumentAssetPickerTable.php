<?php

namespace App\Filament\Support\MediaPicker;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Media picker restricted to document assets (Documents section).
 */
class DocumentAssetPickerTable
{
    public static function configure(Table $table): Table
    {
        return MediaAssetPickerTable::base($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereHas(
                'media',
                fn (Builder $q): Builder => $q->where('mime_type', 'not like', 'image/%'),
            ));
    }
}
