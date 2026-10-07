<?php
/** Build a clean Laravel ZIP, excluding secrets, user data and machine-specific caches. */
$root = dirname(__DIR__);
chdir($root);
if (!class_exists(ZipArchive::class)) {
    fwrite(STDERR, "Activa la extensión PHP zip.\n"); exit(1);
}
foreach (['vendor/autoload.php', 'public/build/manifest.json'] as $required) {
    if (!is_file($required)) {
        fwrite(STDERR, "Falta {$required}. Instala dependencias y compila el frontend.\n"); exit(1);
    }
}
$installed = json_decode(file_get_contents('vendor/composer/installed.json'), true);
if (!isset($installed['dev']) || $installed['dev'] !== false) {
    fwrite(STDERR, "Prepara el paquete en una copia limpia con composer install --no-dev.\n"); exit(1);
}
if (!is_dir('dist')) mkdir('dist', 0755);
$output = 'dist/bigggym-'.date('Ymd-His').'-'.bin2hex(random_bytes(3)).'.zip';
$zip = new ZipArchive();
if ($zip->open($output, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
    fwrite(STDERR, "No se pudo crear el ZIP.\n"); exit(1);
}
$roots = ['app', 'bootstrap', 'config', 'database/migrations', 'database/seeders', 'lang', 'public', 'resources', 'routes', 'vendor', 'docs', 'deploy', 'scripts'];
foreach ($roots as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        $path = str_replace('\\', '/', $file->getPathname());
        if (!$file->isFile() || $file->isLink()) continue;
        if (str_starts_with($path, 'bootstrap/cache/') || str_starts_with($path, 'public/storage/') || $path === 'public/hot') continue;
        if (str_starts_with(basename($path), '.env') || str_ends_with($path, '.log')) continue;
        $zip->addFile($file->getPathname(), $path);
    }
}
foreach (['artisan', 'composer.json', 'composer.lock', '.env.example', 'README.md', 'instalar_cron.sh', 'crontab'] as $file) $zip->addFile($file, $file);
foreach (['storage/app/private/public', 'storage/app/backups', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache'] as $directory) $zip->addEmptyDir($directory);
$zip->close();
echo "Paquete creado: {$output}\nSin .env, fotos, base de datos, Docker, Python ni node_modules.\n";
