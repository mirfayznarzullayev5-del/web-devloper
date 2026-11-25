# NEXDEV Portfolio (PHP)

Premium personal brand website in PHP + HTML + CSS + JavaScript.

## Run locally

PHP built-in server:

```bash
cd nexdev-php
php -S localhost:8000
```

Open http://localhost:8000

Apache/Nginx: document root = this folder. Need PHP 8+.

## Configure

Edit `config.php`:

- `telegramUsername`
- social links
- phone
- projects

Contact form posts to `send.php` and appends JSON lines to `storage/requests.log`.
