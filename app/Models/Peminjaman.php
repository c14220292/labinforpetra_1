<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';
    protected $primaryKey = 'id_peminjaman';

    protected $fillable = [
        'id_perlengkapan',
        'laboratorium',
        'email_peminjaman',
        'waktu_peminjaman',
        'waktu_pengembalian',
        'status',
    ];

    protected $casts = [
        'waktu_peminjaman' => 'datetime',
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
