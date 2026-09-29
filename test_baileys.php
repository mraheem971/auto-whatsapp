<?php

header('Content-Type: application/json');

require __DIR__ . '/core/vendor/autoload.php';
$app = require_once __DIR__ . '/core/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$results = [];

// 1. Decode listening TCP ports from /proc/net/tcp
$listeningPorts = [];
foreach (['/proc/net/tcp', '/proc/net/tcp6'] as $file) {
    if (file_exists($file) && is_readable($file)) {
        $lines = file($file);
        foreach ($lines as $i => $line) {
            if ($i === 0) continue; // header
            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) >= 4) {
                $state = $parts[3];
                if ($state === '0A') { // 0A = TCP_LISTEN
                    list($hexIp, $hexPort) = explode(':', $parts[1]);
                    $port = hexdec($hexPort);
                    $listeningPorts[] = $port;
                }
            }
        }
    }
}
$results['listening_ports'] = array_values(array_unique($listeningPorts));

// 2. Check node/pm2 execution from PHP
$cmdOutput = [];
$cmds = [
    'which_node' => 'which node 2>&1',
    'which_pm2'  => 'which pm2 2>&1 || find /home/u858498424 -name pm2 2>&1',
    'ps_node'    => 'ps aux | grep node 2>&1',
];
if (function_exists('shell_exec')) {
    foreach ($cmds as $key => $cmd) {
        $cmdOutput[$key] = shell_exec("export PATH=/usr/local/bin:/usr/bin:/bin:/opt/alt/alt-nodejs20/root/usr/bin:\$PATH; " . $cmd);
    }
}
$results['shell_commands'] = $cmdOutput;

// 3. Test HTTP health on candidate ports
$testPorts = array_values(array_unique(array_merge([3333, 3000, 8000, 8001], $listeningPorts)));
$healthChecks = [];
foreach ($testPorts as $p) {
    if ($p < 1000 || $p > 65000) continue;
    $url = "http://127.0.0.1:{$p}/health";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    $healthChecks[$p] = [
        'http_code' => $code,
        'body'      => $body ? substr($body, 0, 100) : null,
        'error'     => $err ?: null,
    ];
}
$results['health_checks'] = $healthChecks;

// 4. BaileysClient info
$results['baileys_client_url'] = \App\Services\BaileysClient::getBaseUrl();
$results['baileys_online'] = \App\Services\BaileysClient::ensureServiceRunning();

// 5. Tail server.js on live disk
$serverJs = __DIR__ . '/baileys-service/server.js';
if (file_exists($serverJs)) {
    $lines = file($serverJs);
    $results['server_js_tail'] = array_slice($lines, -20);
}

echo json_encode($results, JSON_PRETTY_PRINT);
