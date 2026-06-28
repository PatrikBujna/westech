#!/bin/sh
set -e

export COMPOSER_ALLOW_SUPERUSER=1

# The project directory is bind-mounted into the container, so dependencies must
# be installed at runtime — a build-time install would be hidden by the mount.
# Install once; vendor/ then persists on the host across container restarts.
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "[entrypoint] vendor/ missing — running composer install..."
    composer install --no-interaction --no-progress
fi

exec docker-php-entrypoint "$@"
