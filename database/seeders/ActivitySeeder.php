<?php

namespace Database\Seeders;

use App\Enums\ProgramStatus;
use App\Models\Direction;
use App\Models\Program;
use Illuminate\Database\Seeder;

/**
 * Reproduces the frontend "Фаъолият" mock (khf-front lib/content/activity.ts).
 * Placeholder translations: tj/ru share the source string; en falls back to tj.
 * Idempotent (directions by slug, programmes by title->tj).
 */
class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDirections();
        $this->seedPrograms();
    }

    private function seedDirections(): void
    {
        $directions = [
            ['slug' => 'rescue', 'icon' => 'LifeBuoy', 'title' => 'Корҳои ҷустуҷӯию наҷотдиҳӣ', 'description' => 'Поиск и спасение людей при стихийных бедствиях, авариях и происшествиях в горной и водной среде.', 'value' => '12 480', 'label' => 'спасено за год'],
            ['slug' => 'prevention', 'icon' => 'ShieldAlert', 'title' => 'Пешгирии ҳолатҳои фавқулодда', 'description' => 'Мониторинг рисков, прогнозирование и предупреждение чрезвычайных ситуаций природного характера.', 'value' => '320+', 'label' => 'пунктов мониторинга'],
            ['slug' => 'civil-defense', 'icon' => 'Users', 'title' => 'Мудофиаи гражданӣ', 'description' => 'Защита населения и территорий, организация эвакуации и пунктов временного размещения.', 'value' => '1 200', 'label' => 'учений в год'],
            ['slug' => 'fire', 'icon' => 'Flame', 'title' => 'Бехатарии оташнишонӣ', 'description' => 'Тушение пожаров, надзор за соблюдением требований пожарной безопасности на объектах.', 'value' => '8 мин', 'label' => 'среднее время реакции'],
            ['slug' => 'hydromet', 'icon' => 'CloudRain', 'title' => 'Гидрометеорология', 'description' => 'Наблюдение за погодой, состоянием рек, ледников и горных озёр, раннее оповещение.', 'value' => '24/7', 'label' => 'режим наблюдения'],
            ['slug' => 'training', 'icon' => 'GraduationCap', 'title' => 'Омӯзиши аҳолӣ', 'description' => 'Обучение населения и специалистов правилам поведения и действиям при чрезвычайных ситуациях.', 'value' => '45 000', 'label' => 'обучено за год'],
        ];

        $sort = 0;

        foreach ($directions as $direction) {
            $sort++;

            if (Direction::query()->where('slug', $direction['slug'])->exists()) {
                continue;
            }

            Direction::create([
                'slug' => $direction['slug'],
                'icon' => $direction['icon'],
                'title' => $this->bilingual($direction['title']),
                'description' => $this->bilingual($direction['description']),
                'stat_value' => $direction['value'],
                'stat_label' => $this->bilingual($direction['label']),
                'sort' => $sort,
                'active' => true,
            ]);
        }
    }

    private function seedPrograms(): void
    {
        $programs = [
            ['title' => 'Стратегияи миллии рушд то соли 2030', 'period' => '2016–2030', 'status' => 'Амалкунанда', 'description' => 'Снижение риска бедствий и повышение устойчивости общин к стихийным явлениям.'],
            ['title' => 'Барномаи давлатии огоҳии барвақт', 'period' => '2023–2027', 'status' => 'Амалкунанда', 'description' => 'Развитие национальной системы раннего оповещения о чрезвычайных ситуациях.'],
            ['title' => 'Мактаби бехатар', 'period' => '2024–2028', 'status' => 'Амалкунанда', 'description' => 'Обучение школьников и педагогов действиям при землетрясениях и пожарах.'],
            ['title' => 'Навсозии техникаи наҷотдиҳӣ', 'period' => '2025–2026', 'status' => 'Ба нақша', 'description' => 'Модернизация парка аварийно-спасательной техники и средств связи.'],
        ];

        $sort = 0;

        foreach ($programs as $program) {
            $sort++;

            if (Program::query()->where('title->tj', $program['title'])->exists()) {
                continue;
            }

            Program::create([
                'title' => $this->bilingual($program['title']),
                'period' => $program['period'],
                'status' => ProgramStatus::fromTjLabel($program['status']) ?? ProgramStatus::Active,
                'description' => $this->bilingual($program['description']),
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
