<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteVisit extends Model
{
    protected $table = 'website_visit';

    protected $fillable = [
        'visit_date',
        'ip_address',
        'session_id',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
        ];
    }
}
