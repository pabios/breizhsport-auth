#!/bin/bash
set -e

# Vérifie si la base de données est prête avant de continuer
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  echo "Waiting for database to be ready..."
  sleep 5
done

# Crée la base de données
php bin/console doctrine:database:create --if-not-exists

# Exécute les migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Charge les fixtures (optionnel)
php bin/console doctrine:fixtures:load --no-interaction || true

# Démarre FrankenPHP
exec "$@"
