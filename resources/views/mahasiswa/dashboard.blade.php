@extends('layouts.mahasiswa')

@section('title', 'Dashboard Mahasiswa - E-Learning STIKesMu')

@section('mahasiswa-content')
<div class="flex-grow w-full space-y-6">
    
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Mahasiswa &gt; Dashboard</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Dashboard Mahasiswa
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Selamat datang, <strong class="text-slate-700">{{ auth()->user()->name }}</strong>. Akses materi pembelajaran dan kelola progres tugas serta ujian Anda.</p>
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <a href="{{ route('mahasiswa.materi.index') }}" class="rounded border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold text-white hover:bg-green-800 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                    <i class="fa-solid fa-book text-xs"></i> Materi Belajar
                </a>
                <a href="{{ route('mahasiswa.history') }}" class="rounded border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                    <i class="fa-solid fa-clock-rotate-left text-xs"></i> Riwayat CBTMu
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid: 4 columns on desktop, 2 on tablet, 1 on mobile -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <div class="bg-white p-5 rounded-xl border border-gray-255 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Mata Kuliah Diikuti</span>
                <span class="text-2xl font-black text-primary leading-tight block">{{ $courseCount }}</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Mata kuliah terdaftar aktif</span>
        </div>

        <!-- Card 2 -->
        <div class="bg-white p-5 rounded-xl border border-gray-255 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tugas Belum Selesai</span>
                <span class="text-2xl font-black text-gray-800 leading-tight block">{{ $uncompletedTasksCount }}</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Tugas mendekati deadline</span>
        </div>

        <!-- Card 3 -->
        <div class="bg-white p-5 rounded-xl border border-gray-255 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Sesi Ujian Hari Ini</span>
                <span class="text-2xl font-black text-gray-800 leading-tight block">{{ $examsTodayCount }}</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Jadwal ujian aktif hari ini</span>
        </div>

        <!-- Card 4 -->
        <div class="bg-white p-5 rounded-xl border border-gray-255 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Rata-rata Nilai</span>
                <span class="text-2xl font-black text-gray-800 leading-tight block">{{ number_format($avgScore, 2) }}</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Skor rekapitulasi ujian selesai</span>
        </div>
    </div>

    <!-- Running Text Announcement Banner -->
    <div class="bg-primary text-white py-2 px-4 rounded-xl shadow-sm flex items-center space-x-3 overflow-hidden">
        <span class="bg-emerald-800 text-white text-[9px] font-extrabold uppercase tracking-widest px-2.5 py-0.5 rounded flex-shrink-0">Pengumuman</span>
        <div class="flex-grow overflow-hidden relative h-5">
            <marquee class="font-semibold text-xs py-0.5 tracking-wide" scrollamount="4">
                Selamat datang di platform E-Learning STIKes Muhammadiyah Lhokseumawe. Akses materi, tugas, dan modul ujian CBTMu dalam satu sistem yang terintegrasi.
            </marquee>
        </div>
    </div>

    <!-- Active/Resume Session Alert -->
    @if ($activeSession)
    <div class="bg-amber-50/50 border border-amber-250 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center space-x-4">
            <div class="h-10 w-10 bg-amber-100 text-amber-700 rounded-lg flex items-center justify-center text-lg flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h3 class="font-bold text-amber-900 text-sm">Sesi Ujian CBTMu Aktif!</h3>
                <p class="text-amber-750 text-xs">Ujian sedang berjalan untuk mata kuliah: <strong>{{ $activeSession->exam->course->name }}</strong>.</p>
            </div>
        </div>
        <a href="{{ route('mahasiswa.exam-room', ['id' => $activeSession->id]) }}" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded-lg text-xs uppercase tracking-wider transition shadow-sm flex items-center space-x-2 w-full sm:w-auto justify-center">
            <i class="fa-solid fa-circle-play"></i>
            <span>Lanjutkan Sekarang</span>
        </a>
    </div>
    @endif

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: E-Learning (Materi & Tugas) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Latest Materials -->
            <div class="bg-white rounded-2xl border border-slate-150 shadow-sm p-6 space-y-4">
                <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center">
                        <i class="fa-solid fa-book-open text-primary mr-2"></i> Materi Pembelajaran Terbaru
                    </h2>
                    <a href="{{ route('mahasiswa.materi.index') }}" class="text-xs text-primary hover:underline font-bold">Lihat Semua</a>
                </div>
                
                <div class="divide-y divide-slate-100">
                    @forelse($latestMateris as $materi)
                        <div class="py-3 first:pt-0 last:pb-0 flex items-start gap-4 hover:bg-slate-50/50 px-2 rounded-lg transition">
                            <div class="h-10 w-10 bg-emerald-50 text-primary rounded-xl flex items-center justify-center text-lg flex-shrink-0">
                                @if($materi->tipe === 'file')
                                    <i class="fa-solid fa-file-pdf"></i>
                                @elseif($materi->tipe === 'link')
                                    <i class="fa-solid fa-link"></i>
                                @else
                                    <i class="fa-solid fa-align-left"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-slate-900 text-sm truncate">{{ $materi->judul }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ $materi->course->name }} &bull; Dosen: {{ $materi->user->name }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-[9px] font-extrabold rounded-full px-2 py-0.5 bg-blue-50 text-primary border border-blue-100 font-mono uppercase">
                                    {{ $materi->course->code }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-folder-open text-2xl mb-2 text-slate-300 block"></i>
                            Belum ada materi pembelajaran yang dirilis.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Upcoming Tasks -->
            <div class="bg-white rounded-2xl border border-slate-150 shadow-sm p-6 space-y-4">
                <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center">
                        <i class="fa-solid fa-file-pen text-primary mr-2"></i> Tugas Mendekati Deadline
                    </h2>
                    <a href="{{ route('mahasiswa.tugas.index') }}" class="text-xs text-primary hover:underline font-bold">Lihat Semua</a>
                </div>
                
                <div class="divide-y divide-slate-100">
                    @forelse($upcomingTugas as $t)
                        <div class="py-3 first:pt-0 last:pb-0 flex items-start justify-between gap-4 hover:bg-slate-50/50 px-2 rounded-lg transition">
                            <div class="flex items-start gap-4 min-w-0">
                                <div class="h-10 w-10 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center text-lg flex-shrink-0">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-slate-900 text-sm truncate">{{ $t->judul }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $t->course->name }} &bull; Dosen: {{ $t->user->name }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-[10px] font-bold text-red-650 block bg-red-50 px-2 py-0.5 rounded border border-red-100">
                                    Batas: {{ $t->deadline ? $t->deadline->format('d M H:i') : 'Tanpa Batas' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-file-circle-check text-2xl mb-2 text-slate-350 block"></i>
                            Bagus! Tidak ada tugas aktif yang belum dikerjakan.
                        </div>
                    @endforelse
                </div>
            </div>
            
        </div>

        <!-- Right Column: CBTMu (Jadwal Ujian Aktif) -->
        <div class="space-y-6">
            
            <div class="bg-white rounded-2xl border border-slate-150 shadow-sm p-6 space-y-4">
                <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest border-b border-slate-100 pb-3 flex items-center">
                    <i class="fa-solid fa-calendar-days text-primary mr-2"></i> Ujian Aktif (CBTMu)
                </h2>
                
                <div class="space-y-4">
                    @forelse ($activeExams as $exam)
                    <div class="border border-slate-200 rounded-xl p-4 space-y-3 hover:border-emerald-700/50 transition bg-slate-50/30">
                        <div>
                            <span class="px-2.5 py-0.5 text-[9px] font-extrabold rounded-full bg-emerald-50 text-emerald-800 border border-emerald-100 uppercase font-mono">
                                {{ $exam->course->code }}
                            </span>
                            <h3 class="font-bold text-slate-900 text-sm mt-1.5">{{ $exam->title }}</h3>
                            <p class="text-xs text-slate-500">Mata Kuliah: {{ $exam->course->name }}</p>
                        </div>
                        
                        <div class="text-[11px] text-slate-600 bg-slate-50 p-2.5 rounded-lg space-y-1 border border-slate-100">
                            <div><i class="fa-solid fa-user text-[10px] mr-1 opacity-70"></i> {{ $exam->dosen->name }}</div>
                            <div><i class="fa-solid fa-clock text-[10px] mr-1 opacity-70"></i> {{ $exam->duration_minutes }} Menit ({{ $exam->total_questions }} Soal)</div>
                            <div><i class="fa-solid fa-circle-info text-[10px] mr-1 opacity-70"></i> Tipe: {{ $exam->exam_type }}</div>
                            <div class="text-red-650 font-semibold"><i class="fa-solid fa-triangle-exclamation text-[10px] mr-1"></i> Batas: {{ $exam->end_time->format('H:i') }} WIB</div>
                        </div>

                        <div>
                            @if ($activeSession)
                                <button disabled class="w-full bg-slate-100 text-slate-400 font-bold py-2 px-4 rounded-xl text-xs uppercase cursor-not-allowed">Selesaikan Sesi Berjalan</button>
                            @else
                                <button type="button" onclick="openTokenModal({{ $exam->id }}, '{{ $exam->title }}')" class="w-full bg-emerald-750 hover:bg-emerald-850 text-white font-bold py-2.5 px-4 rounded-xl text-xs uppercase tracking-wider transition shadow-sm flex items-center justify-center space-x-1.5">
                                    <i class="fa-solid fa-key"></i>
                                    <span>Ikut Ujian</span>
                                </button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="py-8 text-center text-slate-400 text-xs bg-slate-50/50 rounded-xl border border-slate-100">
                        <i class="fa-solid fa-calendar-xmark text-3xl mb-3 text-slate-300 block"></i>
                        Tidak ada jadwal ujian aktif hari ini.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
        
    </div>
</div>

<!-- Modal Token Ujian -->
<div id="token-modal" class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 border border-slate-100">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/80">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest"><i class="fa-solid fa-lock text-primary mr-1.5"></i>Konfirmasi Token Ujian</h3>
            <button onclick="closeTokenModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="token-form" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="exam-id-input">
            
            <div class="text-center space-y-2">
                <h4 class="font-extrabold text-slate-800 text-sm" id="exam-title-text">UTS</h4>
                <p class="text-[10px] text-slate-500">Masukkan token ujian 6 digit yang diberikan oleh Dosen / Pengawas.</p>
            </div>

            <div>
                <label for="token-input" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 text-center">Token Ujian (6 Karakter)</label>
                <input type="text" id="token-input" required max="6" placeholder="XYZ123" class="w-full text-center border border-slate-300 rounded-xl px-3 py-3 font-mono font-black text-lg tracking-widest uppercase focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
            </div>

            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 text-xs text-emerald-800 leading-relaxed">
                <i class="fa-solid fa-circle-info mr-1"></i><strong>INFO:</strong> Menutup atau memuat ulang halaman saat ujian berlangsung tetap akan mengurangi durasi pengerjaan Anda.
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeTokenModal()" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-full text-xs font-bold hover:bg-slate-50 transition">Batal</button>
                <button type="submit" id="btn-start-exam" class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-full text-xs font-bold transition shadow-sm">Mulai Ujian</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Token Modal Functions
    function openTokenModal(examId, examTitle) {
        document.getElementById('exam-id-input').value = examId;
        document.getElementById('exam-title-text').textContent = examTitle;
        
        const modal = document.getElementById('token-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.querySelector('div').classList.remove('scale-95');
            modal.querySelector('div').classList.add('scale-100');
            document.getElementById('token-input').focus();
        }, 10);
    }

    function closeTokenModal() {
        const modal = document.getElementById('token-modal');
        modal.querySelector('div').classList.remove('scale-100');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.getElementById('token-form').reset();
        }, 200);
    }

    // Submit Start Exam
    document.getElementById('token-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btnStart = document.getElementById('btn-start-exam');
        const originalText = btnStart.innerHTML;
        
        btnStart.disabled = true;
        btnStart.innerHTML = `<i class="fa-solid fa-circle-notch animate-spin mr-2"></i> Menyiapkan...`;

        const examId = document.getElementById('exam-id-input').value;
        const token = document.getElementById('token-input').value;

        axios.post("/api/v1/exam/start", {
            exam_id: examId,
            token: token
        })
        .then(res => {
            if (res.data.status === 'success') {
                closeTokenModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Ujian Dimulai',
                    text: 'Mempersiapkan ruang ujian...',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = `/mahasiswa/ruang-ujian/${res.data.session_id}`;
                });
            }
        })
        .catch(err => {
            btnStart.disabled = false;
            btnStart.innerHTML = originalText;
            
            const msg = err.response && err.response.data && err.response.data.message 
                ? err.response.data.message 
                : 'Terjadi kesalahan saat memulai ujian.';
            showError(msg);
        });
    });
</script>
@endsection
