# API-CONTRACT — точный контракт khf-backend ↔ khf-front

Выведен из кода фронта (`lib/api/*`, `lib/data.ts`, `lib/content/*.ts`). **Имена ключей, типы значений и обёртки менять нельзя.** Лишние поля добавлять можно (фронт их игнорирует).

## 1. Общие правила

- База: `/api/v1`. Локаль — опциональный префикс пути: `/api/v1/tg/...`, `/api/v1/ru/...`, `/api/v1/en/...`. Без префикса → tj. (`tg` = таджикский, ISO 639-1.)
- Fallback переводов: нет перевода поля → отдать tj-версию.
- Auth: все GET — `Authorization: Bearer <статический токен>`; нет/неверный → `401`. POST форм и `POST /news/{id}/view` — без токена.
- Обёртки: одиночный ресурс `{ "data": {...} }`; список — Laravel-пагинация:

```json
{ "data": [], "meta": { "current_page": 1, "last_page": 1, "per_page": 12, "total": 0 },
  "links": { "first": null, "last": null, "prev": null, "next": null } }
```

- Ошибки: `404` not found; `422` — стандарт Laravel (`{ "message": "...", "errors": { "field": ["..."] } }`); `429` throttle.
- Даты в ответах — строки `DD.MM.YYYY`. Опциональные поля (`?`) можно опускать или слать `null`.

## 2. Общие типы

```jsonc
// ImageSet — абсолютные URL конверсий; поле image везде может быть null
{ "thumb": "https://…/x-thumb.webp", "card": "https://…/x-card.webp",
  "hero": "https://…/x-hero.webp", "original": "https://…/x.jpg" }
```

## 3. GET-эндпоинты

### GET /news · параметры: page, per_page (деф. 12), category, search
Пагинированный список **NewsItem**, сортировка `published_at desc`. `search` — по заголовку. `category` — по id/label категории.

```jsonc
// NewsItem
{
  "id": 6105,
  "slug": "spasateli-vyzvolili-troih",
  "category": "СПАСЕНИЕ",              // label в текущей локали
  "categoryColor": "text-alert",        // из набора: text-alert | text-brand | text-success | text-warn
  "title": "Спасатели вызволили троих граждан из реки Вахш",
  "date": "14.06.2026",
  "excerpt": "Поисково-спасательное подразделение…",
  "body": "<p>…санитизированный HTML из Tiptap…</p>",
  "author": "Пресс-центр КҲФ",
  "views": 1842,
  "region": "Хатлон",                   // опционально
  "image": { /* ImageSet */ }           // или null
}
```

### GET /news/{idOrSlug}
`{ "data": NewsItem }`. Искать по slug, затем по числовому id. Нет → `404`.

### GET /news/{idOrSlug}/related · параметры: limit (деф. 3)
Пагинированный список NewsItem той же категории/региона, исключая текущую.

### POST /news/{idOrSlug}/view
Публичный счётчик просмотров. Без тела. Throttle 10 rpm/IP, дедуп IP+UA на 1 час. Ответ `204`.

### GET /home/slides
`{ "data": Slide[] }` — активные, в ручном порядке.

```jsonc
// Slide
{ "id": 6105, "category": "Спасение", "title": "…", "date": "14.06.2026",
  "source": "Пресс-центр КҲФ", "image": { /* ImageSet */ },
  "newsSlug": "spasateli-vyzvolili-troih" }   // слаг связанной новости или null
```

### GET /home
```jsonc
{ "data": {
  "services": [                          // быстрые кнопки главной
    { "id": "112", "title": "112", "subtitle": "Экстренный вызов", "tel": "112", "primary": true },
    { "id": "report", "title": "Сообщить о ЧС", "subtitle": "Онлайн-заявка", "route": "report" }
  ],                                     // route — ключ маршрута фронта: report | safety | subscribe | …
  "president": { "name": "Эмомалӣ Раҳмон", "role": "Президенти ҶТ", "quote": "«…»", "href": "https://president.tj" },
  "stats": { "today": "1 240", "month": "38 902", "rescued": "12 480", "reaction": "8 мин" }  // строки!
} }
```

### GET /structure
```jsonc
{ "data": {
  "leadership":  [ { "name": "…", "role": "Раиси Кумита", "rank": "генерал-лейтенант", "bio": "…" } ],  // rank опц.
  "departments": [ { "title": "…", "description": "…", "head": "…" } ],                                  // head опц.
  "offices":     [ { "region": "…", "head": "…", "phone": "…", "address": "…" } ]
} }
```

### GET /activities
```jsonc
{ "data": {
  "directions": [ { "id": "rescue", "icon": "LifeBuoy", "title": "…", "description": "…",
                    "stat": { "value": "52", "label": "станции" } } ],   // icon — имя lucide-react
  "programs":   [ { "title": "…", "period": "2024–2028", "status": "Амалкунанда", "description": "…" } ]
} }               // status (локализуемый label): Амалкунанда | Ба нақша | Анҷомёфта
```

### GET /documents · параметры: category
```jsonc
{ "data": {
  "categories": [ { "id": "all", "label": "Ҳама", "count": 12 }, { "id": "laws", "label": "Қонунҳо", "count": 3 } ],
  "items": [ { "id": "d1", "title": "Қонуни ҶТ «…»", "category": "laws", "number": "№ 53",
               "date": "15.07.2004", "type": "PDF", "size": "2,4 МБ" } ]
} }   // category id: laws | decrees | orders | guides | reports; type: PDF | DOCX | XLSX
      // + у items должен быть URL файла (поле url — фронт согласует при подключении скачивания)
```

### GET /forum
```jsonc
{ "data": {
  "categories": [ { "id": "general", "title": "Умумӣ", "description": "…", "topics": 124, "posts": 980, "icon": "MessagesSquare" } ],
  "topics": [ { "id": "t1", "title": "…", "category": "general", "author": "…",
                "replies": 12, "views": 340, "lastActivity": "2 соат пеш", "pinned": true } ],  // pinned опц.
  "stats": { "members": "8 420", "topics": "470", "posts": "3 512", "online": "63" }             // строки!
} }
```

### GET /regions
```jsonc
{ "data": {
  "regions": [ { "id": "dushanbe", "name": "Душанбе", "center": "Душанбе", "risk": "medium",
                 "activeIncidents": 3, "stations": 9, "note": "…" } ],   // risk: low | medium | high
  "stats": { "regions": 5, "stations": 52, "activeIncidents": 12, "monitoring": "320+" }  // числа + строка monitoring
} }
```

### GET /contacts
```jsonc
{ "data": {
  "hotlines": [ { "number": "112", "label": "Хадамоти ягонаи наҷот", "note": "Круглосуточно…", "primary": true } ],
  "headOffice": { "region": "Дастгоҳи марказӣ", "address": "734013, ш. Душанбе, кӯчаи Лоҳутӣ 26",
                  "phone": "(992 37) 223-13-11", "email": "info@khf.tj", "hours": "Душанбе–Ҷумъа, 8:00–17:00" },
  "offices": [ /* Office[] — та же форма, что headOffice */ ]
} }
```

## 4. POST-формы (без префикса локали, без токена, throttle 5 rpm/IP + honeypot)

| Эндпоинт | Payload (все поля обязательны) | Успех (200) |
|---|---|---|
| `POST /reports` | `{ "type": "...", "region": "...", "location": "...", "description": "...", "phone": "..." }` | `{ "ok": true, "reference": "ЧС-000123" }` |
| `POST /contact` | `{ "name": "...", "email": "...", "subject": "...", "message": "..." }` | `{ "ok": true }` |
| `POST /subscriptions` | `{ "channel": "...", "region": "...", "categories": ["..."], "contact": "..." }` | `{ "ok": true, "reference": "SUB-000123" }` |

Ответ успеха — **без обёртки `data`**. Ошибки валидации — стандартный `422`.

## 5. Вебхук ревалидации ISR (backend → frontend)

При publish/update/delete/archive контента (очередь, 3 ретрая с backoff):

```
POST {FRONTEND_URL}/api/revalidate
Header: x-revalidate-secret: {REVALIDATE_SECRET}
Body:   { "tags": ["..."], "paths": [] }
```

| Изменение | tags |
|---|---|
| Новость | `news`, `news:{slug}`, `home` |
| Слайдер | `home`, `news` |
| Документы | `documents` |
| Структура | `structure` |
| Деятельность | `activities` |
| Регионы/карта | `regions` |
| Контакты | `contacts` |
| Форум | `forum` |
| Глобальные блоки главной | `home` |
| Кнопка «Сбросить весь кеш» | все теги |
