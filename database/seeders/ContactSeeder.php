<?php

namespace Database\Seeders;

use App\Models\ContactOffice;
use App\Models\Hotline;
use Illuminate\Database\Seeder;

/**
 * Reproduces the frontend "Тамос" mock (khf-front lib/content/contacts.ts:
 * HOTLINES, HEAD_OFFICE, OFFICES). tj/ru share the source string until editors
 * provide real per-locale text; en falls back to tj. Idempotent (hotlines by
 * number, offices by region->tj).
 */
class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHotlines();
        $this->seedOffices();
    }

    private function seedHotlines(): void
    {
        $hotlines = [
            ['number' => '112', 'label' => 'Хадамоти ягонаи наҷот', 'note' => 'Круглосуточно, бесплатно со всех операторов', 'primary' => true],
            ['number' => '(992 37) 221-12-12', 'label' => 'Дежурно-диспетчерская служба', 'note' => 'Оперативный дежурный Комитета', 'primary' => false],
            ['number' => '(992 37) 223-13-11', 'label' => 'Хатти боварӣ', 'note' => 'Телефон доверия, приём обращений граждан', 'primary' => false],
            ['number' => '(992 37) 224-18-00', 'label' => 'Пресс-центр', 'note' => 'Для СМИ и запросов о деятельности Комитета', 'primary' => false],
        ];

        $sort = 0;

        foreach ($hotlines as $hotline) {
            $sort++;

            if (Hotline::query()->where('number', $hotline['number'])->exists()) {
                continue;
            }

            Hotline::create([
                'number' => $hotline['number'],
                'label' => $this->bilingual($hotline['label']),
                'note' => $this->bilingual($hotline['note']),
                'is_primary' => $hotline['primary'],
                'sort' => $sort,
                'active' => true,
            ]);
        }
    }

    private function seedOffices(): void
    {
        $head = [
            'region' => 'Дастгоҳи марказӣ',
            'address' => '734013, ш. Душанбе, кӯчаи Лоҳутӣ 26',
            'phone' => '(992 37) 223-13-11',
            'email' => 'info@khf.tj',
            'hours' => 'Душанбе–Ҷумъа, 8:00–17:00',
        ];

        $offices = [
            ['region' => 'вилояти Хатлон', 'address' => 'ш. Бохтар, кӯчаи Истиқлол 14', 'phone' => '(992 3222) 2-22-12', 'email' => 'khatlon@khf.tj', 'hours' => 'Душанбе–Ҷумъа, 8:00–17:00'],
            ['region' => 'вилояти Суғд', 'address' => 'ш. Хуҷанд, кӯчаи Ленин 180', 'phone' => '(992 3422) 6-33-12', 'email' => 'sugd@khf.tj', 'hours' => 'Душанбе–Ҷумъа, 8:00–17:00'],
            ['region' => 'ВМКБ', 'address' => 'ш. Хоруғ, кӯчаи Ленин 1', 'phone' => '(992 3522) 2-12-12', 'email' => 'gbao@khf.tj', 'hours' => 'Душанбе–Ҷумъа, 8:00–17:00'],
            ['region' => 'ноҳияҳои тобеи ҷумҳурӣ', 'address' => 'ш. Ваҳдат, кӯчаи Марказӣ 5', 'phone' => '(992 37) 224-15-15', 'email' => 'ntj@khf.tj', 'hours' => 'Душанбе–Ҷумъа, 8:00–17:00'],
        ];

        $sort = 0;
        $this->upsertOffice($head, ++$sort, isHead: true);

        foreach ($offices as $office) {
            $this->upsertOffice($office, ++$sort, isHead: false);
        }
    }

    /**
     * @param  array{region: string, address: string, phone: string, email: string, hours: string}  $office
     */
    private function upsertOffice(array $office, int $sort, bool $isHead): void
    {
        if (ContactOffice::query()->where('region->tj', $office['region'])->exists()) {
            return;
        }

        ContactOffice::create([
            'region' => $this->bilingual($office['region']),
            'address' => $this->bilingual($office['address']),
            'hours' => $this->bilingual($office['hours']),
            'phone' => $office['phone'],
            'email' => $office['email'],
            'is_head' => $isHead,
            'sort' => $sort,
            'active' => true,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function bilingual(string $value): array
    {
        return ['tj' => $value, 'ru' => $value];
    }
}
