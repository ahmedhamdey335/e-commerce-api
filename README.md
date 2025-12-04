# E-Commerce API (Laravel 12)

A professional backend REST API for a e-commerce store, built with Laravel 12 and Laravel Sail. This project features a complete e-commerce flow from product management to secure checkout with database transactions.

## 🚀 Features

-   **Role-Based Access Control (RBAC):** Distinct roles for Admins and Customers.
-   **Product Management:** Search, filtering (by category/price), pagination, and image uploads.
-   **Shopping Cart:** Database-driven cart with real-time stock validation.
-   **Checkout System:** Atomic database transactions to ensure data integrity during purchase.
-   **User Profile:** Management of profile details and a multi-address book system.
-   **Security:** API Token authentication using Laravel Sanctum.
-   **API Documentation:** Auto-generated interactive documentation using Scribe.

## 🛠️ Prerequisites

-   **Docker Desktop** installed and running on your machine.
-   **WSL2** (if using Windows).

## ⚙️ Installation & Setup

1. \*\*Clone the repository\*\* (or unzip the project folder):

    ```bash
    git clone <repository-url>
    cd <project-folder>
    ```

2. \*\*Start the application using Laravel Sail:

    ```Bash
    ./vendor/bin/sail up -d
    ```

3. \*\*Install PHP dependencies:

    ```Bash
    ./vendor/bin/sail composer install
    ```

4. \*\*Setup the database and seed test data:

    ```Bash
    ./vendor/bin/sail artisan migrate:fresh --seed
    ```

5. \*\*Link the storage (for product images):

    ```Bash
    ./vendor/bin/sail artisan storage:link
    ```

6. \*\*Generate the API Documentation:

    ```Bash
    ./vendor/bin/sail artisan scribe:generate
    ```

## Credentials

After running `./vendor/bin/sail artisan migrate:fresh --seed`, use these accounts:

- **Admin Account:**
  - Email: `admin@example.com`
  - Password: `password`

- **Customer Account:**
  - Email: `customer@example.com`
  - Password: `password`
