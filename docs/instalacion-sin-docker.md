# BiggGym sin Docker

Laravel administra el gimnasio con PHP 8.4 y MySQL 8. El reconocimiento facial es opcional y puede ejecutarse en la misma máquina o en un servicio remoto. No se necesita Python para socios, membresías, pagos, reportes ni acceso por código.

## Elegir la instalación

| Destino | Requisitos en el equipo de destino | Reconocimiento |
| --- | --- | --- |
| PC local | PHP 8.4, Apache/Nginx, MySQL 8 | Opcional, local o remoto |
| Hosting compartido con dominio | PHP 8.4, MySQL, HTTPS, cron y acceso a consola/terminal del panel | Remoto si el proveedor no permite procesos Python |
| VPS Linux | PHP 8.4-FPM, Nginx/Apache, MySQL, cron | Local con servicio systemd |

Composer y Node.js se necesitan para preparar el código fuente. El ZIP de distribución incluye vendor y public/build; el equipo de destino no necesita Composer ni Node. No todos los hostings compartidos permiten Python, enlaces simbólicos, mysqldump o tareas de larga duración: confirma estas funciones con el proveedor. El dominio por sí solo no ejecuta la aplicación.

## 1. Preparar desde el código fuente

PHP CLI y el servidor web deben usar 8.4 o posterior. Extensiones: PDO MySQL, mbstring, OpenSSL, fileinfo, curl, DOM/XML y zip; Composer comprobará los demás requisitos. Para respaldos se necesita mysqldump.

```sh
composer install
npm ci
npm run build
composer setup
```

Para hosting, usa `composer setup:hosting` en lugar de `composer setup`. Si existe `.env`, ambos comandos lo conservan. Ninguno ejecuta migraciones, crea cuentas ni reinicia la clave existente. Si cambias de local a hosting, ajusta tú mismo APP_ENV, APP_DEBUG, APP_URL y SESSION_SECURE_COOKIE.

Crea una base MySQL vacía y un usuario limitado a esa base. Configura `.env`:

```dotenv
APP_URL=http://localhost:8000
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bigggym
DB_USERNAME=bigggym
DB_PASSWORD="tu contraseña"
BIOMETRICS_ENABLED=false
```

Solo para una instalación nueva:

```sh
php artisan migrate --force
php artisan app:create-admin
php artisan storage:link
php artisan app:doctor
```

El administrador se crea de forma interactiva, sin contraseña predeterminada. Después configura el gimnasio y las tarifas desde la aplicación. No ejecutes seeders de ejemplo sobre una base existente: pueden sobrescribir configuración. Para una prueba local rápida: `php artisan serve --host=127.0.0.1 --port=8000`; abre http://localhost:8000. Para desarrollo con recompilación: `composer dev`.

Para uso diario instala Apache/Nginx y MySQL como servicios; no uses `artisan serve` como servidor público. Apunta el DocumentRoot a `public/`, nunca a la raíz del proyecto. Hay ejemplos en `deploy/apache.conf.example` y `deploy/nginx.conf.example`. Concede escritura a `storage/` y `bootstrap/cache/` al usuario del servidor, sin permisos globales 777.

El proyecto conserva su ubicación original de fotografías: `storage/app/private/public/`. `php artisan storage:link` conecta esa carpeta con `public/storage`. No cambies a `storage/app/public` durante una migración. En Windows, habilita permisos para enlaces simbólicos (modo desarrollador o consola elevada únicamente para crear el enlace).

## 2. Hosting y dominio

1. Configura el dominio y certificado HTTPS en el proveedor.
2. Sube la aplicación fuera de la carpeta pública y apunta el dominio a su subcarpeta `public/`. Si el panel no permite configurar esta raíz, pide un dominio adicional con raíz configurable; no subas todo el proyecto a una carpeta accesible por web.
3. Prepara `.env` con `php scripts/setup.php --production`, o copia `deploy/hosting.env.example` y conserva la APP_KEY si migras.
4. Configura la base proporcionada por el hosting y `APP_URL=https://tu-dominio`. Mantén `APP_ENV=production`, `APP_DEBUG=false` y `SESSION_SECURE_COOKIE=true`.
5. Ejecuta las migraciones y enlace de storage. Crea administrador solo en una base nueva.
6. Ejecuta `php artisan config:cache`, `php artisan view:cache` y `php artisan app:doctor`.
7. Configura SMTP desde el panel del gimnasio y comprueba un envío. El `.env` inicial usa log para no enviar correo durante pruebas.
8. Configura el programador cada minuto con la ruta de PHP 8.4 del proveedor.

Se usan sesiones y caché en archivos y trabajos síncronos para evitar Redis y un worker permanente. Las campañas grandes de correo pueden requerir QUEUE_CONNECTION=database y un worker propio; el modo síncrono no garantiza completar campañas que excedan el tiempo permitido por el hosting.

## 3. Paquete para otros equipos

En una copia limpia del proyecto, con PHP 8.4:

```sh
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
composer package:hosting
```

El ZIP aparece en `dist/`. El empaquetador rechaza dependencias de desarrollo, excluye `.env`, cachés, fotos, bases de datos, logs, Docker, Python y node_modules. No ejecutes `composer install --no-dev` sobre un entorno que estés usando para desarrollar; usa una copia para distribuir. Tras extraer, ejecuta `php scripts/setup.php --production` para hosting o `php scripts/setup.php` para local. Instala aparte el servicio facial si lo necesitas.

El ZIP incluye dependencias PHP: prepáralo con las mismas versiones de PHP y extensiones del destino. `composer audit` permite revisar avisos de seguridad antes de publicar.

## 4. Reconocimiento facial opcional

Con `BIOMETRICS_ENABLED=false`, no se inicia ni consulta Python. Para conservarlo, usa Python 3.11 y un entorno aislado. En Ubuntu/Debian instala Python/venv, compilador C++ y CMake antes de compilar dlib. Ejemplo para una distribución que incluya Python 3.11:

```sh
sudo apt install python3.11 python3.11-venv python3.11-dev build-essential cmake
python3.11 ai-service/install.py
```

El instalador también funciona desde Python en Windows, pero dlib necesita herramientas C++/CMake y el proyecto face_recognition no soporta Windows oficialmente. Para una instalación sencilla en Windows se recomienda conectar Laravel a un motor Linux remoto; la app administrativa funciona nativamente en Windows. No se descargan ejecutables de reconocimiento de terceros.

Genera un secreto una sola vez con `php -r "echo bin2hex(random_bytes(32));"`. Guárdalo en `BIOMETRICS_TOKEN` tanto en el `.env` de Laravel como en `ai-service/.env`. No lo agregues al repositorio.

Laravel, motor en la misma máquina:

```dotenv
BIOMETRICS_ENABLED=true
BIOMETRICS_URL=http://127.0.0.1:8005
BIOMETRICS_TOKEN=el_mismo_secreto_de_64_caracteres
```

Inicia el motor:

```sh
# Linux
ai-service/.venv/bin/python ai-service/run.py
# Windows
ai-service\.venv\Scripts\python.exe ai-service\run.py
```

Para arranque automático en Linux adapta `deploy/bigggym-ai.service`, colócalo en `/etc/systemd/system/`, y ejecuta `sudo systemctl daemon-reload` y `sudo systemctl enable --now bigggym-ai`. El usuario indicado debe poder leer el código, modelos, entorno virtual y archivo de configuración. El proceso usa un solo worker, escucha en loopback y no activa autoreload.

Para hosting con motor en otro servidor: publica el motor con un proxy HTTPS (ejemplo `deploy/ai-nginx-location.conf.example`) y configura `BIOMETRICS_URL=https://reconocimiento.tu-dominio`. El secreto viaja solo entre Laravel y Python, nunca al navegador. Laravel rechaza conexiones remotas HTTP y redirecciones. No expongas directamente el puerto 8005 a Internet. No configures localhost en el hosting para apuntar a la PC de recepción: localhost siempre es la máquina donde corre Laravel.

Después de modificar `.env`:

```sh
php artisan config:cache
php artisan app:doctor --biometrics
```

Reinicia también el servicio Python si cambias su secreto. La cámara del navegador requiere HTTPS o localhost. Conserva los vectores en MySQL; no hay que registrar nuevamente los rostros al cambiar de servidor. La detección de vida no está implementada.

## 5. Tareas automáticas y respaldos

Linux: revisa e instala `crontab` como ejemplo, o ejecuta `bash instalar_cron.sh` con el PHP 8.4 correcto en PATH. Elimina manualmente cualquier tarea antigua de Sail para evitar duplicados; el instalador solo reemplaza tareas marcadas como propias.

Windows (PowerShell):

```powershell
./scripts/install-scheduler.ps1 -PhpPath 'C:/php84/php.exe'
```

Crea una tarea cada minuto para la cuenta actual; funciona mientras esa cuenta tenga sesión iniciada. Para ejecución sin sesión, configura una cuenta de servicio en el Programador de tareas. Apache/MySQL deben tener su propio arranque automático.

Para respaldos configura DB_DUMP_BINARY_PATH con el directorio de mysqldump si no está en PATH. Ejemplo Windows: `DB_DUMP_BINARY_PATH="C:/mysql/bin"`. Algunos proveedores bloquean su ejecución; en ese caso usa sus respaldos de MySQL más una copia de fotografías. Verifica recuperación de ambas partes: el restaurador web existente importa SQL, pero no restaura fotografías.

## 6. Migrar desde Docker sin perder datos

Haz esto en la instalación anterior antes de sustituir el código:

1. Detén las escrituras de la aplicación y exporta MySQL desde el contenedor/administrador de base de datos. Usa un dump consistente de toda la base, incluyendo usuarios, permisos, settings y face_vector. No copies el volumen de MySQL como si fuera un archivo SQL.
2. Guarda una copia privada del `.env` original (especialmente APP_KEY) y toda la carpeta `storage/app/private/public/`. Conserva también los backups que necesites de `storage/app/backups/`.
3. Instala PHP 8.4, servidor web y MySQL en el destino. Importa el dump en una base nueva y copia las fotografías a la misma ruta relativa.
4. Copia y ajusta el `.env`: DB_HOST deja de ser mysql y pasa a la dirección del nuevo servidor; configura APP_URL, DB_*, MAIL_* y BIOMETRICS_*. Conserva APP_KEY. Limpia cachés con `php artisan optimize:clear`.
5. Ejecuta `php artisan migrate --force` (nunca migrate:fresh), `php artisan storage:link`, compila o usa los assets del paquete y ejecuta `php artisan app:doctor`.
6. Comprueba inicio de sesión, socios y fotos, pagos, acceso por código, correo, tareas y reconocimiento si está habilitado. Cambia tráfico/DNS solo tras validar el destino. Conserva la copia anterior para volver atrás si algo falla; no borres volúmenes de Docker durante la migración.

## Fuentes

- https://laravel.com/docs/12.x/deployment
- https://fastapi.tiangolo.com/deployment/server-workers/
- https://github.com/ageitgey/face_recognition
