# AI Sales Gen

Laravel app for generating AI-assisted sales pages.

## Neon PostgreSQL

This project supports Neon as the online PostgreSQL database. Neon provides a standard PostgreSQL connection string; the app accepts it through `DATABASE_URL` for the `pgsql` connection.

1. In Neon, open your project and click **Connect**.
2. Copy the PostgreSQL connection string. For production or serverless-style deployments, prefer the pooled connection string with `-pooler` in the host.
3. Set these variables:

```dotenv
DB_CONNECTION=pgsql
DATABASE_URL="postgresql://USER:PASSWORD@HOST/neondb?sslmode=require&channel_binding=require"
DB_SSLMODE=require
DB_SEARCH_PATH=public
```

You can also use split variables instead of `DATABASE_URL`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=ep-example-pooler.us-east-2.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=your-password
DB_SSLMODE=require
DB_SEARCH_PATH=public
```

After updating environment variables, clear cached config and run migrations:

```bash
php artisan config:clear
php artisan migrate
```

## Development

Install dependencies and run the app:

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
npm run dev
```

Run validation:

```bash
vendor/bin/pint --test
php artisan test
npm run build
```

## Laravel

This project uses Laravel. For framework documentation, see [laravel.com/docs](https://laravel.com/docs).

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
