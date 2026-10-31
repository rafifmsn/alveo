# Architecture Decision Records (ADR)

This document records the architectural tradeoffs and technical rationales behind key design decisions in Alveo.

## Selection of WeatherAPI Over Open-Meteo

The project initially evaluated Open-Meteo for atmospheric and air quality forecasts but transitioned to WeatherAPI for production viability.

* **Ambiguous IP-Based Rate Limiting:** Open-Meteo's free tier does not issue API keys and enforces unauthenticated IP-based throttling.
  On shared hosting, cloud VPS providers, or container clusters sharing an outbound gateway IP, unexpected 429 rate limit errors can occur when other tenants consume the pool.
* **Commercial Viability:** Open-Meteo restricts its free tier to non-commercial usage.
  WeatherAPI explicitly permits commercial and production applications within its 100,000 monthly free tier quota.
* **Predictable Keyed Quota:** WeatherAPI uses authenticated API keys.
  This allows Alveo to monitor usage accurately and avoid noisy neighbor issues.
* **Complete Pollutant Suite in One Query:** WeatherAPI returns current atmospheric conditions, 3 days of hourly forecasts, and full air quality metrics (`pm2_5`, `pm10`, `o3`, `no2`, `so2`, `co`, `us-epa-index`, and `gb-defra-index`) in a single HTTP request.

## On-Demand Database Caching Over Redis and Background Daemons

Alveo uses PostgreSQL database caching (`CACHE_STORE=database`) with coordinate grid rounding rather than standalone Redis instances or background cron daemons.

* **Zero Infrastructure Bloat:** PostgreSQL is already required for persistent relational models (users, routes, logs, and broadcasts).
  Adding Redis introduces another container daemon, additional memory footprint, and separate connection configurations for an application that easily handles its read volume within PostgreSQL.
* **Elimination of Unnecessary Server-Side Cron Jobs:** A background cron daemon polling forecasts for all routes every hour would burn through API calls regardless of whether athletes are active.
  On-demand lazy caching queries WeatherAPI only when a user requests an uncached corridor.
* **Coordinate Grid Normalization:** Geographic coordinates are rounded to 0.01 degrees (approximately 1.1 km).
  Athletes inspecting nearby locations (such as adjacent gates at the GBK sports complex) hit the exact same cached payload.
* **45-Minute Time-To-Live:** Atmospheric dispersion changes on hourly timescales.
  A 45-minute database cache TTL guarantees fresh micro-meteorological data while preventing redundant API consumption.

## Manual Account Verification Gate and Seeded Data Architecture

The application requires administrator account verification prior to dashboard access, implemented without outbound SMTP mail dependencies.

* **Zero SMTP DevOps Debt:** Setting up production email delivery requires domain reputation warmup, SPF/DKIM/DMARC records, and third-party transactional mail services.
  Alveo eliminates this overhead by omitting mandatory email verification tokens and handling activation at the database layer.
* **API Quota Protection:** Because WeatherAPI provides 100,000 requests per month on the free tier, allowing automated public registrations could expose the application to malicious scraping or quota exhaustion.
  New registrations default to `email_verified_at = null` and are held at the `/verify` screen.
* **Direct Database Administration (Adminer):** Rather than maintaining custom CLI commands that require server terminal access and routine maintenance, administrators verify accounts directly using Adminer (`127.0.0.1:8080`) by setting `email_verified_at = now()`.
* **Seeded Data Architecture:**
  * `rafif@alveo.id` (Verified): Pre-verified athlete account for immediate exploration of all 6 Blade screens.
  * `pending@alveo.id` (Unverified): Pre-seeded unverified account for end-to-end testing of the `/verify` holding screen and manual `mailto:` request link.
  * Verified Hotspots: Six pre-configured Greater Jakarta running and cycling corridors (GBK Outer Perimeter, Sudirman Corridor, PIK Coastal Loop, BSD Green Boulevard, UI Depok Forest Trail, and Kelapa Gading Loop) with realistic distances and surface metadata.
  * Historical Logs and Dispatches: Pre-seeded training logs and club broadcasts demonstrating the 7-day exposure charts and Telegram dispatch formatting.

