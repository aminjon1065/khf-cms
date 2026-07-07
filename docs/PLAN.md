# PLAN — рабочий чеклист khf-cms

Агент: работай сверху вниз, отмечай `[x]`, добавляй подзадачи по месту. Критерии приёмки этапов — ToR §12.

## M1. Каркас

- [x] Laravel 13 (starter kit: none), Pint, Pest
- [x] laravel/boost (guidelines в CLAUDE.md/AGENTS.md)
- [x] Filament 5 (`filament:install --panels`), панель `/admin` (id `admin`)
- [x] `.env.example` со всеми переменными: `DB_*` (MySQL 8), `FRONTEND_URL`, `REVALIDATE_SECRET`, `MEDIA_DISK`, `MAIL_*`, `QUEUE_CONNECTION=database`
- [x] spatie/laravel-permission: роли `admin`, `editor`; сидер ролей
- [x] Filament-ресурс «Пользователи» (только admin), создание/деактивация, без публичной регистрации
- [x] 2FA (TOTP) для admin, троттлинг входа, тайм-аут сессии
- [x] spatie/laravel-activitylog + страница «Журнал» (только admin)
- [x] Sanctum: команда/страница генерации статического API-токена фронта, middleware `auth:sanctum` на GET API
- [x] Каркас API: маршруты `/api/v1/{locale?}/...`, middleware локали (tg|ru|en, fallback tj), обработчик 404/422/429 в JSON

## M2. Новости + медиа + интеграция с фронтом

- [x] Справочники (только admin): категории новостей (label 🌐, color: text-alert|text-brand|text-success|text-warn), регионы (name 🌐)
- [x] Модель News: поля по ToR §6.1, translatable JSON, slug (транслит, уникальный), enum статусов, `views`
- [x] Медиатека: spatie/laravel-medialibrary, конверсии `thumb` 400×300 / `card` 800×600 / `hero` 1920×1080 + WebP q82, генерация в очереди
- [x] Filament-ресурс «Новости»: Tiptap RichEditor (вставка картинок из медиатеки), автосейв черновика, переключатель языков + индикатор переводов, фильтры/поиск/массовые действия, дублирование
- [x] Ревизии записей (15 версий, просмотр/откат)
- [x] Планировщик публикации `scheduled → published` (schedule:run, ежеминутно)
- [x] Санитизация HTML (белый список) при сохранении body
- [x] API: `GET /news` (page, per_page=12, category, search, сортировка published_at desc), `GET /news/{idOrSlug}` (slug или id, 404), `GET /news/{idOrSlug}/related?limit=3`
- [x] `POST /news/{idOrSlug}/view` — публичный, throttle 10 rpm/IP, дедуп IP+UA 1 час
- [x] Слайдер: модель + Filament-ресурс (drag-сортировка, active, связь с новостью) + `GET /home/slides`
- [x] Вебхук ревалидации: очередь + 3 ретрая, карта тегов из контракта; настройки (FRONTEND_URL, секрет) в CMS + кнопки «Проверить» / «Сбросить весь кеш»
- [x] Сидеры новостей/слайдов/категорий = мок-данные фронта (см. `lib/data.ts` в khf-front)
- [x] Контрактные Pest-тесты всех эндпоинтов M2 (+ тест fallback-локали, тест 401 без токена)
- [x] ✅ Проверка этапа: фронт со staging-API показывает новости и слайдер на 3 языках; публикация обновляет сайт ≤ 1 мин

## M3. Остальной контент + формы

- [x] Документы: категории (laws|decrees|orders|guides|reports, enum с локализ. label) + записи с файлом (pdf/docx/xlsx; type/size из файла, тип по content-MIME) + `GET /documents?category=` (categories с count и виртуальной «all», тег ревалидации `documents`)
- [x] Структура: leadership / departments / regional_offices (модели + sort/active, translatable, 3 Filament-ресурса группы «Структура») + `GET /structure` (composed, тег ревалидации `structure`)
- [x] Деятельность: directions (slug=id, icon lucide, stat{value,label}) / programs (status enum, локализ. label) + `GET /activities` (composed, тег `activities`). Трейт ревалидации обобщён в RevalidatesContent.
- [x] Карта регионов: MapRegion (risk enum, activeIncidents/stations, inline-редактирование в таблице; отдельно от справочника Region) + глобальный блок stats (MapSetting singleton monitoring + вычисляемые счётчики) + `GET /regions` (тег `regions`)
- [x] Контакты: hotlines / offices (головной офис = запись `is_head`, единственность enforced) + `GET /contacts` (composed, тег ревалидации `contacts`)
- [x] Форум (read-only витрина): categories / topics / stats (ForumStat singleton, все значения — строки) + `GET /forum` (composed, тег ревалидации `forum`)
- [x] Глобальные блоки главной (только admin): services, president, site stats + `GET /home`
- [x] Формы: `POST /reports`, `POST /contact`, `POST /subscriptions` — Form Requests, honeypot (`website` prohibited), throttle 5rpm/IP, нормализация телефона (`App\Support\PhoneNumber`), reference `ЧС-NNNNNN`/`SUB-NNNNNN` (трейт `GeneratesReference`); ответ без обёртки `data`
- [x] CMS-разделы «Обращения» (3 шт.): Report/ContactMessage/Subscription инбоксы, статусы new/in_progress/closed (`SubmissionStatus`, inline `SelectColumn`), фильтры, экспорт CSV (`App\Support\CsvExporter`, BOM+защита от формул); политики admin+editor (view/update), создание запрещено, удаление только admin
- [x] E-mail уведомление дежурному о новой заявке о ЧС (`NewReportNotification`, очередь; адреса в `khf.duty.emails` из `DUTY_EMAILS`)
- [x] Сидеры всех разделов = мок-данные фронта (`lib/content/*.ts` в khf-front) — все контент-разделы; обращения генерируются формами (мока нет)
- [x] Контрактные тесты всех эндпоинтов M3
- [ ] ✅ Проверка этапа: фронт полностью работает с `NEXT_PUBLIC_USE_MOCKS=false` (код готов; нужен live-прогон front↔staging)

## M4. Сдача

- [x] OpenAPI 3 спецификация (`docs/openapi.yaml`, 3.1, все 16 эндпоинтов + 33 схемы) + Postman-коллекция и окружение (`docs/khf-api.postman_*.json`); тест-страж дрейфа (`OpenApiSpecTest`: спец ↔ реальные роуты)
- [ ] spatie/laravel-backup: БД ежедневно (30 дней), медиа еженедельно; тест восстановления
- [ ] Чек-лист безопасности ToR §10 (HSTS, санитизация, MIME, ротация токена)
- [ ] CI: pint + pest
- [ ] Docker compose (php-fpm, nginx, mysql, worker, scheduler) + README деплоя
- [ ] Инструкция редактора (RU, со скриншотами)

## Доп. возможности (по запросу заказчика)

- [x] Переиспользуемая медиатека (WordPress-style): модель `MediaAsset` (spatie, изображения+документы, конверсии thumb/card/hero для картинок), Filament-раздел «Медиатека». News: обложка (`cover_media_asset_id`) + галерея (`news_gallery`, CMS-only, не в API) выбираются из медиатеки; Documents: файл (`media_asset_id`) выбирается из медиатеки. Один файл переиспользуется многократно. Защита от удаления используемого файла (`isInUse` + `deleting`-гард + гард в Filament, включая bulk). Смена файла ассета — ревалидация связанных news/documents + ресинк type/size документов (слушатель `MediaHasBeenAddedEvent`). Пикеры обложки/галереи ограничены изображениями. Формы API не менялись (обложка → тот же ImageSet; документы → тот же url/type/size).

## Вне рамок v1 (не делать без запроса)

Публичные аккаунты, постинг на форуме, рассылки подписчикам, Telegram, Next Draft Mode, page builder, RSS, полнотекстовый поиск по сайту.
