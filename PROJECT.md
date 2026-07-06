# khf-cms — инструкции проекта для ИИ-агента

Backend/CMS официального портала КЧС и ГО Республики Таджикистан.
**Laravel 13 · Filament 5 (панель: `/admin`) · MySQL 8 · PHP 8.3+ (рантайм 8.5).**
Публичный read-only JSON API для готового фронта на Next.js 14 (репозиторий `khf-front`, ISR).

Также действуют Laravel Boost guidelines (в CLAUDE.md/AGENTS.md) — при конфликте по стилю кода приоритет у Boost, по предметной области — у этого файла и ToR.

## Источники истины (по убыванию приоритета)

1. `docs/API-CONTRACT.md` — точный контракт API. **Формы ответов, имена ключей (camelCase!), форматы дат менять НЕЛЬЗЯ** — фронт уже написан под них.
2. `ToR.md` — полное ТЗ (роли, контент-модель, CMS-требования, безопасность).
3. `docs/PLAN.md` — рабочий чеклист. Работай по нему сверху вниз, отмечай `[x]` сделанное, добавляй обнаруженные подзадачи.

## Команды

```bash
php artisan serve                         # dev-сервер
php artisan migrate:fresh --seed          # БД с сидерами (= мок-данные фронта)
php artisan queue:work                    # конверсии, вебхуки, письма
vendor/bin/pint --dirty --format agent    # обязателен перед коммитом
php artisan test --compact                # Pest, обязателен перед коммитом
```

## Пакеты

Установлены: `filament/filament:^5.0`, `laravel/boost`, Pest 4, Pint.
Добавить по мере надобности (согласно PLAN): `laravel/sanctum`, `spatie/laravel-medialibrary`, `spatie/laravel-translatable` (+ filament-плагин), `spatie/laravel-permission`, `spatie/laravel-activitylog`, `spatie/laravel-settings`, `spatie/laravel-backup`. Другие зависимости — только с одобрения.

## Правила кода (дополняют Boost)

- Тонкие контроллеры: валидация — Form Requests, ответы — **только через Eloquent API Resources** (никогда не отдавать модель/массив напрямую). Ключи ответов — camelCase по контракту (`categoryColor`, `newsSlug`, `activeIncidents`, `lastActivity`, `headOffice`).
- Статусы/enum'ы — PHP backed enums (`NewsStatus`, `RiskLevel`, `DocType`, …).
- Переводимые поля — JSON-колонки (spatie/translatable), локали `tj|ru|en`, fallback → `tj`.
- Тяжёлое (конверсии изображений, вебхук ревалидации, e-mail) — только через очередь.
- Бизнес-логика — в Actions/Services, не в Filament-ресурсах и не в контроллерах.
- Миграции только аддитивные после первого деплоя; индексы по ToR §11.
- HTML из Tiptap санитизировать на сервере перед сохранением (белый список).

## Правила API (кратко; детали в docs/API-CONTRACT.md)

- База `/api/v1`, локаль — опциональный префикс `/{tg|ru|en}/`, без префикса = tj.
- Все GET закрыты статическим Bearer-токеном (Sanctum). POST форм — публичные, throttle 5 rpm/IP + honeypot.
- В выдачу попадают только записи `published` с `published_at <= now`.
- Пагинация — стандартный Laravel Resource (`data/meta/links`). Даты — строки `DD.MM.YYYY`.
- После публикации/изменения контента — задача в очередь: POST на вебхук фронта `/api/revalidate` (карта тегов в контракте).

## Definition of Done (каждая задача)

1. `vendor/bin/pint --dirty --format agent` — чисто; `php artisan test --compact` — зелёный.
2. Новый эндпоинт → контрактный Pest-тест (структура ответа = JSON-схеме из контракта, включая имена ключей).
3. Новая коллекция → Filament-ресурс с политиками ролей (admin/editor по матрице ToR §4) + сидер.
4. Пункт в `docs/PLAN.md` отмечен `[x]`.

## Нельзя

- Менять формы ответов API, имена ключей, форматы дат.
- Добавлять публичную регистрацию/авторизацию на сайте (вход только в CMS-панель).
- Хардкодить секреты (только `.env`).
- Отключать/пропускать тесты, чтобы «пока заработало».
- Придумывать новые разделы CMS, которых нет в ToR, без пометки «предложение» в PLAN.
