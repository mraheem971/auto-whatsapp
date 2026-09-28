<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BaileysClient
{
    public static function getBaseUrl()
    {
        return rtrim(env('BAILEYS_URL', env('WHATSAPP_SERVER_URL', 'http://127.0.0.1:3000')), '/');
    }

    /**
     * Ensure the Baileys Node.js background process is alive
     */
    public static function ensureServiceRunning()
    {
        try {
            $baseUrl = self::getBaseUrl();
            try {
                $res = Http::timeout(2)->get($baseUrl . '/health');
                if ($res && $res->successful()) {
                    return true;
                }
            } catch (\Throwable $e) {}

            self::spawnNodeService();

            // Wait up to 3 seconds for service to answer health check
            for ($i = 0; $i < 6; $i++) {
                usleep(500000); // 500ms
                try {
                    $res = Http::timeout(2)->get($baseUrl . '/health');
                    if ($res && $res->successful()) {
                        return true;
                    }
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {
            Log::info("BaileysClient ensureServiceRunning notice: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Start the Baileys Node.js microservice in the background safely
     */
    public static function spawnNodeService()
    {
        try {
            $servicePath = base_path('../baileys-service');
            if (!is_dir($servicePath)) {
                $servicePath = base_path('baileys-service');
            }

            if (is_dir($servicePath)) {
                $realPath = realpath($servicePath);
                if ($realPath) {
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        if (function_exists('popen') && function_exists('pclose')) {
                            @pclose(@popen("start /B cmd /c \"cd /d \"{$realPath}\" && node server.js\"", "r"));
                        }
                    } else {
                        $cmd = "export PATH=/opt/alt/alt-nodejs20/root/usr/bin:\$PATH; cd \"{$realPath}\" && (./node_modules/.bin/pm2 resurrect 2>/dev/null || ./node_modules/.bin/pm2 restart baileys-whatsapp 2>/dev/null || PORT=3333 node server.js > /dev/null 2>&1 &)";
                        if (function_exists('exec')) {
                            @exec($cmd);
                        } elseif (function_exists('shell_exec')) {
                            @shell_exec($cmd);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::info("BaileysClient spawnNodeService notice: " . $e->getMessage());
        }
    }

    /**
     * Resilient POST request to Baileys with automatic retry and watchdog
     */
    public static function post($endpoint, $data = [], $timeout = 15)
    {
        if (is_numeric($data)) {
            $timeout = (int) $data;
            $data = [];
        }
        $data = is_array($data) ? $data : [];

        $url = rtrim(self::getBaseUrl(), '/') . '/' . ltrim($endpoint, '/');

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $response = Http::timeout($timeout)->post($url, $data);
                return $response;
            } catch (\Throwable $e) {
                if ($attempt === 1) {
                    self::ensureServiceRunning();
                    usleep(500000);
                } else {
                    Log::warning("BaileysClient POST {$endpoint} failed: " . $e->getMessage());
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * Resilient GET request to Baileys with automatic retry and watchdog
     */
    public static function get($endpoint, $query = [], $timeout = 10)
    {
        if (is_numeric($query)) {
            $timeout = (int) $query;
            $query = [];
        }
        $query = is_array($query) ? $query : [];

        $url = rtrim(self::getBaseUrl(), '/') . '/' . ltrim($endpoint, '/');

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $response = Http::timeout($timeout)->get($url, $query);
                return $response;
            } catch (\Throwable $e) {
                if ($attempt === 1) {
                    self::ensureServiceRunning();
                    usleep(500000);
                } else {
                    Log::warning("BaileysClient GET {$endpoint} failed: " . $e->getMessage());
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * Resilient DELETE request
     */
    public static function delete($endpoint, $data = [], $timeout = 10)
    {
        if (is_numeric($data)) {
            $timeout = (int) $data;
            $data = [];
        }
        $data = is_array($data) ? $data : [];

        $url = rtrim(self::getBaseUrl(), '/') . '/' . ltrim($endpoint, '/');

        try {
            return Http::timeout($timeout)->delete($url, $data);
        } catch (\Throwable $e) {
            self::ensureServiceRunning();
            try {
                return Http::timeout($timeout)->delete($url, $data);
            } catch (\Throwable $ex) {
                Log::warning("BaileysClient DELETE {$endpoint} failed: " . $ex->getMessage());
                return null;
            }
        }
    }
}
