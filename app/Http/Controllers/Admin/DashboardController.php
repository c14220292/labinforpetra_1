<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\Laboratorium;
use App\Models\Perlengkapan;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_pengguna' => Pengguna::count(),
            'total_laboratorium' => Laboratorium::count(),
            'total_perlengkapan' => Perlengkapan::count(),
            'perlengkapan_tersedia' => Perlengkapan::where('kondisi', 'Tersedia')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
