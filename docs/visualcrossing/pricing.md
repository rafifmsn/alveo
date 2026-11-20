# Visual Crossing Pricing & Plan Limits

Commercial use is permitted on all plans, including the Free tier.

## Core Features

| Feature | Free | Professional | Metered | Corporate | Enterprise |
|---|---|---|---|---|---|
| 15-day Weather Forecast | Yes | Yes | Yes | Yes | Yes |
| 50+ Years of History Data | Yes | Yes | Yes | Yes | Yes |
| Events (Hail, Tornadoes, etc.) | Yes | Yes | Yes | Yes | Yes |
| Sub-hourly Weather History | Yes | No | Yes | Yes | Yes |
| Weather Data Download | Yes | Yes | Yes | Yes | Yes |
| Weather API | Yes | Yes | Yes | Yes | Yes |
| Records / Month | 30,000 (1,000 records/day) | 10,000,000 | Unlimited | Unlimited (Fair use) | Unlimited |

## Weather API Limits

| Limit | Free | Professional | Metered | Corporate | Enterprise |
|---|---|---|---|---|---|
| Records / API Query | 1,000 | 10,000 | 25,000 | 25,000 | Custom |
| Locations / API Query | 5 | 100 | 5 | 1,000 | Custom |
| Maximum API Concurrency | 1 | 1 | Unlimited | 10 | Unlimited |

## Weather Maps Limits

| Limit | Free | Professional | Metered | Corporate | Enterprise |
|---|---|---|---|---|---|
| Access to Maps | Yes | Yes | Yes | Yes | Yes |
| Monthly Request Limit | 10,000 | 10,000 | Metered | 1,000,000 | Custom |

## Query Builder Dataset Limits

| Limit | Free | Professional | Metered | Corporate | Enterprise |
|---|---|---|---|---|---|
| Records / dataset | 1,000 | 50,000 | 1,000,000 | 5,000,000 | Custom |
| Locations / dataset | 5 | 25 | 1,000 | 5,000 | Custom |
| Dataset scheduling | No | Yes | No | Yes | Yes |

## Quota Notes for Alveo (Free Tier: 1,000 Records/Day)

Visual Crossing meters **records**, not raw HTTP requests.
A 3-day hourly query (`today/next2days` with `include=hours,current`) returns 72 hourly records (~72 records/call), which yields roughly 13 to 14 uncached location fetches per day before hitting the 1,000 records/day cap.
Keeping the `0.01` degree spatial cache with a 45-minute (or 60-minute) TTL prevents repeat requests for the same corridor, and sequential server-side calls respect the Free plan's `Maximum API Concurrency: 1` limit.
