<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('jenis_lb3s')
            ->where('nama', 'DLL')
            ->update(['nama' => 'Lainnya']);
    }

    public function down(): void
    {
        DB::table('jenis_lb3s')
            ->where('nama', 'Lainnya')
            ->update(['nama' => 'DLL']);
    }
};
