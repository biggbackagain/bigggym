<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use LogicException;

class BiometricClient
{
    public function connection(): PendingRequest
    {
        if (!config('services.biometrics.enabled')) {
            throw new LogicException('El reconocimiento facial está desactivado.');
        }
        $url = rtrim((string) config('services.biometrics.url'), '/');
        $token = (string) config('services.biometrics.token');
        $host = parse_url($url, PHP_URL_HOST);
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)) {
            throw new LogicException('BIOMETRICS_URL debe ser una URL HTTP o HTTPS válida.');
        }
        if ($scheme !== 'https' && !in_array($host, ['127.0.0.1', 'localhost', '[::1]'], true)) {
            throw new LogicException('El reconocimiento remoto requiere HTTPS.');
        }
        if (strlen($token) < 32) {
            throw new LogicException('Configura BIOMETRICS_TOKEN con al menos 32 caracteres.');
        }
        return Http::baseUrl($url)->acceptJson()->withToken($token)
            ->connectTimeout(3)->timeout(15)->withoutRedirecting();
    }
}
