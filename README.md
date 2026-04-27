# E-Commerce REST API

A full-featured e-commerce REST API built with Laravel 12, featuring role-based access control for admin, seller, and customer roles. Covers authentication, product management, cart, checkout, and order tracking.

## Tech Stack
- PHP / Laravel 12
- MySQL
- Laravel Sanctum
- Laravel Policies

## Installation

```bash
git clone https://github.com/your-username/e-commerce-api.git
cd e-commerce-api
composer install
cp .env.example .env
php artisan key:generate
```

Set your database credentials in `.env`, then:

```bash
php artisan migrate --seed
php artisan storage:link
```

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
