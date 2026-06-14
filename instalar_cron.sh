#!/bin/bash

# 1. Detecta dinámicamente la ruta sin importar en qué computadora o servidor estés
DIRECTORIO_ACTUAL=$(pwd)

# 2. Creamos un PATH universal que cubre Mac (Apple Silicon e Intel) y Servidores Linux (Ubuntu, Debian, etc.)
UNIVERSAL_PATH="/usr/local/bin:/opt/homebrew/bin:/usr/bin:/bin:/snap/bin"

# 3. Arma el comando usando el estándar de Laravel (schedule:run)
COMANDO_CRON="* * * * * cd \"$DIRECTORIO_ACTUAL\" && PATH=\"$UNIVERSAL_PATH\" ./vendor/bin/sail artisan schedule:run >> \"$DIRECTORIO_ACTUAL/cron_log.txt\" 2>&1"

echo "========================================="
echo "⚙️ Configurando el motor automático..."
echo "📂 Ruta detectada: $DIRECTORIO_ACTUAL"

# 4. Limpieza inteligente: Busca si ya hay un cron instalado para ESTA ruta y lo limpia para no hacer basura
crontab -l 2>/dev/null | grep -q "$DIRECTORIO_ACTUAL"
if [ $? -eq 0 ]; then
    echo "⚠️  Actualizando instalación previa..."
    (crontab -l 2>/dev/null | grep -v "$DIRECTORIO_ACTUAL"; echo "$COMANDO_CRON") | crontab -
else
    echo "🚀 Instalando tarea por primera vez..."
    (crontab -l 2>/dev/null; echo "$COMANDO_CRON") | crontab -
fi

echo "✅ ¡Listo! El Cronjob quedó anclado correctamente."
echo "========================================="