<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use App\Models\Setting;
use App\Services\MonitoringService;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingController extends Controller
{
    public function edit(MonitoringService $monitoring)
    {
        $estimatedAt = Setting::get('maintenance_estimated_at');

        return view('admin.settings.edit', [
            'user'         => auth()->user(),
            'isSuperadmin' => auth()->user()->isSuperadmin(),
            'maintenanceEnabled'    => (bool) Setting::get('maintenance_enabled', false),
            'maintenanceEstimatedAt' => $estimatedAt ? Carbon::parse($estimatedAt)->format('Y-m-d\TH:i') : '',
            'captchaEnabled' => \App\Support\Captcha::enabled(),
            'b2'   => $monitoring->b2Storage(),
            'neon' => $monitoring->neonDatabase(),
            'providers' => AiProvider::orderBy('priority')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'maintenance_enabled' => ['nullable', 'string', 'in:1,0'],
            'maintenance_estimated_at' => ['nullable', 'string'],
            'captcha_enabled' => ['nullable', 'string', 'in:1,0'],
        ]);

        // Setting global — hanya superadmin
        if ($user->isSuperadmin()) {
            $maintenanceValue = ($validated['maintenance_enabled'] ?? '0') === '1';
            $estimatedAt = ! empty($validated['maintenance_estimated_at'])
                ? Carbon::parse($validated['maintenance_estimated_at'])->format('Y-m-d H:i:s')
                : null;

            Setting::put('maintenance_enabled', $maintenanceValue, 'system');
            Setting::put('maintenance_estimated_at', $estimatedAt, 'system');
            Setting::put('captcha_enabled', ($validated['captcha_enabled'] ?? '0') === '1', 'system');
        }

        ActivityLogger::log('updated', 'Pengaturan diperbarui', 'settings', null, ['settings' => $validated], $user);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function storeProvider(Request $request)
    {
        $this->ensureSuperadmin();

        $validated = $this->validateProvider($request);

        $provider = AiProvider::create([
            'name'      => $validated['name'],
            'type'      => $validated['type'],
            'base_url'  => $validated['base_url'],
            'api_key'   => $validated['api_key'],
            'model'     => $validated['model'],
            'priority'  => $validated['priority'] ?? 1,
            'is_active' => $validated['is_active'],
        ]);

        ActivityLogger::log('created', "Provider AI \"{$provider->name}\" ditambahkan", 'settings', null, ['provider' => $provider->only(['name', 'type', 'base_url', 'model', 'priority'])], $provider);

        return back()->with('success', "Provider \"{$provider->name}\" berhasil ditambahkan.");
    }

    public function updateProvider(Request $request, AiProvider $provider)
    {
        $this->ensureSuperadmin();

        $validated = $this->validateProvider($request, $provider);

        // API key kosong berarti pertahankan key lama — tapi pastikan key lama
        // masih terbaca dengan APP_KEY aktif. Kalau tidak (mis. APP_KEY pernah
        // berganti setelah key disimpan), minta admin mengetik ulang key
        // daripada membiarkan provider rusak atau melempar error 500.
        if (empty($validated['api_key']) && ! $this->storedKeyReadable($provider)) {
            return back()
                ->withInput()
                ->withErrors(['api_key' => 'Kunci layanan tidak dapat digunakan. Masukkan kunci baru untuk layanan ini.']);
        }

        try {
            $old = $provider->only(['name', 'type', 'base_url', 'model', 'priority', 'is_active']);

            $provider->fill([
                'name'      => $validated['name'],
                'type'      => $validated['type'],
                'base_url'  => $validated['base_url'],
                'model'     => $validated['model'],
                'priority'  => $validated['priority'] ?? $provider->priority,
                'is_active' => $validated['is_active'],
            ]);

            if (! empty($validated['api_key'])) {
                $provider->api_key = $validated['api_key'];
            }

            $provider->save();

            ActivityLogger::log('updated', "Layanan AI \"{$provider->name}\" diperbarui", 'settings', $old, ['provider' => $provider->only(['name', 'type', 'base_url', 'model', 'priority', 'is_active'])], $provider);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors(['provider' => 'Gagal menyimpan konfigurasi layanan AI. Silakan periksa kembali data Anda.']);
        }

        return back()->with('success', "Layanan AI \"{$provider->name}\" berhasil diperbarui.");
    }

    /**
     * Cek apakah api_key tersimpan masih dapat didekripsi dengan APP_KEY aktif.
     *
     * Membaca api_key provider yang terenkripsi dengan APP_KEY lama akan
     * melempar DecryptException; bungkus di sini agar pemanggil bisa memberi
     * pesan yang ramah alih-alih 500.
     */
    private function storedKeyReadable(AiProvider $provider): bool
    {
        try {
            return filled($provider->api_key);
        } catch (\Throwable) {
            return false;
        }
    }

    public function destroyProvider(AiProvider $provider)
    {
        $this->ensureSuperadmin();

        $name = $provider->name;

        ActivityLogger::log('deleted', "Layanan AI \"{$name}\" dihapus", 'settings', ['provider' => $provider->only(['name', 'type', 'base_url', 'model', 'priority'])], null, $provider);

        $provider->delete();

        return back()->with('success', "Layanan AI \"{$name}\" berhasil dihapus.");
    }

    /**
     * Ambil daftar model dari provider (OpenRouter, Google, & custom OpenAI-compatible).
     *
     * Saat mengedit provider, api_key boleh kosong dengan menyertakan
     * provider_id — key terenkripsi yang sudah tersimpan akan dipakai,
     * sehingga admin tidak perlu mengetik ulang key hanya untuk memuat model.
     */
    public function fetchModels(Request $request)
    {
        $this->ensureSuperadmin();

        $validated = $request->validate([
            'type'        => ['required', 'in:openrouter,google,custom'],
            'api_key'     => ['nullable', 'string'],
            'provider_id' => ['nullable', 'integer', 'exists:ai_provider,id'],
            'base_url'    => ['nullable', 'required_if:type,custom', 'url'],
        ]);

        $apiKey = $validated['api_key'] ?? '';

        if ($apiKey === '' && ! empty($validated['provider_id'])) {
            try {
                $apiKey = (string) (AiProvider::find($validated['provider_id'])?->api_key ?? '');
            } catch (\Throwable) {
                // Key tersimpan tidak dapat didekripsi dengan APP_KEY aktif
                return response()->json(['error' => 'Kunci layanan tidak dapat digunakan. Masukkan kunci baru secara manual.'], 422);
            }
        }

        if ($apiKey === '') {
            return response()->json(['error' => 'Kunci API wajib diisi.'], 422);
        }

        try {
            if ($validated['type'] === AiProvider::TYPE_CUSTOM) {
                $this->assertSafeOutboundUrl($validated['base_url']);
            }

            $models = match ($validated['type']) {
                AiProvider::TYPE_OPENROUTER => $this->fetchOpenRouterModels(),
                AiProvider::TYPE_GOOGLE     => $this->fetchGoogleModels($apiKey),
                AiProvider::TYPE_CUSTOM     => $this->fetchCustomModels($validated['base_url'], $apiKey),
            };

            return response()->json(['models' => $models]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['error' => 'Gagal memuat daftar model. Pastikan kunci layanan valid dan memiliki akses internet.'], 422);
        }
    }

    /**
     * Model dari endpoint OpenAI-compatible ({base_url}/models).
     *
     * @return list<array{id: string, label: string}>
     */
    private function fetchCustomModels(string $baseUrl, string $apiKey): array
    {
        // withoutRedirecting(): mencegah bypass anti-SSRF lewat redirect
        // dari endpoint luar ke alamat internal.
        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->withoutRedirecting()
            ->get(rtrim($baseUrl, '/') . '/models');

        if ($response->redirect()) {
            throw new \RuntimeException('Endpoint model melakukan redirect (tidak diizinkan).');
        }

        if ($response->failed()) {
            throw new \RuntimeException('Endpoint model merespons dengan status ' . $response->status());
        }

        $models = [];
        foreach ($response->json('data', []) as $item) {
            $id = is_array($item) ? ($item['id'] ?? '') : $item;

            if (is_string($id) && $id !== '') {
                $models[] = ['id' => $id, 'label' => $id];
            }
        }

        usort($models, fn ($a, $b) => strcmp($a['id'], $b['id']));

        return $models;
    }

    /**
     * Model gratis OpenRouter (id berakhiran ":free" atau pricing 0).
     *
     * @return list<array{id: string, label: string}>
     */
    private function fetchOpenRouterModels(): array
    {
        $response = Http::timeout(30)->get('https://openrouter.ai/api/v1/models');

        if ($response->failed()) {
            throw new \RuntimeException('OpenRouter merespons dengan status ' . $response->status());
        }

        $models = [];
        foreach ($response->json('data', []) as $item) {
            $id = $item['id'] ?? '';
            $isFree = str_ends_with($id, ':free')
                || (float) data_get($item, 'pricing.prompt', 1) === 0.0;

            if ($id !== '' && $isFree) {
                $models[] = ['id' => $id, 'label' => $item['name'] ?? $id];
            }
        }

        usort($models, fn ($a, $b) => strcmp($a['id'], $b['id']));

        return $models;
    }

    /**
     * Model Gemini yang tersedia lewat API key Google AI Studio.
     *
     * @return list<array{id: string, label: string}>
     */
    private function fetchGoogleModels(string $apiKey): array
    {
        $response = Http::timeout(30)->get(
            'https://generativelanguage.googleapis.com/v1beta/models',
            ['key' => $apiKey]
        );

        if ($response->failed()) {
            throw new \RuntimeException(data_get($response->json(), 'error.message', 'Google merespons dengan status ' . $response->status()));
        }

        $models = [];
        foreach ($response->json('models', []) as $item) {
            $name = $item['name'] ?? '';
            $methods = $item['supportedGenerationMethods'] ?? [];

            if (! str_starts_with($name, 'models/gemini') || ! in_array('generateContent', $methods, true)) {
                continue;
            }

            $id = substr($name, strlen('models/'));
            $models[] = ['id' => $id, 'label' => $item['displayName'] ?? $id];
        }

        return $models;
    }

    /**
     * Validasi form provider; base URL ditetapkan server-side sesuai tipe.
     *
     * @return array<string, mixed>
     */
    private function validateProvider(Request $request, ?AiProvider $provider = null): array
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'type'      => ['required', 'in:openrouter,google,custom'],
            'base_url'  => ['required_if:type,custom', 'nullable', 'url', 'max:255'],
            'api_key'   => [$provider ? 'nullable' : 'required', 'string', 'max:500'],
            'model'     => ['required', 'string', 'max:150'],
            'priority'  => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_active' => ['nullable', 'string', 'in:1,0'],
        ]);

        // Base URL untuk OpenRouter/Google ditetapkan server-side.
        $validated['base_url'] = $validated['type'] === AiProvider::TYPE_CUSTOM
            ? $validated['base_url']
            : AiProvider::defaultBaseUrls()[$validated['type']];

        // Cegah SSRF: base_url custom tidak boleh menunjuk ke alamat internal.
        if ($validated['type'] === AiProvider::TYPE_CUSTOM) {
            $this->assertSafeOutboundUrl($validated['base_url']);
        }

        $validated['is_active'] = ($validated['is_active'] ?? ($provider ? '0' : '1')) === '1';

        return $validated;
    }

    /**
     * Tolak URL outbound yang mengarah ke alamat internal/privat (anti-SSRF).
     *
     * Dipakai sebelum server menghubungi base_url custom milik admin
     * (fetch model & validasi provider). Host dicek dua lapis: pola nama
     * (localhost, IP privat literal) lalu hasil resolusi DNS-nya.
     */
    private function assertSafeOutboundUrl(string $url): void
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = $parts['host'] ?? '';

        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            throw new \RuntimeException('URL harus http/https dan memiliki host yang valid.');
        }

        $hostLower = strtolower(trim($host, '[]'));

        $blockedPattern = '/^(localhost|127\.|0\.|10\.|172\.(1[6-9]|2\d|3[01])\.|192\.168\.|169\.254\.|::1$|f[cd][0-9a-f]{2}:|fe80:)/i';

        if (preg_match($blockedPattern, $hostLower)) {
            throw new \RuntimeException('URL tidak boleh mengarah ke alamat internal/privat.');
        }

        // Lapis kedua: bila host berupa IP literal, validasi langsung;
        // bila bukan, resolusikan DNS (A + AAAA) lalu validasi SEMUA hasil.
        // dns_get_record dipakai karena gethostbyname hanya mengembalikan
        // satu record IPv4 dan mengabaikan IPv6.
        if (filter_var($hostLower, FILTER_VALIDATE_IP) !== false) {
            $candidates = [$hostLower];
        } else {
            $records = @dns_get_record($hostLower, DNS_A | DNS_AAAA) ?: [];
            $candidates = array_values(array_unique(array_filter(
                array_map(fn ($r) => $r['ip'] ?? $r['ipv6'] ?? null, $records)
            )));
        }

        foreach ($candidates as $ip) {
            $isPublic = filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );

            if (! $isPublic) {
                throw new \RuntimeException('URL tidak boleh mengarah ke alamat internal/privat.');
            }
        }
    }

    private function ensureSuperadmin(): void
    {
        abort_unless(auth()->user()?->isSuperadmin(), 403);
    }
}
