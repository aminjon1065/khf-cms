<?php

namespace Database\Seeders;

use App\Enums\CategoryColor;
use App\Models\NewsCategory;
use Illuminate\Database\Seeder;

class NewsCategorySeeder extends Seeder
{
    /**
     * News categories used by the frontend mock (khf-front lib/data.ts).
     *
     * NOTE: the mock only ships Russian labels, so tj/ru are seeded with the
     * same Russian source string as a placeholder and en is left to fall back
     * to tj. Real tj/en translations are entered by editors (and refined by the
     * dedicated "seeders = frontend mock data" task later in M2).
     *
     * @var list<array{string, CategoryColor}>
     */
    private const CATEGORIES = [
        ['СПАСЕНИЕ', CategoryColor::Alert],
        ['СОТРУДНИЧЕСТВО', CategoryColor::Brand],
        ['ВМКБ', CategoryColor::Success],
        ['ПРОФИЛАКТИКА', CategoryColor::Warn],
        ['ГИДРОМЕТ', CategoryColor::Brand],
        ['ПОЖАРНАЯ БЕЗОПАСНОСТЬ', CategoryColor::Alert],
        ['ОБУЧЕНИЕ', CategoryColor::Success],
        ['ТЕХНИКА', CategoryColor::Brand],
        ['СЕЛЬ', CategoryColor::Warn],
        ['МЕЖДУНАРОДНОЕ', CategoryColor::Brand],
    ];

    public function run(): void
    {
        $sort = 0;

        foreach (self::CATEGORIES as [$label, $color]) {
            $sort++;

            if (NewsCategory::query()->where('label->tj', $label)->exists()) {
                continue;
            }

            NewsCategory::create([
                'label' => ['tj' => $label, 'ru' => $label],
                'color' => $color,
                'sort' => $sort,
                'active' => true,
            ]);
        }
    }
}
