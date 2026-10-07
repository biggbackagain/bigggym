<?php
/** Prepare an installation without overwriting configuration or migrating data. */
$root = dirname(__DIR__);
chdir($root);
$production = in_array('--production', $argv, true);
if (PHP_VERSION_ID < 80400) {
    fwrite(STDERR, "Se requiere PHP 8.4 o posterior. Actual: ".PHP_VERSION."\n");
    exit(1);
}
foreach (['pdo_mysql', 'mbstring', 'openssl', 'fileinfo', 'curl', 'dom', 'xml', 'zip'] as $extension) {
    if (!extension_loaded($extension)) {
        fwrite(STDERR, "Activa la extensión PHP: {$extension}\n");
        exit(1);
    }
}
if (!is_file('vendor/autoload.php')) {
    fwrite(STDERR, "Ejecuta composer install antes de preparar la instalación.\n");
    exit(1);
}
require 'vendor/autoload.php';
if (!is_file('.env')) {
    copy($production ? 'deploy/hosting.env.example' : '.env.example', '.env');
}
// Never regenerate an existing key: encrypted data and sessions depend on it.
$environment = Dotenv\Dotenv::parse(file_get_contents('.env'));
if (empty($environment['APP_KEY'])) {
    $contents = file_get_contents('.env');
    $key = 'base64:'.base64_encode(random_bytes(32));
    $contents = preg_match('/^APP_KEY=.*$/m', $contents)
        ? preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$key, $contents)
        : $contents."\nAPP_KEY={$key}\n";
    file_put_contents('.env', $contents);
}
foreach (['storage/app/private/public', 'storage/app/backups', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache'] as $directory) {
    if (!is_dir($directory)) mkdir($directory, 0775, true);
}
echo "Configuración preparada; se conservó cualquier .env existente.\n";
echo "1. Configura DB_*, APP_URL y el correo en .env.\n";
echo "2. Base nueva: php artisan migrate --force; php artisan app:create-admin\n";
echo "   Migración: importa primero los datos anteriores y conserva su APP_KEY.\n";
echo "3. Ejecuta php artisan storage:link y php artisan app:doctor\n";
echo "4. Producción: php artisan config:cache y php artisan view:cache\n";
