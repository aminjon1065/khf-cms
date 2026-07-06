<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Leader;
use App\Models\RegionalOffice;
use Illuminate\Database\Seeder;

/**
 * Reproduces the frontend "Сохтор" mock (khf-front lib/content/structure.ts).
 * Placeholder translations: tj/ru share the source string until editors provide
 * real per-locale text; en falls back to tj. Idempotent per leading field.
 */
class StructureSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLeadership();
        $this->seedDepartments();
        $this->seedOffices();
    }

    private function seedLeadership(): void
    {
        $leaders = [
            ['name' => 'Рустам Назарзода', 'role' => 'Раиси Кумита', 'rank' => 'генерал-лейтенант', 'bio' => 'Осуществляет общее руководство деятельностью Комитета, координацию сил и средств при ликвидации чрезвычайных ситуаций.'],
            ['name' => 'Далер Каримзода', 'role' => 'Муовини якуми раис', 'rank' => 'генерал-майор', 'bio' => 'Курирует вопросы оперативного реагирования, поисково-спасательных работ и готовности подразделений.'],
            ['name' => 'Фирӯза Саидова', 'role' => 'Муовини раис', 'rank' => 'полковник', 'bio' => 'Отвечает за гражданскую оборону, обучение населения и международное сотрудничество.'],
            ['name' => 'Шерали Ҳакимзода', 'role' => 'Муовини раис', 'rank' => 'полковник', 'bio' => 'Курирует материально-техническое обеспечение, связь и информационные технологии.'],
        ];

        $sort = 0;

        foreach ($leaders as $leader) {
            $sort++;

            if (Leader::query()->where('name->tj', $leader['name'])->exists()) {
                continue;
            }

            Leader::create([
                'name' => $this->bilingual($leader['name']),
                'role' => $this->bilingual($leader['role']),
                'rank' => $this->bilingual($leader['rank']),
                'bio' => $this->bilingual($leader['bio']),
                'sort' => $sort,
                'active' => true,
            ]);
        }
    }

    private function seedDepartments(): void
    {
        $departments = [
            ['title' => 'Сарраёсати амалиётӣ', 'description' => 'Оперативное управление силами и средствами, дежурно-диспетчерская служба 112.', 'head' => 'управление'],
            ['title' => 'Раёсати корҳои ҷустуҷӯию наҷотдиҳӣ', 'description' => 'Организация и проведение поисково-спасательных и аварийно-спасательных работ.', 'head' => 'управление'],
            ['title' => 'Раёсати мудофиаи гражданӣ', 'description' => 'Планирование и проведение мероприятий гражданской обороны, защита населения.', 'head' => 'управление'],
            ['title' => 'Раёсати пешгирии ҲФ', 'description' => 'Предупреждение чрезвычайных ситуаций, мониторинг и прогнозирование рисков.', 'head' => 'управление'],
            ['title' => 'Хадамоти давлатии оташнишонӣ', 'description' => 'Обеспечение пожарной безопасности и тушение пожаров на территории республики.', 'head' => 'служба'],
            ['title' => 'Маркази таълимӣ', 'description' => 'Подготовка спасателей, обучение населения и специалистов действиям при ЧС.', 'head' => 'центр'],
        ];

        $sort = 0;

        foreach ($departments as $department) {
            $sort++;

            if (Department::query()->where('title->tj', $department['title'])->exists()) {
                continue;
            }

            Department::create([
                'title' => $this->bilingual($department['title']),
                'description' => $this->bilingual($department['description']),
                'head' => $this->bilingual($department['head']),
                'sort' => $sort,
                'active' => true,
            ]);
        }
    }

    private function seedOffices(): void
    {
        $offices = [
            ['region' => 'ш. Душанбе', 'head' => 'полковник А. Раҳимов', 'phone' => '(992 37) 221-12-12', 'address' => 'ш. Душанбе, кӯчаи Лоҳутӣ 26'],
            ['region' => 'вилояти Хатлон', 'head' => 'полковник М. Сафаров', 'phone' => '(992 3222) 2-22-12', 'address' => 'ш. Бохтар, кӯчаи Истиқлол 14'],
            ['region' => 'вилояти Суғд', 'head' => 'полковник Б. Юсуфзода', 'phone' => '(992 3422) 6-33-12', 'address' => 'ш. Хуҷанд, кӯчаи Ленин 180'],
            ['region' => 'ВМКБ', 'head' => 'полковник Н. Давлатов', 'phone' => '(992 3522) 2-12-12', 'address' => 'ш. Хоруғ, кӯчаи Ленин 1'],
            ['region' => 'ноҳияҳои тобеи ҷумҳурӣ', 'head' => 'полковник Ҷ. Назаров', 'phone' => '(992 37) 224-15-15', 'address' => 'ш. Ваҳдат, кӯчаи Марказӣ 5'],
        ];

        $sort = 0;

        foreach ($offices as $office) {
            $sort++;

            if (RegionalOffice::query()->where('region->tj', $office['region'])->exists()) {
                continue;
            }

            RegionalOffice::create([
                'region' => $this->bilingual($office['region']),
                'head' => $this->bilingual($office['head']),
                'phone' => $office['phone'],
                'address' => $this->bilingual($office['address']),
                'sort' => $sort,
                'active' => true,
            ]);
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
