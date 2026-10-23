# 📰 NewsRoom — Headless CMS API

A decoupled, high-performance CMS API built for **TechNova**. The project follows modern software architecture patterns with a strong focus on scalability, security and clean code.

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)
![Sanctum](https://img.shields.io/badge/Auth-Sanctum-FF2D20)
![Redis](https://img.shields.io/badge/Cache-Redis-DC382D?logo=redis&logoColor=white)
![MySQL](https://img.shields.io/badge/DB-MySQL-4479A1?logo=mysql&logoColor=white)

---

## 📑 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Getting Started](#-getting-started)
- [Database Design](#-database-design)
- [Architectural Decisions (ADRs)](#-architectural-decisions-adrs)
- [Authentication](#-authentication)
- [API Endpoints](#-api-endpoints)
- [Background Processing](#-background-processing)
- [Postman Collection](#-postman-collection)

---

## ✨ Features

- **Versioned API** — `v1` for web clients and `v2` for mobile clients with richer payloads.
- **Role-based access** (admin, writer, reader) enforced through Policies.
- **Editorial workflow** — writers publish, admins review.
- **Repository & Service layers** keeping controllers thin and testable.
- **Redis atomic locks** to protect the dashboard from cache stampedes.
- **Queued events, jobs and notifications** for heavy or slow work.
- **Scheduled commands** for archiving old articles and generating weekly reports.
- **Rate limiting** — 60 requests/minute per user (or IP for guests).
- **Request telemetry** logged after the response is sent.

## 🛠 Tech Stack

| Layer | Technology |
| :--- | :--- |
| Language | PHP 8.2+ |
| Framework | Laravel 11 |
| Authentication | Laravel Sanctum (Bearer tokens) |
| Database | MySQL / MariaDB (SQLite works for local testing) |
| Cache & Locks | Redis (`predis/predis`) |
| Queue | Database driver |
| API Testing | Postman |

---

## 🚀 Getting Started

### Prerequisites

- PHP **8.2+** with `openssl`, `pdo` and `mbstring` enabled
- Composer
- MySQL / MariaDB
- Redis (recommended for cache & atomic locks)

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/hasan123dayoub-del/NewsRooom.git
cd NewsRooom

# 2. Install dependencies
composer install

# 3. Create the environment file
cp .env.example .env
php artisan key:generate
```

Update the database, cache and queue settings in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=newsroom_db
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=redis        # or database / file
QUEUE_CONNECTION=database
```

```bash
# 4. Build the schema and seed users, categories, tags and articles
php artisan migrate --seed

# 5. Run the application
php artisan serve

# 6. (In separate terminals) process queued jobs and scheduled tasks
php artisan queue:work
php artisan schedule:work
```

The API is now available at **http://127.0.0.1:8000/api**.

---

## 🗄 Database Design

The schema is normalized and indexed to handle heavy concurrent reads and writes.

```text
[ User ] 1 ──── 1 [ Profile ]
   │
   └── 1 ──── * [ Article ] * ──── * [ Tag ]        (polymorphic pivot: taggables)
                   │
                   ├── 1 ──── * [ Comment ]
                   ├── 1 ──── * [ Attachment ]
                   └── * ──── 1 [ Category ]
```

| Relationship | Type | Implementation |
| :--- | :--- | :--- |
| User ↔ Profile | 1 : 1 | `$user->profile()` / `$profile->user()` |
| User ↔ Article | 1 : N | `$user->articles()` / `$article->user()` |
| Article ↔ Comment | 1 : N | `$article->comments()` / `$comment->article()` |
| Article ↔ Tag | N : M (polymorphic) | `$article->tags()` / `$tag->articles()` |
| Article ↔ Attachment | 1 : N | `$article->attachments()` |

---

## 🏛 Architectural Decisions (ADRs)

### A. Repository Pattern & Contract-Driven Design
**Decision:** All query logic lives behind `ArticleRepositoryInterface`, bound to its Eloquent implementation in `AppServiceProvider`.
**Why:** Loose coupling — controllers never touch Eloquent directly. Switching the data source (e.g. to MongoDB or an external service) means changing a single binding.

### B. Service Layer (Slim Controllers)
**Decision:** Business logic such as syncing tags and processing input lives in `ArticleService` and `DashboardService`.
**Why:** Single Responsibility — controllers only receive the request and return the response.

### C. Contextual Binding for Notifications
**Decision:** `NotificationSenderInterface` is resolved per controller:

```php
$this->app->when(AdminArticleController::class)
    ->needs(NotificationSenderInterface::class)
    ->give(DatabaseNotificationSender::class);

$this->app->when(WriterArticleController::class)
    ->needs(NotificationSenderInterface::class)
    ->give(EmailNotificationSender::class);
```

**Why:** No `if/else` role checks — the container picks the right channel (database or email) automatically.

### D. API Versioning (V1 / V2)
**Decision:** Web endpoints live under `/api/v1`, mobile endpoints under `/api/v2`, each with its own controllers and `ArticleResource`.
**Why:** Mobile clients get aggregated data (tags, `comments_count`) in one round trip without breaking existing V1 integrations.

### E. Cache Stampede Protection (Atomic Locks)
**Decision:** `DashboardService` rebuilds its statistics inside a Redis lock:

```php
$lock = Cache::lock('calculating_dashboard_stats', 10);
$lock->block(5);
```

**Why:** When the cache expires under heavy traffic, only one worker queries the database while the others wait for the fresh result.

### F. After-Response Telemetry
**Decision:** `LogRequestData` middleware records request metrics in its `terminate()` method.
**Why:** Logging happens after the response has been sent, so it adds no latency for the client.

---

## 🔐 Authentication

NewsRoom uses **Laravel Sanctum** for stateless, token-based authentication suitable for both web and mobile clients.

- **Register** — creates a user with a Bcrypt-hashed password.
- **Login** — returns a `Bearer` token.
- **Responses** — shaped by `UserResource`, so only public fields are exposed.

Send the token with every protected request:

```http
Authorization: Bearer <your-token>
```

---

## 📡 API Endpoints

### Authentication

| Method | Endpoint | Description | Auth |
| :--- | :--- | :--- | :---: |
| `POST` | `/api/register` | Create a new account | — |
| `POST` | `/api/login` | Log in and receive a token | — |
| `POST` | `/api/logout` | Revoke the current token | ✅ |
| `GET` | `/api/user` | Get the authenticated user | ✅ |

### V1 — Web Client

| Method | Endpoint | Description | Auth |
| :--- | :--- | :--- | :---: |
| `GET` | `/api/v1/articles` | List published articles | — |
| `GET` | `/api/v1/articles/{id}` | Show an article | — |
| `GET` | `/api/v1/dashboard` | Cached dashboard statistics | — |
| `POST` | `/api/v1/articles` | Create an article (writer) | ✅ |
| `PUT` | `/api/v1/articles/{id}` | Update own article (writer) | ✅ |
| `DELETE` | `/api/v1/articles/{id}` | Delete own article (writer) | ✅ |
| `POST` | `/api/v1/articles/{id}/publish` | Publish an article (writer) | ✅ |
| `POST` | `/api/v1/articles/{id}/review` | Review an article (admin) | ✅ |

### V2 — Mobile Client

| Method | Endpoint | Description | Auth |
| :--- | :--- | :--- | :---: |
| `GET` | `/api/v2/articles` | List articles with tags & comment counts | — |
| `GET` | `/api/v2/articles/{id}` | Show an article with full details | — |
| `POST` | `/api/v2/articles` | Create an article | ✅ |
| `PUT` | `/api/v2/articles/{id}` | Update an article | ✅ |
| `DELETE` | `/api/v2/articles/{id}` | Delete an article | ✅ |

> All `v1` and `v2` routes are rate-limited to **60 requests per minute**.

---

## ⚙️ Background Processing

| Component | Purpose |
| :--- | :--- |
| `ArticlePublished` → `NotifyEmployeesOfNewArticle` | Queued listener that notifies staff about new articles |
| `UserRegistered` → `SendWelcomeMessage` | Queued welcome message for new users |
| `SendNotificationToChunkJob` | Sends notifications in chunks to avoid memory spikes |
| `articles:archive 30` | Archives articles older than 30 days — runs **monthly** |
| `articles:report` | Generates the articles report — runs **every Friday at 08:00** |

---

## 🧪 Postman Collection

📥 **[Download the Postman collection](TechNova%20NewsRoom%20API%20-%202026.postman_collection.json)**

The collection injects the token for you:

1. Run the **login** request in the `Authentication` folder.
2. Its **Tests** script saves the `access_token` into the `sanctum_token` environment variable.
3. Every other request then uses it automatically as a `Bearer` token.

---

<p align="center">Built with ❤️ using Laravel — TechNova NewsRoom 2026</p>
