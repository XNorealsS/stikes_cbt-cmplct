@extends('layouts.app')
 
@section('sidebar-menu')
<nav class="space-y-1.5">
    {{-- Beranda --}}
    <div class="space-y-1">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs md:text-sm font-semibold transition rounded-sm {{ Route::is('admin.dashboard') ? 'active-link' : '' }}">
            <i class="fa-solid fa-house w-4 text-center opacity-80"></i>
            <span class="flex-grow">Beranda</span>
        </a>
    </div>
 
    {{-- Akademik (Collapsible) --}}
    <div class="space-y-1">
        <button type="button" id="btn-akademik" onclick="toggleCollapsible('akademik')" class="w-full flex items-center justify-between px-3 py-2 text-xs md:text-sm font-semibold transition hover:bg-[var(--sidebar-hover-bg)] text-[var(--sidebar-text)] rounded-sm text-left cursor-pointer border-0 bg-transparent">
            <span class="flex items-center gap-2.5">
                <i class="fa-solid fa-graduation-cap w-4 text-center opacity-80"></i>
                <span class="flex-grow">Akademik</span>
            </span>
            <i class="fa-solid fa-chevron-down text-[9px] chevron transition-transform duration-200"></i>
        </button>
        <div id="submenu-akademik" class="sidebar-submenu pl-4 space-y-1 mt-1">
            <a href="{{ route('admin.tahun-akademik.index') }}" class="rounded-sm {{ Route::is('admin.tahun-akademik.index') ? 'active-link' : '' }}">
                Tahun Akademik
            </a>
            <a href="{{ route('admin.prodi.index') }}" class="rounded-sm {{ Route::is('admin.prodi.index') ? 'active-link' : '' }}">
                Program Studi
            </a>
            <a href="{{ route('admin.classes.index') }}" class="rounded-sm {{ Route::is('admin.classes.index') ? 'active-link' : '' }}">
                Data Kelas
            </a>
            <a href="{{ route('admin.courses.index') }}" class="rounded-sm {{ Route::is('admin.courses.index') ? 'active-link' : '' }}">
                Mata Kuliah
            </a>
        </div>
    </div>

    {{-- CBTMu (Ujian) (Collapsible) --}}
    <div class="space-y-1">
        <button type="button" id="btn-cbtmu" onclick="toggleCollapsible('cbtmu')" class="w-full flex items-center justify-between px-3 py-2 text-xs md:text-sm font-semibold transition hover:bg-[var(--sidebar-hover-bg)] text-[var(--sidebar-text)] rounded-sm text-left cursor-pointer border-0 bg-transparent">
            <span class="flex items-center gap-2.5">
                <i class="fa-solid fa-desktop w-4 text-center opacity-80"></i>
                <span class="flex-grow">CBTMu (Ujian)</span>
            </span>
            <i class="fa-solid fa-chevron-down text-[9px] chevron transition-transform duration-200"></i>
        </button>
        <div id="submenu-cbtmu" class="sidebar-submenu pl-4 space-y-1 mt-1">
            <a href="{{ route('admin.bank-soal.index') }}" class="rounded-sm {{ Route::is('admin.bank-soal.index') ? 'active-link' : '' }}">
                Bank Soal
            </a>
            <a href="{{ route('admin.exams.index') }}" class="rounded-sm {{ Route::is('admin.exams.index') ? 'active-link' : '' }}">
                Jadwal Ujian
            </a>
            <a href="{{ route('admin.monitoring.index') }}" class="rounded-sm {{ Route::is('admin.monitoring.index') || Route::is('admin.monitoring.detail') ? 'active-link' : '' }}">
                Monitoring Ujian
            </a>
            <a href="{{ route('admin.analisis.index') }}" class="rounded-sm {{ Route::is('admin.analisis.index') ? 'active-link' : '' }}">
                Analisis Butir Soal
            </a>
            <a href="{{ route('admin.pengumuman.index') }}" class="rounded-sm {{ Route::is('admin.pengumuman.index') ? 'active-link' : '' }}">
                Pengumuman
            </a>
        </div>
    </div>
 
    {{-- Master & Feeder (Collapsible) --}}
    <div class="space-y-1">
        <button type="button" id="btn-feeder" onclick="toggleCollapsible('feeder')" class="w-full flex items-center justify-between px-3 py-2 text-xs md:text-sm font-semibold transition hover:bg-[var(--sidebar-hover-bg)] text-[var(--sidebar-text)] rounded-sm text-left cursor-pointer border-0 bg-transparent">
            <span class="flex items-center gap-2.5">
                <i class="fa-solid fa-users w-4 text-center opacity-80"></i>
                <span class="flex-grow">Master & Feeder</span>
            </span>
            <i class="fa-solid fa-chevron-down text-[9px] chevron transition-transform duration-200"></i>
        </button>
        <div id="submenu-feeder" class="sidebar-submenu pl-4 space-y-1 mt-1">
            <a href="{{ route('admin.users.index') }}" class="rounded-sm {{ Route::is('admin.users.index') ? 'active-link' : '' }}">
                Data Pengguna
            </a>
            <a href="{{ route('admin.feeder.index') }}" class="rounded-sm {{ Route::is('admin.feeder.index') ? 'active-link' : '' }}">
                Sinkronisasi Feeder
            </a>
        </div>
    </div>

    {{-- Sistem (Collapsible) --}}
    <div class="space-y-1">
        <button type="button" id="btn-system" onclick="toggleCollapsible('system')" class="w-full flex items-center justify-between px-3 py-2 text-xs md:text-sm font-semibold transition hover:bg-[var(--sidebar-hover-bg)] text-[var(--sidebar-text)] rounded-sm text-left cursor-pointer border-0 bg-transparent">
            <span class="flex items-center gap-2.5">
                <i class="fa-solid fa-gears w-4 text-center opacity-80"></i>
                <span class="flex-grow">Sistem</span>
            </span>
            <i class="fa-solid fa-chevron-down text-[9px] chevron transition-transform duration-200"></i>
        </button>
        <div id="submenu-system" class="sidebar-submenu pl-4 space-y-1 mt-1">
            <a href="{{ route('admin.audit.index') }}" class="rounded-sm {{ Route::is('admin.audit.index') ? 'active-link' : '' }}">
                Audit System
            </a>
        </div>
    </div>
</nav>
@endsection
 
@section('content')
    @yield('admin-content')
@endsection
