<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'pengguna';
    protected $primaryKey = 'id_pengguna';

    protected $fillable = [
        'nama_pengguna',
        'email',
        'password',
        'role',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'password' => 'hashed',
    ];

    public function laboratoriums()
    {
        return $this->belongsToMany(Laboratorium::class, 'pengguna_lab', 'id_pengguna', 'kode_lab', 'id_pengguna', 'kode_lab');
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isKepalaLab()
    {
        return $this->role === 'kepalalab';
    }

    public function isAsistenLab()
    {
        return $this->role === 'asistenlab';
    }
}
