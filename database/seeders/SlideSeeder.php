<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\Slide;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SlideSeeder extends Seeder
{
    // Mute events so seeding doesn't fire the revalidation webhook.
    use WithoutModelEvents;

    /**
     * Home slider reproduced from the frontend mock (khf-front lib/data.ts SLIDES).
     *
     * The mock ships Russian text only, so tj/ru share the source string. Each
     * slide links to the news item with the same title (seeded by NewsSeeder),
     * so the API resolves `newsSlug`. Must run after NewsSeeder.
     *
     * @var list<array{category: string, title: string, date: string, source: string}>
     */
    private const SLIDES = [
        [
            'category' => 'Спасение',
            'title' => 'Спасатели вызволили троих граждан из реки Вахш в Хатлонской области',
            'date' => '14.06.2026',
            'source' => 'Пресс-центр КҲФ',
        ],
        [
            'category' => 'Сотрудничество',
            'title' => 'Международное взаимодействие Комитета продолжает расширяться',
            'date' => '13.06.2026',
            'source' => 'Пресс-центр КҲФ',
        ],
        [
            'category' => 'ВМКБ',
            'title' => 'Проверка профессиональной подготовки личного состава',
            'date' => '12.06.2026',
            'source' => 'Пресс-центр КҲФ',
        ],
    ];

    public function run(): void
    {
        $sort = 0;

        foreach (self::SLIDES as $slide) {
            $sort++;

            if (Slide::query()->where('title->tj', $slide['title'])->exists()) {
                continue;
            }

            $news = News::query()->where('title->tj', $slide['title'])->first();

            Slide::create([
                'title' => ['tj' => $slide['title'], 'ru' => $slide['title']],
                'category' => ['tj' => $slide['category'], 'ru' => $slide['category']],
                'date' => $slide['date'],
                'source' => ['tj' => $slide['source'], 'ru' => $slide['source']],
                'news_id' => $news?->id,
                'sort' => $sort,
                'active' => true,
            ]);
        }
    }
}
