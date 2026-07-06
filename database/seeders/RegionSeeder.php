<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Regions referenced by the frontend mock (khf-front lib/data.ts news items).
     *
     * NOTE: placeholder translations — tj/ru share the same source string and en
     * falls back to tj until editors provide real translations. The map-region
     * collection (ToR §6.6) with operational fields is a separate M3 model.
     *
     * @var list<string>
     */
    private const REGIONS = [
        'Хатлон',
        'Душанбе',
        'ВМКБ',
        'НТҶ',
    ];

    public function run(): void
    {
        $sort = 0;

        foreach (self::REGIONS as $name) {
            $sort++;

            if (Region::query()->where('name->tj', $name)->exists()) {
                continue;
            }

            Region::create([
                'name' => ['tj' => $name, 'ru' => $name],
                'sort' => $sort,
                'active' => true,
            ]);
        }
    }
}
