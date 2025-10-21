<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# maseco-express

## Demo data seeder

To reset the supplier invoice datasets with demo information run:

```bash
php artisan db:seed --class=FacturiFurnizoriDemoSeeder
```

> **Note:** When invoking the seeder from a Unix shell do not include single backslashes in the class name (for example, `Database\Seeders\...`). The shell treats `\` as an escape character and will pass an invalid class reference to Artisan. The shorter `--class=FacturiFurnizoriDemoSeeder` form avoids that pitfall while still resolving to the fully qualified seeder class.

## Calup file storage

Supplier payment batch (calup) uploads are now stored in directories that mirror the calup identifier (`storage/app/facturi-furnizori/calupuri/{calup_id}`).

To migrate legacy uploads into the new structure, run:

```bash
php artisan facturi-furnizori:organize-calup-files
```

You can perform a dry run first to inspect the planned moves without making changes:

```bash
php artisan facturi-furnizori:organize-calup-files --dry-run
```

## Tech toolkit rollout checklist

Follow these steps the first time you deploy the new Tech → Migration Center tooling:

1. **Pull the code** on the server (e.g., `git pull origin work`).
2. **Run the migrations**: `php artisan migrate --force`. This creates the `roles` and `role_user` tables, backfills the existing `users.role` assignments, and now ships with the cleanup that drops the legacy `users.role` column. The guarded legacy schema import (`2024_01_01_000000_import_legacy_schema.php`) exits immediately when a `users` table already exists, so it will not try to recreate your production schema.
3. **Seed the Tech roles**: `php artisan db:seed --class=RolesTableSeeder --force`. This grants the Super Admin role to user ID 1 so future role checks rely on the pivot table instead of the legacy column.
4. **Verify access**: user ID 1 always keeps access to the Tech menu, even before the seeder runs, so you can open the Migration Center to preview pending changes with the new interface.
5. **Confirm the legacy cleanup**: once you are satisfied that all users have the correct records in `role_user`, rerun `php artisan migrate --force` if needed so the included drop migration removes the old `users.role` column. Keeping your code pivot-aware avoids regressions now that the column is gone.

> Tip: from the Migration Center, you can trigger `php artisan migrate --pretend` to review the SQL that will run before committing migrations.

Once your roles are seeded, you can also use the Tech → **Seeder Center** to run `php artisan db:seed --force` for the default `DatabaseSeeder` or any individual seeder class that lives in `database/seeders`. The UI captures the artisan output so you can confirm what ran after each execution.
