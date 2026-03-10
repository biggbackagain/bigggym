#!/bin/bash

# 1. Detecta en qué carpeta estamos parados exactamente en este momento
DIRECTORIO_ACTUAL=$(pwd)

# 2. Arma el comando de Cron con la ruta correcta que acaba de detectar
COMANDO_CRON="* * * * * cd $DIRECTORIO_ACTUAL && ./vendor/bin/sail artisan schedule:run >> /dev/null 2>&1"

# 3. Revisa si el comando ya existe en el Cron para no duplicarlo
crontab -l | grep -q "$DIRECTORIO_ACTUAL"
if [ $? -eq 0 ]; then
    echo "⚠️  El Cronjob ya estaba instalado para la ruta: $DIRECTORIO_ACTUAL"
else
    # 4. Inyecta el comando nuevo al Cron sin borrar los que ya existan
    (crontab -l 2>/dev/null; echo "$COMANDO_CRON") | crontab -
    echo "✅ ¡Éxito! El Cronjob del Velador se instaló automáticamente en: $DIRECTORIO_ACTUAL"
fi
