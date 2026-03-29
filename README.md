# Unpublished Geocaches

Download GPX files for your unpublished geocaches from [geocaching.com](https://www.geocaching.com).

A hosted instance is available at **[unpublished.vaguelibre.net](http://unpublished.vaguelibre.net/)**.

---

## The problem

When you create a geocache on geocaching.com, it stays unpublished until a reviewer approves it. During that time, geocaching.com gives you no way to download a GPX file for it. This makes preparing events or powertrails painful — you can't load your own draft caches into a GPS or planning tool without a lot of manual copy-paste, even with tools like GSAK.

## What this does

This web app authenticates with your geocaching.com account via OAuth2 and lets you:

- **List all your unpublished caches** automatically on login
- **Search by geocode** to add specific caches (including published, locked, or archived ones)
- **Select** any combination of caches from the list
- **Generate one or several GPX files** — with optional splitting by a configurable number of caches per file
- **Download** the resulting GPX files directly to your device

Basic members can download full details for up to 3 geocaches per 24 hours; Premium members have no such limit.

---

## Technical overview

|               |                                                        |
| ------------- | ------------------------------------------------------ |
| **Backend**   | PHP 8.4, Symfony 8.0                                   |
| **Auth**      | Geocaching.com OAuth2 with PKCE                        |
| **Frontend**  | Twig, Bootstrap 5, Stimulus.js                         |
| **Storage**   | Stateless — no database; all state in session or files |
| **Container** | Docker (nginx + php-fpm)                               |

### How it works

1. User logs in → OAuth2 PKCE flow with geocaching.com → access token stored in session
2. App calls the Geocaching REST API to fetch unpublished caches
3. User selects caches and clicks "Create GPX"
4. App fetches full cache details, renders one XML waypoint file per cache, assembles them into GPX file(s), and returns download links
5. Generated GPX files live in `public/gpx/` and are cleaned up after 24 hours by a cron command

---

## Requirements

- PHP 8.4+
- Composer
- Node.js / npm
- A Geocaching.com API access

---

## Setup

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Configure environment

Create `.env.local` with your OAuth credentials:

```dotenv
GEOCACHING_OAUTH_KEY=your_client_id
GEOCACHING_OAUTH_SECRET=your_client_secret
GEOCACHING_CALLBACK=http://localhost:8000/callback
GEOCACHING_ENV=staging   # or: production
APP_SECRET=a_random_string
```

`GEOCACHING_ENV` controls which Geocaching API environment is used. Use `staging` for development and `production` for live deployments.

### 3. Run

**With Docker (recommended):**

```bash
docker-compose up
```

App is available at [http://localhost:8000](http://localhost:8000).

**Without Docker:**

```bash
symfony server:start
```

---

## Development

```bash
# Clear cache
php bin/console cache:clear

# Lint PHP config and templates
php bin/console lint:twig templates/
php bin/console lint:yaml config/

# Lint and format JavaScript (Biome)
npm run lint
npm run format

# Run tests
php bin/phpunit

# Delete generated GPX files older than 24h (run via cron in production)
php bin/console app:cleanup
```