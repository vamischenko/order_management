# Order Management API

Backend-сервис управления заказами для интернет-магазина запчастей.

**Стек:** PHP 8.4, Laravel 12, PostgreSQL 16, Redis 7, Docker

---

## Быстрый старт

### 1. Клонирование и настройка

```bash
git clone <repo-url> order_management
cd order_management
cp .env.example .env
```

### 2. Запуск Docker

```bash
docker compose up -d --build
```

Контейнеры:

- `app` — PHP 8.4-FPM (приложение)
- `nginx` — веб-сервер (порт **8080**)
- `postgres` — PostgreSQL 16 (порт **5432**)
- `redis` — Redis 7 (порт **6379**)
- `queue` — воркер очереди (автоматически)

### 3. Генерация APP_KEY

```bash
docker compose exec app php artisan key:generate
```

### 4. Миграции и сидер

```bash
# Накатить миграции
docker compose exec app php artisan migrate

# Заполнить тестовыми данными (50 товаров, 10 клиентов)
docker compose exec app php artisan db:seed
```

### 5. Генерация Swagger-документации

```bash
docker compose exec app php artisan l5-swagger:generate
```

Документация доступна по адресу: [http://localhost:8080/api/documentation](http://localhost:8080/api/documentation)

---

## Запуск очереди

Очередь запускается автоматически в контейнере `queue`. При необходимости перезапустить вручную:

```bash
docker compose exec app php artisan queue:work redis \
  --queue=exports,default \
  --sleep=3 \
  --tries=3 \
  --max-time=3600
```

---

## Запуск тестов

```bash
docker compose exec app php artisan test
```

Или напрямую через phpunit:

```bash
docker compose exec app ./vendor/bin/phpunit --testdox
```

Тесты используют SQLite in-memory — не требуют подключения к PostgreSQL.

---

## REST API

Базовый URL: `http://localhost:8080/api/v1`

| Метод   | URL                   | Описание                                  |
| ------- | --------------------- | ----------------------------------------- |
| `GET`   | `/products`           | Список товаров с фильтрацией и пагинацией |
| `POST`  | `/orders`             | Создание заказа                           |
| `GET`   | `/orders`             | Список заказов с фильтрами                |
| `GET`   | `/orders/{id}`        | Детали заказа                             |
| `PATCH` | `/orders/{id}/status` | Смена статуса                             |

### Примеры запросов

#### GET /api/v1/products

```
GET /api/v1/products?category=Двигатель&search=фильтр&per_page=10&page=1
```

#### POST /api/v1/orders

```json
{
  "customer_id": 1,
  "items": [
    {"product_id": 1, "quantity": 2},
    {"product_id": 5, "quantity": 1}
  ]
}
```

#### PATCH /api/v1/orders/1/status

```json
{"status": "confirmed"}
```

### Статусы заказа

```text
new → confirmed → processing → shipped → completed
new → cancelled
confirmed → cancelled
```

### Rate Limiting

Создание заказов ограничено: **10 запросов в минуту** по IP.
При превышении возвращается `429 Too Many Requests`.

---

## Архитектура

```text
app/
├── Data/           # DTO (readonly классы для передачи данных в сервис)
├── Enums/          # OrderStatus enum с логикой переходов
├── Events/         # OrderConfirmed event
├── Exceptions/     # InsufficientStockException, InvalidStatusTransitionException
├── Http/
│   ├── Controllers/Api/V1/   # Тонкие контроллеры
│   ├── Requests/             # Form Request валидация
│   └── Resources/            # API Resource классы
├── Jobs/           # ExportOrderJob (3 попытки, очередь exports)
├── Listeners/      # DispatchOrderExport
├── Models/         # Eloquent модели
└── Services/       # OrderService — бизнес-логика
```

### Ключевые решения

- **DTO** — `CreateOrderData` / `OrderItemData` для передачи данных в `OrderService`
- **Service Layer** — `OrderService` содержит всю бизнес-логику; контроллеры только маршрутизируют
- **Атомарность** — создание заказа в `DB::transaction()` с `lockForUpdate()` на товарах
- **Event/Listener** — `OrderConfirmed` event → `DispatchOrderExport` listener вместо прямого dispatch
- **Redis кеш** — список товаров кешируется с инвалидацией при изменении остатков
- **order_exports** — отдельная таблица для отслеживания статуса экспорта (pending/success/failed)
- **OpenAPI** — аннотации в контроллерах и ресурсах, Swagger UI на `/api/documentation`

---

## Переменные окружения

| Переменная                   | Описание                                       | Значение по умолчанию      |
| ---------------------------- | ---------------------------------------------- | -------------------------- |
| `DB_HOST`                    | Хост PostgreSQL                                | `postgres`                 |
| `DB_DATABASE`                | Имя БД                                         | `order_management`         |
| `DB_USERNAME`                | Пользователь                                   | `postgres`                 |
| `DB_PASSWORD`                | Пароль                                         | `secret`                   |
| `REDIS_HOST`                 | Хост Redis                                     | `redis`                    |
| `EXPORT_URL`                 | URL внешней системы экспорта                   | `https://httpbin.org/post` |
| `QUEUE_CONNECTION`           | Драйвер очереди                                | `redis`                    |
| `CACHE_STORE`                | Драйвер кеша                                   | `redis`                    |
| `L5_SWAGGER_GENERATE_ALWAYS` | Авто-генерация Swagger при каждом запросе      | `false`                    |
