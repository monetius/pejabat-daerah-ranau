PDR CMS - every page editable
=============================

BEFORE YOU START: back up your project (or commit to git). This zip REPLACES two
existing files:
  - routes/web.php                 (the old Route::view lines are removed; pages now come from the database)
  - resources/views/layouts/site.blade.php   (only the navbar/footer include lines changed)
  - app/Models/User.php            (adds the is_admin field)
Your old page views in resources/views/site/ are NOT touched. Keep them: the importer
reads them once, and they are your backup.

INSTALL (run in the project root, XAMPP MySQL running):
  1. Unzip over the project folder.
  2. php artisan migrate
  3. php artisan db:seed --class=AdminSeeder
  4. php artisan cms:import            <- copies all 20 pages + navbar + footer into the database
  5. php artisan serve
  6. Open http://localhost:8000/admin/login
        admin@pdranau.test / Admin12345!   (change after testing)

WHAT YOU CAN EDIT
  Admin > Halaman          every page (title, URL, SEO text, HTML content, CSS files, scripts, status)
  Admin > Navbar & Footer  top menu and footer shown on all pages
  Admin > Hebahan          announcements (/hebahan)
  Admin > Media            upload images/PDF, copy the /uploads/... URL into a page

SAFETY
  - Every save keeps the previous version (30 per page). Use "Pulihkan" to go back.
  - "Pratonton" shows your unsaved changes in the real site layout in a new tab.
  - Re-running cms:import skips anything already in the database.
    Use  php artisan cms:import --force  only to reset pages back to the Blade files.
