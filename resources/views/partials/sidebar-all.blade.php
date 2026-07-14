@php
    $role = auth()->user()->role ?? 'mahasiswa';
@endphp

@if (View::hasSection('sidebar-menu'))
    @yield('sidebar-menu')
@else
    @if ($role === 'admin')
        <nav class="space-y-1.5">
            {{-- Beranda --}}
            <div class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs md:text-sm font-semibold transition rounded-sm {{ Route::is('admin.dashboard') ? 'active-link' : '' }}">
                    <i class="fa-solid fa-table-cells-large w-4 text-center opacity-80"></i>
                    <span class="flex-grow">Beranda</span>
                </a>
            </div>

            {{-- Akademik --}}
            <div class="space-y-1">
                <button type="button" id="btn-akademik" onclick="toggleCollapsible('akademik')" class="w-full flex items-center justify-between px-3 py-2 text-xs md:text-sm font-semibold transition hover:bg-[var(--sidebar-hover-bg)] text-[var(--sidebar-text)] rounded-sm text-left cursor-pointer border-0 bg-transparent">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-graduation-cap w-4 text-center opacity-80"></i>
                        <span class="flex-grow">Akademik</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] chevron transition-transform duration-200"></i>
                </button>
                <div id="submenu-akademik" class="sidebar-submenu pl-4 space-y-1 mt-1">
                    <a href="{{ route('admin.tahun-akademik.index') }}" class="rounded-sm {{ Route::is('admin.tahun-akademik.index') ? 'active-link' : '' }}">Tahun Akademik</a>
                    <a href="{{ route('admin.prodi.index') }}" class="rounded-sm {{ Route::is('admin.prodi.index') ? 'active-link' : '' }}">Program Studi</a>
                    <a href="{{ route('admin.classes.index') }}" class="rounded-sm {{ Route::is('admin.classes.index') ? 'active-link' : '' }}">Data Kelas</a>
                    <a href="{{ route('admin.courses.index') }}" class="rounded-sm {{ Route::is('admin.courses.index') ? 'active-link' : '' }}">Mata Kuliah</a>
                </div>
            </div>

            {{-- CBTMu (Ujian) --}}
            <div class="space-y-1">
                <button type="button" id="btn-cbtmu" onclick="toggleCollapsible('cbtmu')" class="w-full flex items-center justify-between px-3 py-2 text-xs md:text-sm font-semibold transition hover:bg-[var(--sidebar-hover-bg)] text-[var(--sidebar-text)] rounded-sm text-left cursor-pointer border-0 bg-transparent">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-clipboard-list w-4 text-center opacity-80"></i>
                        <span class="flex-grow">CBTMu (Ujian)</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] chevron transition-transform duration-200"></i>
                </button>
                <div id="submenu-cbtmu" class="sidebar-submenu pl-4 space-y-1 mt-1">
                    <a href="{{ route('admin.bank-soal.index') }}" class="rounded-sm {{ Route::is('admin.bank-soal.index') ? 'active-link' : '' }}">Bank Soal</a>
                    <a href="{{ route('admin.exams.index') }}" class="rounded-sm {{ Route::is('admin.exams.index') ? 'active-link' : '' }}">Jadwal Ujian</a>
                    <a href="{{ route('admin.monitoring.index') }}" class="rounded-sm {{ Route::is('admin.monitoring.index') || Route::is('admin.monitoring.detail') ? 'active-link' : '' }}">Monitoring Ujian</a>
                    <a href="{{ route('admin.analisis.index') }}" class="rounded-sm {{ Route::is('admin.analisis.index') ? 'active-link' : '' }}">Analisis Butir Soal</a>
                    <a href="{{ route('admin.pengumuman.index') }}" class="rounded-sm {{ Route::is('admin.pengumuman.index') ? 'active-link' : '' }}">Pengumuman</a>
                </div>
            </div>

            {{-- Master & Feeder --}}
            <div class="space-y-1">
                <button type="button" id="btn-feeder" onclick="toggleCollapsible('feeder')" class="w-full flex items-center justify-between px-3 py-2 text-xs md:text-sm font-semibold transition hover:bg-[var(--sidebar-hover-bg)] text-[var(--sidebar-text)] rounded-sm text-left cursor-pointer border-0 bg-transparent">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-users-gear w-4 text-center opacity-80"></i>
                        <span class="flex-grow">Master &amp; Feeder</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] chevron transition-transform duration-200"></i>
                </button>
                <div id="submenu-feeder" class="sidebar-submenu pl-4 space-y-1 mt-1">
                    <a href="{{ route('admin.users.index') }}" class="rounded-sm {{ Route::is('admin.users.index') ? 'active-link' : '' }}">Data Pengguna</a>
                    <a href="{{ route('admin.feeder.index') }}" class="rounded-sm {{ Route::is('admin.feeder.index') ? 'active-link' : '' }}">Sinkronisasi Feeder</a>
                </div>
            </div>

            {{-- Sistem --}}
            <div class="space-y-1">
                <button type="button" id="btn-system" onclick="toggleCollapsible('system')" class="w-full flex items-center justify-between px-3 py-2 text-xs md:text-sm font-semibold transition hover:bg-[var(--sidebar-hover-bg)] text-[var(--sidebar-text)] rounded-sm text-left cursor-pointer border-0 bg-transparent">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-sliders w-4 text-center opacity-80"></i>
                        <span class="flex-grow">Sistem</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] chevron transition-transform duration-200"></i>
                </button>
                <div id="submenu-system" class="sidebar-submenu pl-4 space-y-1 mt-1">
                    <a href="{{ route('admin.audit.index') }}" class="rounded-sm {{ Route::is('admin.audit.index') ? 'active-link' : '' }}">Audit System</a>
                </div>
            </div>
        </nav>
    @elseif ($role === 'dosen')
        <nav class="space-y-1.5">
            <div class="space-y-1">
                <a href="{{ route('dosen.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-xs md:text-sm font-semibold transition rounded-sm {{ Route::is('dosen.dashboard') ? 'active-link' : '' }}">
                    <i class="fa-solid fa-table-cells-large w-4 text-center opacity-80"></i>
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
            <div class="space-y-1">
                <button type="button" id="btn-cbtmu" onclick="toggleCollapsible('cbtmu')" class="w-full flex items-center justify-between px-3 py-2 text-xs md:text-sm font-semibold transition hover:bg-[var(--sidebar-hover-bg)] text-[var(--sidebar-text)] rounded-sm text-left cursor-pointer border-0 bg-transparent">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-clipboard-list w-4 text-center opacity-80"></i>
                        <span class="flex-grow">CBTMu (Ujian)</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] chevron transition-transform duration-200"></i>
                </button>
                <div id="submenu-cbtmu" class="sidebar-submenu pl-4 space-y-1 mt-1">
                    <a href="{{ route('dosen.questions.index') }}" class="rounded-sm {{ Route::is('dosen.questions.index') || Route::is('dosen.bank-soal.show') ? 'active-link' : '' }}">Bank Soal</a>
                    <a href="{{ route('dosen.exams.index') }}" class="rounded-sm {{ Route::is('dosen.exams.index') ? 'active-link' : '' }}">Jadwal Ujian</a>
                    <a href="{{ route('dosen.grades.index') }}" class="rounded-sm {{ Route::is('dosen.grades.index') ? 'active-link' : '' }}">Rekap &amp; Cetak Nilai</a>
                    <a href="{{ route('dosen.analisis.index') }}" class="rounded-sm {{ Route::is('dosen.analisis.index') ? 'active-link' : '' }}">Analisis Butir Soal</a>
                </div>
            </div>
        </nav>
    @else
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
                    <a href="{{ route('mahasiswa.dashboard') }}" class="rounded-sm {{ Route::is('mahasiswa.dashboard') ? 'active-link' : '' }}">
                        Ujian
                    </a>
                    <a href="{{ route('mahasiswa.history') }}" class="rounded-sm {{ Route::is('mahasiswa.history') || Route::is('mahasiswa.review') ? 'active-link' : '' }}">
                        Riwayat Ujian
                    </a>
                </div>
            </div>
        </nav>
    @endif
@endif
