# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

E-commerce platform built with **Laravel 12** (backend/admin) + **React 19** (frontend) connected via **Inertia.js**. It has two distinct user-facing surfaces:
- **Admin panel** at `/backend` — powered by Filament 4
- **Customer storefront** at `/` — React pages served via Inertia

## Commands

### Backend (PHP/Laravel)

```bash
# Initial setup (install dependencies + migrate)
composer run setup

# Start all dev services concurrently (server, queue, logs, Vite watcher)
composer run dev

# Run tests
composer run test

# Run a single test file
php artisan test tests/Feature/ExampleTest.php

# Run tests with filter
php artisan test --filter=TestName

# Run migrations
php artisan migrate

# PHP code style (Pint)
./vendor/bin/pint
```

### Frontend (Node/Vite)

```bash
# Start Vite dev server only
npm run dev

# Production build
npm run build

# Lint
npm run lint

# Auto-fix lint issues
npm run lint:fix
```

### Docker

```bash
# Start all containers
docker compose up -d

# The app is available at http://localhost:8000
# Vite dev server at http://localhost:5173
```

The Docker setup includes 4 services: `app` (PHP 8.3-FPM), `node` (Vite), `nginx` (port 8000), and `mysql` (port 3306).

## Architecture

### Backend Structure

- `app/Models/` — Eloquent models. Key models: `Product`, `ProductVariant`, `Customer`, `Order`, `OrderItem`, `Coupon`, `CouponUsage`, `Category`, `CategoryGroup`, `Tag`
- `app/Http/` — Controllers and middleware
- `app/Filament/` — Admin panel resources, pages, and widgets (auto-discovered)
- `app/Services/` — Business logic (e.g., `CouponService` for discount validation/application)
- `app/Enums/` — PHP enums for domain values: `ProductTypeEnum`, `OrderStatusEnum`, `PaymentStatusEnum`, `CouponTypeEnum`, `CouponScopeEnum`, `DiscountTypeEnum`
- `routes/web.php` — Inertia-rendered frontend routes
- `routes/api.php` — REST API for customer authentication (Sanctum tokens)

### Authentication Layers

Two separate auth systems coexist:
1. **Admin users** (`users` table) — standard Laravel auth for Filament panel
2. **Customers** (`customers` table) — API token auth via Laravel Sanctum

Customer API endpoints (`/api/customer/`): `register`, `login`, `logout`, `me`, `profile`

### Frontend Structure

- `resources/js/Pages/` — Inertia page components (resolved by `import.meta.glob`)
- `resources/js/Shared/` — Shared layout, header, sidebar, contexts, hooks
- State managed via React Context API (`CategoryContext`, `SideBarContext`)
- Custom hooks: `useSidebar`, `useCategories`, `useInfinity`
- HTTP requests via Axios (configured in `resources/js/bootstrap.js`)

### Inertia.js Integration

Laravel routes return Inertia responses that map to React page components. The `HandleInertiaRequests` middleware shares server-side data with the frontend. Page components live in `resources/js/Pages/` and are auto-resolved by component name.

### Media Handling

Spatie Media Library is used for product assets. Images are converted to WebP with a `thumb` conversion (368×232px).

### Database

SQLite for testing (in-memory), MySQL 8.0 for development/production. The schema centers around the e-commerce domain: products with variants (referencing `colors` and `sizes`), hierarchical categories, orders with line items, and a flexible coupon system supporting percentage/fixed discounts scoped to general/category/product levels with per-user and total usage limits.

## Key Conventions

- Use PHP enums (in `app/Enums/`) for all domain type values — never raw strings
- Filament resources go in `app/Filament/Resources/` and are auto-discovered
- New API endpoints for customers go in `routes/api.php` under the `api/customer/` prefix with Sanctum auth middleware
- Vite is configured for Docker (polling enabled, `0.0.0.0:5173` host)
- Testing uses SQLite in-memory — configure via `phpunit.xml` env overrides
