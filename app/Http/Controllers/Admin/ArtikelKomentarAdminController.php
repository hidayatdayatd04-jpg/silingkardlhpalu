<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Models\ArtikelKomentar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArtikelKomentarAdminController extends Controller
{
    private const SORTS = ['terbaru', 'terlama', 'teratas'];

    /** Batas jumlah komentar utama yang boleh disematkan per artikel. */
    private const MAX_PINNED = 3;

    public function index(Request $request, string $artikelId)
    {
        $artikel = $this->internalArtikel($artikelId);

        $sort = (string) $request->query('sort', 'terbaru');
        if (!in_array($sort, self::SORTS, true)) {
            $sort = 'terbaru';
        }

        $q = ArtikelKomentar::query()
            ->with(['user', 'replies' => fn($qq)=>$qq->withCount(['likes','loves'])
                // balasan admin selalu paling atas di dalam thread-nya
                ->orderBy('is_admin','desc')->orderBy('is_pinned','desc')->orderBy('created_at','asc')])
            ->withCount(['likes', 'loves', 'replies'])
            ->where('artikel_id', $artikel->id)
            ->whereNull('parent_id');

        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $q->where(function ($qb) use ($search) {
                $qb->where('body', 'ilike', '%'.$search.'%')
                   ->orWhere('nama', 'ilike', '%'.$search.'%');
            });
        }

        switch ((string) $request->query('status')) {
            case 'hidden':
                $q->where('is_hidden', true);
                break;
            case 'visible':
                $q->where('is_hidden', false);
                break;
            case 'pinned':
                $q->where('is_pinned', true);
                break;
            case 'admin':
                $q->where('is_admin', true);
                break;
        }

        // komentar admin selalu paling atas sebelum urutan lainnya
        $q->orderBy('is_admin', 'desc')->orderBy('is_pinned', 'desc');

        if ($sort === 'teratas') {
            $q->orderByRaw(
                '(select count(*) from artikel_komentar_reaction r'
                .' where r.komentar_id = artikel_komentar.id and r.type = ?) desc',
                ['love']
            )->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        } elseif ($sort === 'terlama') {
            $q->orderBy('created_at', 'asc')->orderBy('id', 'asc');
        } else {
            $q->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        }

        $komentars = $q->paginate(20)->withQueryString();

        // satu query agregat, bukan 4 query terpisah di dalam view
        $stats = ArtikelKomentar::where('artikel_id', $artikel->id)
            ->selectRaw('count(*) as total')
            ->selectRaw('count(*) filter (where is_hidden = false) as visible')
            ->selectRaw('count(*) filter (where is_hidden = true) as hidden')
            ->selectRaw('count(*) filter (where is_pinned = true) as pinned')
            ->selectRaw('count(*) filter (where is_admin = true) as admin')
            ->selectRaw('count(*) filter (where parent_id is null) as root')
            ->first();

        return view('admin.artikel.komentar', [
            'artikel' => $artikel,
            'komentars' => $komentars,
            'sort' => $sort,
            'stats' => [
                'total' => (int) ($stats->total ?? 0),
                'visible' => (int) ($stats->visible ?? 0),
                'hidden' => (int) ($stats->hidden ?? 0),
                'pinned' => (int) ($stats->pinned ?? 0),
                'admin' => (int) ($stats->admin ?? 0),
                'root' => (int) ($stats->root ?? 0),
                'reply' => (int) (($stats->total ?? 0) - ($stats->root ?? 0)),
            ],
        ]);
    }

    public function store(Request $request, string $artikelId)
    {
        $artikel = $this->internalArtikel($artikelId);

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:2000'],
            'parent_id' => ['nullable', 'integer'],
            'pin' => ['nullable', 'boolean'],
        ]);

        $parent = null;
        if (!empty($validated['parent_id'])) {
            $parent = ArtikelKomentar::where('id', $validated['parent_id'])
                ->where('artikel_id', $artikel->id)
                ->first();

            if (!$parent) {
                return back()
                    ->withInput()
                    ->withErrors(['parent_id' => 'Komentar yang dibalas tidak ditemukan pada artikel ini.']);
            }
            // batasi 1 level: balasan dari balasan diratakan ke induk utama
            if ($parent->parent_id !== null) {
                $parent = ArtikelKomentar::where('id', $parent->parent_id)
                    ->where('artikel_id', $artikel->id)
                    ->first() ?? $parent;
            }
        }

        // Balasan tidak disematkan (semat hanya berlaku untuk komentar utama).
        $pin = $parent === null && $request->boolean('pin', true);

        // batas semat: jika sudah penuh, kirim komentar tanpa semat
        $pinSkipped = false;
        if ($pin && $this->pinnedCount($artikel->id) >= self::MAX_PINNED) {
            $pin = false;
            $pinSkipped = true;
        }

        ArtikelKomentar::create([
            'artikel_id' => $artikel->id,
            'parent_id' => $parent?->id,
            'user_id' => auth()->id(),
            'nama' => auth()->user()?->name,
            'body' => trim($validated['body']),
            'is_admin' => true,
            'is_pinned' => $pin,
            'is_hidden' => false,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit($request->userAgent() ?? '', 500, ''),
        ]);

        return back()->with('success', $parent
            ? 'Balasan admin berhasil dikirim.'
            : ($pin
                ? 'Komentar admin ditambahkan dan disematkan.'
                : ($pinSkipped
                    ? 'Komentar admin ditambahkan. Batas semat tercapai (maksimal '.self::MAX_PINNED.'), komentar dikirim tanpa semat.'
                    : 'Komentar admin ditambahkan.')));
    }

    /**
     * Sembunyikan / tampilkan komentar.
     * Menyembunyikan komentar utama juga menyembunyikan seluruh balasannya,
     * supaya tidak ada balasan "menggantung" yang tetap tampil di publik.
     */
    public function toggleHide(string $artikelId, int $id)
    {
        $this->internalArtikel($artikelId);
        $k = ArtikelKomentar::where('artikel_id', $artikelId)->findOrFail($id);
        $next = !$k->is_hidden;

        DB::transaction(function () use ($k, $next) {
            $k->is_hidden = $next;
            $k->save();

            if ($k->parent_id === null) {
                ArtikelKomentar::where('parent_id', $k->id)->update([
                    'is_hidden' => $next,
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', $next
            ? 'Komentar disembunyikan'.($k->parent_id === null ? ' beserta balasannya.' : '.')
            : 'Komentar ditampilkan kembali'.($k->parent_id === null ? ' beserta balasannya.' : '.'));
    }

    /** Semat hanya untuk komentar utama. */
    public function togglePin(string $artikelId, int $id)
    {
        $this->internalArtikel($artikelId);
        $k = ArtikelKomentar::where('artikel_id', $artikelId)->findOrFail($id);

        if ($k->parent_id !== null) {
            return back()->withErrors(['pin' => 'Balasan tidak dapat disematkan.']);
        }
        if ($k->is_hidden && !$k->is_pinned) {
            return back()->withErrors(['pin' => 'Tampilkan komentar terlebih dahulu sebelum menyematkan.']);
        }

        // batas semat: maksimal N komentar utama per artikel
        if (!$k->is_pinned && $this->pinnedCount($artikelId) >= self::MAX_PINNED) {
            return back()->withErrors(['pin' => 'Batas semat tercapai — maksimal '.self::MAX_PINNED.' komentar utama. Lepas semat lainnya terlebih dahulu.']);
        }

        $k->is_pinned = !$k->is_pinned;
        $k->save();

        return back()->with('success', $k->is_pinned ? 'Komentar disematkan.' : 'Semat dilepas.');
    }

    /** Jumlah komentar utama yang sedang disematkan pada sebuah artikel. */
    private function pinnedCount(string $artikelId): int
    {
        return ArtikelKomentar::where('artikel_id', $artikelId)
            ->whereNull('parent_id')
            ->where('is_pinned', true)
            ->count();
    }

    public function bulkDestroy(Request $request, string $artikelId)
    {
        $this->internalArtikel($artikelId);
        $request->validate(['ids' => ['required','array'], 'ids.*' => ['integer']]);
        $ids = $request->input('ids', []);
        $deleted = 0;
        DB::transaction(function() use ($artikelId, $ids, &$deleted){
            $komentars = ArtikelKomentar::where('artikel_id', $artikelId)->whereIn('id', $ids)->get();
            foreach($komentars as $k){
                if($k->parent_id === null){
                    $children = ArtikelKomentar::where('parent_id', $k->id)->get();
                    foreach($children as $child){ $child->reactions()->delete(); $child->delete(); }
                }
                $k->reactions()->delete();
                $k->delete();
                $deleted++;
            }
        });
        return back()->with('success', $deleted.' komentar terpilih berhasil dihapus.');
    }

    /**
     * Hapus komentar. Soft delete TIDAK ikut ke balasan secara otomatis,
     * jadi balasan dihapus eksplisit di sini agar tidak jadi data yatim.
     */
    public function destroy(string $artikelId, int $id)
    {
        $this->internalArtikel($artikelId);
        $k = ArtikelKomentar::where('artikel_id', $artikelId)->findOrFail($id);

        $replies = 0;
        DB::transaction(function () use ($k, &$replies) {
            if ($k->parent_id === null) {
                $children = ArtikelKomentar::where('parent_id', $k->id)->get();
                $replies = $children->count();
                foreach ($children as $child) {
                    $child->reactions()->delete();
                    $child->delete();
                }
            }
            $k->reactions()->delete();
            $k->delete();
        });

        return back()->with('success', $replies > 0
            ? "Komentar dihapus beserta {$replies} balasan."
            : 'Komentar dihapus.');
    }

    private function internalArtikel(string $artikelId): Artikel
    {
        return Artikel::query()->whereKey($artikelId)->where('article_type', 'internal')->firstOrFail();
    }
}
