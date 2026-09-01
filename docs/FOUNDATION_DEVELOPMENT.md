# Foundation Development

## Pinned runtimes

- PHP `8.4.25`
- Composer `2.10.3`
- Node.js `24.20.0`
- npm `11.9.0`
- MySQL `8.4.11`

Install dependencies only from the committed `composer.lock` and `package-lock.json`.

## Local setup

1. Copy `.env.example` to `.env` and generate `APP_KEY`.
2. Create the MySQL databases `lamafood` and `lamafood_test` with a least-privilege local user.
3. Put a local-only Godfather username and strong password in `LAMATECH_GODFATHER_USERNAME` and `LAMATECH_GODFATHER_PASSWORD`.
4. Run migrations, seed Foundation RBAC, then bootstrap the account:

```bash
php artisan migrate --seed
php artisan lamatech:bootstrap-godfather
```

Running the same Godfather command again rotates its configured username/password. Never commit `.env` or copy its values into tickets, logs, documentation, or frontend code.

## Quality gates

```bash
php artisan test
composer analyse
vendor/bin/pint --test
npm run build
composer audit
npm audit --audit-level=high
```

CI runs the test suite against MySQL 8.4.11. The application does not use SQLite as its test contract.
