# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Spending Tracker: a Symfony 8 (PHP 8.5) web app, run entirely through `docker-compose.yml` (nginx `spending-tracker-webserver` on host port **26000** + `spending-tracker-php-fpm` + Postgres `db`). It started from a Symfony skeleton: no Symfony Flex, so bundles are registered by hand in `config/bundles.php` and `config/packages/*.yaml` is hand-maintained.

Current scope: auth plus the app shell (sidebar, home dashboard with fake data, sync screens, settings); real spending data comes later. Opening `http://localhost:26000/` shows the login/signup screen; once logged in you get the sidebar layout.

## Commands

All PHP tooling runs inside the `spending-tracker-php-fpm` container through wrapper scripts in `docker/`. Bring containers up first: `docker compose up -d --build`.

- `docker/composer <args>`: composer
- `docker exec -t spending-tracker-php-fpm php bin/console <args>`: Symfony console (no wrapper)
- `docker/phpunit <args>`: tests (`tests/Unit`, `tests/Integration`, `tests/Functional`; base classes in `tests/Common/`)
- `docker/phpstan analyse`: static analysis (config: `phpstan.neon`)
- `docker/php-cs-fixer`: auto-fix style (config: `.php-cs-fixer.dist.php`)
- `docker/quality-check`: cs-fixer (dry-run) + phpstan + phpunit in sequence, mirrors CI (`.github/workflows/.github-ci.yml`)
- `docker/shell`: bash in the php-fpm container

## Definition of done

A task is done only when:

1. `docker/quality-check` passes (cs-fixer, phpstan, phpunit), with new/changed behavior covered by tests.
2. The feature works in the running app at `http://localhost:26000` (check it, don't just assume); for signup changes, check all three states (open, invite-only, disabled).
3. `CLAUDE.md` is updated for anything a future session needs to know (see "Keeping this file current"). Do not touch `README.md`.
4. No secrets committed: real values belong in `.env.local` (gitignored), never `.env`.

## Architecture

- **Docker**: `docker-compose.yml` (use `docker-compose`, the `docker compose` plugin isn't installed here) runs `webserver` (nginx, host 26000), `php-fpm` and `db` (`postgres:16-alpine`, not exposed to the host, user/password/db all `spending_tracker`, named volume `spending-tracker-db-data`). If php-fpm is recreated, `docker restart spending-tracker-webserver` (nginx caches its IP and returns 502).
- **Database**: schema is NOT managed by Doctrine migrations. It lives in `db/schema.sql`, loaded by `db/init-db.sh` (drops and recreates the DB; only run deliberately). Entities in `src/Entity/` map onto it by hand: when changing a column, edit `schema.sql` and the entity together. `DATABASE_URL` is in `.env`.
- **Env**: `.env` holds safe defaults, `.env.local` (gitignored) holds real values. `scripts/setup.sh` writes `APP_ENV=prod`, `APP_DEBUG=0`, random `APP_SECRET`. `scripts/enable_invite_only.sh` writes a random `INVITE_SECRET` and prints the invite link (re-run to rotate). Both use an idempotent `set_env_var` helper.
- **Auth**: `User` entity (`users` table: id, username, password, email, country (nullable ISO code), created_at; bcrypt). `config/packages/security.yaml`: `form_login` at `/login` (CSRF on), `remember_me`, logout at `/logout`; `access_control` makes `/login`, `/signup`, `/status` public and gates everything else (`^/`) behind `ROLE_USER` (anonymous goes to `/login` via the firewall entry point). Config in `config/packages/` is hand-maintained (no Flex).
  - `SecurityController` (`/login`, `/logout`), `RegistrationController` (`/signup`, form `RegistrationFormType`, auto-logs-in after signup via `Security::login($user, 'form_login')`).
  - **Signup states**: `src/Security/SignupGate` + `SignupState` read `INVITE_SECRET` (param `app.invite_secret`): empty = open, `disabled` = off, anything else = invite-only (`/signup?invite=<value>`, compared with `hash_equals`). Controller returns 404 "closed" for disabled and 403 "invite required" for a missing/wrong invite, both rendered by `templates/security/signup_unavailable.html.twig`. The login page's "Sign up" button is a plain link to `/signup`, so the check happens there.
- **App shell**: `templates/app/layout.html.twig` (extended by every logged-in page) = indigo frame with a left sidebar (avatar with the first two letters of the username, nav icons, logout at the bottom) wrapping the main panel. Child templates set `{% set active_nav = 'home|expenses|sync|settings' %}` to highlight the nav item. Icons are a Twig macro in `templates/app/_icons.html.twig`; **macro imports are not inherited**, so each child template that uses icons must `{% import 'app/_icons.html.twig' as icons %}` itself.
- **Pages**: `/` Home (`HomeController`: if the user has no country set it shows only a centered "Choose country" button to `/settings/users` (`home/choose_country.html.twig`); otherwise big Sync button to `/sync`, line chart + last-month donut, both with hover tooltips via inline JS in `home/index.html.twig`; data is fake, from `src/Dashboard/FakeSpendingData`, SVG geometry computed server-side by `ChartGeometry`, no JS libs), `/expenses` (empty), `/sync` (Import from bank -> `/sync/bank`; Import manually disabled), `/sync/bank` (bank picker, only "OTP banka (HR)", logo in `public/images/banks/`, click does nothing yet), `/settings` -> `/settings/users` (first tab: country select via `CountryFormType` + change-password form `ChangePasswordFormType`, both handled in `SettingsController`) and `/settings/classes` (empty). Countries come from the hand-kept list in `src/Country/Countries.php` (symfony/intl isn't installed). Existing DBs need `ALTER TABLE users ADD COLUMN country CHAR(2)` (schema.sql already has it).
- **Templates**: all extend `templates/base.html.twig`, which holds the CSS inline (no asset pipeline).
- `GET /status` (`StatusController`) returns `{"status":"ok"}`.
- **Tests**: `tests/Unit` (e.g. `SignupGateTest`), `tests/Functional` (curl against the running stack via `BASE_LOCAL_URL`, helpers in `tests/Common/FunctionalTestCase.php`; requires containers up). phpstan is level 9.

## Keeping this file current

**When you add or change a feature, update this file in the same change** so the next session doesn't have to re-explore. Specifically:

- Add or adjust the entry in "Architecture" with the key files/classes, routes, env vars, and non-obvious decisions (the *why*, not a file listing).
- Record new commands, scripts, services/ports, env vars (and whether they live in `.env` or `.env.local`), and database/schema conventions (how the schema is managed and where).
- Remove or correct anything that is no longer true. Keep it concise; don't duplicate what the code makes obvious.
- **Never edit `README.md`**; the user maintains it themselves. Put anything a future session needs in this file instead.
