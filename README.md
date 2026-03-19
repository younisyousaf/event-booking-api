# Event Booking API

A RESTful API built with Laravel 10 for managing events and seat bookings. Users can browse events, make bookings, and cancel them. The system handles seat availability automatically and prevents race conditions using database-level locking.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Environment Setup](#environment-setup)
- [Database Setup](#database-setup)
- [Running the App](#running-the-app)
- [Test Credentials](#test-credentials)
- [Application Flow](#application-flow)
- [API Reference](#api-reference)

---

## Requirements

- PHP >= 8.1
- Composer
- MySQL or MariaDB
- XAMPP (or any local server stack)

---

## Installation

```bash
# Clone the repository
git clone https://github.com/your-username/event-booking-api.git
cd event-booking-api

# Install dependencies
composer install
```

---

## Environment Setup

```bash
# Copy the example env file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Open `.env` and update the database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=event_booking
DB_USERNAME=root
DB_PASSWORD=
```

> If you are using XAMPP with default settings, `DB_USERNAME=root` and `DB_PASSWORD=` (empty) is correct.

Create the database manually in phpMyAdmin or via MySQL CLI:

```sql
CREATE DATABASE event_booking;
```

---

## Database Setup

Run all migrations to create the required tables:

```bash
php artisan migrate
```

This creates the following tables:

| Table                      | Purpose                               |
| -------------------------- | ------------------------------------- |
| `users`                    | Registered user accounts              |
| `personal_access_tokens`   | Sanctum API tokens                    |
| `events`                   | Event listings with seat tracking     |
| `bookings`                 | Booking records with status management|

### Seed Sample Data

```bash
php artisan db:seed
```

This creates:

- 2 test users (admin + regular user)
- 8 additional randomly generated users
- 22 sample events (15 upcoming, 5 past, 2 sold-out)

To reset and reseed from scratch:

```bash
php artisan migrate:fresh --seed
```

---

## Running the App

```bash
php artisan serve
```

API is available at `http://localhost:8000/api`

---

## Test Credentials

These accounts are created automatically by the seeder:

| Role  | Email                   | Password |
| ----- | ----------------------- | -------- |
| Admin | `admin@example.com`     | password |
| User  | `user@example.com`      | password |

Use either account to log in and receive a Bearer token for authenticated requests.

---

## Application Flow

### 1. Authentication

Register a new account via `POST /api/auth/register`, then login via `POST /api/auth/login`. The login response includes a `token`. Pass it in every authenticated request as:

```text
Authorization: Bearer {token}
```

Logout via `POST /api/auth/logout` to invalidate the token.

### 2. Browsing Events

Anyone can list and view events without logging in. Events can be filtered by **date** and/or **location**, and results are paginated (default 15 per page):

```text
GET /api/events
GET /api/events?date=2026-06-15
GET /api/events?location=Paris
GET /api/events?date=2026-06-15&location=Paris
GET /api/events?per_page=5&page=2
```

### 3. Creating a Booking

Browse events and pick one with available seats. Send a `POST /api/bookings` request with the event ID and how many seats you want. The system checks that the event has not passed and that enough seats are available. On success, `available_seats` on the event is decremented automatically inside a database transaction.

### 4. How Seat Availability Works

Each event stores `total_seats` and `available_seats`. When a booking is confirmed, `available_seats` is reduced by the seats booked. When a booking is cancelled, the seats are returned. Bookings use **pessimistic locking** (`lockForUpdate`) so two users cannot book the last seat at the same time. An event with `available_seats = 0` is returned with `"is_sold_out": true`.

### 5. Cancelling a Booking

Send `PATCH /api/bookings/{id}/cancel`. Only the user who created the booking can cancel it — the `EnsureBookingOwner` middleware enforces this. Already cancelled bookings cannot be cancelled again, and seats are restored to the event immediately.

---

## API Reference

### Required Headers

```text
Accept: application/json
Content-Type: application/json
```

For authenticated routes, also include:

```text
Authorization: Bearer {token}
```

---

### Auth Endpoints

| Method | Endpoint              | Auth Required | Description                          |
| ------ | --------------------- | ------------- | ------------------------------------ |
| POST   | `/api/auth/register`  | No            | Register a new user                  |
| POST   | `/api/auth/login`     | No            | Login and receive a token            |
| GET    | `/api/auth/me`        | Yes           | Get the authenticated user's profile |
| POST   | `/api/auth/logout`    | Yes           | Logout and revoke the token          |

**Register body:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Login body:**

```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

---

### Event Endpoints

| Method    | Endpoint             | Auth Required | Description                    |
| --------- | -------------------- | ------------- | ------------------------------ |
| GET       | `/api/events`        | No            | List upcoming events (paginated)|
| GET       | `/api/events/{id}`   | No            | Get a single event             |
| POST      | `/api/events`        | Yes           | Create a new event             |
| PUT/PATCH | `/api/events/{id}`   | Yes           | Update an event                |
| DELETE    | `/api/events/{id}`   | Yes           | Delete an event                |

**Create/Update event body:**

```json
{
    "title": "Laravel Conference 2026",
    "description": "Annual Laravel developer conference.",
    "location": "Paris, France",
    "event_datetime": "2026-09-15 09:00:00",
    "total_seats": 200
}
```

**Available query parameters for listing:**

| Param      | Type    | Example        | Description                                      |
| ---------- | ------- | -------------- | ------------------------------------------------ |
| `date`     | string  | `2026-06-15`   | Filter by exact date                             |
| `location` | string  | `Paris`        | Filter by location (partial match, case-insensitive) |
| `per_page` | integer | `10`           | Results per page (1–100, default 15)             |
| `page`     | integer | `2`            | Page number                                      |

---

### Booking Endpoints

| Method | Endpoint                      | Auth Required | Description                  |
| ------ | ----------------------------- | ------------- | ---------------------------- |
| GET    | `/api/bookings`               | Yes           | List your bookings (paginated)|
| POST   | `/api/bookings`               | Yes           | Create a booking             |
| GET    | `/api/bookings/{id}`          | Yes           | Get a single booking         |
| PATCH  | `/api/bookings/{id}/cancel`   | Yes           | Cancel a booking             |

**Create booking body:**

```json
{
    "event_id": 1,
    "seats_booked": 2
}
```

---

## Tech Stack

| Layer           | Technology                    |
| --------------- | ----------------------------- |
| Framework       | Laravel 10                    |
| Authentication  | Laravel Sanctum (token-based) |
| Database        | MySQL / MariaDB               |
| Validation      | Laravel Form Request classes  |
| API Responses   | Laravel API Resources         |
