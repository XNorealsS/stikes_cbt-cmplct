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

        </div>
    </div>



    <!-- Running Text Announcement Banner -->
   

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

    <!-- Main Content Layout (Flat Stack) -->
    <div class="space-y-6">
        
        <!-- Latest Materials Table -->
        <div class="bg-white border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex justify-between items-center border-b border-slate-100 p-4">
                <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center">
                    <i class="fa-solid fa-book-open text-primary mr-2"></i> Materi Pembelajaran Terbaru
                </h2>
                <a href="{{ route('mahasiswa.materi.index') }}" class="text-xs text-primary hover:underline font-bold">Lihat Semua</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" style="min-width: 780px;">
                    <thead>
                        <tr class="bg-green-700 text-xs font-bold text-white uppercase tracking-wider">
                            <th class="py-3 px-4" style="min-width: 95px; width: 95px;">Kode</th>
                            <th class="py-3 px-4" style="min-width: 180px; width: 180px;">Mata Kuliah</th>
                            <th class="py-3 px-4" style="min-width: 280px; width: 280px;">Judul Materi</th>
                            <th class="py-3 px-4" style="min-width: 110px; width: 110px;">Tipe</th>
                            <th class="py-3 px-4" style="min-width: 160px; width: 160px;">Dosen</th>
                            <th class="py-3 px-4 text-right" style="min-width: 160px; width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @forelse($latestMateris as $materi)
                        @php
                            $ytThumbnail = null;
                            if ($materi->tipe === 'link' && $materi->link_url) {
                                $url = $materi->link_url;
                                $parsedUrl = parse_url($url);
                                $videoId = null;
                                if (isset($parsedUrl['host']) && (str_contains($parsedUrl['host'], 'youtube.com') || str_contains($parsedUrl['host'], 'youtu.be'))) {
                                    if (str_contains($parsedUrl['host'], 'youtu.be')) {
                                        $videoId = ltrim($parsedUrl['path'], '/');
                                    } elseif (isset($parsedUrl['query'])) {
                                        parse_str($parsedUrl['query'], $queryVars);
                                        $videoId = $queryVars['v'] ?? null;
                                    }
                                }
                                if ($videoId) {
                                    $ytThumbnail = "https://img.youtube.com/vi/" . $videoId . "/mqdefault.jpg";
                                }
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="py-4 px-6">
                                <span class="px-2 py-0.5 text-[9px] font-extrabold rounded-full bg-blue-50 text-primary border border-blue-100 font-mono uppercase">
                                    {{ $materi->course->code }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-semibold text-gray-900">{{ $materi->course->name }}</td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    @if($ytThumbnail)
                                        <a href="{{ route('mahasiswa.materi.show', $materi->id) }}" class="w-16 h-10 flex-shrink-0 border border-slate-200 overflow-hidden relative block hover:opacity-90 transition">
                                            <img src="{{ $ytThumbnail }}" alt="Thumbnail" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-black/20 flex items-center justify-center text-white text-[10px]">
                                                <i class="fa-solid fa-circle-play text-[14px] text-red-650 bg-white rounded-full"></i>
                                            </div>
                                        </a>
                                    @elseif($materi->tipe === 'file')
                                        <div class="w-16 h-10 flex-shrink-0 bg-red-50 border border-red-100 flex items-center justify-center text-red-500 text-base">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </div>
                                    @else
                                        <div class="w-16 h-10 flex-shrink-0 bg-slate-50 border border-slate-250 flex items-center justify-center text-slate-500 text-base">
                                            <i class="fa-solid fa-file-lines"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('mahasiswa.materi.show', $materi->id) }}" class="font-bold text-gray-900 leading-snug hover:text-primary transition block">
                                            {{ $materi->judul }}
                                        </a>
                                        @if($materi->deskripsi)
                                            <p class="text-xs text-gray-400 mt-0.5 line-clamp-1 font-normal">{{ $materi->deskripsi }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center gap-1 text-xs">
                                    @if($materi->tipe === 'file')
                                        <i class="fa-solid fa-file-pdf text-red-500"></i> File PDF
                                    @elseif($materi->tipe === 'link')
                                        <i class="fa-solid fa-link text-blue-500"></i> Link URL
                                    @else
                                        <i class="fa-solid fa-align-left text-gray-500"></i> Teks Bacaan
                                    @endif
                                </span>
                            </td>
                            <td class="py-4 px-6 text-gray-500 text-xs md:text-sm">{{ $materi->user->name }}</td>
                            <td class="py-4 px-6 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- Lihat Detail Button -->
                                    <a href="{{ route('mahasiswa.materi.show', $materi->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 border border-slate-300 text-slate-700 text-[11px] font-bold transition hover:bg-slate-50 cursor-pointer rounded-sm">
                                        <i class="fa-solid fa-circle-info"></i> Detail
                                    </a>

                                    <!-- Tipe-specific Button -->
                                    @if($materi->tipe === 'file')
                                        <a href="{{ route('mahasiswa.materi.download', $materi->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-primary hover:bg-primary-900 text-white text-[11px] font-bold transition-all active:scale-95 rounded-sm">
                                            <i class="fa-solid fa-download"></i> Download
                                        </a>
                                    @elseif($materi->tipe === 'link')
                                        <a href="{{ route('mahasiswa.materi.open', $materi->id) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-primary hover:bg-primary-900 text-white text-[11px] font-bold transition-all active:scale-95 rounded-sm">
                                            <i class="fa-solid fa-external-link"></i> Buka
                                        </a>
                                    @else
                                        <a href="{{ route('mahasiswa.materi.show', $materi->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-primary hover:bg-primary-900 text-white text-[11px] font-bold transition-all active:scale-95 rounded-sm">
                                            <i class="fa-solid fa-book-open"></i> Baca
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                <i class="fa-solid fa-folder-open text-2xl mb-2 text-slate-300 block"></i>
                                Belum ada materi pembelajaran yang dirilis.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upcoming Tasks Table -->
        <div class="bg-white border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex justify-between items-center border-b border-slate-100 p-4">
                <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center">
                    <i class="fa-solid fa-file-pen text-primary mr-2"></i> Tugas Mendekati Deadline
                </h2>
                <a href="{{ route('mahasiswa.tugas.index') }}" class="text-xs text-primary hover:underline font-bold">Lihat Semua</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" style="min-width: 820px;">
                    <thead>
                        <tr class="bg-green-700 text-xs font-bold text-white uppercase tracking-wider">
                            <th class="py-3 px-4" style="min-width: 95px; width: 95px;">Kode</th>
                            <th class="py-3 px-4" style="min-width: 180px; width: 180px;">Mata Kuliah</th>
                            <th class="py-3 px-4" style="min-width: 280px; width: 280px;">Judul Tugas</th>
                            <th class="py-3 px-4 text-center" style="min-width: 80px; width: 80px;">Poin</th>
                            <th class="py-3 px-4" style="min-width: 160px; width: 160px;">Dosen</th>
                            <th class="py-3 px-4" style="min-width: 150px; width: 150px;">Batas Waktu</th>
                            <th class="py-3 px-4 text-right" style="min-width: 120px; width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @forelse($upcomingTugas as $t)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="py-4 px-6">
                                <span class="px-2 py-0.5 text-[9px] font-extrabold rounded-full bg-blue-50 text-primary border border-blue-100 font-mono uppercase">
                                    {{ $t->course->code }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-semibold text-gray-900">{{ $t->course->name }}</td>
                            <td class="py-4 px-6 text-gray-700 font-semibold">{{ $t->judul }}</td>
                            <td class="py-4 px-6 text-center font-bold text-slate-700">{{ number_format($t->poin_nilai, 0) }}</td>
                            <td class="py-4 px-6 text-gray-500 text-xs md:text-sm">{{ $t->user->name }}</td>
                            <td class="py-4 px-6">
                                <span class="text-[11px] font-bold text-red-650 bg-red-50 px-2.5 py-1 rounded border border-red-150">
                                    {{ $t->deadline ? $t->deadline->format('d M Y H:i') : 'Tanpa Batas' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('mahasiswa.tugas.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary hover:bg-primary-900 text-white text-[11px] font-bold transition-all active:scale-95" style="border-radius: var(--radius-sm);">
                                    <i class="fa-solid fa-file-pen text-[9px]"></i> Kerjakan
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                <i class="fa-solid fa-file-circle-check text-2xl mb-2 text-slate-350 block"></i>
                                Bagus! Tidak ada tugas aktif yang belum dikerjakan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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
