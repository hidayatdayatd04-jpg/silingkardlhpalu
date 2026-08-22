<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\CarbonInterface;

class ArtikelKomentar extends Model
{
    use SoftDeletes;

    protected $table = 'artikel_komentar';

    protected $fillable = [
        'artikel_id',
        'parent_id',
        'user_id',
        'nama',
        'email',
        'body',
        'is_hidden',
        'is_pinned',
        'is_admin',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'is_pinned' => 'boolean',
        'is_admin' => 'boolean',
    ];

    protected $appends = ['time_ago', 'initials', 'display_name'];

    public function artikel(): BelongsTo
    {
        return $this->belongsTo(Artikel::class, 'artikel_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        // balasan admin selalu paling atas di dalam thread-nya
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('is_admin', 'desc')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'asc');
    }

    // replies yang visible untuk public (non hidden)
    public function visibleReplies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('is_hidden', false)
            ->orderBy('is_admin', 'desc')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'asc');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ArtikelKomentarReaction::class, 'komentar_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ArtikelKomentarReaction::class, 'komentar_id')->where('type', 'like');
    }

    public function loves(): HasMany
    {
        return $this->hasMany(ArtikelKomentarReaction::class, 'komentar_id')->where('type', 'love');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->is_admin && $this->user) {
            return $this->user->name;
        }
        if ($this->user) {
            return $this->user->name;
        }
        return $this->nama ?: 'Anonim';
    }

    public function getInitialsAttribute(): string
    {
        $name = $this->display_name;
        $parts = preg_split('/\s+/', trim($name));
        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
        }
        return mb_strtoupper(mb_substr($name, 0, 2));
    }

    public function getTimeAgoAttribute(): string
    {
        return static::timeAgoId($this->created_at);
    }

    public static function timeAgoId(?CarbonInterface $date): string
    {
        if (! $date) return '-';
        $now = now();
        $diff = $now->diffInSeconds($date, false);
        // diff negative if date in past? Actually now->diffInSeconds(date,false) negative when date past. Use abs.
        $seconds = abs((int) $now->diffInSeconds($date));
        if ($seconds < 60) {
            return $seconds <= 5 ? 'Baru saja' : $seconds . ' Detik Lalu';
        }
        $minutes = intdiv($seconds, 60);
        if ($minutes < 60) {
            return $minutes === 1 ? '1 Menit Lalu' : $minutes . ' Menit Lalu';
        }
        $hours = intdiv($minutes, 60);
        if ($hours < 24) {
            return $hours === 1 ? '1 Jam Lalu' : $hours . ' Jam Lalu';
        }
        $days = intdiv($hours, 24);
        if ($days < 7) {
            return $days === 1 ? '1 Hari Lalu' : $days . ' Hari Lalu';
        }
        if ($days < 30) {
            $weeks = intdiv($days, 7);
            return $weeks === 1 ? '1 Minggu Lalu' : $weeks . ' Minggu Lalu';
        }
        $months = intdiv($days, 30);
        if ($months < 12) {
            return $months === 1 ? '1 Bulan Lalu' : $months . ' Bulan Lalu';
        }
        $years = intdiv($months, 12);
        return $years === 1 ? '1 Tahun Lalu' : $years . ' Tahun Lalu';
    }

    public function scopeRoot($q)
    {
        return $q->whereNull('parent_id');
    }

    public function scopeVisible($q)
    {
        return $q->where('is_hidden', false);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('is_pinned', 'desc')->orderBy('created_at', 'desc');
    }
}
