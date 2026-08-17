<?php

namespace App\Services;

use App\Models\GpsVehicleCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GpsService
{
    protected string $loginUrl;

    protected string $monitoringUrl;

    protected string $username;

    protected string $password;

    protected float $defaultLat;

    protected float $defaultLng;

    public function __construct()
    {
        $this->loginUrl = config('services.gps.login_url');
        $this->monitoringUrl = config('services.gps.monitoring_url');
        $this->username = config('services.gps.username');
        $this->password = config('services.gps.password');
        $this->defaultLat = (float) config('services.gps.default_lat');
        $this->defaultLng = (float) config('services.gps.default_lng');
    }

    public function getToken(bool $forceRefresh = false): ?string
    {
        if (! $forceRefresh) {
            $cachedToken = Cache::get('gps_token');
            if ($cachedToken) {
                return $cachedToken;
            }
        }

        try {
            $response = Http::post($this->loginUrl, [
                'username' => $this->username,
                'password' => $this->password,
                'lat_lon' => "{$this->defaultLat}, {$this->defaultLng}",
                'client_ip' => request()->ip() ?? '127.0.0.1',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === true && isset($data['message']['data']['token'])) {
                    $token = $data['message']['data']['token'];
                    Cache::put('gps_token', $token, now()->addHours(12));

                    return $token;
                }
            }
        } catch (\Exception $e) {
            Log::error('GPS Login Failed: '.$e->getMessage());
        }

        return null;
    }

    public function fetchAndCache(): bool
    {
        $token = $this->getToken();
        if (! $token) {
            return false;
        }

        $response = $this->fetchWithToken($token);

        if ($this->isUnauthorized($response)) {
            $token = $this->getToken(true);
            if ($token) {
                $response = $this->fetchWithToken($token);
            }
        }

        if ($response && $response->successful()) {
            $data = $response->json();
            if (isset($data['status']) && $data['status'] === true && isset($data['message']['data'])) {
                $vehicles = $data['message']['data'];
                $this->updateCache($vehicles);

                return true;
            }
        }

        return false;
    }

    protected function fetchWithToken(string $token)
    {
        try {
            return Http::withToken($token)->get($this->monitoringUrl);
        } catch (\Exception $e) {
            Log::error('GPS Fetch Failed: '.$e->getMessage());

            return null;
        }
    }

    protected function isUnauthorized($response): bool
    {
        if (! $response) {
            return true;
        }

        if ($response->status() === 401) {
            return true;
        }

        $data = $response->json();
        if (isset($data['status']) && $data['status'] === false && isset($data['message']) && stripos($data['message'], 'token') !== false) {
            return true;
        }

        return false;
    }

    protected function updateCache(array $vehicles): void
    {
        $upsertData = [];
        $now = now();

        foreach ($vehicles as $v) {
            if (empty($v['imei'])) {
                continue;
            }

            $upsertData[] = [
                'imei' => (string) $v['imei'],
                'title' => $v['title'] ?? ($v['plate'] ?? 'Unknown'),
                'veh_type' => (int) ($v['veh_type'] ?? 3),
                'latitude' => (float) ($v['latitude'] ?? $this->defaultLat),
                'longitude' => (float) ($v['longitude'] ?? $this->defaultLng),
                'speed' => (int) ($v['speed'] ?? 0),
                'angle' => (int) ($v['angle'] ?? 0),
                'acc' => (int) ($v['acc'] ?? 0),
                'server_time' => $v['server_time'] ?? $now->toDateTimeString(),
                'raw_data' => json_encode($v),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($upsertData)) {
            GpsVehicleCache::upsert($upsertData, ['imei'], [
                'title',
                'veh_type',
                'latitude',
                'longitude',
                'speed',
                'angle',
                'acc',
                'server_time',
                'raw_data',
                'updated_at',
            ]);
        }
    }
}
