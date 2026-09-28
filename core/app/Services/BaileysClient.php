<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BaileysClient
{
    protected static $resolvedBaseUrl = null;

    /**
     * Get the active Baileys service base URL with auto-detection across ports 3333 and 3000
     */
    public static function getBaseUrl($forceProbe = false)
    {
        if (self::$resolvedBaseUrl && !$forceProbe) {
            return self::$resolvedBaseUrl;
        }

        $envUrl = env('BAILEYS_URL', env('WHATSAPP_SERVER_URL'));
        if (!empty($envUrl)) {
            $envUrl = rtrim($envUrl, '/');
            // If explicitly configured, test it quickly
            try {
                $check = Http::timeout(0.6)->get($envUrl . '/health');
                if ($check && $check->successful()) {
                    self::$resolvedBaseUrl = $envUrl;
                    return self::$resolvedBaseUrl;
                }
            } catch (\Throwable $e) {}
        }

        // Candidate ports (3333 is primary on live server PM2, 3000 is fallback/local)
        $candidates = array_unique(array_filter([
            $envUrl,
            'http://127.0.0.1:3333',
            'http://localhost:3333',
            'http://127.0.0.1:3000',
            'http://localhost:3000',
        ]));

        foreach ($candidates as $cand) {
            $cand = rtrim($cand, '/');
            try {
                $res = Http::timeout(0.5)->get($cand . '/health');
                if ($res && $res->successful()) {
                    self::$resolvedBaseUrl = $cand;
                    return self::$resolvedBaseUrl;
                }
            } catch (\Throwable $e) {}
        }

        // Default to live PM2 port 3333 if neither responded yet
        self::$resolvedBaseUrl = !empty($envUrl) ? $envUrl : 'http://127.0.0.1:3333';
        return self::$resolvedBaseUrl;
    }

    /**
     * Ensure the Baileys Node.js background process is alive
     */
    public static function ensureServiceRunning()
    {
        try {
            $baseUrl = self::getBaseUrl();
            try {
                $res = Http::timeout(1.5)->get($baseUrl . '/health');
                if ($res && $res->successful()) {
                    return true;
                }
            } catch (\Throwable $e) {}

            // Try re-detecting active port before spawning
            $activeUrl = self::getBaseUrl(true);
            try {
                $res = Http::timeout(1.5)->get($activeUrl . '/health');
                if ($res && $res->successful()) {
                    return true;
                }
            } catch (\Throwable $e) {}

            self::spawnNodeService();

            // Wait up to 3 seconds for service to answer health check
            for ($i = 0; $i < 6; $i++) {
                usleep(500000); // 500ms
                $activeUrl = self::getBaseUrl(true);
                try {
                    $res = Http::timeout(1.5)->get($activeUrl . '/health');
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

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $url = rtrim(self::getBaseUrl(), '/') . '/' . ltrim($endpoint, '/');
            try {
                $response = Http::timeout($timeout)->post($url, $data);
                return $response;
            } catch (\Throwable $e) {
                if ($attempt === 1) {
                    self::$resolvedBaseUrl = null; // force rediscovery
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

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $url = rtrim(self::getBaseUrl(), '/') . '/' . ltrim($endpoint, '/');
            try {
                $response = Http::timeout($timeout)->get($url, $query);
                return $response;
            } catch (\Throwable $e) {
                if ($attempt === 1) {
                    self::$resolvedBaseUrl = null; // force rediscovery
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

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $url = rtrim(self::getBaseUrl(), '/') . '/' . ltrim($endpoint, '/');
            try {
                return Http::timeout($timeout)->delete($url, $data);
            } catch (\Throwable $e) {
                if ($attempt === 1) {
                    self::$resolvedBaseUrl = null;
                    self::ensureServiceRunning();
                    usleep(500000);
                } else {
                    Log::warning("BaileysClient DELETE {$endpoint} failed: " . $e->getMessage());
                    return null;
                }
            }
        }

        return null;
    }
}
