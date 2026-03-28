# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A Symfony 7.0 PHP web application that lets geocachers download GPX files for unpublished geocaches via the Geocaching.com OAuth2 API. Stateless (no database) — all state lives in sessions or files.

## Common Commands

```bash
# Install dependencies
composer install
npm install

# Start development (Docker recommended)
docker-compose up          # Access at http://localhost:8000

# Or local dev server
symfony server:start

# Clear cache
php bin/console cache:clear

# Lint
php bin/console lint:twig templates/
php bin/console lint:yaml config/
npx eslint .

# Tests
php bin/phpunit

# Cleanup old GPX files (runs via cron in production)
php bin/console app:cleanup
```

## Architecture

### Request Flow

1. **Auth**: User → `OAuthController::login()` → Geocaching OAuth PKCE → `callback()` → `GeocachingAuthenticator` validates token → `User` stored in session with `ROLE_BASIC` or `ROLE_PREMIUM`

2. **API queries**: `DefaultController` → `GeocachingService::checkAndRefreshToken()` → `UnpublishedService` → Geocaching SDK → JSON response

3. **GPX generation**: `DefaultController::getCreateGpx()` → fetches full cache details → renders `waypoint.xml` per cache to `/waypoints/` → chunks into GPX files → renders `geocaches.xml` → pretty-prints with DomDocument → writes to `/public/gpx/` → returns download URLs

### Key Files

- `src/Controller/DefaultController.php` — main routes: `/`, `/unpublished` (GET/POST), `/createGpx`
- `src/Controller/OAuthController.php` — `/login`, `/callback`, `/logout`
- `src/Service/GeocachingService.php` — wraps Geocaching SDK, handles token refresh
- `src/Service/UnpublishedService.php` — queries unpublished caches with rate limiting/pagination
- `src/Security/GeocachingAuthenticator.php` — OAuth2 authenticator, sets user roles
- `config/packages/security.yaml` — firewall, role hierarchy, access control (`/unpublished` and `/createGpx` require `ROLE_BASIC`)
- `config/packages/knpu_oauth2_client.yaml` — OAuth2 provider config

### Environment Variables

Required in `.env.local` (not committed):
- `GEOCACHING_OAUTH_KEY` — OAuth client ID
- `GEOCACHING_OAUTH_SECRET` — OAuth client secret
- `GEOCACHING_CALLBACK` — OAuth callback URL
- `GEOCACHING_ENV` — API environment (`staging` or `production`)

The `.env.dev` file sets the callback to `http://localhost:8000/callback` and uses the staging environment.

### Generated Files

- `/public/gpx/` — generated GPX downloads (served statically, cleaned up after 24h)
- `/waypoints/` — intermediate per-cache XML files (cleaned up by `CleanupCommand`)

### Frontend

Uses Symfony Asset Mapper (not Webpack). JS modules declared in `importmap.php`. Stimulus.js for interactivity. Bootstrap 5 + jQuery.
