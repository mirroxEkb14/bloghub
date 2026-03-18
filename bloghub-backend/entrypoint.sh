#!/usr/bin/env bash
set -e

cd /var/www/html

if [ ! -f ".env" ]; then
  cp .env.example .env
fi

mkdir -p \
  storage/framework/views storage/framework/cache storage/framework/sessions \
  storage/logs bootstrap/cache storage/app/private storage/app/public \
  storage/app/public/creator-profiles/avatars \
  storage/app/public/creator-profiles/covers \
  storage/app/public/users/avatars \
  storage/app/public/posts/media \
  storage/app/public/tiers/covers
chmod -R 775 storage bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 777 storage/app/public

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

  NEED_SEED=1
  if php -r '
    $env = "/var/www/html/.env";
    if (!is_readable($env)) { exit(1); }
    $vars = $_ENV;
    foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
      $line = trim($line);
      if ($line !== "" && $line[0] !== "#" && strpos($line, "=") !== false) {
        list($k, $v) = explode("=", $line, 2);
        $vars[trim($k)] = trim($v, " \x27\t\"");
      }
    }
    $get = function ($k, $d) use ($vars) { return isset($vars[$k]) ? $vars[$k] : $d; };
    $host = $get("DB_HOST", "mysql");
    $port = $get("DB_PORT", "3306");
    $name = $get("DB_DATABASE", "bloghub");
    $user = $get("DB_USERNAME", "bloghub");
    $pass = $get("DB_PASSWORD", "");
    try {
      $dsn = "mysql:host=" . $host . ";port=" . $port . ";dbname=" . $name . ";charset=utf8mb4";
      $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
      $count = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
      exit($count > 0 ? 0 : 1);
    } catch (Throwable $e) {
      exit(1);
    }
  ' 2>/dev/null; then
    NEED_SEED=0
  fi
  if [ "$NEED_SEED" = "1" ]; then
    php artisan db:seed --force || true
  else
    echo "   INFO  Nothing to seed."
  fi
  [ -e public/storage ] || php artisan storage:link || true
) &

tail -f /dev/null
