@extends('layouts.mahasiswa')

@section('title', 'Ujian Aktif - E-Learning STIKesMu')

@section('mahasiswa-content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Mahasiswa &gt; CBTmu &gt; Ujian</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Daftar Ujian Aktif (CBTMu)
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Silakan pilih ujian yang sedang berlangsung dan masukkan token untuk memulai.</p>
            </div>
            <div>
                <button type="button" onclick="window.location.reload()" class="h-9 inline-flex items-center justify-center gap-1.5 px-3.5 border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 text-xs font-bold transition rounded-sm cursor-pointer shadow-sm">
                    <i class="fa-solid fa-arrows-rotate animate-hover"></i> Refresh Jadwal
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Sesi Aktif / In Progress -->
    @if ($activeSession)
    <div class="bg-amber-50 border-l-4 border-amber-500 p-5 shadow-sm space-y-4">
        <div class="flex items-start gap-3">
            <div class="h-10 w-10 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center text-lg flex-shrink-0 animate-pulse">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="space-y-1">
                <h3 class="font-black text-amber-900 text-sm">Sesi Ujian Sedang Berlangsung!</h3>
                <p class="text-xs text-amber-700 leading-relaxed">
                    Anda memiliki sesi pengerjaan aktif pada ujian <strong>{{ $activeSession->exam->course->name }} - {{ $activeSession->exam->title }}</strong>. 
                    Segera kembali ke ruang ujian untuk menyelesaikan pengerjaan sebelum waktu habis.
                </p>
            </div>
        </div>
        <div class="flex justify-start pl-13">
            <a href="{{ route('mahasiswa.exam-room', $activeSession->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition rounded-sm uppercase tracking-wider shadow-sm">
                <i class="fa-solid fa-right-to-bracket"></i> Kembali ke Ruang Ujian
            </a>
        </div>
    </div>
    @endif

    <!-- Daftar Ujian -->
    <div class="space-y-4">
        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest pl-2.5 border-l-4 border-primary bg-slate-50 py-1">
            Ujian Tersedia
        </h3>

        <div class="bg-white border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" style="min-width: 820px;">
                    <thead>
                        <tr class="bg-green-700 text-xs font-bold text-white uppercase tracking-wider">
                            <th class="py-3 px-4" style="min-width: 95px; width: 95px;">Kode</th>
                            <th class="py-3 px-4" style="min-width: 280px; width: 280px;">Mata Kuliah / Ujian</th>
                            <th class="py-3 px-4" style="min-width: 150px; width: 150px;">Mulai</th>
                            <th class="py-3 px-4" style="min-width: 150px; width: 150px;">Selesai</th>
                            <th class="py-3 px-4 text-center" style="min-width: 90px; width: 90px;">Durasi</th>
                            <th class="py-3 px-4" style="min-width: 160px; width: 160px;">Dosen</th>
                            <th class="py-3 px-4 text-right" style="min-width: 130px; width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @forelse ($activeExams as $exam)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="py-4 px-6">
                                <span class="px-2 py-0.5 text-[9px] font-extrabold rounded-full bg-blue-50 text-primary border border-blue-100 font-mono uppercase">
                                    {{ $exam->course->code }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div>
                                    <span class="font-bold text-gray-900 block leading-snug">{{ $exam->course->name }}</span>
                                    <span class="text-xs text-gray-400 font-normal mt-0.5 block">{{ $exam->title }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-slate-650 text-xs font-semibold">
                                {{ \Carbon\Carbon::parse($exam->start_time)->format('d M Y, H:i') }}
                            </td>
                            <td class="py-4 px-6 text-slate-650 text-xs font-semibold">
                                {{ \Carbon\Carbon::parse($exam->end_time)->format('d M Y, H:i') }}
                            </td>
                            <td class="py-4 px-6 text-center text-xs font-bold font-mono text-slate-700">
                                {{ $exam->duration_minutes }} Menit
                            </td>
                            <td class="py-4 px-6 text-slate-500 text-xs md:text-sm">{{ $exam->dosen->name }}</td>
                            <td class="py-4 px-6 text-right">
                                @if ($activeSession && $activeSession->exam_id === $exam->id)
                                    <a href="{{ route('mahasiswa.exam-room', $activeSession->id) }}" class="h-8 inline-flex items-center justify-center gap-1.5 px-3 bg-amber-600 hover:bg-amber-700 text-white text-[11px] font-bold transition rounded-sm uppercase tracking-wide">
                                        <i class="fa-solid fa-play text-[9px]"></i> Lanjut
                                    </a>
                                @elseif ($activeSession)
                                    <button type="button" disabled class="h-8 inline-flex items-center justify-center gap-1.5 px-3 bg-slate-200 text-slate-400 text-[11px] font-bold rounded-sm uppercase tracking-wide cursor-not-allowed">
                                        <i class="fa-solid fa-lock text-[9px]"></i> Terkunci
                                    </button>
                                @else
                                    <button type="button" onclick="openTokenModal({{ $exam->id }}, '{{ $exam->course->name }} - {{ $exam->title }}')" class="h-8 inline-flex items-center justify-center gap-1.5 px-3 bg-primary hover:bg-primary-900 text-white text-[11px] font-bold transition rounded-sm uppercase tracking-wide cursor-pointer">
                                        <i class="fa-solid fa-key text-[9px]"></i> Mulai Ujian
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                <i class="fa-solid fa-desktop text-4xl mb-3 text-gray-300 block"></i>
                                <p class="text-xs font-semibold">Tidak ada sesi ujian aktif terjadwal saat ini untuk kelas Anda.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Token Entry Modal -->
<div id="token-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeTokenModal()"></div>
    
    <!-- Modal Content -->
    <div class="bg-white border border-slate-200 shadow-xl w-full max-w-md p-6 relative z-10 flex flex-col space-y-4">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 bg-green-50 text-green-700 border border-green-100 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Konfirmasi Mulai Ujian</h3>
                    <p class="text-[10px] text-slate-400 font-medium">Langkah validasi token pengawas / proktor</p>
                </div>
            </div>
            <button type="button" onclick="closeTokenModal()" class="text-slate-400 hover:text-slate-650 cursor-pointer border-0 bg-transparent">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <div class="space-y-3">
            <div class="bg-slate-50 p-3 border border-slate-150 rounded-sm">
                <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider">Ujian Terpilih</span>
                <strong id="modal-exam-title" class="text-xs text-slate-800 font-bold">Nama Ujian</strong>
            </div>

            <div class="space-y-1">
                <label for="exam-token" class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider">Masukkan Token Ujian</label>
                <input type="text" id="exam-token" placeholder="CONTOH: ABCDEF" class="w-full text-center tracking-widest text-lg font-black uppercase border border-slate-300 p-2.5 focus:border-primary focus:ring-1 focus:ring-primary rounded-none outline-none">
                <span class="block text-[9px] text-red-500 font-medium hidden" id="token-error">Token tidak cocok. Silakan minta token ke pengawas ujian.</span>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" onclick="closeTokenModal()" class="px-4 py-2 border border-slate-300 text-slate-700 text-xs font-bold transition hover:bg-slate-50 rounded-sm cursor-pointer">
                Batal
            </button>
            <button type="button" id="btn-submit-token" onclick="submitExamToken()" class="px-4 py-2 bg-primary hover:bg-primary-900 text-white text-xs font-bold transition rounded-sm uppercase tracking-wider cursor-pointer">
                Mulai Sekarang
            </button>
        </div>
    </div>
</div>

@section('scripts')
<script>
    let selectedExamId = null;

    function openTokenModal(examId, examTitle) {
        selectedExamId = examId;
        document.getElementById('modal-exam-title').textContent = examTitle;
        document.getElementById('exam-token').value = '';
        document.getElementById('token-error').classList.add('hidden');
        document.getElementById('token-modal').classList.remove('hidden');
        
        // Auto focus the token input
        setTimeout(() => {
            document.getElementById('exam-token').focus();
        }, 100);
    }

    function closeTokenModal() {
        selectedExamId = null;
        document.getElementById('token-modal').classList.add('hidden');
    }

    function submitExamToken() {
        const tokenInput = document.getElementById('exam-token');
        const errorMsg = document.getElementById('token-error');
        const token = tokenInput.value.trim();
        const submitBtn = document.getElementById('btn-submit-token');

        if (!token) {
            errorMsg.textContent = "Token wajib diisi.";
            errorMsg.classList.remove('hidden');
            return;
        }

        // Disable elements during request
        tokenInput.disabled = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> Memuat...';
        errorMsg.classList.add('hidden');

        fetch('/api/v1/exam/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                exam_id: selectedExamId,
                token: token
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Redirect to exam room
                window.location.href = `/mahasiswa/ruang-ujian/${data.session_id}`;
            } else {
                // Show error
                errorMsg.textContent = data.message || 'Token tidak valid.';
                errorMsg.classList.remove('hidden');
                tokenInput.disabled = false;
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Mulai Sekarang';
            }
        })
        .catch(err => {
            console.error('Error starting exam:', err);
            errorMsg.textContent = 'Terjadi kesalahan sistem. Coba lagi.';
            errorMsg.classList.remove('hidden');
            tokenInput.disabled = false;
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Mulai Sekarang';
        });
    }
</script>
@endsection
@endsection
