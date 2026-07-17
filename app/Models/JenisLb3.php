<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisLb3 extends Model
{
    protected $fillable = [
        'nama',
    ];

    public function registrasiUsahas(): HasMany
    {
        return $this->hasMany(RegistrasiUsahaLb3::class, 'jenis_lb3_id');
    }
}
