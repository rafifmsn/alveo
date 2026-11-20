# Architecture Decision Records (ADR)

This document records the architectural tradeoffs and technical rationales behind key design decisions in Alveo.

## Selection of Visual Crossing Over WeatherAPI and Open-Meteo

The project initially evaluated Open-Meteo and WeatherAPI before transitioning to Visual Crossing for live atmospheric and air quality forecasts.

* **Ambiguous IP-Based Rate Limiting (Open-Meteo):** Open-Meteo's free tier does not issue API keys and enforces unauthenticated IP-based throttling, alongside non-commercial licensing restrictions.
* **Commercial Viability:** Visual Crossing explicitly permits commercial usage on all plans, including the Free tier (30,000 records/month, 1,000 records/day).
* **Real Hourly Air Quality (vs. WeatherAPI Free Tier):** WeatherAPI's free tier omits per-hour `air_quality` forecasts (`hour[].air_quality`), whereas Visual Crossing's Timeline API returns real hourly CAMS pollutant layers (`pm2p5`, `pm10`, `o3`, `no2`, `so2`, `co`, `aqius`, `aqieur`) alongside weather metrics for 3 days (`today/next2days`, ~72 hourly records) in a single HTTP request.
* **Predictable Keyed Quota:** Visual Crossing uses authenticated API keys, allowing Alveo to conserve its 1,000 records/day budget via spatial caching and sequential requests (`Maximum API Concurrency: 1`).

## On-Demand Database Caching Over Redis and Background Daemons

Alveo uses PostgreSQL database caching (`CACHE_STORE=database`) with coordinate grid rounding rather than standalone Redis instances or background cron daemons.

* **Zero Infrastructure Bloat:** PostgreSQL is already required for persistent relational models (users, routes, logs, and broadcasts).
  Adding Redis introduces another container daemon, additional memory footprint, and separate connection configurations for an application that easily handles its read volume within PostgreSQL.
* **Elimination of Unnecessary Server-Side Cron Jobs:** A background cron daemon polling forecasts for all routes every hour would burn through API records regardless of whether athletes are active.
  On-demand lazy caching queries Visual Crossing only when a user requests an uncached corridor.
* **Coordinate Grid Normalization:** Geographic coordinates are rounded to 0.01 degrees (approximately 1.1 km).
  Athletes inspecting nearby locations (such as adjacent gates at the GBK sports complex) hit the exact same cached payload.
* **60-Minute Time-To-Live:** Atmospheric dispersion changes on hourly timescales.
  A 60-minute database cache TTL guarantees fresh micro-meteorological data while conserving Visual Crossing's 1,000 records/day Free quota.

## Manual Account Verification Gate and Seeded Data Architecture

The application requires administrator account verification prior to dashboard access, implemented without outbound SMTP mail dependencies.

* **Zero SMTP DevOps Debt:** Setting up production email delivery requires domain reputation warmup, SPF/DKIM/DMARC records, and third-party transactional mail services.
  Alveo eliminates this overhead by omitting mandatory email verification tokens and handling activation at the database layer.
* **API Quota Protection:** Because Visual Crossing provides 1,000 records per day on the free tier, allowing automated public registrations could expose the application to malicious scraping or quota exhaustion.
  New registrations default to `email_verified_at = null` and are held at the `/verify` screen.
* **Direct Database Administration (Adminer):** Rather than maintaining custom CLI commands that require server terminal access and routine maintenance, administrators verify accounts directly using Adminer (`127.0.0.1:8080`) by setting `email_verified_at = now()`.
* **Seeded Data Architecture:**
  * `rafif@alveo.id` (Verified): Pre-verified athlete account for immediate exploration of all 6 Blade screens.
  * `pending@alveo.id` (Unverified): Pre-seeded unverified account for end-to-end testing of the `/verify` holding screen and manual `mailto:` request link.
  * Verified Hotspots: Six pre-configured Greater Jakarta running and cycling corridors (GBK Outer Perimeter, Sudirman Corridor, PIK Coastal Loop, BSD Green Boulevard, UI Depok Forest Trail, and Kelapa Gading Loop) with realistic distances and surface metadata.
  * Historical Logs and Dispatches: Pre-seeded training logs and club broadcasts demonstrating the 7-day exposure charts and Telegram dispatch formatting.

## Denormalized Follower Counter Cache in Club Broadcasts

Club broadcasts use a denormalized integer counter column (`followers_count`) alongside a relational join table (`club_broadcast_follows`).

* **O(1) Feed Listing Performance:** Querying active club clearances on the broadcast feed does not require executing dynamic `COUNT(*)` aggregations or group-by join queries for every card on page load.
* **Realistic Seeding Without User Bloat:** Community clubs in the initial database seeder carry realistic baseline followings (such as 42 members in Senayan Dawn Runners) without generating dozens of dummy user records in the `users` table.
* **Atomic Increment and Decrement:** When an active user clicks Follow or Unfollow, the system updates the `club_broadcast_follows` pivot table for ownership checks and atomically increments or decrements `followers_count` in a single transaction.

