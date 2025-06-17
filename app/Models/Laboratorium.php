<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laboratorium extends Model
{
    use HasFactory;

    protected $table = 'laboratorium';
    protected $primaryKey = 'id_lab';

    protected $fillable = [
        'kode_lab',
        'nama_lab',
        'gedung',
    ];

    public function penggunas()
    {
        return $this->belongsToMany(Pengguna::class, 'pengguna_lab', 'kode_lab', 'id_pengguna', 'kode_lab', 'id_pengguna');
    }

    public function perlengkapans()
    {
        return $this->hasMany(Perlengkapan::class, 'laboratorium', 'kode_lab');
    }
}
