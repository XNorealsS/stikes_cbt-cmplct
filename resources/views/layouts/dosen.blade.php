@extends('layouts.app')

@section('sidebar-menu')
<nav class="space-y-1.5">
    <!-- E-Learning Group (Top Level - Flat) -->
    <div class="space-y-1">
        <a href="{{ route('dosen.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs md:text-sm font-semibold transition rounded-sm {{ Route::is('dosen.dashboard') ? 'active-link' : '' }}">
            <i class="fa-solid fa-house w-4 text-center opacity-80"></i>
            <span class="flex-grow">Beranda Dosen</span>
        </a>

        <a href="{{ route('dosen.materi.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs md:text-sm font-semibold transition rounded-sm {{ Route::is('dosen.materi.index') ? 'active-link' : '' }}">
            <i class="fa-solid fa-book w-4 text-center opacity-80"></i>
            <span class="flex-grow">Materi Pembelajaran</span>
        </a>

        <a href="{{ route('dosen.tugas.index') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs md:text-sm font-semibold transition rounded-sm {{ Route::is('dosen.tugas.index') || Route::is('dosen.tugas.submissions') ? 'active-link' : '' }}">
            <i class="fa-solid fa-file-pen w-4 text-center opacity-80"></i>
            <span class="flex-grow">Tugas Kuliah</span>
        </a>
    </div>

    <!-- CBTMu (Ujian) (Collapsible) -->
    <div class="space-y-1">
        <button type="button" id="btn-cbtmu" onclick="toggleCollapsible('cbtmu')" class="w-full flex items-center justify-between px-3 py-2 text-xs md:text-sm font-semibold transition hover:bg-[var(--sidebar-hover-bg)] text-[var(--sidebar-text)] rounded-sm text-left cursor-pointer border-0 bg-transparent">
            <span class="flex items-center gap-2.5">
                <i class="fa-solid fa-desktop w-4 text-center opacity-80"></i>
                <span class="flex-grow">CBTMu (Ujian)</span>
            </span>
            <i class="fa-solid fa-chevron-down text-[9px] chevron transition-transform duration-200"></i>
        </button>
        <div id="submenu-cbtmu" class="sidebar-submenu pl-4 space-y-1 mt-1">
            <a href="{{ route('dosen.questions.index') }}" class="rounded-sm {{ Route::is('dosen.questions.index') || Route::is('dosen.bank-soal.show') ? 'active-link' : '' }}">
                Bank Soal
            </a>
            <a href="{{ route('dosen.exams.index') }}" class="rounded-sm {{ Route::is('dosen.exams.index') ? 'active-link' : '' }}">
                Jadwal Ujian
            </a>
            <a href="{{ route('dosen.grades.index') }}" class="rounded-sm {{ Route::is('dosen.grades.index') ? 'active-link' : '' }}">
                Rekap & Cetak Nilai
            </a>
            <a href="{{ route('dosen.analisis.index') }}" class="rounded-sm {{ Route::is('dosen.analisis.index') ? 'active-link' : '' }}">
                Analisis Butir Soal
            </a>
        </div>
    </div>
</nav>
@endsection

@section('content')
    @yield('dosen-content')
@endsection
