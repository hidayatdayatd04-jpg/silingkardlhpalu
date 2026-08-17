<?php

use App\Models\Artikel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ids = DB::table('artikel')
            ->whereNull('slug')
            ->orWhere('slug', '')
            ->pluck('id');

        foreach ($ids as $id) {
            $artikel = DB::table('artikel')->where('id', $id)->first();

            if (! $artikel || ! $artikel->judul) {
                continue;
            }

            $slug = Artikel::generateUniqueSlug($artikel->judul, $id);

            DB::table('artikel')->where('id', $id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        // No-op: tidak mengembalikan slug karena akan kembali NULL.
    }
};
