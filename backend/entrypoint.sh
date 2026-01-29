#!/usr/bin/env bash
set -e

cd /var/www/html

if [ ! -f ".env" ]; then
  cp .env.example .env
fi

mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache || true

if [ ! -f "vendor/autoload.php" ]; then
  composer install --no-interaction
fi

php-fpm -D

(
  if [ ! -f "public/livewire/livewire.js" ]; then
    php artisan vendor:publish --tag=livewire:assets --force || true
  fi

  if [ ! -f ".env.testing" ]; then
    if [ -f ".env.testing.example" ]; then
      cp .env.testing.example .env.testing
    else
      cp .env .env.testing
    fi
  fi

  APP_KEY_VALUE="$(grep -E '^APP_KEY=' .env | head -n1 || true)"
  if [ -n "$APP_KEY_VALUE" ]; then
    if grep -qE '^APP_KEY=' .env.testing; then
      sed -i "s|^APP_KEY=.*|$APP_KEY_VALUE|" .env.testing
    else
      echo "$APP_KEY_VALUE" >> .env.testing
    fi
  fi

  php -r '
  $host=getenv("DB_HOST") ?: "mysql";
  $port=getenv("DB_PORT") ?: "3306";
  for ($i=0; $i<30; $i++) {
    $fp=@fsockopen($host, (int)$port, $e, $s, 1);
    if ($fp) { fclose($fp); exit(0); }
    sleep(1);
  }
  fwrite(STDERR, "DB not reachable\n");
  exit(1);
  ';

  php artisan migrate --force || true
  php artisan db:seed --force || true
  php artisan storage:link || true
) &

tail -f /dev/null
