@extends('layouts.admin')

@section('title', 'Admin Dashboard - E-Learning STIKesMu')

@section('admin-content')
<div class="space-y-6">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Admin &gt; Dashboard</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Dashboard Administrasi CBT
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Monitoring ringkas pelaksanaan ujian dan manajemen akademik kampus STIKes Muhammadiyah Lhokseumawe.</p>
            </div>
        </div>
    </div>

    <!-- Stats Grid: 4 columns on desktop, 2 on tablet, 1 on mobile -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <div class="bg-white p-5 rounded-xl border border-gray-255 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Dosen Aktif</span>
                <span class="text-2xl font-black text-primary leading-tight block">{{ $dosenCount }}</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Dosen terdaftar aktif</span>
        </div>

        <!-- Card 2 -->
        <div class="bg-white p-5 rounded-xl border border-gray-255 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Mahasiswa Aktif</span>
                <span class="text-2xl font-black text-gray-800 leading-tight block">{{ $mahasiswaCount }}</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Mahasiswa terdaftar aktif</span>
        </div>

        <!-- Card 3 -->
        <div class="bg-white p-5 rounded-xl border border-gray-255 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Kelas Berjalan</span>
                <span class="text-2xl font-black text-gray-800 leading-tight block">{{ $classCount }}</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Jumlah kelas akademik</span>
        </div>

        <!-- Card 4 -->
        <div class="bg-white p-5 rounded-xl border border-gray-255 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Mata Kuliah</span>
                <span class="text-2xl font-black text-gray-800 leading-tight block">{{ $courseCount }}</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Mata kuliah terdaftar</span>
        </div>
    </div>

    <!-- Live Exams & PDDIKTI Banner -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Live Monitoring Box -->
        <div class="bg-white p-5 rounded-xl border border-gray-255 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Ujian Berjalan (Live)</span>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="h-2.5 w-2.5 bg-red-500 rounded-full {{ $activeExams > 0 ? 'animate-ping' : '' }}"></span>
                    <span class="text-2xl font-black text-red-700 block leading-tight">{{ $activeExams }}</span>
                </div>
            </div>
            <div class="pt-3 border-t border-gray-200 mt-4 flex">
                <a href="{{ route('admin.monitoring.index') }}" class="text-[10px] text-red-700 hover:underline font-bold uppercase tracking-wider inline-flex items-center">Buka Monitor Live <i class="fa-solid fa-arrow-right ml-1"></i></a>
            </div>
        </div>

        <!-- Feeder Info Box -->
        <div class="lg:col-span-2 bg-amber-50 border border-amber-250 p-5 rounded-xl flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-900 flex items-center">
                    <i class="fa-solid fa-cloud-arrow-down mr-1.5"></i> Integrasi Web Service Neo Feeder
                </h3>
                <p class="text-[11px] text-amber-700 mt-1 leading-relaxed">
                    Semua data Mahasiswa, Dosen, Kelas, dan Mata Kuliah disinkronkan langsung dari feeder PDDIKTI untuk menjamin keaslian data. Kelola pengaturan sinkronisasi Neo Feeder secara berkala untuk memperbarui data mahasiswa aktif.
                </p>
            </div>
            <div class="pt-3 border-t border-amber-200 mt-4 flex justify-end">
                <a href="{{ route('admin.feeder.index') }}" class="inline-flex items-center bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded text-[10px] font-bold uppercase tracking-wider transition shadow-sm">
                    <i class="fa-solid fa-rotate mr-1"></i> Buka Konfigurasi Feeder
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
