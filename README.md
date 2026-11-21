# Alveo

[![Laravel](https://img.shields.io/badge/Laravel-11%20%7C%2012-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=flat-square&logo=postgresql&logoColor=white)](https://www.postgresql.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.4-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![DaisyUI](https://img.shields.io/badge/DaisyUI-4.12-5A0EF8?style=flat-square)](https://daisyui.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-064E3B?style=flat-square)](LICENSE)

Alveo is an aerobic clean-air window scheduler and pulmonary exposure engine built for outdoor athletes.
It helps runners, cyclists, and community fitness groups pinpoint the cleanest training windows across urban areas like Greater Jakarta where morning particulate spikes and surface inversions pose respiratory hazards.

Instead of passive historical pollution charts, Alveo translates atmospheric particulate concentrations into active pulmonary exposure calculations.
It computes minute-ventilation across athletic heart rate zones, evaluates contiguous hourly forecast blocks, delivers binary Go or Postpone recommendations, and offloads reminders via native calendar events and Telegram dispatch cards.

## Quickstart (Local Docker)

1. Clone the repository and enter the directory:
   ```bash
   git clone https://github.com/rafifmsn/alveo.git
   cd alveo
   ```
2. Copy the environment file and set your Visual Crossing API key:
   ```bash
   cp .env.example .env
   ```
   Add your API key to `VISUALCROSSING_KEY` in `.env` (optional; the built-in deterministic model serves fallback data if left blank).
   Customize `SEED_ADMIN_*` and `SEED_USER_*` in `.env` if you want non-default seeded credentials.
3. Start the containers using Docker Compose:
   ```bash
   docker compose up -d --build
   ```
   On startup, `docker/entrypoint.sh` waits for `db`, runs migrations, and executes `DatabaseSeeder` idempotently.
4. Access the web services:
   - Main Application: [http://localhost:8085](http://localhost:8085)
   - Database UI (Adminer): [http://localhost:8080](http://localhost:8080) (System: PostgreSQL, Server: `db`, Username: `alveo`, Password: `alveo_secret`, Database: `alveo`)
5. Sign in using the seeded accounts (derived from `SEED_ADMIN_*` and `SEED_USER_*` in `.env`):
   - Administrator: `admin@alveo.id` / `password` (`/admin`)
   - Verified Athlete: `rafif@alveo.id` / `password` (`/finder`)
   - Newly registered accounts are verified by default so evaluators can immediately access `/finder`.

## Useful Docker CLI Commands

When running the application stack via Docker Compose, you can execute maintenance and development tasks against the `app` container using the commands below:

```bash
# Seed or re-seed initial data (admin, athlete, 3 presets, logs, and 1 broadcast)
docker compose exec app php artisan db:seed

# Clear application forecast cache (stored in the PostgreSQL cache table)
docker compose exec app php artisan cache:clear

# Clear cached configuration, routes, and compiled views
docker compose exec app php artisan optimize:clear

# Run pending database migrations
docker compose exec app php artisan migrate

# Fresh migration with re-seeding (resets all tables to clean state)
docker compose exec app php artisan migrate:fresh --seed

# Run the automated test suite
docker compose exec app php artisan test

# Run code styling checks and autofix with Laravel Pint
docker compose exec app ./vendor/bin/pint

# Rebuild frontend assets (Tailwind CSS and Vite)
docker compose exec app npm run build

# Open an interactive PHP Tinker shell
docker compose exec app php artisan tinker

# Open a shell inside the running application container
docker compose exec app bash
```

## Production Deployment (Render + GHCR + External PostgreSQL)

Alveo publishes a single-container image to GitHub Container Registry (`ghcr.io/rafifmsn/alveo:latest`) whenever tests pass on `main`.

1. Ensure the `alveo` package visibility on GitHub Packages is set to **Public** so Render can pull it without registry credentials.
2. In Render, create a **Web Service** configured with **Deploy an existing image from a registry** pointing to `ghcr.io/rafifmsn/alveo:latest`.
3. Configure these required Environment Variables in Render:
   ```env
   APP_NAME=Alveo
   APP_ENV=production
   APP_KEY=base64:QWx2ZW9DbGVhbkFpcldpbmRvd0VuZ2luZTIwMjZLRVk=
   APP_DEBUG=false
   APP_TIMEZONE=Asia/Jakarta
   APP_URL=https://your-service-name.onrender.com

   LOG_CHANNEL=stderr
   LOG_LEVEL=error

   DB_CONNECTION=pgsql
   DB_URL=postgresql://user:password@your-postgres-host/neondb?sslmode=require&channel_binding=require
   DB_SSLMODE=require

   SESSION_DRIVER=database
   CACHE_STORE=database

   VISUALCROSSING_KEY=your_visual_crossing_key
   VISUALCROSSING_CACHE_TTL=3600

   SEED_ADMIN_NAME="Alveo Admin"
   SEED_ADMIN_EMAIL=admin@alveo.id
   SEED_ADMIN_PASSWORD=password
   SEED_USER_NAME="Rafif Muchsin"
   SEED_USER_EMAIL=rafif@alveo.id
   SEED_USER_PASSWORD=password
   ```
4. Database migration and seeding work in two ways:
   - **Automatic on first boot**: `docker/entrypoint.sh` skips the local `pg_isready` host check whenever `DB_URL` is set, runs `php artisan migrate --force`, and runs `php artisan db:seed --force` automatically against your remote PostgreSQL database.
   - **Manual seed from local container (optional)**: If you want to initialize a fresh remote PostgreSQL database from your machine before deploying, start the local container (`docker compose up -d`) and run:
     ```bash
     docker compose exec -e DB_URL="your-postgres-url" app php artisan migrate --force
     docker compose exec -e DB_URL="your-postgres-url" app php artisan db:seed --force
     ```
   - **In-app reset**: Once deployed, administrators can wipe and re-seed demo data anytime from `/admin/system` without terminal access.

## High-Level Architecture Overview

```mermaid
flowchart LR
    Athlete([Athlete / Admin]) -->|HTTPS / Browser| UI[Blade Screens + DaisyUI]
    UI --> Router[Laravel Web & API Routes]
    Router --> RoleGate{Role & Access Check}
    RoleGate -->|Admin| AdminPanel["/admin Control Plane"]
    RoleGate -->|Verified Athlete| Controllers[Domain Controllers]
    Controllers --> Engine[ExposureEngine & Dosimetry]
    Engine --> CacheRepo[(PostgreSQL Cache)]
    CacheRepo -->|Cache Hit < 60m| Response[Calculated Verdict & Action Links]
    CacheRepo -->|Cache Miss| VisualCrossing[Visual Crossing Timeline API]
    VisualCrossing -->|Hourly AQI + Wind| CacheRepo
```

Alveo operates as a stateless computational pipeline with on-demand database caching.
Requests pass through Laravel session authentication and role-based route protection (`admin` vs. `athlete.page` and `verified.account`) before reaching domain controllers.
Atmospheric forecasts are cached in PostgreSQL for 60 minutes per coordinate grid, eliminating redundant upstream calls and keeping the system within the Visual Crossing Free Tier (1,000 records/day).

## Documentation Index

Explore the detailed architecture and engineering rationale in the documentation folder:

- [Architecture Guide](docs/architecture.md): Complete technical breakdown, component directory cheatsheet, dosimetry formulas, and database schemas.
- [Architecture Decision Records](docs/decisions.md): Engineering rationales for API provider selection, caching strategy, and role-separated account lifecycle.
- [Visual Crossing Documentation Map](docs/visualcrossing/INDEX.md): Line-by-line index of Timeline API parameters, response elements, and air quality schemas.
- [Visual Crossing Pricing & Limits](docs/visualcrossing/pricing.md): Plan comparison, 1,000 records/day quota details, and concurrency constraints.
