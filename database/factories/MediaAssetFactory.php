<?php

namespace Database\Factories;

use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

/**
 * @extends Factory<MediaAsset>
 *
 * The `withImage()` / `withDocument()` states attach a tiny fake file. Tests that
 * use them should Storage::fake('public') and Queue::fake() first so medialibrary
 * conversions do not run inline (see the media-tests-queue-fake note).
 */
class MediaAssetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'alt' => fake()->sentence(3),
        ];
    }

    public function withImage(): static
    {
        return $this->afterCreating(function (MediaAsset $asset): void {
            $asset->addMedia(UploadedFile::fake()->image('image.png', 60, 40))
                ->toMediaCollection(MediaAsset::COLLECTION);
        });
    }

    public function withDocument(): static
    {
        return $this->afterCreating(function (MediaAsset $asset): void {
            // Real %PDF bytes so medialibrary's content-based MIME check accepts it.
            $pdf = "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF";

            $asset->addMediaFromString($pdf)
                ->usingFileName('document.pdf')
                ->toMediaCollection(MediaAsset::COLLECTION);
        });
    }
}
