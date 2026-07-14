@extends('layouts.admin')

@section('title', 'Detail Monitoring — {{ $exam->title }}')

@section('admin-content')
<div class="space-y-5">

    {{-- ── Page Header ──────────────────────────────────────────────── --}}
    <div class="page-header">
        <div class="min-w-0 flex-1">
            <h1 class="page-header-title">
                <span class="title-bar"></span>
                <span class="truncate">{{ $exam->title }}</span>
            </h1>
            <p class="page-header-subtitle">
                <span class="font-semibold text-slate-600">{{ $exam->course->name }}</span>
                <span class="text-slate-300 mx-1.5">·</span>
                <span>{{ $exam->course->code }}</span>
                <span class="text-slate-300 mx-1.5">·</span>
                <span>{{ $exam->dosen->name }}</span>
            </p>
        </div>
        <a href="{{ route('admin.monitoring.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex-shrink-0"
           style="border-radius: var(--radius-md);">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            Kembali
        </a>
    </div>

    {{-- ── Stats Summary ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Total Mahasiswa --}}
        <div class="card-stat">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Total Peserta</p>
                    <p class="text-3xl font-black text-slate-800 leading-none">{{ $students->count() }}</p>
                    <p class="text-[10px] text-slate-400 mt-2">Mahasiswa terdaftar</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-users text-slate-500 text-base"></i>
                </div>
            </div>
        </div>

        {{-- Sedang Ujian --}}
        @php
            $countProgress = $students->filter(fn($s) => optional($s->exam_session)->status === 'progress')->count();
            $countFinished = $students->filter(fn($s) => optional($s->exam_session)->status === 'finished')->count();
            $countPending  = $students->filter(fn($s) => optional($s->exam_session)->status === 'pending')->count();
        @endphp
        <div class="card-stat">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Sedang Ujian</p>
                    <p class="text-3xl font-black text-sky-700 leading-none">{{ $countProgress }}</p>
                    <p class="text-[10px] text-slate-400 mt-2">Sedang mengerjakan</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-pen-to-square text-sky-600 text-base"></i>
                </div>
            </div>
        </div>

        {{-- Selesai --}}
        <div class="card-stat">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Selesai Ujian</p>
                    <p class="text-3xl font-black text-emerald-700 leading-none">{{ $countFinished }}</p>
                    <p class="text-[10px] text-slate-400 mt-2">Sudah menyelesaikan</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                </div>
            </div>
        </div>

        {{-- Ditangguhkan --}}
        <div class="card-stat">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Ditangguhkan</p>
                    <p class="text-3xl font-black text-amber-600 leading-none">{{ $countPending }}</p>
                    <p class="text-[10px] text-slate-400 mt-2">Sesi dijeda / pending</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-pause text-amber-500 text-base"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Student Detail Table ─────────────────────────────────────── --}}
    <div class="card overflow-hidden">

        {{-- Filter Bar --}}
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="relative max-w-xs w-full">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                <input type="text" id="search-student"
                       placeholder="Cari nama atau NIM..."
                       class="w-full pl-8 pr-3 py-2 text-xs bg-white border border-slate-200 focus:border-primary-700"
                       style="border-radius: var(--radius-md);">
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Filter:</span>
                <select id="filter-status"
                        class="border border-slate-200 px-3 py-2 text-xs bg-white text-slate-700 min-w-[140px]"
                        style="border-radius: var(--radius-md);">
                    <option value="all">Semua Status</option>
                    <option value="not_started">Belum Mulai</option>
                    <option value="progress">Sedang Ujian</option>
                    <option value="pending">Ditangguhkan</option>
                    <option value="finished">Selesai</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left" id="monitoring-table">
                <thead>
                    <tr>
                        <th class="w-[28%]">Mahasiswa</th>
                        <th class="w-[14%]">Kelas</th>
                        <th class="text-center w-[11%]">Mulai</th>
                        <th class="text-center w-[12%]">Sisa Waktu</th>
                        <th class="text-center w-[13%]">Status</th>
                        <th class="text-center w-[10%]">Nilai</th>
                        <th class="text-right w-[12%]">Kontrol</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                    @php
                        $session     = $student->exam_session;
                        $remaining   = '—';
                        $statusClass = 'not_started';

                        if ($session && $session->status === 'progress') {
                            $statusClass   = 'progress';
                            $startedAt     = \Carbon\Carbon::parse($session->started_at);
                            $elapsedSec    = now()->diffInSeconds($startedAt);
                            $totalSec      = $exam->duration_minutes * 60;
                            $remSec        = $totalSec - $elapsedSec;
                            $remaining     = $remSec > 0 ? ceil($remSec / 60) . ' mnt' : 'Habis';

                        } elseif ($session && $session->status === 'pending') {
                            $statusClass   = 'pending';
                            $startedAt     = \Carbon\Carbon::parse($session->started_at);
                            $suspendedAt   = \Carbon\Carbon::parse($session->suspended_at ?? now());
                            $elapsedSec    = $suspendedAt->diffInSeconds($startedAt);
                            $totalSec      = $exam->duration_minutes * 60;
                            $remSec        = $totalSec - $elapsedSec;
                            $remaining     = ceil(max(0, $remSec) / 60) . ' mnt';

                        } elseif ($session && $session->status === 'finished') {
                            $statusClass = 'finished';
                        }
                    @endphp
                    <tr class="student-row"
                        data-name="{{ strtolower($student->name) }}"
                        data-nim="{{ strtolower($student->username) }}"
                        data-status="{{ $statusClass }}">

                        <td>
                            <span class="font-bold text-slate-800 text-xs block">{{ $student->name }}</span>
                            <span class="text-[10px] text-slate-400 font-mono block mt-0.5">{{ $student->username }}</span>
                        </td>
                        <td class="text-xs text-slate-600 font-medium">
                            {{ $student->classRoom ? $student->classRoom->name : '—' }}
                        </td>
                        <td class="text-center font-mono text-[11px] text-slate-600">
                            {{ $session ? \Carbon\Carbon::parse($session->started_at)->format('H:i:s') : '—' }}
                        </td>
                        <td class="text-center font-mono text-[11px] font-semibold
                            {{ $statusClass === 'progress' ? 'text-sky-700' : ($statusClass === 'pending' ? 'text-amber-600' : 'text-slate-400') }}">
                            {{ $remaining }}
                        </td>
                        <td class="text-center">
                            @if (!$session)
                                <span class="badge badge-neutral badge-sm">Belum Mulai</span>
                            @elseif ($session->status === 'progress')
                                <span class="badge badge-sm" style="background-color:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;">
                                    <i class="fa-solid fa-pen-to-square text-[8px]"></i> Ujian
                                </span>
                            @elseif ($session->status === 'pending')
                                <span class="badge badge-warning badge-sm">
                                    <i class="fa-solid fa-pause text-[8px]"></i> Dijeda
                                </span>
                            @elseif ($session->status === 'finished')
                                <span class="badge badge-success badge-sm">
                                    <i class="fa-solid fa-check text-[8px]"></i> Selesai
                                </span>
                            @endif
                        </td>
                        <td class="text-center font-mono font-bold text-[12px]
                            {{ ($session && $session->score !== null) ? 'text-slate-800' : 'text-slate-300' }}">
                            {{ $session && $session->score !== null ? number_format($session->score, 2) : '—' }}
                        </td>
                        <td class="text-right">
                            @if ($session)
                                <div class="inline-flex items-center gap-1">
                                    @if ($session->status === 'progress')
                                        <button type="button"
                                                onclick="togglePending({{ $session->id }}, '{{ $student->name }}', 'pause')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold transition active:scale-95"
                                                style="border-radius: var(--radius-sm);">
                                            <i class="fa-solid fa-pause text-[8px]"></i> Pause
                                        </button>
                                    @elseif ($session->status === 'pending')
                                        <button type="button"
                                                onclick="togglePending({{ $session->id }}, '{{ $student->name }}', 'resume')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-sky-600 hover:bg-sky-700 text-white text-[10px] font-bold transition active:scale-95"
                                                style="border-radius: var(--radius-sm);">
                                            <i class="fa-solid fa-play text-[8px]"></i> Resume
                                        </button>
                                    @endif
                                    <button type="button"
                                            onclick="confirmReset({{ $session->id }}, '{{ $student->name }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[10px] font-bold transition active:scale-95"
                                            style="border-radius: var(--radius-sm);">
                                        <i class="fa-solid fa-arrow-rotate-left text-[8px]"></i> Reset
                                    </button>
                                </div>
                            @else
                                <span class="text-slate-300 text-[10px] italic">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr id="empty-row">
                        <td colspan="7" class="py-14 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
                                    <i class="fa-solid fa-users text-xl text-slate-300"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">Tidak ada mahasiswa terdaftar</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    // Client-side search & filter
    const searchInput  = document.getElementById('search-student');
    const statusSelect = document.getElementById('filter-status');
    const rows         = document.querySelectorAll('.student-row');

    function filterTable() {
        const q   = searchInput.value.toLowerCase().trim();
        const sel = statusSelect.value;
        rows.forEach(row => {
            const matchSearch = row.dataset.name.includes(q) || row.dataset.nim.includes(q);
            const matchStatus = sel === 'all' || row.dataset.status === sel;
            row.style.display = (matchSearch && matchStatus) ? '' : 'none';
        });
    }

    searchInput?.addEventListener('input', filterTable);
    statusSelect?.addEventListener('change', filterTable);

    // Reset
    function confirmReset(sessionId, studentName) {
        Swal.fire({
            title: 'Reset Ujian?',
            html: `Riwayat sesi dan jawaban <strong>${studentName}</strong> akan dihapus. Mahasiswa dapat mengulang ujian kembali.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Ya, Reset',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                axios.post(`/admin/monitoring/student-exam/${sessionId}/reset`)
                    .then(res => {
                        if (res.data.success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.data.message,
                                showConfirmButton: false, timer: 1500 })
                                .then(() => window.location.reload());
                        }
                    })
                    .catch(() => Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal mereset sesi ujian.' }));
            }
        });
    }

    // Pause / Resume
    function togglePending(sessionId, studentName, action) {
        Swal.fire({
            title: action === 'pause' ? 'Tangguhkan Ujian?' : 'Lanjutkan Ujian?',
            html: action === 'pause'
                ? `Sesi ujian <strong>${studentName}</strong> akan dijeda.`
                : `Sesi ujian <strong>${studentName}</strong> akan dilanjutkan kembali.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#14532D',
            cancelButtonColor: '#64748B',
            confirmButtonText: action === 'pause' ? 'Ya, Jeda' : 'Ya, Lanjutkan',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                axios.post(`/admin/monitoring/student-exam/${sessionId}/toggle-pending`)
                    .then(res => {
                        if (res.data.success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.data.message,
                                showConfirmButton: false, timer: 1500 })
                                .then(() => window.location.reload());
                        }
                    })
                    .catch(() => Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal mengubah status penangguhan.' }));
            }
        });
    }
</script>
@endsection
