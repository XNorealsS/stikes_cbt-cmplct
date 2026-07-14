@extends('layouts.app')

@section('sidebar-menu')
<nav class="space-y-4">
    <!-- E-Learning Group (Top Level - Flat) -->
    <div class="space-y-1">
        <a href="{{ route('mahasiswa.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs md:text-sm font-semibold transition {{ Route::is('mahasiswa.dashboard') ? 'active-link' : '' }}">
            <i class="fa-solid fa-house w-4 text-center opacity-80"></i>
            <span class="flex-grow">Dashboard</span>
        </a>

        <a href="{{ route('mahasiswa.materi.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs md:text-sm font-semibold transition {{ Route::is('mahasiswa.materi.index') ? 'active-link' : '' }}">
            <i class="fa-solid fa-book w-4 text-center opacity-80"></i>
            <span class="flex-grow">Materi Pembelajaran</span>
        </a>

        <a href="{{ route('mahasiswa.tugas.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs md:text-sm font-semibold transition {{ Route::is('mahasiswa.tugas.index') ? 'active-link' : '' }}">
            <i class="fa-solid fa-file-pen w-4 text-center opacity-80"></i>
            <span class="flex-grow">Tugas Kuliah</span>
        </a>
    </div>

    <!-- CBTMu (Ujian) (Collapsible) -->
    <div class="space-y-1">
        <button type="button" id="btn-cbtmu" onclick="toggleCollapsible('cbtmu')" class="w-full flex items-center justify-between px-3 py-2 text-xs md:text-sm font-semibold transition hover:bg-[var(--sidebar-hover-bg)] text-[var(--sidebar-text)] rounded-[var(--r-sm)] text-left cursor-pointer border-0 bg-transparent">
            <span class="flex items-center gap-2.5">
                <i class="fa-solid fa-desktop w-4 text-center opacity-80"></i>
                <span class="flex-grow">CBTMu (Ujian)</span>
            </span>
            <i class="fa-solid fa-chevron-right text-[9px] chevron transition-transform duration-200"></i>
        </button>
        <div id="submenu-cbtmu" class="sidebar-submenu pl-4 space-y-1 mt-1">
            <a href="{{ route('mahasiswa.history') }}" class="{{ Route::is('mahasiswa.history') || Route::is('mahasiswa.review') ? 'active-link' : '' }}">
                Riwayat Ujian
            </a>
        </div>
    </div>
</nav>
@endsection

@section('content')
    @yield('mahasiswa-content')
@endsection
