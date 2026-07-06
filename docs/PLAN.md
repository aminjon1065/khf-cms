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
- [ ] Слайдер: модель + Filament-ресурс (drag-сортировка, active, связь с новостью) + `GET /home/slides`
- [ ] Вебхук ревалидации: очередь + 3 ретрая, карта тегов из контракта; настройки (FRONTEND_URL, секрет) в CMS + кнопки «Проверить» / «Сбросить весь кеш»
- [ ] Сидеры новостей/слайдов/категорий = мок-данные фронта (см. `lib/data.ts` в khf-front)
- [ ] Контрактные Pest-тесты всех эндпоинтов M2 (+ тест fallback-локали, тест 401 без токена)
- [ ] ✅ Проверка этапа: фронт со staging-API показывает новости и слайдер на 3 языках; публикация обновляет сайт ≤ 1 мин

## M3. Остальной контент + формы

- [ ] Документы: категории (laws|decrees|orders|guides|reports) + записи с файлом (pdf/docx/xlsx; type/size из файла) + `GET /documents?category=` (categories с count и виртуальной «all»)
- [ ] Структура: leadership / departments / regional_offices (+ sort) + `GET /structure`
- [ ] Деятельность: directions (icon lucide, stat) / programs (status enum) + `GET /activities`
- [ ] Карта регионов: regions (risk enum, activeIncidents, inline-редактирование в таблице) + глобальный блок stats + `GET /regions`
- [ ] Контакты: hotlines / head_office (singleton) / offices + `GET /contacts`
- [ ] Форум (read-only витрина): categories / topics / stats + `GET /forum`
- [ ] Глобальные блоки главной (только admin): services, president, site stats + `GET /home`
- [ ] Формы: `POST /reports`, `POST /contact`, `POST /subscriptions` — Form Requests, honeypot, throttle, нормализация телефона, reference `ЧС-NNNNNN`/`SUB-NNNNNN`
- [ ] CMS-разделы «Обращения» (3 шт.): статусы new/in_progress/closed, фильтры, экспорт CSV
- [ ] E-mail уведомление дежурному о новой заявке о ЧС (адреса в настройках)
- [ ] Сидеры всех разделов = мок-данные фронта (`lib/content/*.ts` в khf-front)
- [ ] Контрактные тесты всех эндпоинтов M3
- [ ] ✅ Проверка этапа: фронт полностью работает с `NEXT_PUBLIC_USE_MOCKS=false`

## M4. Сдача

- [ ] OpenAPI 3 спецификация + коллекция Bruno/Postman
- [ ] spatie/laravel-backup: БД ежедневно (30 дней), медиа еженедельно; тест восстановления
- [ ] Чек-лист безопасности ToR §10 (HSTS, санитизация, MIME, ротация токена)
- [ ] CI: pint + pest
- [ ] Docker compose (php-fpm, nginx, mysql, worker, scheduler) + README деплоя
- [ ] Инструкция редактора (RU, со скриншотами)

## Вне рамок v1 (не делать без запроса)

Публичные аккаунты, постинг на форуме, рассылки подписчикам, Telegram, Next Draft Mode, page builder, RSS, полнотекстовый поиск по сайту.
