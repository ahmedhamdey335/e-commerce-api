# E-Commerce REST API

A role-based e-commerce REST API built with **Laravel 12**. It supports product catalog browsing, seller product management, customer cart/checkout, shipping addresses, and order workflows—with consistent JSON responses and policy-based authorization.

## Features

- **Authentication** — Register, login, logout (Laravel Sanctum bearer tokens)
- **Password reset** — Forgot / reset password flow with email token
- **Products** — List, search, filter, paginate; sellers manage their own products; admins manage any product
- **Categories** — Public browse; admin CRUD
- **Cart** — Customers add/remove items with stock checks
- **Checkout** — Place orders from cart with address validation and stock deduction
- **Orders** — Role-specific order listing; admin status updates
- **Addresses** — Customer shipping address CRUD
- **Authorization** — `CheckRole` middleware + Laravel Policies (`Product`, `Order`, `Address`)
- **API docs** — Scribe-generated documentation at `/docs`
- **Tests** — Feature tests for endpoints; unit tests for policies

## Tech Stack

| Layer | Technology |
|--------|------------|
| Framework | Laravel 12 |
| PHP | 8.2+ |
| Auth | Laravel Sanctum |
| Database | MySQL |
| API docs | [Scribe](https://scribe.knuckles.wtf/laravel) |
| HTTP testing | PHPUnit 11 |

## User Roles

| Role | Capabilities |
|------|----------------|
| **customer** | Cart, checkout, addresses, view own orders |
| **seller** | Create/update/delete own products; view orders containing their products |
| **admin** | Full category management; update any product; update order status; view all orders |

New registrations default to `customer`.

## Requirements

- PHP >= 8.2 with extensions: `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`
- Composer
- MySQL 8+ (or SQLite for local/testing)
- Node.js & npm (optional, for frontend assets)

## Installation

### 1. Clone and install dependencies

```bash
git clone https://github.com/your-username/e-commerce-api.git
cd e-commerce-api
composer install
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env` (MySQL example):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_api
DB_USERNAME=root
DB_PASSWORD=
```

Set `APP_URL` to your local domain (e.g. `http://e-commerce-api.test` on Laragon).

### 3. Database

```bash
php artisan migrate --seed
php artisan storage:link
```

### 4. Run the application

```bash
php artisan serve
```

Or use Laragon’s virtual host pointing to the `public` directory.

### Quick setup (Composer script)

```bash
composer setup
```

Runs install, env copy, key generate, migrate, and npm build when applicable.

## Test Users

All accounts use the password: `password`

| Role     | Email                |
|----------|----------------------|
| Admin    | admin@example.com    |
| Seller 1 | seller1@example.com  |
| Seller 2 | seller2@example.com  |
| Customer | customer@example.com |

## API Documentation

Visit `/docs` to view the interactive API documentation.

Import `postman_collection.json` into Postman to test all endpoints.
