#!/bin/sh
php82 /opt/app/bin/console doctrine:migrations:migrate --no-interaction

chmod 777 -R /opt/app/var

exec "$@"