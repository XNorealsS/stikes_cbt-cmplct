@extends('layouts.admin')

@section('title', 'Monitoring Pelaksanaan Ujian - SIAKAD STIKesMu')

@section('admin-content')
<div class="space-y-4">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Admin &gt; Monitoring Ujian</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Monitoring Pelaksanaan Ujian
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Pantau jalannya ujian, token sesi, dan kelola status pengerjaan mahasiswa secara real-time.</p>
            </div>
        </div>
    </div>

    <!-- Monitoring Table Container (SIAKAD style) -->
    <div class="border border-slate-200 bg-white rounded-lg shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-slate-800 border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 font-bold">
                        <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-[11px]">Nama Sesi Ujian</th>
                        <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-[11px]">Mata Kuliah</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px]">Jadwal Ujian</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px]">Durasi</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px]">Token</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px]">Aktif / Selesai / Ditunda</th>
                        <th class="px-4 py-3 text-right font-semibold uppercase tracking-wider text-[11px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($exams as $exam)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-800 block">{{ $exam->title }}</span>
                            <span class="text-[10px] text-slate-400">Dibuat oleh: {{ $exam->dosen->name }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-semibold text-slate-700 block">{{ $exam->course->name }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $exam->course->code }}</span>
                        </td>
                        <td class="px-4 py-3 text-center font-mono text-slate-600">
                            {{ \Carbon\Carbon::parse($exam->start_time)->format('d/m/Y H:i') }} - {{ \Carbon\Carbon::parse($exam->end_time)->format('H:i') }}
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-slate-700">
                            {{ $exam->duration_minutes }} Menit
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2.5 py-0.5 border border-emerald-300 bg-emerald-50 text-emerald-800 font-mono font-bold rounded text-[11px]">
                                {{ $exam->token }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1">
                                <span class="px-1.5 py-0.5 bg-sky-100 text-sky-800 font-bold rounded text-[10px]" title="Sedang Mengerjakan">{{ $exam->total_started }}</span>
                                <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded text-[10px]" title="Selesai">{{ $exam->total_finished }}</span>
                                <span class="px-1.5 py-0.5 bg-amber-100 text-amber-800 font-bold rounded text-[10px]" title="Ditangguhkan">{{ $exam->total_pending }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-1.5">
                            <a href="{{ route('admin.monitoring.detail', $exam->id) }}" class="rounded border border-emerald-700 bg-emerald-700 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-800 transition inline-flex items-center gap-1 shadow-none cursor-pointer">
                                <i class="fa-solid fa-desktop text-[10px]"></i>
                                <span>Monitor</span>
                            </a>
                            <button type="button" onclick="openAdjustTimeModal({{ json_encode($exam) }})" class="rounded border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition inline-flex items-center gap-1 shadow-none cursor-pointer">
                                <i class="fa-solid fa-clock text-[10px]"></i>
                                <span>Atur Waktu</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">Tidak ada sesi pelaksanaan ujian saat ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Atur Waktu (SIAKAD style) -->
<div id="adjust-time-modal" class="fixed inset-0 z-50 bg-slate-900/40 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full border border-slate-200 shadow-lg overflow-hidden transform scale-95 transition-all duration-200">
        <div class="px-5 py-3.5 border-b border-slate-200 flex justify-between items-center bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800"><i class="fa-solid fa-clock text-emerald-700 mr-2"></i>Atur Waktu Sesi Ujian</h3>
            <button onclick="closeAdjustTimeModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark text-base"></i></button>
        </div>
        <form id="adjust-time-form" class="p-5 space-y-4">
            @csrf
            <input type="hidden" id="adjust-id">

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Sesi Ujian</label>
                <input type="text" id="adjust-title" readonly class="w-full bg-slate-100 border border-slate-200 px-3 py-2 text-xs text-slate-500 rounded focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Durasi Pengerjaan (Menit)</label>
                <input type="number" id="adjust-duration" name="duration_minutes" required min="1" class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:ring-1 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Waktu Selesai (Jadwal Tutup)</label>
                <input type="datetime-local" id="adjust-end-time" name="end_time_input" required class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:ring-1 focus:ring-green-500">
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-200">
                <button type="button" onclick="closeAdjustTimeModal()" class="rounded border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer">Batal</button>
                <button type="submit" class="rounded border border-transparent bg-green-700 px-4 py-1.5 text-xs font-semibold text-white hover:bg-green-800 transition cursor-pointer">Simpan Perubahan</button>
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
        
        // Format ISO string to local datetime string (YYYY-MM-DDTHH:MM)
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
        setTimeout(() => {
            modal.querySelector('div').classList.remove('scale-95');
            modal.querySelector('div').classList.add('scale-100');
        }, 10);
    }

    function closeAdjustTimeModal() {
        const modal = document.getElementById('adjust-time-modal');
        modal.querySelector('div').classList.remove('scale-100');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.getElementById('adjust-time-form').reset();
        }, 200);
    }

    document.getElementById('adjust-time-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('adjust-id').value;
        const duration = document.getElementById('adjust-duration').value;
        const localEndTime = document.getElementById('adjust-end-time').value;
        
        // Convert local date time back to format Y-m-d H:i:s
        const date = new Date(localEndTime);
        const pad = (n) => n < 10 ? '0' + n : n;
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
                }).then(() => {
                    window.location.reload();
                });
            }
        })
        .catch(err => {
            const msg = err.response && err.response.data && err.response.data.message 
                ? err.response.data.message 
                : 'Gagal memperbarui waktu ujian.';
            showError(msg);
        });
    });
</script>
@endsection
