<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BaileysClient
{
    protected static $baseUrl = 'http://127.0.0.1:3000';

    /**
     * Ensure the Baileys Node.js background process is alive
     */
    public static function ensureServiceRunning()
    {
        try {
            $res = Http::timeout(2)->get(self::$baseUrl . '/health');
            if ($res->successful()) {
                return true;
            }
        } catch (\Throwable $e) {
            self::spawnNodeService();
        }

        // Wait up to 4 seconds for service to answer health check
        for ($i = 0; $i < 8; $i++) {
            usleep(500000); // 500ms
            try {
                $res = Http::timeout(2)->get(self::$baseUrl . '/health');
                if ($res->successful()) {
                    return true;
                }
            } catch (\Throwable $e) {}
        }

        return false;
    }

    /**
     * Start the Baileys Node.js microservice in the background on Windows
     */
    public static function spawnNodeService()
    {
        $servicePath = base_path('../baileys-service');
        if (!is_dir($servicePath)) {
            $servicePath = base_path('baileys-service');
        }

        if (is_dir($servicePath)) {
            $escapedPath = escapeshellarg($servicePath);
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                pclose(popen("start /B cmd /c cd /d {$escapedPath} && node server.js", "r"));
            } else {
                exec("cd {$escapedPath} && node server.js > /dev/null 2>&1 &");
            }
        }
    }

    /**
     * Resilient POST request to Baileys with automatic retry and watchdog
     */
    public static function post($endpoint, array $data = [], $timeout = 15)
    {
        $url = rtrim(self::$baseUrl, '/') . '/' . ltrim($endpoint, '/');

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $response = Http::timeout($timeout)->post($url, $data);
                return $response;
            } catch (\Throwable $e) {
                if ($attempt === 1) {
                    self::ensureServiceRunning();
                    usleep(500000);
                } else {
                    throw $e;
                }
            }
        }

        return Http::timeout($timeout)->post($url, $data);
    }

    /**
     * Resilient GET request to Baileys with automatic retry and watchdog
     */
    public static function get($endpoint, array $query = [], $timeout = 10)
    {
        $url = rtrim(self::$baseUrl, '/') . '/' . ltrim($endpoint, '/');

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $response = Http::timeout($timeout)->get($url, $query);
                return $response;
            } catch (\Throwable $e) {
                if ($attempt === 1) {
                    self::ensureServiceRunning();
                    usleep(500000);
                } else {
                    throw $e;
                }
            }
        }

        return Http::timeout($timeout)->get($url, $query);
    }

    /**
     * Resilient DELETE request
     */
    public static function delete($endpoint, array $data = [], $timeout = 10)
    {
        $url = rtrim(self::$baseUrl, '/') . '/' . ltrim($endpoint, '/');

        try {
            return Http::timeout($timeout)->delete($url, $data);
        } catch (\Throwable $e) {
            self::ensureServiceRunning();
            return Http::timeout($timeout)->delete($url, $data);
        }
    }
}
