<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GpsVehicleCache extends Model
{
    protected $table = 'gps_vehicle_cache';

    protected $primaryKey = 'imei';

    /**
     * Kolom yang aman dipublikasikan (endpoint /api/armada-aktif & peta
     * persampahan). 'raw_data' sengaja dikecualikan agar payload mentah
     * GPS tracker tidak bocor ke pengunjung.
     */
    public const PUBLIC_COLUMNS = [
        'imei',
        'title',
        'veh_type',
        'latitude',
        'longitude',
        'speed',
        'angle',
        'acc',
        'server_time',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'imei',
        'title',
        'veh_type',
        'latitude',
        'longitude',
        'speed',
        'angle',
        'acc',
        'server_time',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'acc' => 'integer',
        'server_time' => 'datetime',
    ];
}
