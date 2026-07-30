# Activity Tracker

## Project overview

Activity Tracker is a pure PHP application with registration, authentication, two
tracked user pages, event-level administration statistics, and pre-aggregated daily
reports.

This is a monolithic application by deployment, but modular by design.

I intentionally avoided using a framework because the role requires pure PHP.
However, I kept explicit architectural boundaries between HTTP controllers,
application use cases, domain objects and infrastructure repositories.

## Implemented requirements

- Registration
- Login
- Logout
- Authenticated Page A with “Buy a cow” button
- User-specific “thankYou” state after clicking “Buy a cow”
- Authenticated Page B with “Download” button
- `.exe` file download flow
- Activity tracking for:
  - login
  - logout
  - registration
  - view-page
  - button-click
- Admin-only statistics page with:
  - date filters
  - exact user email filter
  - action filter
  - paginated activity event table
- Admin-only reports page with:
  - daily SVG graph
  - daily report table
  - Page A views
  - Page B views
  - Buy a cow clicks
  - Download clicks

## Stack

- PHP 8.3 FPM
- Nginx
- MySQL 8.4
- Composer 2 with PSR-4 autoloading
- Vanilla JavaScript and SVG
- Docker Compose

There is no PHP or frontend framework and no npm build.

## Running with Docker

Requirements: Docker with the Compose plugin and `make`.

Copying `.env.example` is optional because Docker Compose provides development
defaults. To change credentials or application settings:

```bash
cp .env.example .env
```

Build the containers, install Composer dependencies, migrate the database, and seed
the users:

```bash
make setup
```

Open <http://localhost:8080>.

The equivalent manual commands are:

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php bin/migrate.php
docker compose exec app php bin/seed.php
```

Useful lifecycle commands:

```bash
make up
make down
make migrate
make seed
```

`make down` preserves MySQL data. Use `docker compose down --volumes` only when you
intentionally want to delete the local database.

## Default users

| Role | Email | Password |
|---|---|---|
| Admin | `admin@example.com` | `password` |
| User | `user@example.com` | `password` |

The seeder is idempotent. It inserts missing accounts and does not overwrite an
existing account's password or role.

## Routes

| Method | Path | Access | Description |
|---|---|---|---|
| GET | `/` | Public | Redirect to login or Page A based on authentication |
| GET | `/register` | Guest | Registration form |
| POST | `/register` | Guest | Create account |
| GET | `/login` | Guest | Login form |
| POST | `/login` | Guest | Authenticate user |
| POST | `/logout` | Authenticated | Logout |
| GET | `/page-a` | Authenticated | Page A with Buy a cow flow |
| POST | `/page-a/buy-cow` | Authenticated | Track Buy a cow click |
| GET | `/page-b` | Authenticated | Page B with Download button |
| POST | `/page-b/download` | Authenticated | Track download and return sample.exe |
| GET | `/admin/stats` | Admin | Activity statistics |
| GET | `/admin/reports` | Admin | Daily reports |

## Architecture decision

The application is one deployable unit organized as a modular monolith:

```text
public/index.php
        |
Shared Kernel (HTTP, routing, security, database, views)
        |
+-------+----------+----------+-----------+
| Auth  | Activity | Pages    | Reporting |
+-------+----------+----------+-----------+

Inside each module:

Presentation -> Application -> Domain
Infrastructure -> implements repository contracts
```

The dependency direction is kept explicit: controllers depend on application use cases, application use cases depend on domain contracts, and infrastructure implements those contracts using PDO.

- Domain objects and repository contracts do not depend on PDO, HTTP, sessions, or
  templates.
- Application handlers implement and orchestrate use cases.
- Infrastructure repositories contain prepared PDO queries.
- Presentation controllers translate HTTP input to use cases and responses.
- Views render escaped values and contain no data access.
- `Application` is the composition root; its small container explicitly wires
  dependencies.

## Module explanation

### Shared

`Shared/Kernel` contains the front-controller application, router, request and
response types, dependency container, PDO connection, transaction manager, session
wrapper, CSRF tokens, password hashing, authenticated user representation, and view
renderer.

### Auth

Owns users and roles. Registration validates input and hashes the password
before opening the database transaction. The user insert, registration activity event,
and relevant daily aggregate update are written atomically.
Login verifies a password and regenerates the session ID.
Logout clears authentication even if activity tracking fails.

### Activity

Owns the append-only event model, tracking use case, and paginated event search.
`ActivityTracker` writes the raw event and updates its daily aggregate in one
database transaction.

### Pages

Owns Page A and Page B use cases. Page A uses a unique user/page/action state row,
making “Buy a cow” user-specific and idempotent. Page B always downloads the fixed
server-side `public/download/sample.exe`; request input cannot select a path.

### Reporting

The statistics page reads raw events in pages of 50. The reports page reads daily
aggregate rows and renders a table plus an SVG graph with vanilla JavaScript.

## Database design

### `users`

Stores unique email, password hash, `user`/`admin` role, and creation time. The role
column is indexed.

### `user_page_states`

The composite primary key `(user_id, page, action)` is both the lookup index and the
idempotency constraint for Page A.

### `activity_events`

Append-only audit/event data. It has indexes for chronological queries and the
required user, action, and page/target filter paths:

- `created_at`
- `(user_id, created_at)`
- `(action, created_at)`
- `(page, target, created_at)`
- `(user_id, action, created_at)` for combined administration filters

### `daily_activity_reports`

One row per date with four counters. Relevant events update it using
`INSERT ... ON DUPLICATE KEY UPDATE`, avoiding repeated aggregation of a large raw
event table.

`schema_migrations` records executed SQL filenames. `bin/migrate.php` discovers
migrations alphabetically, skips completed files, and records a migration only after
its SQL succeeds. MySQL may implicitly commit DDL, so each migration is one focused
file and the runner uses transactions where MySQL permits them.

## Performance considerations

- Statistics use indexed filters, `COUNT(*)`, stable newest-first ordering, and
  `LIMIT`/`OFFSET`; they never load all events into PHP.
- The statistics page filters users by email instead of loading all users into a
  dropdown, because loading the full users table would not scale for a large user
  base.
- Reports read the small daily aggregate table rather than grouping millions of raw
  events per request.
- User state checks use a composite primary key.
- Email lookups use a unique index.
- Raw events and their relevant daily counter are updated atomically.
- Prepared statements use native prepares.

At much larger event volumes, keyset pagination would be preferable to deep offset
pagination. Event tables would likely be partitioned by date, retention would be
explicit, and reporting ranges could be cached.

For a production system with very high traffic, I would move activity tracking and
aggregation to an asynchronous queue or event stream. For the scope of this task,
events are written synchronously but isolated behind ActivityTracker so the
implementation can be replaced without changing controllers or use cases.

## Security considerations

- Passwords use `password_hash()` and `password_verify()`.
- Successful login regenerates the session ID.
- Session cookies are HTTP-only, `SameSite=Lax`, and secure when served over HTTPS.
- PHP strict session mode and cookie-only session IDs are enabled.
- Session IDs and CSRF tokens rotate across login/logout identity boundaries.
- Every POST form has a cryptographically random session CSRF token.
- Admin endpoints enforce the admin role on the server.
- Views escape dynamic output with `htmlspecialchars()`.
- PDO native prepared statements bind all request-derived SQL values.
- The download filename and filesystem path are server-controlled.
- The user agent and IP address are length-limited before storage.

Production deployment should terminate TLS, set secure operational secrets, add
login rate limiting, structured security logging, content security policy headers,
and centralized session storage.

## Trade-offs and production improvements

- Synchronous event writes make request success dependent on MySQL, but keep this
  exercise deterministic and are isolated behind `ActivityTracker`.
- Session authentication is appropriate for the single application; multiple PHP
  replicas would use Redis or another shared session store.
- Daily rows only exist for dates with activity; a reporting/calendar layer could
  fill zero-activity dates when a continuous axis is required.
- The SVG chart is intentionally dependency-free and minimal. A production UI would
  add richer interactive tooltips, accessible data descriptions, and richer
  responsive axes.
- User role management UI was intentionally not implemented because it was not
  explicitly required. In this task, an admin inherits regular user permissions and
  additionally has access to statistics and reports.
- The sample.exe file is a harmless placeholder file used only to demonstrate the
  download flow required by the task. It is not intended to be executed.
