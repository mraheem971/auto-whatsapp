<?php

header('Content-Type: application/json');

require __DIR__ . '/core/vendor/autoload.php';
$app = require_once __DIR__ . '/core/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$results = [];

// 1. Raw /proc/net/tcp entries matching 0D05 (3333) or 0BB8 (3000)
$rawTcp = [];
foreach (['/proc/net/tcp', '/proc/net/tcp6'] as $file) {
    if (file_exists($file) && is_readable($file)) {
        foreach (file($file) as $line) {
            if (stripos($line, ':0D05') !== false || stripos($line, ':0BB8') !== false) {
                $rawTcp[] = trim($line);
            }
        }
    }
}
$results['raw_tcp_matches'] = $rawTcp;

// 2. Who is PHP running as?
$results['php_user'] = [
    'get_current_user' => get_current_user(),
    'whoami' => function_exists('exec') ? @exec('whoami') : null,
    'uid' => function_exists('posix_getuid') ? posix_getuid() : null,
    'euid' => function_exists('posix_geteuid') ? posix_geteuid() : null,
];

// 3. Test various host strings for port 3333 and 3000
$testHosts = [
    '127.0.0.1',
    'localhost',
    '82.180.152.18',
    '::1',
];

$connTests = [];
foreach ($testHosts as $host) {
    foreach ([3333, 3000] as $port) {
        $errno = 0;
        $errstr = '';
        $t0 = microtime(true);
        $fp = @fsockopen($host, $port, $errno, $errstr, 0.5);
        $elapsed = round((microtime(true) - $t0) * 1000, 2);
        
        $connTests["{$host}:{$port}"] = [
            'connected' => (bool)$fp,
            'errno'     => $errno,
            'errstr'    => $errstr,
            'time_ms'   => $elapsed,
        ];
        if ($fp) {
            fwrite($fp, "GET /health HTTP/1.0\r\nHost: {$host}\r\n\r\n");
            $response = fread($fp, 256);
            fclose($fp);
            $connTests["{$host}:{$port}"]['response'] = substr($response, 0, 100);
        }
    }
}
$results['socket_tests'] = $connTests;

// 4. Check if pm2/node can be launched or if it's currently running
$results['proc_cmdline'] = @file_get_contents('/proc/1120130/cmdline');
$results['proc_status'] = @file_get_contents('/proc/1120130/status');

echo json_encode($results, JSON_PRETTY_PRINT);
