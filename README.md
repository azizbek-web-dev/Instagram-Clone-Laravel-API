# Instagram Clone - Laravel API

Laravel backend API for Instagram Clone React frontend.

## Requirements

- PHP 8.2+
- Composer
- SQLite (default) or MySQL

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Run Server

```bash
php artisan serve
```

API: http://localhost:8000

## API Endpoints

### Auth

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/auth/register | Register new user |
| POST | /api/auth/login | Login |
| POST | /api/auth/logout | Logout (Bearer token required) |

### Register Request
```json
{
  "name": "Jacob West",
  "email": "jacob@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Login Request
```json
{
  "email": "jacob@example.com",
  "password": "password123"
}
```

### Response (Register/Login)
```json
{
  "user": {...},
  "token": "1|xxx...",
  "token_type": "Bearer"
}
```

### Protected Routes
Add header: `Authorization: Bearer {token}`

## Frontend

React frontend: https://github.com/azizbek-web-dev/Instagram-Clone

## Tech Stack

- Laravel 12
- Laravel Sanctum (API Authentication)
- SQLite
