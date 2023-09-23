#!/bin/bash

ENV_FILENAME="/opt/app/.env"

function getValue(){
  if [ $# -ne 2 ]; then
    echo "Error: expected 2 parameters"
    exit 1
  fi

  local P_NAME="$1"
  local P_DEFAULT="$2"
  local value="${P_DEFAULT}"

  if [ "${!P_NAME:-}" ]; then
    value="${!P_NAME:-}"
  fi

  export $P_NAME=$value
  echo "$value"
}

echo "APP_ENV=$(getValue APP_ENV prod)" > $ENV_FILENAME
echo "APP_SECRET=$(getValue APP_SECRET 'change-me')" >> $ENV_FILENAME
echo "DATABASE_URL=$(getValue DATABASE_URL 'sqlite:///%kernel.project_dir%/var/database/data.db')" >> $ENV_FILENAME
echo "MAILER_DSN=$(getValue MAILER_DSN 'smtp://user:pass@smtp.example.com:25')" >> $ENV_FILENAME
echo "APP_REGISTRATION_ENABLED=$(getValue APP_REGISTRATION_ENABLED true)" >> $ENV_FILENAME
echo "APP_REGISTRATION_EMAIL_VERIFICATION_ENABLED=$(getValue APP_REGISTRATION_EMAIL_VERIFICATION_ENABLED false)" >> $ENV_FILENAME

php82 /opt/app/bin/console doctrine:migrations:migrate --no-interaction

chmod 777 -R /opt/app/var

exec "$@"