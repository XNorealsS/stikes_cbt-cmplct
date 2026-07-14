@extends('layouts.admin')

@section('title', 'Detail Monitoring Ujian - SIAKAD STIKesMu')

@section('admin-content')
<div class="space-y-4">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Admin &gt; Monitoring Ujian &gt; Detail Mahasiswa</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Sesi Monitoring: {{ $exam->title }}
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Mata Kuliah: <span class="font-semibold text-slate-700">{{ $exam->course->name }} ({{ $exam->course->code }})</span> | Dosen: {{ $exam->dosen->name }}</p>
            </div>
            <a href="{{ route('admin.monitoring.index') }}" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition inline-flex items-center gap-1.5 shadow-none cursor-pointer">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Kembali ke List</span>
            </a>
        </div>
    </div>

    <!-- Statistics Summary Boxes (SIAKAD style) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="border border-slate-200 bg-white p-3.5 rounded-lg shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Mahasiswa</p>
            <p class="mt-0.5 text-xl font-bold text-slate-800 font-heading">{{ $students->count() }}</p>
        </div>
        <div class="border border-slate-200 bg-white p-3.5 rounded-lg shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sedang Ujian</p>
            <p class="mt-0.5 text-xl font-bold text-sky-700 font-heading">
                {{ $students->filter(fn($s) => optional($s->exam_session)->status === 'progress')->count() }}
            </p>
        </div>
        <div class="border border-slate-200 bg-white p-3.5 rounded-lg shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Selesai Ujian</p>
            <p class="mt-0.5 text-xl font-bold text-emerald-700 font-heading">
                {{ $students->filter(fn($s) => optional($s->exam_session)->status === 'finished')->count() }}
            </p>
        </div>
        <div class="border border-slate-200 bg-white p-3.5 rounded-lg shadow-xs">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Ditangguhkan / Pending</p>
            <p class="mt-0.5 text-xl font-bold text-amber-700 font-heading">
                {{ $students->filter(fn($s) => optional($s->exam_session)->status === 'pending')->count() }}
            </p>
        </div>
    </div>

    <!-- Real-time Student Table Container (SIAKAD style) -->
    <div class="border border-slate-200 bg-white rounded-lg shadow-xs p-4 space-y-4">
        <!-- Filter Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 pb-3 border-b border-slate-150">
            <div class="flex flex-1 items-center gap-2 max-w-md">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" id="search-student" placeholder="Cari nama mahasiswa atau NIM..." class="w-full border border-slate-300 pl-9 pr-3 py-1.5 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none bg-white">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500 font-semibold whitespace-nowrap">Filter Status:</span>
                <select id="filter-status" class="border border-slate-300 px-3 py-1.5 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none bg-white min-w-[140px]">
                    <option value="all">Semua Status</option>
                    <option value="not_started">Belum Mulai</option>
                    <option value="progress">Sedang Ujian</option>
                    <option value="pending">Ditangguhkan</option>
                    <option value="finished">Selesai</option>
                </select>
            </div>
        </div>

        <!-- Student Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-slate-800 border-collapse" id="monitoring-table">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 font-bold">
                        <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-[11px] w-[30%]">Mahasiswa</th>
                        <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-[11px] w-[15%]">Kelas</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px] w-[12%]">Waktu Mulai</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px] w-[12%]">Sisa Waktu</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px] w-[13%]">Status</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px] w-[10%]">Nilai Ujian</th>
                        <th class="px-4 py-3 text-right font-semibold uppercase tracking-wider text-[11px] w-[18%]">Aksi Kontrol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($students as $student)
                    @php
                        $session = $student->exam_session;
                        $remaining = '-';
                        $statusClass = 'not_started';
                        if ($session && $session->status === 'progress') {
                            $statusClass = 'progress';
                            $startedAt = \Carbon\Carbon::parse($session->started_at);
                            $elapsedSeconds = now()->diffInSeconds($startedAt);
                            $totalSeconds = $exam->duration_minutes * 60;
                            $remSeconds = $totalSeconds - $elapsedSeconds;
                            if ($remSeconds > 0) {
                                $remMinutes = ceil($remSeconds / 60);
                                $remaining = $remMinutes . ' Menit';
                            } else {
                                $remaining = 'Waktu Habis';
                            }
                        } elseif ($session && $session->status === 'pending') {
                            $statusClass = 'pending';
                            $startedAt = \Carbon\Carbon::parse($session->started_at);
                            $suspendedAt = \Carbon\Carbon::parse($session->suspended_at ?? now());
                            $elapsedSeconds = $suspendedAt->diffInSeconds($startedAt);
                            $totalSeconds = $exam->duration_minutes * 60;
                            $remSeconds = $totalSeconds - $elapsedSeconds;
                            $remaining = ceil(max(0, $remSeconds) / 60) . ' Menit (Paused)';
                        } elseif ($session && $session->status === 'finished') {
                            $statusClass = 'finished';
                        }
                    @endphp
                    <tr class="student-row hover:bg-slate-50 transition" 
                        data-name="{{ strtolower($student->name) }}" 
                        data-nim="{{ strtolower($student->username) }}" 
                        data-status="{{ $statusClass }}">
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-800 block">{{ $student->name }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">NIM: {{ $student->username }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-700 font-medium truncate">
                            {{ $student->classRoom ? $student->classRoom->name : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center font-mono text-slate-600">
                            {{ $session ? \Carbon\Carbon::parse($session->started_at)->format('H:i:s') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center font-mono text-slate-800 font-semibold">
                            {{ $remaining }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if (!$session)
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 font-semibold rounded text-[10px] uppercase">
                                    Belum Mulai
                                </span>
                            @elseif ($session->status === 'progress')
                                <span class="px-2 py-0.5 bg-sky-100 text-sky-800 font-semibold rounded text-[10px] uppercase">
                                    Sedang Ujian
                                </span>
                            @elseif ($session->status === 'pending')
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-semibold rounded text-[10px] uppercase">
                                    Ditangguhkan
                                </span>
                            @elseif ($session->status === 'finished')
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-semibold rounded text-[10px] uppercase">
                                    Selesai
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-mono font-bold text-slate-800">
                            {{ $session && $session->score !== null ? number_format($session->score, 2) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right space-x-1">
                            @if ($session)
                                @if ($session->status === 'progress')
                                    <button type="button" onclick="togglePending({{ $session->id }}, '{{ $student->name }}', 'pause')" class="bg-amber-600 hover:bg-amber-700 text-white px-2 py-1 rounded text-xs font-semibold transition cursor-pointer">
                                        Pause
                                    </button>
                                @elseif ($session->status === 'pending')
                                    <button type="button" onclick="togglePending({{ $session->id }}, '{{ $student->name }}', 'resume')" class="bg-sky-600 hover:bg-sky-700 text-white px-2 py-1 rounded text-xs font-semibold transition cursor-pointer">
                                        Resume
                                    </button>
                                @endif
                                
                                <button type="button" onclick="confirmReset({{ $session->id }}, '{{ $student->name }}')" class="bg-rose-600 hover:bg-rose-700 text-white px-2 py-1 rounded text-xs font-semibold transition cursor-pointer">
                                    Reset
                                </button>
                            @else
                                <span class="text-slate-400 text-[10px] italic">Belum ada sesi</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr id="empty-row">
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">Tidak ada mahasiswa terdaftar di sistem.</td>
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
    // Client-side search and filtering logic
    const searchInput = document.getElementById('search-student');
    const statusSelect = document.getElementById('filter-status');
    const rows = document.querySelectorAll('.student-row');

    function filterTable() {
        const searchText = searchInput.value.toLowerCase().trim();
        const selectedStatus = statusSelect.value;

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const nim = row.getAttribute('data-nim');
            const status = row.getAttribute('data-status');

            const matchesSearch = name.includes(searchText) || nim.includes(searchText);
            const matchesStatus = selectedStatus === 'all' || status === selectedStatus;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput && statusSelect) {
        searchInput.addEventListener('input', filterTable);
        statusSelect.addEventListener('change', filterTable);
    }

    // Reset Student Session Action
    function confirmReset(sessionId, studentName) {
        Swal.fire({
            title: 'Reset Ujian Mahasiswa?',
            text: `Anda akan menghapus riwayat sesi ujian dan jawaban mahasiswa "${studentName}". Mahasiswa ini dapat mengulang ujian kembali menggunakan token.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Ya, Reset Ujian',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post(`/admin/monitoring/student-exam/${sessionId}/reset`)
                    .then(res => {
                        if (res.data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.data.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    })
                    .catch(err => {
                        showError('Gagal mereset sesi ujian mahasiswa.');
                    });
            }
        });
    }

    // Pause/Resume Student Session Action
    function togglePending(sessionId, studentName, action) {
        const text = action === 'pause' 
            ? `Apakah Anda yakin ingin menangguhkan (pending) sesi ujian mahasiswa "${studentName}"?`
            : `Apakah Anda yakin ingin melanjutkan sesi ujian mahasiswa "${studentName}"?`;
            
        Swal.fire({
            title: action === 'pause' ? 'Tangguhkan Ujian?' : 'Lanjutkan Ujian?',
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1A4731',
            cancelButtonColor: '#64748B',
            confirmButtonText: action === 'pause' ? 'Ya, Tangguhkan' : 'Ya, Lanjutkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post(`/admin/monitoring/student-exam/${sessionId}/toggle-pending`)
                    .then(res => {
                        if (res.data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.data.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    })
                    .catch(err => {
                        showError('Gagal mengubah status penangguhan ujian mahasiswa.');
                    });
            }
        });
    }
</script>
@endsection
