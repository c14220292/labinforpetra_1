<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemeliharaan extends Model
{
    use HasFactory;

    protected $table = 'pemeliharaan';
    protected $primaryKey = 'id_pemeliharaan';

    protected $fillable = [
        'id_perlengkapan',
        'laboratorium',
        'waktu_pemeliharaan',
        'waktu_pengembalian',
        'deskripsi_pemeliharaan',
        'status',
    ];

    protected $casts = [
        'waktu_pemeliharaan' => 'datetime',
        'waktu_pengembalian' => 'datetime',
    ];

    public function perlengkapan()
    {
        return $this->belongsTo(Perlengkapan::class, 'id_perlengkapan', 'id_perlengkapan');
    }

    public function laboratoriumModel()
    {
        return $this->belongsTo(Laboratorium::class, 'laboratorium', 'kode_lab');
    }
}
