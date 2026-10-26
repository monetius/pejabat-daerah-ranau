# Deployment instructions — Pejabat Daerah Ranau

For the hosting provider deploying the supplied `deploy-test` release.

**The code belongs to the project author. Code changes, feature development and application testing remain the project author's responsibility. The hosting provider handles hosting, server configuration and deployment only. Report application bugs to the project author; do not modify the code without their instruction.**

**This website is still under development. So there is still some bugs and unfinished feature/option**

## 1. Prepare before deployment

The project author supplies the code release, current database backup, matching `public/uploads/` files and administrator email details. For updates/restores, retain the existing application key supplied for that environment.

The hosting provider prepares the server, production database and connection details, HTTPS for the agreed domain, persistent storage and the scheduler. Configure SMTP using the agreed mail service; exchange credentials privately.

The host needs PHP 8.5, Composer 2, MySQL, HTTPS and a scheduler. Node is needed only when building assets; use a version supported by the lockfile (Node 26 was tested locally).

Keep a backup of the database, uploaded files and existing production `APP_KEY` before changing anything.

## 2. Prepare the application

Set the web server's document root to **`<application>/public`** and enable Laravel URL rewrites.

Keep `public/uploads/` and required `storage/` files on persistent storage. They must survive updates and container replacement. Give the application and deployment users the required write access to `storage/`, `bootstrap/cache/` and `public/uploads/`. Do not use blanket `777` permissions.

From the application root, ensure the directories exist before installing dependencies. Linux example:

```bash
mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs public/uploads
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
composer check-platform-reqs --no-dev
npm ci --include=dev
npm run build
```

The frontend build may run in CI instead; include its `public/build/` output in the deployed release. Keep `public/assets/` too. Do not deploy development `public/hot` or Windows `node_modules/`.

## 3. Configure the environment

For a new environment, copy [.env.production.example](.env.production.example) to a private `.env`, or enter its values in the hosting platform. Do not overwrite an existing production `.env`.

Fill in:

- `APP_URL`: the real HTTPS domain.
- `DB_*`: the production database connection.
- `MAIL_*`: the SMTP service, credentials and sender address.

Keep `APP_ENV=production`, `APP_DEBUG=false`, `APP_TIMEZONE=Asia/Kuala_Lumpur` and secure cookies enabled, as shown in the template.

Leave `CALENDAR_HOLIDAY_CA_BUNDLE=` empty to use the server's trusted certificates. Set a server-local path only if a custom CA file is required. Do not copy the Windows development path or disable HTTPS verification.

```bash
php artisan config:clear
```

For a new environment using `.env`, generate the key once:

```bash
php artisan key:generate
```

If using platform-managed secrets, generate/store the key through that platform's process. **Preserve the existing production key for updates or restores.** Never put passwords or keys in Git.

## 4. Restore content and update the database

Restore the database backup supplied by the project author, including migration history, and copy the matching files into persistent `public/uploads/`. Source code alone does not include the latest CMS content.

Confirm the target database, then apply the migrations supplied with the release:

```bash
php artisan migrate:status
php artisan migrate --force
```

If the database backup or administrator account has not been supplied, contact the project author before proceeding. Do not create sample users or replace the supplied CMS content.

**Do not run `migrate:fresh`, `migrate:refresh`, development seeders, `cms:import --force` or `composer run setup` on an existing production site.** Do not assume the repository's `pdr.sql` is a current backup.

## 5. Configure the host

- Enable HTTPS and configure trusted proxies if the host uses a load balancer.
- Block web access to `.env`, `.git`, SQL dumps and private files. Disable script execution in uploads.
- Set upload/request limits to allow Media files up to 10 MB plus request overhead.
- Run `php artisan schedule:run` every minute through cron or the hosting scheduler. The holiday update runs at 03:15 Malaysia time.
- Set up backups, log rotation, monitoring and certificate renewal.

CMS verification email sends synchronously; no queue worker is currently required. Scheduled news uses publication-date filtering.

**Docker:** this branch contains `.dockerignore` only. A runnable Docker setup must still be provided and reviewed by the hosting team.

## 6. Check before launch

```bash
php artisan optimize
php artisan deploy:check
```

The check does not test real email delivery, database connectivity, backup recovery or storage persistence. Verify these manually:

- [ ] HTTPS, homepage, nested pages, `/up` and `/sitemap.xml` work.
- [ ] CMS login emails arrive and verification works. Codes expire after 3 minutes; resend cooldown is 1 minute.
- [ ] Existing images/PDFs load, and new uploads survive redeployment.
- [ ] No missing assets, localhost links or Linux filename-case errors.
- [ ] Backups and restore work.
- [ ] Supply the test URL to the project author for application testing and confirmation before launch.

For later updates, deploy only the release supplied by the project author. Back up first, preserve the environment/key and persistent files, then rebuild caches and repeat the checks. Keep the previous release available. Do not restore an old database over new content without an agreed recovery plan.
