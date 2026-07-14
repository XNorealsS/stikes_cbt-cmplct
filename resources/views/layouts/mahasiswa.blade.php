@extends('layouts.app')

@section('sidebar-menu')
<nav class="space-y-1.5">
    <!-- Dashboard Link (Flat) -->
    <div class="space-y-1">
        <a href="{{ route('mahasiswa.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs md:text-sm font-semibold transition rounded-sm {{ Route::is('mahasiswa.dashboard') ? 'active-link' : '' }}">
            <i class="fa-solid fa-house w-4 text-center opacity-80"></i>
            <span class="flex-grow">Dashboard</span>
        </a>
    </div>

    <!-- E-Learning Dropdown (Collapsible) -->
    <div class="space-y-1">
        <button type="button" id="btn-elearning" onclick="toggleCollapsible('elearning')" class="w-full flex items-center justify-between px-3 py-2 text-xs md:text-sm font-semibold transition hover:bg-[var(--sidebar-hover-bg)] text-[var(--sidebar-text)] rounded-sm text-left cursor-pointer border-0 bg-transparent">
            <span class="flex items-center gap-2.5">
                <i class="fa-solid fa-graduation-cap w-4 text-center opacity-80"></i>
                <span class="flex-grow">E-Learning</span>
            </span>
            <i class="fa-solid fa-chevron-down text-[9px] chevron transition-transform duration-200"></i>
        </button>
        <div id="submenu-elearning" class="sidebar-submenu pl-4 space-y-1 mt-1">
            <a href="{{ route('mahasiswa.materi.index') }}" class="rounded-sm {{ Route::is('mahasiswa.materi.index') ? 'active-link' : '' }}">
                Materi Pembelajaran
            </a>
            <a href="{{ route('mahasiswa.tugas.index') }}" class="rounded-sm {{ Route::is('mahasiswa.tugas.index') ? 'active-link' : '' }}">
                Tugas Kuliah
            </a>
        </div>
    </div>

    <!-- CBTmu Dropdown (Collapsible) -->
    <div class="space-y-1">
        <button type="button" id="btn-cbtmu" onclick="toggleCollapsible('cbtmu')" class="w-full flex items-center justify-between px-3 py-2 text-xs md:text-sm font-semibold transition hover:bg-[var(--sidebar-hover-bg)] text-[var(--sidebar-text)] rounded-sm text-left cursor-pointer border-0 bg-transparent">
            <span class="flex items-center gap-2.5">
                <i class="fa-solid fa-desktop w-4 text-center opacity-80"></i>
                <span class="flex-grow">CBTmu</span>
            </span>
            <i class="fa-solid fa-chevron-down text-[9px] chevron transition-transform duration-200"></i>
        </button>
        <div id="submenu-cbtmu" class="sidebar-submenu pl-4 space-y-1 mt-1">
            <a href="{{ route('mahasiswa.ujian.index') }}" class="rounded-sm {{ Route::is('mahasiswa.ujian.index') ? 'active-link' : '' }}">
                Ujian
            </a>
            <a href="{{ route('mahasiswa.history') }}" class="rounded-sm {{ Route::is('mahasiswa.history') || Route::is('mahasiswa.review') ? 'active-link' : '' }}">
                Riwayat Ujian
            </a>
        </div>
    </div>
</nav>
@endsection

@section('content')
    @yield('mahasiswa-content')
@endsection
