<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perlengkapan extends Model
{
    use HasFactory;

    protected $table = 'perlengkapan';
    protected $primaryKey = 'id_perlengkapan';

    protected $fillable = [
        'kode_seri',
        'nama_perlengkapan',
        'jenis_perlengkapan',
        'set_komputer',
        'spesifikasi',
        'laboratorium',
        'kondisi',
        'status',
        'waktu_masuk',
    ];

    protected $casts = [
        'waktu_masuk' => 'datetime',
    ];

    public function laboratoriumModel()
    {
        return $this->belongsTo(Laboratorium::class, 'laboratorium', 'kode_lab');
    }

    public function pemeliharaans()
    {
        return $this->hasMany(Pemeliharaan::class, 'id_perlengkapan', 'id_perlengkapan');
    }

    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class, 'id_perlengkapan', 'id_perlengkapan');
    }
}
