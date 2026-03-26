#!/bin/bash
set -e

echo "Instalando dependências PHP..."
composer install --optimize-autoloader --no-interaction 2>&1

if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
	echo "Executando migrations..."
	vendor/bin/phinx migrate -c phinx.php

	if [ "${AUTO_SEED:-true}" = "true" ]; then
		echo "Executando seed inicial..."
		vendor/bin/phinx seed:run -c phinx.php
	fi

	echo "Migrations finalizadas com sucesso."
else
	echo "AUTO_MIGRATE=false, migrations automáticas desativadas."
fi

echo ""
echo "Comandos manuais disponíveis:"
echo "  docker compose exec php composer migrate"
echo "  docker compose exec php composer seed"
echo "  docker compose exec php composer rollback"
echo ""

echo "Iniciando PHP-FPM..."
exec "$@"
