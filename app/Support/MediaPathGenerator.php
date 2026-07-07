<?php

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Stores media by upload date: {Y}/{m}/{d}/{media_id}/file.ext, with conversions
 * and responsive images grouped under the same folder. The trailing {media_id}
 * segment is kept on purpose so files that share a name (and their thumb/card/hero
 * conversions) never overwrite each other, while the tree stays browsable by date.
 */
class MediaPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->basePath($media);
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->basePath($media).'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->basePath($media).'responsive-images/';
    }

    private function basePath(Media $media): string
    {
        $date = ($media->created_at ?? now())->format('Y/m/d');

        return "{$date}/{$media->getKey()}/";
    }
}
