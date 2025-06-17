<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;

    protected $table = 'laporan';
    protected $primaryKey = 'id_laporan';

    protected $fillable = [
        'jenis_laporan',
        'user',
        'keterangan',
        'laboratorium',
        'waktu_laporan',
    ];

    protected $casts = [
        'waktu_laporan' => 'datetime',
    ];
}
