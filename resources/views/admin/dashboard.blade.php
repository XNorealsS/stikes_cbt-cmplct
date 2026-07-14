@extends('layouts.admin')

@section('title', 'Dashboard Admin — STIKesMu CBT')

@section('admin-content')
<div class="space-y-6">

    {{-- ── Page Header ──────────────────────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h1 class="page-header-title">
                <span class="title-bar"></span>
                Dashboard Administrasi
            </h1>
            <p class="page-header-subtitle">Monitoring ringkas pelaksanaan ujian &amp; manajemen akademik STIKes Muhammadiyah Lhokseumawe.</p>
        </div>
        <span class="badge badge-primary text-[10px] uppercase tracking-wider">
            <i class="fa-solid fa-circle-dot text-[8px] animate-pulse"></i>
            Sistem Aktif
        </span>
    </div>

    {{-- ── Stats Grid ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Card: Total Dosen --}}
        <div class="card-stat group">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Dosen Aktif</p>
                    <p class="text-3xl font-black text-primary-800 leading-none">{{ $dosenCount }}</p>
                    <p class="text-[10px] text-slate-400 font-medium mt-2">Dosen terdaftar</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-primary-50 flex items-center justify-center flex-shrink-0 group-hover:bg-primary-100 transition-colors">
                    <i class="fa-solid fa-chalkboard-user text-primary-700 text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Card: Total Mahasiswa --}}
        <div class="card-stat group">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Mahasiswa Aktif</p>
                    <p class="text-3xl font-black text-slate-800 leading-none">{{ $mahasiswaCount }}</p>
                    <p class="text-[10px] text-slate-400 font-medium mt-2">Mahasiswa terdaftar</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition-colors">
                    <i class="fa-solid fa-users text-blue-600 text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Card: Total Kelas --}}
        <div class="card-stat group">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Kelas Berjalan</p>
                    <p class="text-3xl font-black text-slate-800 leading-none">{{ $classCount }}</p>
                    <p class="text-[10px] text-slate-400 font-medium mt-2">Kelas akademik</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-violet-50 flex items-center justify-center flex-shrink-0 group-hover:bg-violet-100 transition-colors">
                    <i class="fa-solid fa-school text-violet-600 text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Card: Total Mata Kuliah --}}
        <div class="card-stat group">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Mata Kuliah</p>
                    <p class="text-3xl font-black text-slate-800 leading-none">{{ $courseCount }}</p>
                    <p class="text-[10px] text-slate-400 font-medium mt-2">MK terdaftar</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-100 transition-colors">
                    <i class="fa-solid fa-book-open text-amber-600 text-lg"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Live Exams & Feeder Banner ───────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Live Monitoring Box --}}
        <div class="card-stat group flex flex-col justify-between gap-4">
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Ujian Berjalan</p>
                        <div class="flex items-center gap-2.5">
                            @if($activeExams > 0)
                                <span class="relative flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                                </span>
                            @else
                                <span class="h-2.5 w-2.5 rounded-full bg-slate-300 inline-flex"></span>
                            @endif
                            <span class="text-3xl font-black {{ $activeExams > 0 ? 'text-red-700' : 'text-slate-400' }} leading-none">{{ $activeExams }}</span>
                        </div>
                        <p class="text-[10px] text-slate-400 font-medium mt-2">{{ $activeExams > 0 ? 'Ujian sedang berlangsung' : 'Tidak ada ujian aktif' }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl {{ $activeExams > 0 ? 'bg-red-50 group-hover:bg-red-100' : 'bg-slate-50 group-hover:bg-slate-100' }} flex items-center justify-center flex-shrink-0 transition-colors">
                        <i class="fa-solid fa-tower-broadcast {{ $activeExams > 0 ? 'text-red-500' : 'text-slate-400' }} text-lg"></i>
                    </div>
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100">
                <a href="{{ route('admin.monitoring.index') }}" class="inline-flex items-center gap-1.5 text-[11px] text-red-700 hover:text-red-800 font-bold uppercase tracking-wider transition">
                    Buka Monitor Live
                    <i class="fa-solid fa-arrow-right text-[9px]"></i>
                </a>
            </div>
        </div>

        {{-- Feeder Info Box --}}
        <div class="lg:col-span-2 rounded-xl border p-5 flex flex-col justify-between gap-4" style="background-color: #FFFBEB; border-color: #FDE68A;">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: #FEF3C7;">
                    <i class="fa-solid fa-cloud-arrow-down text-amber-600 text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-[11px] font-bold uppercase tracking-wider text-amber-900 flex items-center gap-1.5 mb-1">
                        Integrasi Web Service Neo Feeder
                    </h3>
                    <p class="text-[12px] text-amber-700 leading-relaxed">
                        Data Mahasiswa, Dosen, Kelas &amp; Mata Kuliah disinkronkan langsung dari <span class="font-bold">PDDIKTI Neo Feeder</span> untuk menjamin keaslian data akademik. Kelola sinkronisasi secara berkala.
                    </p>
                </div>
            </div>
            <div class="pt-3 border-t flex justify-end" style="border-color: #FDE68A;">
                <a href="{{ route('admin.feeder.index') }}" class="inline-flex items-center gap-2 text-white px-4 py-2 text-[11px] font-bold uppercase tracking-wider transition-all hover:opacity-90 active:scale-95" style="background-color: #D97706; border-radius: var(--radius-md);">
                    <i class="fa-solid fa-rotate"></i>
                    Buka Konfigurasi Feeder
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
