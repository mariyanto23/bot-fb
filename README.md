# FB Affiliate Comment Bot

Native PHP 8 MVC dashboard for Facebook affiliate comment automation, Telegram notifications, and cron execution on shared hosting.

## Install

1. Upload the project and point the domain document root to `public/`.
2. Copy `.env.example` to `.env` and fill database, app URL, bot, and Telegram values.
3. Import `database.sql` into MySQL.
4. Composer is optional. The app includes a fallback autoloader for cPanel hosting without Composer.
5. Create an admin from cPanel Terminal when available:

```bash
php cron/create_admin.php admin@example.com strong-password
```

If cPanel Terminal is not available, set `INSTALL_ADMIN_TOKEN` in `.env` to a long random string, then open:

```text
https://your-domain.com/install_admin.php?token=your-long-random-string
```

Create the admin, then immediately delete `public/install_admin.php` or clear `INSTALL_ADMIN_TOKEN`.

## Cron

Use one of these commands from cPanel cron:

```bash
php /full/path/to/project/cron/run_bot.php
php /full/path/to/project/cron/fetch_posts.php
php /full/path/to/project/cron/send_comments.php
```

`run_bot.php` fetches posts and sends pending comments in one locked execution.

## Notes

- Database access uses PDO prepared statements.
- Facebook automation uses cURL and a saved cookie file.
- Passwords are stored with `password_hash`.
- CSRF protection is enabled for all POST routes.
- Runtime settings can be changed from the dashboard.
