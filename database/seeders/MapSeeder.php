<?php

namespace Database\Seeders;

use App\Enums\RiskLevel;
use App\Models\MapRegion;
use App\Models\MapSetting;
use Illuminate\Database\Seeder;

/**
 * Reproduces the frontend "Харита" mock (khf-front lib/content/regions.ts:
 * REGIONS + MAP_STATS). tj/ru share the source string; en falls back to tj.
 * Idempotent (regions by slug, monitoring on the singleton row).
 */
class MapSeeder extends Seeder
{
    /**
     * @var list<array{slug: string, name: string, center: string, risk: RiskLevel, incidents: int, stations: int, note: string}>
     */
    private const REGIONS = [
        ['slug' => 'dushanbe', 'name' => 'ш. Душанбе', 'center' => 'Душанбе', 'risk' => RiskLevel::Low, 'incidents' => 1, 'stations' => 6, 'note' => 'Обстановка стабильная, подразделения в режиме повседневной готовности.'],
        ['slug' => 'khatlon', 'name' => 'вилояти Хатлон', 'center' => 'Бохтар', 'risk' => RiskLevel::High, 'incidents' => 4, 'stations' => 14, 'note' => 'Повышенный риск паводков и селей у горных рек, силы в готовности.'],
        ['slug' => 'sugd', 'name' => 'вилояти Суғд', 'center' => 'Хуҷанд', 'risk' => RiskLevel::Medium, 'incidents' => 2, 'stations' => 12, 'note' => 'Местами возможны подъёмы воды в реках, ведётся мониторинг.'],
        ['slug' => 'gbao', 'name' => 'ВМКБ', 'center' => 'Хоруғ', 'risk' => RiskLevel::Medium, 'incidents' => 2, 'stations' => 9, 'note' => 'Риск схода лавин и камнепадов на горных дорогах.'],
        ['slug' => 'ntj', 'name' => 'ноҳияҳои тобеи ҷумҳурӣ', 'center' => 'Ваҳдат', 'risk' => RiskLevel::High, 'incidents' => 3, 'stations' => 11, 'note' => 'Селевая опасность на трассе Душанбе–Хорог, движение под контролем.'],
    ];

    public function run(): void
    {
        $sort = 0;

        foreach (self::REGIONS as $region) {
            $sort++;

            if (MapRegion::query()->where('slug', $region['slug'])->exists()) {
                continue;
            }

            MapRegion::create([
                'slug' => $region['slug'],
                'name' => $this->bilingual($region['name']),
                'center' => $this->bilingual($region['center']),
                'note' => $this->bilingual($region['note']),
                'risk' => $region['risk'],
                'active_incidents' => $region['incidents'],
                'stations' => $region['stations'],
                'sort' => $sort,
                'active' => true,
            ]);
        }

        $setting = MapSetting::current();

        if (blank($setting->monitoring)) {
            $setting->monitoring = '320+';
            $setting->save();
        }
    }

    /**
     * @return array<string, string>
     */
    private function bilingual(string $value): array
    {
        return ['tj' => $value, 'ru' => $value];
    }
}
