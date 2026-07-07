<?php

namespace Database\Seeders;

use App\Models\ForumCategory;
use App\Models\ForumStat;
use App\Models\ForumTopic;
use Illuminate\Database\Seeder;

/**
 * Reproduces the frontend "Форум" mock (khf-front lib/content/forum.ts:
 * FORUM_CATEGORIES, FORUM_TOPICS, FORUM_STATS). tj/ru share the source string;
 * en falls back to tj. Idempotent (categories/topics by slug, stats singleton).
 */
class ForumSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategories();
        $this->seedTopics();
        $this->seedStats();
    }

    private function seedCategories(): void
    {
        $categories = [
            ['slug' => 'general', 'title' => 'Умумӣ', 'description' => 'Общие вопросы безопасности и работы Комитета.', 'topics' => 124, 'posts' => 980, 'icon' => 'MessagesSquare'],
            ['slug' => 'alerts', 'title' => 'Огоҳӣ ва пешгирӣ', 'description' => 'Раннее оповещение, прогнозы, обсуждение рисков в регионах.', 'topics' => 86, 'posts' => 612, 'icon' => 'ShieldAlert'],
            ['slug' => 'help', 'title' => 'Кӯмаки тарафайн', 'description' => 'Взаимопомощь жителей при чрезвычайных ситуациях.', 'topics' => 57, 'posts' => 433, 'icon' => 'HeartHandshake'],
            ['slug' => 'qa', 'title' => 'Саволу ҷавоб', 'description' => 'Вопросы специалистам Комитета и ответы на них.', 'topics' => 203, 'posts' => 1487, 'icon' => 'HelpCircle'],
        ];

        $sort = 0;

        foreach ($categories as $category) {
            $sort++;

            if (ForumCategory::query()->where('slug', $category['slug'])->exists()) {
                continue;
            }

            ForumCategory::create([
                'slug' => $category['slug'],
                'title' => $this->bilingual($category['title']),
                'description' => $this->bilingual($category['description']),
                'topics' => $category['topics'],
                'posts' => $category['posts'],
                'icon' => $category['icon'],
                'sort' => $sort,
                'active' => true,
            ]);
        }
    }

    private function seedTopics(): void
    {
        $topics = [
            ['slug' => 't1', 'title' => 'Правила поведения при сходе селя — собираем памятку', 'category' => 'alerts', 'author' => 'Дилшод_77', 'replies' => 42, 'views' => 1820, 'activity' => '2 соат пеш', 'pinned' => true],
            ['slug' => 't2', 'title' => 'Как подписаться на оповещения по Хатлонской области?', 'category' => 'qa', 'author' => 'Гулнора', 'replies' => 11, 'views' => 540, 'activity' => '5 соат пеш', 'pinned' => false],
            ['slug' => 't3', 'title' => 'Куда сообщать о трещинах на склоне у дороги?', 'category' => 'help', 'author' => 'Фаррух', 'replies' => 8, 'views' => 312, 'activity' => 'имрӯз, 09:14', 'pinned' => false],
            ['slug' => 't4', 'title' => 'Сейсмостойкость домов — что важно знать жителям', 'category' => 'general', 'author' => 'инженер_Б', 'replies' => 27, 'views' => 1190, 'activity' => 'дирӯз', 'pinned' => false],
            ['slug' => 't5', 'title' => 'Готовимся к паводковому сезону: чек-лист для семьи', 'category' => 'alerts', 'author' => 'Мадина', 'replies' => 19, 'views' => 760, 'activity' => 'дирӯз', 'pinned' => false],
            ['slug' => 't6', 'title' => 'Где пройти курсы первой помощи в Душанбе?', 'category' => 'qa', 'author' => 'Шаҳзод', 'replies' => 14, 'views' => 489, 'activity' => '2 рӯз пеш', 'pinned' => false],
        ];

        $sort = 0;

        foreach ($topics as $topic) {
            $sort++;

            if (ForumTopic::query()->where('slug', $topic['slug'])->exists()) {
                continue;
            }

            ForumTopic::create([
                'slug' => $topic['slug'],
                'title' => $this->bilingual($topic['title']),
                'category' => $topic['category'],
                'author' => $topic['author'],
                'replies' => $topic['replies'],
                'views' => $topic['views'],
                'last_activity' => $this->bilingual($topic['activity']),
                'pinned' => $topic['pinned'],
                'sort' => $sort,
                'active' => true,
            ]);
        }
    }

    private function seedStats(): void
    {
        $stat = ForumStat::current();

        if (blank($stat->members)) {
            $stat->fill([
                'members' => '8 420',
                'topics' => '470',
                'posts' => '3 512',
                'online' => '63',
            ]);
            $stat->save();
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
