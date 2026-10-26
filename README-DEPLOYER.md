# Hosting notes — Pejabat Daerah Ranau

For the Laravel hosting team deploying the supplied `deploy-test` release.

**The project author owns and maintains the code. Hosting scope is server configuration and deployment; report application issues to the author.**

The website is still under development, with some unfinished features and possible bugs. Provide a staging URL for the author to test before launch.

## Important project details

- **Legacy files:** the site has migrated to Laravel. `legacy/` is the archived old build, is not used by the current application and is included in the package for reference only. Serve the Laravel application from `public/`, not from `legacy/`.

- **Runtime:** Laravel 13, PHP 8.5 and MySQL. The CMS uses modern PHP DOM APIs. Frontend builds use the supplied npm lockfile; Node 26 was tested locally.
- **Environment:** use [.env.production.example](.env.production.example). Fill in the real domain, database and SMTP settings. Keep `APP_TIMEZONE=Asia/Kuala_Lumpur`.
- **Application key:** establish a production `APP_KEY` once and preserve it across updates/restores. Do not regenerate it during routine deployment.
- **Content transfer:** use the author's current database backup, including migration history, and matching uploads. Do not assume the repository's `pdr.sql` is current or recreate CMS content from templates.
- **Uploads:** this CMS stores images/PDFs in **`public/uploads/`**. Transfer and persist this directory separately. `storage:link` does not preserve it. Retain required private files in `storage/` too.
- **Assets:** include both `public/assets/` and the generated `public/build/`. Build tools are dev dependencies: use `npm ci --include=dev` before `npm run build` if building on the host.
- **Email:** real SMTP delivery is required for CMS login. Codes send synchronously, expire after 3 minutes, and have a 1-minute resend cooldown. Confirm delivery from the hosted environment.
- **Sessions/cache:** use persistent stores; the production template uses database tables. Multiple application instances need shared session/cache storage and uploads.
- **Calendar:** run Laravel's scheduler every minute. Holiday sync runs at **03:15 Malaysia time**. Leave `CALENDAR_HOLIDAY_CA_BUNDLE` empty for system CA trust; use a server-local path only if a custom bundle is needed.
- **Uploads limit:** allow 10 MB files plus request overhead. The upload directory must be writable and must not execute scripts.
- **Queues:** no queue worker is currently required. Login email is synchronous; scheduled news uses publication-date filtering on requests.

## Dependencies

`vendor/` and `node_modules/` are not included. From the application root, install the production PHP dependencies:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
composer check-platform-reqs --no-dev
```

Built frontend assets are already included. Install npm dependencies only if rebuilding them:

```bash
npm ci --include=dev
npm run build
```

## Deployment cautions

- Back up the database, uploads and production key before updates. Apply only the migrations supplied with the release.
- Do not run `migrate:fresh`, `migrate:refresh`, development seeders, `cms:import --force` or `composer run setup` against the existing production site.
- If the database backup or administrator details are missing, contact the author before proceeding.
- Docker has not been configured: `.dockerignore` is present, but no Dockerfile or Compose stack is supplied.

## Before handover

Run `php artisan deploy:check` after configuring the target environment and rebuilding caches. It checks configuration, not actual email delivery, database connectivity or storage persistence.

Confirm HTTPS/routes/assets work, CMS verification emails arrive, uploads survive redeployment, and backups can be restored. Return the staging URL and any application errors to the project author for testing before launch.
