# Deployment and preparation guide

Pejabat Daerah Ranau website and CMS — Laravel 13, PHP 8.5.

This guide applies to `deploy-test`. It describes preparation and deployment; it does not deploy anything automatically. Hosting has not been selected. There is currently no Dockerfile, Compose stack, or provider-specific deployment configuration in this branch.

## 1. Decide these details before deployment

Ask the hosting provider or department IT team to confirm:

- Who owns the server, domain/DNS, certificate renewal, backups and incident response?
- Is the target a managed Laravel platform, Linux/Windows server, or Docker host?
- Can the web root point to this application's `public/` directory?
- Are PHP 8.5, Composer 2, a supported database, and scheduled tasks available?
- Can the application send SMTP mail and reach external HTTPS services?
- Where will persistent uploaded files and private storage live across releases?
- Is HTTPS terminated at the web server or at a reverse proxy/load balancer?
- Is there a staging domain for testing before production DNS changes?

Do not publish the existing development server or use `php artisan serve` as the production web server.

## 2. Pre-deployment checklist

### Access, content and credentials

- [ ] Review the exact Git revision and commit the intended changes on `deploy-test` before a Git-based deployment. Uncommitted changes are not present in a clone or `git archive HEAD`.
- [ ] Remove test news, sample images, test accounts and outdated contact details from the database intended for production.
- [ ] Confirm the department approves the public content, linked documents and policies.
- [ ] Replace development passwords and rotate credentials shared during development.
- [ ] Ensure each CMS administrator controls the inbox receiving their verification codes.
- [ ] Keep secrets outside Git, Docker images, screenshots and deployment logs.
- [ ] Review access carefully: the current CMS does not provide separate editor/publisher roles.

### Backups and migration data

- [ ] Export the current approved database, including migration history, using the database's native backup tools or the provider's backup service.
- [ ] Back up `public/uploads/` separately. It contains uploaded images and PDFs; it is not part of a deployment archive.
- [ ] Back up required private files in `storage/` and the environment's existing `APP_KEY` securely.
- [ ] Test restoring those backups into an isolated staging environment.
- [ ] Choose a content freeze/cutover window so edits made after the export are not lost.
- [ ] Keep backups off the application server with restricted access and an agreed retention policy.

The checked-in `pdr.sql` is not an authoritative production backup. Review a fresh export instead of importing it blindly. CMS pages, news, navigation and footer content live in the database: deploying source code alone does not transfer their latest edits.

### Runtime and services

- [ ] Use PHP 8.5 consistently for the web process, CLI, scheduler and any worker.
- [ ] Enable DOM, Fileinfo, OpenSSL and the PDO driver for the selected database, plus Composer's required extensions. The editor uses modern DOM APIs; do not rely only on the looser PHP constraint in `composer.json`.
- [ ] Use an actively supported Node version compatible with the lockfile to build assets. The locked Vite requires Node `^20.19.0 || >=22.12.0`; Node 26 was used locally. Node is not needed in the serving container/server after assets are built elsewhere.
- [ ] Set database/session/cache access and review the pending schema changes.
- [ ] Set server time correctly and use `APP_TIMEZONE=Asia/Kuala_Lumpur`.
- [ ] Obtain a real mail service and sender address; test sending from the actual host.
- [ ] Configure persistent storage, HTTPS, health monitoring and the scheduler.

## 3. Understand what must persist

| Data | Location | Deployment requirement |
| --- | --- | --- |
| CMS content, accounts and configuration | Database | Persistent database and tested backups |
| Uploaded images and documents | `public/uploads/` | Shared/persistent directory or volume; transfer existing uploads before launch |
| Private application files | `storage/` | Retain required files across releases; protect backups |
| Verification codes and rate limits | Configured Laravel cache | Persistent shared store; never `array` in production |
| Sessions | Configured Laravel session store | Persistent store; database is the template default |
| Generated frontend assets | `public/build/` | Build and deploy with each release |
| Compiled application configuration | `bootstrap/cache/` | Rebuild per environment/release; do not copy local cached configuration |

`.gitattributes` excludes database dumps, runtime uploads and development material from Git archives. `.dockerignore` excludes them from Docker build contexts. These exclusions do not remove already tracked files from Git history or from ordinary clones. Restrict repository access and review deployment artifacts.

`php artisan storage:link` only exposes `storage/app/public` through `public/storage`. It does not move or preserve this CMS's `public/uploads` files. Persist that directory explicitly. [Laravel filesystem reference](https://github.com/laravel/docs/blob/13.x/filesystem.md)

## 4. Configure the production environment

Use the provider's secret/environment settings, or copy `.env.production.example` to a private `.env` on the target. Linux examples below run from the application root; adapt paths, PHP executable and task syntax on Windows.

```bash
cp .env.production.example .env
```

Only do this for a new environment without an existing `.env`. Never overwrite an existing production environment file during an update.

Fill in the real values:

- `APP_ENV=production`, `APP_DEBUG=false`, and the final HTTPS `APP_URL`.
- Database host/name/user/password with appropriately restricted database access.
- `SESSION_SECURE_COOKIE=true` and `SESSION_HTTP_ONLY=true`; leave `SESSION_DOMAIN=null` unless a specific domain scope is required.
- Persistent cache/session drivers. The template uses database tables created by the application migrations.
- SMTP credentials and the approved `MAIL_FROM_ADDRESS`. Port 587 uses `MAIL_SCHEME=smtp` with the provider's TLS settings; port 465 normally uses `smtps`. Follow the selected provider's requirements.
- A cache prefix unique to the environment. Staging and production must not share sessions, verification caches or database credentials.

Create an `APP_KEY` once for a genuinely new environment:

```bash
php artisan key:generate
```

Preserve the existing key when updating an environment or restoring its encrypted data. Regenerating it on every deployment invalidates encrypted values and cookies. Keep a secure backup of it.

Leave `CALENDAR_HOLIDAY_CA_BUNDLE` empty when the host has a working trusted CA store. If IT requires a custom CA file, use a path valid on that host. Do not copy the development machine's Windows path or disable TLS verification.

If the host terminates HTTPS at a proxy, configure Laravel's trusted proxies for that actual infrastructure before testing URLs and cookies. Do not trust arbitrary proxy headers without checking the topology with IT.

## 5. Build the release

Check out the reviewed branch/revision, then install the locked production PHP dependencies:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
composer check-platform-reqs --no-dev
```

Build frontend assets in CI, a build container, or on the target with build tools installed:

```bash
npm ci
npm run build
```

Include `public/build/` in the release artifact; it is intentionally ignored by Git. Most site assets also live directly in `public/assets/` and must be deployed. Do not copy `public/hot` from development.

Do not run `composer run setup` in production: it includes first-install operations such as generating a key. Do not run `composer update` or `npm update` as part of routine deployment.

## 6. Prepare the database and CMS content

Choose one path before exposing the application:

**Moving this existing website:** restore a reviewed current database backup into the target database, preserve its migration history, and copy the matching uploaded files. Review the target connection and pending migrations before applying them.

**Starting with an empty database:** create the database and apply the migrations below. `php artisan cms:import` can populate missing CMS pages/header/footer from the bundled source templates, but it does not transfer the latest database-only edits or create an administrator. Arrange secure admin provisioning with the maintainer before launch; there is no public administrator registration flow. Do not run the development seeders as an admin setup procedure.

After reviewing the target connection and taking a backup:

```bash
php artisan migrate:status
php artisan migrate --force
```

For an empty database, inspect the available migrations before applying them. Do not use `migrate:fresh`, `migrate:refresh`, or `cms:import --force` on an existing production site. They can destroy data or overwrite edited content.

## 7. Configure the host and activate

### Server managed by IT

1. Configure Nginx, Apache or IIS with the document root set to `<application>/public`.
2. Enable the appropriate routing/rewrite rules; never move `index.php` into the project root to work around routing.
3. Install HTTPS and configure trusted proxies if applicable.
4. Restore/link persistent `public/uploads` and required `storage` directories before serving requests.
5. Make `storage/`, `bootstrap/cache/` and `public/uploads/` writable by the application user. Avoid blanket `777` permissions. Disable script execution in uploads at the web-server level.
6. Set PHP/web-server upload limits to accommodate the CMS Media limit of 10 MB, with request size greater than the file limit.
7. Configure backups, log rotation, monitoring and the scheduled task below.

Laravel's deployment guide explains the `public/` web root and release caching. [Official Laravel deployment reference](https://laravel.com/framework/docs/deployment)

### Managed Laravel hosting

Configure the same runtime, environment variables, database and build commands through the provider. Attach persistent storage for `public/uploads` and required private storage, configure the scheduler and mail service, and verify the platform's proxy/HTTPS settings. Do not assume that attaching a database also configures uploads, email, or backups.

### Docker host

Docker is an alternative way to package the same application, not a hosting provider. This branch has `.dockerignore` preparation only; it does not yet contain a runnable Docker deployment.

Before selecting Docker, IT must provide or review an image/stack that includes PHP 8.5 and the required extensions, a web server, a persistent database (or external database), a scheduler, and persistent upload/storage mounts. Supply secrets at runtime. Run migrations once per release, not concurrently in every replica. Keep uploads out of disposable container filesystems. Never remove production database/upload volumes when replacing containers.

The exact Docker build, services, ports, mounts and HTTPS configuration must be agreed with the host before Docker deployment instructions can be finalized.

### Finish application configuration

After the real environment, database and writable directories are ready:

```bash
php artisan config:clear
php artisan optimize
php artisan deploy:check
```

These steps must run in the target environment, not with your local development `.env`. The readiness command prints checks and returns a nonzero exit code when configuration is unsuitable. It does not send email, test the database connection, apply migrations, create users, verify backups, or prove storage persistence. Complete those checks separately. Do not treat a passing result as a full security audit.

## 8. Scheduler and email verification

Run Laravel's scheduler every minute. Example Linux cron entry (replace both paths):

```cron
* * * * * cd /srv/pdr/current && /usr/bin/php artisan schedule:run >> /srv/pdr/shared/scheduler.log 2>&1
```

Use an existing writable private log directory and rotate the log. On managed hosting use its scheduler; on Windows use the task scheduler; on Docker use a managed scheduler process/service. Avoid duplicate schedulers across replicas.

The declared holiday task runs at **03:15 Asia/Kuala_Lumpur**. This application's announcement visibility is checked against the publication date on requests; it does not require a queue worker to publish scheduled news. [Laravel scheduler reference](https://github.com/laravel/docs/blob/13.x/scheduling.md)

The production template uses `QUEUE_CONNECTION=sync`. CMS verification email currently sends synchronously, so a queue worker is not required for that flow. If future features dispatch queued jobs, configure and supervise workers before switching their queue connection.

Test CMS sign-in from the deployed domain: correct credentials send a code automatically, codes expire after three minutes, and resend cooldown is one minute. Test real delivery, spam folders and resend without recording codes in logs. Email delivery failures prevent completion of sign-in; establish an authorised recovery procedure with IT.

## 9. Launch acceptance checklist

- [ ] HTTPS works and HTTP redirects correctly, including behind any proxy.
- [ ] `/up` responds; the homepage, nested pages, sitemap and robots routes work.
- [ ] `.env`, `.git`, database dumps and private storage cannot be fetched from the website.
- [ ] CMS login, email code, resend, expiry, logout and idle timeout work.
- [ ] Existing images/PDFs load and new uploads survive an application restart/redeployment.
- [ ] Crop/resize, news author, drafts, immediate publication and a scheduled announcement work.
- [ ] The holiday feed and marked CMS calendar events work; outbound HTTPS is available.
- [ ] Mobile navigation, accessibility settings, dark mode, language controls and maps work.
- [ ] Browser/network checks find no localhost URLs, missing assets or mixed HTTP content.
- [ ] Test on the actual target OS: Windows development can hide filename-case issues that Linux exposes.
- [ ] Backups have been restored successfully in staging; a responsible person receives failure alerts.
- [ ] DNS cutover and rollback are agreed before public traffic is moved.

External maps, fonts, translation and holiday data depend on third-party connectivity. Review those integrations with IT and provide the public content they approve.

## 10. Updates, rollback and recovery

For each release: review the revision, back up, build an immutable artifact, install production dependencies, attach persistent directories, apply reviewed migrations, rebuild configuration caches, run `deploy:check`, and perform smoke tests. Prefer release directories/atomic switching where the host supports them. Use a controlled maintenance window when database changes require it.

Keep the previous known-good release available. If a release fails, restore that code revision and its matching assets, preserve the production `.env`, key and runtime data, and rebuild caches. A code rollback does not reverse database changes: decide separately whether an approved database restore is needed, and account for content added since the backup. Do not blindly run `migrate:rollback` or restore an older database over new edits.

Keep source code, database, uploaded files and the key together in the recovery plan. Deployments and backups should have named owners and a documented restore process.
