<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GpsVehicleCache extends Model
{
    protected $table = 'gps_vehicle_cache';

    protected $primaryKey = 'imei';

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
