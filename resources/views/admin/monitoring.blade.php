@extends('layouts.admin')

@section('title', 'Monitoring Pelaksanaan Ujian — STIKesMu CBT')

@section('admin-content')
<div class="space-y-5">

    {{-- ── Page Header ──────────────────────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h1 class="page-header-title">
                <span class="title-bar"></span>
                Monitoring Ujian
            </h1>
            <p class="page-header-subtitle">Pantau pelaksanaan ujian, token sesi, dan status mahasiswa secara real-time.</p>
        </div>
        <span class="badge badge-danger text-[10px] uppercase tracking-wider">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
            </span>
            Live Monitor
        </span>
    </div>

    {{-- ── Exam List Table ──────────────────────────────────────────── --}}
    <div class="card overflow-hidden">
        <div class="card-header">
            <span class="flex items-center gap-2">
                <i class="fa-solid fa-tower-broadcast text-primary-700 text-xs"></i>
                Daftar Sesi Ujian Aktif
            </span>
            <span class="text-[10px] font-normal text-slate-400">{{ count($exams) }} sesi ditemukan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr>
                        <th>Nama Sesi Ujian</th>
                        <th>Mata Kuliah</th>
                        <th class="text-center">Jadwal</th>
                        <th class="text-center">Durasi</th>
                        <th class="text-center">Token</th>
                        <th class="text-center">Progres</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exams as $exam)
                    <tr>
                        <td>
                            <span class="font-bold text-slate-800 block text-xs">{{ $exam->title }}</span>
                            <span class="text-[10px] text-slate-400 mt-0.5 block">
                                <i class="fa-solid fa-user-tie text-[8px] mr-0.5"></i>
                                {{ $exam->dosen->name }}
                            </span>
                        </td>
                        <td>
                            <span class="font-semibold text-slate-700 block text-xs">{{ $exam->course->name }}</span>
                            <span class="text-[10px] text-slate-400 font-mono mt-0.5 block">{{ $exam->course->code }}</span>
                        </td>
                        <td class="text-center">
                            <span class="font-mono text-[11px] text-slate-600 block">
                                {{ \Carbon\Carbon::parse($exam->start_time)->format('d/m/Y') }}
                            </span>
                            <span class="font-mono text-[10px] text-slate-400 block">
                                {{ \Carbon\Carbon::parse($exam->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($exam->end_time)->format('H:i') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-700">
                                <i class="fa-regular fa-clock text-[9px] text-slate-400"></i>
                                {{ $exam->duration_minutes }} menit
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="inline-flex items-center px-2.5 py-1 border border-primary-100 bg-primary-50 text-primary-800 font-mono font-bold text-[11px] tracking-widest" style="border-radius: var(--radius-sm);">
                                {{ $exam->token }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-sky-50 text-sky-700 text-[10px] font-bold border border-sky-100" style="border-radius: var(--radius-sm);" title="Sedang Mengerjakan">
                                    <i class="fa-solid fa-pen-to-square text-[8px]"></i>
                                    {{ $exam->total_started }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-100" style="border-radius: var(--radius-sm);" title="Selesai">
                                    <i class="fa-solid fa-circle-check text-[8px]"></i>
                                    {{ $exam->total_finished }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-700 text-[10px] font-bold border border-amber-100" style="border-radius: var(--radius-sm);" title="Ditangguhkan">
                                    <i class="fa-solid fa-pause text-[8px]"></i>
                                    {{ $exam->total_pending }}
                                </span>
                            </div>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.monitoring.detail', $exam->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary hover:bg-primary-900 text-white text-[11px] font-bold transition-all active:scale-95"
                                   style="border-radius: var(--radius-sm);">
                                    <i class="fa-solid fa-desktop text-[9px]"></i>
                                    Monitor
                                </a>
                                <button type="button" onclick="openAdjustTimeModal({{ json_encode($exam) }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-[11px] font-bold transition-all active:scale-95"
                                        style="border-radius: var(--radius-sm);">
                                    <i class="fa-regular fa-clock text-[9px]"></i>
                                    Atur Waktu
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-14 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
                                    <i class="fa-solid fa-tower-broadcast text-xl text-slate-300"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-400">Tidak ada sesi ujian aktif</p>
                                    <p class="text-xs text-slate-300 mt-0.5">Sesi ujian yang berjalan akan muncul di sini.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── Modal Atur Waktu ──────────────────────────────────────────────── --}}
<div id="adjust-time-modal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white max-w-md w-full overflow-hidden" style="border-radius: var(--radius-xl); box-shadow: var(--shadow-xl);">

        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-regular fa-clock text-primary-700"></i>
                Atur Waktu Sesi Ujian
            </h3>
            <button onclick="closeAdjustTimeModal()" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition" style="border-radius: var(--radius-sm);">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <form id="adjust-time-form" class="flex flex-col max-h-[85vh]">
            @csrf
            <input type="hidden" id="adjust-id">

            <div class="p-6 space-y-4 overflow-y-auto">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nama Sesi Ujian</label>
                    <input type="text" id="adjust-title" readonly
                           class="w-full bg-slate-50 cursor-not-allowed">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Durasi (Menit)</label>
                        <input type="number" id="adjust-duration" name="duration_minutes" required min="1"
                               class="w-full">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Waktu Selesai</label>
                        <input type="datetime-local" id="adjust-end-time" name="end_time_input" required
                               class="w-full">
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 flex justify-end gap-2 border-t border-slate-100 bg-slate-50 flex-shrink-0">
                <button type="button" onclick="closeAdjustTimeModal()"
                        class="px-4 py-2 border border-slate-300 text-slate-600 text-xs font-semibold hover:bg-slate-100 transition cursor-pointer"
                        style="border-radius: var(--radius-md);">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-primary hover:bg-primary-900 text-white text-xs font-bold transition cursor-pointer"
                        style="border-radius: var(--radius-md);">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openAdjustTimeModal(exam) {
        document.getElementById('adjust-id').value = exam.id;
        document.getElementById('adjust-title').value = exam.title;
        document.getElementById('adjust-duration').value = exam.duration_minutes;

        const date = new Date(exam.end_time);
        const pad = (n) => n < 10 ? '0' + n : n;
        const localDateTime = date.getFullYear() + '-' +
            pad(date.getMonth() + 1) + '-' +
            pad(date.getDate()) + 'T' +
            pad(date.getHours()) + ':' +
            pad(date.getMinutes());
        document.getElementById('adjust-end-time').value = localDateTime;

        const modal = document.getElementById('adjust-time-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        requestAnimationFrame(() => {
            modal.querySelector('div').style.transform = 'scale(1)';
        });
    }

    function closeAdjustTimeModal() {
        const modal = document.getElementById('adjust-time-modal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        document.getElementById('adjust-time-form').reset();
    }

    document.getElementById('adjust-time-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id       = document.getElementById('adjust-id').value;
        const duration = document.getElementById('adjust-duration').value;
        const localEndTime = document.getElementById('adjust-end-time').value;

        const date = new Date(localEndTime);
        const pad  = (n) => n < 10 ? '0' + n : n;
        const formattedEndTime = date.getFullYear() + '-' +
            pad(date.getMonth() + 1) + '-' +
            pad(date.getDate()) + ' ' +
            pad(date.getHours()) + ':' +
            pad(date.getMinutes()) + ':00';

        axios.post(`/admin/monitoring/exam/${id}/adjust-time`, {
            duration_minutes: duration,
            end_time: formattedEndTime
        })
        .then(res => {
            if (res.data.success) {
                closeAdjustTimeModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => window.location.reload());
            }
        })
        .catch(err => {
            const msg = err.response?.data?.message ?? 'Gagal memperbarui waktu ujian.';
            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
        });
    });
</script>
@endsection
