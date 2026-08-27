<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\ArtikelKomentar;
use App\Models\ArtikelKomentarReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ArtikelKomentarController extends Controller
{
    private const SORTS = ['terbaru', 'teratas'];

    public function index(Request $request, string $slug)
    {
        $artikel = Artikel::where('slug', $slug)->firstOrFail();
        abort_if($artikel->isExternal(), 404);
        if(!($artikel->komentar_enabled ?? true)){
            return response()->json(['data'=>[],'current_page'=>1,'last_page'=>1,'total'=>0,'total_comments'=>0,'komentar_disabled'=>true]);
        }

        $sort = (string) $request->query('sort', 'terbaru');
        if (! in_array($sort, self::SORTS, true)) {
            $sort = 'terbaru';
        }

        $page = max(1, (int) $request->query('page', 1));

        $query = ArtikelKomentar::query()
            ->with(['user', 'replies' => fn ($q) => $q->where('is_hidden', false)
                // balasan admin selalu paling atas di dalam thread-nya
                ->orderBy('is_admin', 'desc')->orderBy('is_pinned', 'desc')->orderBy('created_at', 'asc')])
            ->withCount(['likes', 'loves'])
            ->where('artikel_id', $artikel->id)
            ->whereNull('parent_id')
            ->where('is_hidden', false)
            // komentar admin selalu paling atas sebelum urutan lainnya
            ->orderBy('is_admin', 'desc')
            ->orderBy('is_pinned', 'desc');

        if ($sort === 'teratas') {
            $query->orderByRaw(
                '(select count(*) from artikel_komentar_reaction r where r.komentar_id = artikel_komentar.id and r.type = ?) desc',
                ['love']
            );
        }

        $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        $komentars = $query->paginate(10, ['*'], 'page', $page);

        $komentars->getCollection()->transform(fn (ArtikelKomentar $k) => $this->serializeComment($k));

        return response()->json([
            'data' => $komentars->items(),
            'current_page' => $komentars->currentPage(),
            'last_page' => $komentars->lastPage(),
            'total' => $komentars->total(),
            'total_comments' => $this->visibleCount($artikel),
        ]);
    }

    public function count(string $slug)
    {
        $artikel = Artikel::where('slug', $slug)->firstOrFail();
        abort_if($artikel->isExternal(), 404);
        if(!($artikel->komentar_enabled ?? true)){
            return response()->json(['total_comments'=>0,'komentar_disabled'=>true]);
        }

        return response()->json([
            'total_comments' => $this->visibleCount($artikel),
        ]);
    }

    public function store(Request $request, string $slug)
    {
        $artikel = Artikel::where('slug', $slug)->firstOrFail();
        abort_if($artikel->isExternal(), 404);
        if(!($artikel->komentar_enabled ?? true)){
            return response()->json(['message'=>'Komentar dinonaktifkan untuk artikel ini.'], 403);
        }

        $key = 'komentar-store:'.$this->fingerprint($request).'/'.$artikel->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'Terlalu banyak komentar. Coba beberapa saat lagi.'], 429);
        }
        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:10000'],
            'nama' => ['nullable', 'string', 'max:60'],
            'parent_id' => ['nullable', 'integer'],
        ]);

        $parent = null;
        if (! empty($validated['parent_id'])) {
            $target = ArtikelKomentar::where('id', $validated['parent_id'])
                ->where('artikel_id', $artikel->id)
                ->where('is_hidden', false)
                ->first();

            if (! $target) {
                return response()->json(['message' => 'Komentar induk tidak ditemukan.'], 422);
            }

            // batasi 1 level: balasan dari balasan diratakan ke komentar utama
            if ($target->parent_id !== null) {
                $root = ArtikelKomentar::where('id', $target->parent_id)
                    ->where('artikel_id', $artikel->id)
                    ->where('is_hidden', false)
                    ->first();

                if (! $root) {
                    return response()->json(['message' => 'Komentar induk tidak ditemukan.'], 422);
                }
                $target = $root;
            }

            $parent = $target;
        }

        $komentar = ArtikelKomentar::create([
            'artikel_id' => $artikel->id,
            'parent_id' => $parent?->id,
            'nama' => $validated['nama'] ?? null,
            'body' => trim($validated['body']),
            'is_hidden' => false,
            'is_pinned' => false,
            'is_admin' => false,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit($request->userAgent() ?? '', 500, ''),
        ]);

        try {
            $senderName = $komentar->display_name ?: 'Pengunjung';
            if ($parent) {
                \App\Support\AdminNotifier::toGroup('konten', [
                    'title' => 'Balasan Komentar Baru',
                    'message' => "{$senderName} membalas komentar pada artikel \"{$artikel->judul}\".",
                    'icon' => 'message-circle',
                    'color' => 'sky',
                    'href' => route('admin.artikel.komentar.index', $artikel->id),
                    'module' => 'artikel',
                ]);
            } else {
                \App\Support\AdminNotifier::toGroup('konten', [
                    'title' => 'Komentar Artikel Baru',
                    'message' => "{$senderName} mengomentari artikel \"{$artikel->judul}\".",
                    'icon' => 'message-circle',
                    'color' => 'sky',
                    'href' => route('admin.artikel.komentar.index', $artikel->id),
                    'module' => 'artikel',
                ]);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Komentar terkirim.',
            'komentar' => $this->serializeComment($komentar),
            'total_comments' => $this->visibleCount($artikel),
        ], 201);
    }

    public function toggleReaction(Request $request, int $id)
    {
        $komentar = ArtikelKomentar::with('artikel')->where('is_hidden', false)->findOrFail($id);
        abort_if($komentar->artikel?->isExternal(), 404);

        $validated = $request->validate([
            'type' => ['required', 'in:like,love'],
        ]);

        $type = $validated['type'];
        $fp = $this->fingerprint($request);
        $candidates = $this->fingerprintCandidates($request);

        try {
            DB::transaction(function () use ($komentar, $candidates, $type, $fp, $request) {
                $mine = ArtikelKomentarReaction::where('komentar_id', $komentar->id)
                    ->whereIn('fingerprint', $candidates)
                    ->lockForUpdate()
                    ->get();

                $hadSameType = $mine->contains(fn (ArtikelKomentarReaction $r) => $r->type === $type);

                if ($mine->isNotEmpty()) {
                    ArtikelKomentarReaction::whereKey($mine->pluck('id')->all())->delete();
                }

                if (! $hadSameType) {
                    ArtikelKomentarReaction::create([
                        'komentar_id' => $komentar->id,
                        'type' => $type,
                        'fingerprint' => $fp,
                        'user_id' => auth()->id(),
                        'ip_address' => $request->ip(),
                    ]);
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // race condition klik ganda — abaikan
        }

        $komentar->loadCount(['likes', 'loves']);
        $myReactions = $this->myReactionsFor($komentar, $request);

        return response()->json([
            'loves_count' => $komentar->loves_count,
            'likes_count' => $komentar->likes_count,
            'loved' => $myReactions->contains('type', 'love'),
            'liked' => $myReactions->contains('type', 'like'),
            'my_reactions' => $myReactions->pluck('type')->values()->all(),
        ]);
    }

    /* ─── helpers ─── */

    private function visibleCount(Artikel $artikel): int
    {
        return ArtikelKomentar::where('artikel_id', $artikel->id)
            ->where('is_hidden', false)
            ->where(function ($q) {
                $q->whereNull('parent_id')
                    ->orWhereHas('parent', fn ($p) => $p->where('is_hidden', false));
            })
            ->count();
    }

    private function serializeComment(ArtikelKomentar $k): array
    {
        return [
            'id' => $k->id,
            'artikel_id' => $k->artikel_id,
            'parent_id' => $k->parent_id,
            'nama' => $k->display_name,
            'body' => nl2br(e($k->body)),
            'is_admin' => $k->is_admin,
            'is_pinned' => $k->is_pinned,
            'is_hidden' => $k->is_hidden,
            'initials' => $k->initials,
            'time_ago' => $k->time_ago,
            'created_at' => $k->created_at?->toISOString(),
            'loves_count' => $k->loves_count ?? 0,
            'likes_count' => $k->likes_count ?? 0,
            'replies' => $k->relationLoaded('replies')
                ? $k->replies->map(fn (ArtikelKomentar $r) => $this->serializeComment($r))
                : [],
        ];
    }

    private function fingerprint(Request $request): string
    {
        $header = $request->header('X-Fingerprint');
        if ($header && preg_match('/^[A-Za-z0-9_-]{8,64}$/', $header)) {
            return $header;
        }

        $cookie = $request->cookie('dlh_fp');
        if ($cookie && preg_match('/^[A-Za-z0-9_-]{8,64}$/', $cookie)) {
            return $cookie;
        }

        return hash('sha256', ($request->ip() ?? '').($request->userAgent() ?? ''));
    }

    private function fingerprintCandidates(Request $request): array
    {
        $active = $this->fingerprint($request);
        $candidates = [$active];

        $cookie = $request->cookie('dlh_fp');
        if ($cookie && $cookie !== $active) {
            $candidates[] = $cookie;
        }

        $hash = hash('sha256', ($request->ip() ?? '').($request->userAgent() ?? ''));
        if ($hash !== $active) {
            $candidates[] = $hash;
        }

        return array_unique($candidates);
    }

    private function myReactionsFor(ArtikelKomentar $komentar, Request $request)
    {
        $candidates = $this->fingerprintCandidates($request);

        return ArtikelKomentarReaction::where('komentar_id', $komentar->id)
            ->whereIn('fingerprint', $candidates)
            ->get();
    }
}
