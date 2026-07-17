<?php

namespace App\Enums;

enum NavigationGroup: string
{
    case PENGENDALIAN = 'Pengendalian Dampak Lingkungan';
    case SAMPAH_LB3 = 'Pengelolaan Sampah & LB3';
    case TATA_PENATAAN = 'Tata Penataan';
    case RTH = 'Ruang Terbuka Hijau';
    case SISTEM = 'Sistem';
    case UMUM = 'Umum';
}
