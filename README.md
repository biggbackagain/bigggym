
# Sistema de Gestión de Gimnasio (Gimnasio App)

Un sistema web completo para la administración de un gimnasio, construido con Laravel 12. Permite gestionar miembros, suscripciones, pagos, inventario, ventas (POS) y generar reportes detallados.

![Captura del Dashboard](https://i.imgur.com/tu_imagen_del_dashboard.png)
*(Reemplaza este enlace con una URL a una captura de pantalla de tu dashboard)*

---

## ✨ Características Principales

* **Dashboard:** Vista principal con estadísticas de miembros (activos/inactivos) y un gestor de tareas pendientes.
* **Gestión de Miembros:** CRUD completo para miembros, incluyendo subida de foto de perfil y estatus (Activo/Vencido).
* **Gestión de Tarifas:** Creación y edición de tipos de membresía (diaria, semanal, mensual) con precios diferenciados para general y estudiantes.
* **Renovación de Membresías:** Flujo fácil para renovar la suscripción de un miembro existente.
* **Control de Acceso (Check-in):** Página para verificar el estatus de un miembro usando su código (`GYM-1`, `GYM-2`, etc.).
* **Punto de Venta (POS):** Interfaz para vender productos (bebidas, suplementos) con registro de método de pago (efectivo, tarjeta, transferencia) y referencia.
* **Gestión de Inventario:** Módulo para crear productos (con precio, SKU) y ajustar el stock (entradas/salidas).
* **Movimientos de Caja:** Registro manual de entradas y salidas de efectivo con descripción.
* **Reportes Avanzados:**
    * **Reporte de Caja (Final):** Muestra el total de ingresos (productos + membresías + movimientos de caja) en un rango de fechas. Permite imprimir o enviar por correo.
    * **Mi Corte:** Reporte individual para el cajero logueado, mostrando solo sus propias transacciones del día.
* **Comunicación:**
    * Envío de correos de bienvenida automáticos a nuevos miembros.
    * Módulo para enviar correos masivos (campañas) a miembros (todos, activos o inactivos).
* **Sistema de Backups:**
    * Creación de backups (`.zip`) con un clic, que incluyen la **base de datos (SQL)** y las **imágenes** (fotos de miembros, logos).
    * Lista de backups para descargar o eliminar.
    * Método de restauración manual documentado.
* **Roles y Permisos:** (Actualmente simplificado, pero la base está)
    * Usuario **Superadmin** con acceso total (creado vía Seeder).
    * Roles de **Admin** y **Recepcionista** con permisos granulares (actualmente desactivados para acceso total).
* **Configuración General:** Panel para configurar el nombre del gimnasio, logo, prefijo de miembro, zona horaria y credenciales de correo (SMTP).

---

## 🛠️ Stack Tecnológico

* **Backend:** Laravel 12 (PHP 8.3+)
* **Frontend:** TailwindCSS y Alpine.js (compilado con Vite)
* **Base de Datos:** MySQL 8
* **Entorno de Desarrollo:** Laravel Sail (Docker)
* **Paquetes Clave:** `spatie/laravel-backup`

---

## 🚀 Instalación Local (Windows 10/11 con WSL 2)

Esta es la guía de instalación para un entorno de desarrollo/producción local en una máquina Windows.

### 1. Requisitos Previos
* **WSL 2 (Ubuntu):** Instálalo desde PowerShell como Admin con `wsl --install -d Ubuntu`. Reinicia.
* **Docker Desktop:** Instálalo y asegúrate de habilitar la integración con "Ubuntu" en `Settings > Resources > WSL Integration`.
* **Git (en WSL):** Abre la terminal `wsl` y ejecuta:
    ```bash
    sudo apt update
    sudo apt install git -y
    ```

### 2. Clonar y Configurar el Proyecto
Abre tu terminal **WSL** (ej. "Ubuntu"):

```bash
# 1. Clona el proyecto
cd ~
git clone [https://github.com/tu-usuario/gimnasio-app.git](https://github.com/tu-usuario/gimnasio-app.git)
cd gimnasio-app

# 2. Crea el archivo .env
cp .env.example .env

# 3. Edita el .env (¡Importante!)
nano .env

Asegúrate de que estas variables estén configuradas:

Fragmento de código
APP_URL=http://localhost:8080
APP_PORT=8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=gimnasio_app
DB_USERNAME=sail
DB_PASSWORD=password
DB_ROOT_PASSWORD=password

SESSION_DRIVER=file
CACHE_DRIVER=file
(Guarda con Ctrl+O, Enter, y sal con Ctrl+X).

3. Instalar Dependencias (Vendor)

Este comando usa Docker para instalar la carpeta vendor (incluyendo Sail).

Bash
docker run --rm \
    -v "$(pwd)":/app \
    -w /app \
    composer:latest composer install
4. Permisos y Clave de App

Bash
# 1. Dar permisos de ejecución a Sail
chmod +x ./vendor/bin/sail

# 2. Generar y Mostrar Clave
./vendor/bin/sail php -r "echo 'APP_KEY=base64:'.base64_encode(random_bytes(32)).PHP_EOL;"

# 3. Copia la clave generada (APP_KEY=base64:...)
# 4. Pégala en tu archivo .env
nano .env 
# (Pega la clave, guarda con Ctrl+O, Enter, Ctrl+X)
5. Iniciar y Configurar la Aplicación

Bash
# 1. Inicia los contenedores (PHP, MySQL, etc.)
./vendor/bin/sail up -d

# 2. Espera 30 segundos a que la BD inicie

# 3. Ejecuta migraciones y seeders (Crea tablas y Superadmin)
./vendor/bin/sail artisan migrate:fresh --seed

# 4. Crea el enlace de storage (para ver imágenes)
./vendor/bin/sail artisan storage:link

# 5. Instala y compila el CSS/JS
./vendor/bin/sail npm install
./vendor/bin/sail npm run build

# 6. Limpia la caché
./vendor/bin/sail artisan optimize:clear
6. ¡Listo!

Abre tu navegador en http://localhost:8080.

Usuario por defecto (Superadmin): admin@gimnasio.com

Contraseña por defecto: password (Estos valores están definidos en database/seeders/SuperAdminSeeder.php).
