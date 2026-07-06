<?php

namespace Database\Seeders;

use App\Models\HomeSetting;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Home global blocks from the frontend mock (khf-front lib/data.ts:
 * SERVICES, PRESIDENT, SITE_STATS). President text is Tajik in the mock and
 * shown as-is on every locale, so only tj is seeded (ru/en fall back to tj).
 */
class HomeContentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * @var list<array{key: string, title: string, subtitle: string, tel: ?string, route: ?string, primary: bool}>
     */
    private const SERVICES = [
        ['key' => '112', 'title' => '112', 'subtitle' => 'Экстренный вызов', 'tel' => '112', 'route' => null, 'primary' => true],
        ['key' => 'report', 'title' => 'Сообщить о ЧС', 'subtitle' => 'Онлайн-заявка', 'tel' => null, 'route' => 'report', 'primary' => false],
        ['key' => 'howto', 'title' => 'Что делать при ЧС', 'subtitle' => 'Памятки и инструкции', 'tel' => null, 'route' => 'safety', 'primary' => false],
        ['key' => 'subscribe', 'title' => 'Подписка', 'subtitle' => 'Оповещения по региону', 'tel' => null, 'route' => 'subscribe', 'primary' => false],
    ];

    public function run(): void
    {
        $sort = 0;

        foreach (self::SERVICES as $service) {
            $sort++;

            if (Service::query()->where('key', $service['key'])->exists()) {
                continue;
            }

            Service::create([
                'key' => $service['key'],
                'title' => ['tj' => $service['title']],
                'subtitle' => ['tj' => $service['subtitle']],
                'tel' => $service['tel'],
                'route' => $service['route'],
                'primary' => $service['primary'],
                'sort' => $sort,
                'active' => true,
            ]);
        }

        $home = HomeSetting::current();

        if (blank($home->president_name)) {
            $home->fill([
                'president_name' => 'Эмомалӣ Раҳмон',
                'president_role' => ['tj' => 'Президенти Ҷумҳурии Тоҷикистон'],
                'president_quote' => ['tj' => '«Таъмини бехатарии ҳаёти инсон вазифаи муҳимтарини давлат аст».'],
                'president_href' => 'https://president.tj',
                'stats_today' => '1 240',
                'stats_month' => '38 902',
                'stats_rescued' => '12 480',
                'stats_reaction' => '8 мин',
            ])->save();
        }
    }
}
