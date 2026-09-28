<?php

header('Content-Type: application/json');

require __DIR__ . '/core/vendor/autoload.php';
$app = require_once __DIR__ . '/core/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$ports = [3333, 3000, 8000, 8001];
$results = [];

foreach ($ports as $port) {
    $url = "http://127.0.0.1:{$port}/health";
    $status = 'failed';
    $code = 0;
    $body = null;
    $err = null;

    try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        $status = ($code >= 200 && $code < 400) ? 'ok' : 'http_error';
    } catch (\Throwable $e) {
        $err = $e->getMessage();
    }

    $results[$port] = [
        'url' => $url,
        'status' => $status,
        'http_code' => $code,
        'error' => $err,
        'body' => $body,
    ];
}

$results['baileys_client_base_url'] = \App\Services\BaileysClient::getBaseUrl();
$results['ensure_service'] = \App\Services\BaileysClient::ensureServiceRunning();

echo json_encode($results, JSON_PRETTY_PRINT);
