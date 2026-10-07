# BiggGym

Sistema de administración de gimnasio con Laravel 12, PHP 8.4 y MySQL 8. Funciona sin Docker. El reconocimiento facial es opcional y se ejecuta como servicio Python local o remoto.

## Instalación

Consulta la [guía de instalación sin Docker](docs/instalacion-sin-docker.md) para PC local, hosting con dominio, VPS, arranque automático, distribución y migración desde la instalación anterior.

Desde el código fuente:

```sh
composer install
npm ci
npm run build
composer setup
```

Configura MySQL y APP_URL en `.env`. En una base nueva:

```sh
php artisan migrate --force
php artisan app:create-admin
php artisan storage:link
php artisan app:doctor
```

No se incluyen credenciales predeterminadas. Para una base existente conserva APP_KEY e importa los datos y fotos antes de ejecutar migraciones; sigue la sección de migración de la guía. No uses migrate:fresh.

## Hosting

Usa `composer setup:hosting`, configura HTTPS y apunta el dominio a `public/`. En una copia para distribución, instala dependencias con `composer install --no-dev`, compila el frontend y ejecuta `composer package:hosting`. El ZIP no requiere Node ni Composer en el destino. Necesita PHP 8.4 y MySQL.

## Reconocimiento

La instalación inicial usa acceso por código (`BIOMETRICS_ENABLED=false`). Para habilitar el reconocimiento consulta la guía: requiere un servicio Python accesible, URL y token compartido. Un hosting solo PHP puede conectarse a un servicio facial remoto por HTTPS.

## Pruebas

```sh
php artisan test --filter='CheckInTest|DeploymentTest'
python -m unittest discover -s ai-service -p test_main.py -v
```

PHPUnit usa SQLite en memoria para no tocar datos del gimnasio. Las pruebas Python usan detección simulada; necesitan las dependencias del servicio más `httpx`.
