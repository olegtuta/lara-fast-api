#!/bin/sh
set -e

# --- .env
# Получаем все переменные окружения, начинающиеся с LARA_ (LARA_DB_HOST=localhost → DB_HOST=localhost)
printenv | grep "^LARA_" | while IFS='=' read -r key value; do
    # Убираем префикс LARA_
    laravel_key="${key#LARA_}"
    # Экранируем $ и пишем значение без кавычек
    escaped_value=$(printf '%s' "$value" | sed 's/\$/\\\$/g')
    echo "${laravel_key}=${escaped_value}"
done > ".env"

# кешируем конфиги laravel
php artisan optimize


# --- NGINX UNIT
# Файл для сигнала о готовности
READY_FLAG="/tmp/unit-ready"

# Фоновый процесс: ждём сокет и загружаем конфиг
(
    while [ ! -S /var/run/control.unit.sock ]; do
        sleep 0.1
    done
    curl -s -X PUT --data-binary @/etc/unit/unit-config.json \
        --unix-socket /var/run/control.unit.sock \
        http://localhost/config/
    touch "$READY_FLAG"
) &

# Запускаем unitd как основной процесс (PID 1)
exec unitd --no-daemon --control unix:/var/run/control.unit.sock

