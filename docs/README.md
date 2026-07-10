# SkillSwap Backend Documentation

## Overview
This folder contains all documentation specific to the Laravel backend of SkillSwap.

## Architecture
The backend is built using Laravel 12 and PHP 8.4+.
It follows a standard MVC pattern with the addition of:
- **Form Requests** for validation.
- **Resources** for API response transformation.
- **Policies** for authorization.
- **Sanctum** for API authentication.

## Setup Instructions
1. Install dependencies: `composer install`
2. Copy `.env.example` to `.env` and configure `DB_*` variables for PostgreSQL.
3. Generate app key: `php artisan key:generate`
4. Run migrations and seeders: `php artisan migrate --seed`
5. Start the server: `php artisan serve`

## API Design
The API follows RESTful principles.
- Base URL: `/api/v1`
- Content-Type: `application/json`

*Detailed API documentation will be generated using Swagger/OpenAPI.*
