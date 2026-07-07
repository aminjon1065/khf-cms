<?php

namespace App\Filament\Support\MediaPicker;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Media picker restricted to image assets (news cover / gallery). The
 * modifyQueryUsing scope composes on top of the relationship query the
 * ModalTableSelect livewire component sets.
 */
class ImageAssetPickerTable
{
    public static function configure(Table $table): Table
    {
        return MediaAssetPickerTable::base($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereHas(
                'media',
                fn (Builder $q): Builder => $q->where('mime_type', 'like', 'image/%'),
            ));
    }
}
