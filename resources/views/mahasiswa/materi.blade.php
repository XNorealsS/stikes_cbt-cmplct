@extends('layouts.mahasiswa')

@section('title', 'Materi E-Learning - E-Learning STIKesMu')

@section('mahasiswa-content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Mahasiswa &gt; Materi Pembelajaran</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Materi Pembelajaran E-Learning
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Akses materi perkuliahan yang diunggah oleh dosen pengampu.</p>
            </div>
        </div>
    </div>

    @php
        $grouped = $materis->groupBy(function($item) {
            return $item->pertemuan_ke ?? 'Umum';
        });
    @endphp

    <div class="space-y-10">
        @forelse ($grouped as $pertemuan => $items)
        <div class="space-y-4">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest pl-2.5 border-l-4 border-primary bg-slate-50 py-1 rounded">
                @if(is_numeric($pertemuan))
                    Pertemuan / Sesi {{ $pertemuan }}
                @else
                    Materi Umum
                @endif
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($items as $materi)
                @php
                    $isViewed = $materi->views->isNotEmpty();
                @endphp
                <div class="bg-white rounded-2xl border border-gray-150 shadow-sm p-6 flex flex-col justify-between space-y-4 hover:shadow-md hover:border-gray-200 transition">
                    <div class="space-y-3">
                        <div class="flex justify-between items-start gap-2">
                            <span class="inline-block px-2.5 py-0.5 text-[9px] font-extrabold rounded-full bg-blue-50 text-primary border border-blue-100 uppercase font-mono">
                                {{ $materi->course->code }}
                            </span>
                            <div class="flex gap-1.5">
                                <span id="read-badge-{{ $materi->id }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-bold border {{ $isViewed ? 'bg-emerald-50 text-emerald-800 border-emerald-100' : 'bg-amber-50 text-amber-800 border-amber-150 animate-pulse' }}">
                                    <i class="fa-solid {{ $isViewed ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                    <span class="badge-text">{{ $isViewed ? 'Sudah Dibaca' : 'Belum Dibaca' }}</span>
                                </span>
                                <span class="text-[9px] bg-slate-50 border border-slate-200 text-slate-500 font-bold px-2 py-0.5 rounded uppercase">
                                    {{ $materi->tipe }}
                                </span>
                            </div>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900 tracking-tight leading-snug">{{ $materi->judul }}</h2>
                        <p class="text-[10px] text-gray-400">Dosen: <strong class="text-gray-700 font-semibold">{{ $materi->user->name }}</strong> | Rilis: {{ $materi->tanggal_tayang ? $materi->tanggal_tayang->format('d M Y') : 'Langsung' }}</p>
                        <p class="text-xs text-gray-650 leading-relaxed line-clamp-3">{{ $materi->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                    </div>

                    <div class="pt-4 border-t border-gray-50">
                        @if ($materi->tipe === 'file' && $materi->file_path)
                        <a href="{{ route('mahasiswa.materi.download', $materi->id) }}" onclick="markAsDownloaded({{ $materi->id }})" class="w-full bg-secondary hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs uppercase tracking-wider transition shadow-sm flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Download File</span>
                        </a>
                        @elseif ($materi->tipe === 'link' && $materi->link_url)
                        <a href="{{ route('mahasiswa.materi.open', $materi->id) }}" onclick="markAsDownloaded({{ $materi->id }})" target="_blank" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs uppercase tracking-wider transition shadow-sm flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>Buka Tautan Eksternal</span>
                        </a>
                        @else
                        <button type="button" onclick="viewTextMateri({{ $materi->id }}, '{{ addslashes($materi->judul) }}', '{{ addslashes($materi->konten) }}')" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-4 rounded-xl text-xs uppercase tracking-wider transition shadow-sm flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-book-open"></i>
                            <span>Baca Materi Tekstual</span>
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white p-8 text-center text-gray-400 rounded-2xl border border-gray-150 shadow-sm">
            <i class="fa-solid fa-book-bookmark text-4xl mb-3 text-gray-300"></i>
            <p class="text-xs font-semibold">Belum ada materi pembelajaran untuk kelas Anda.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Modal Baca Materi Tekstual -->
<div id="materi-modal" class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 border border-slate-100">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 id="materi-judul" class="text-sm font-bold text-gray-800 uppercase tracking-widest">Materi</h3>
            <button onclick="closeMateriModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[60vh] space-y-4">
            <div id="materi-konten" class="text-gray-700 text-xs whitespace-pre-line leading-relaxed"></div>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button onclick="closeMateriModal()" class="px-5 py-2 bg-slate-800 hover:bg-slate-950 text-white rounded-xl text-xs font-bold transition">Tutup</button>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function viewTextMateri(id, judul, konten) {
        document.getElementById('materi-judul').textContent = judul;
        document.getElementById('materi-konten').innerHTML = konten;
        document.getElementById('materi-modal').classList.remove('hidden');
        document.getElementById('materi-modal').classList.add('flex');
        
        axios.post(`/mahasiswa/materi/${id}/view`)
            .then(res => {
                if (res.data.success) {
                    const badge = document.getElementById(`read-badge-${id}`);
                    if (badge) {
                        badge.className = "inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-bold border bg-emerald-50 text-emerald-800 border-emerald-100";
                        const icon = badge.querySelector('i');
                        if (icon) icon.className = "fa-solid fa-eye";
                        const text = badge.querySelector('.badge-text');
                        if (text) text.textContent = "Sudah Dibaca";
                    }
                }
            });
    }

    function closeMateriModal() {
        const modal = document.getElementById('materi-modal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    function markAsDownloaded(id) {
        setTimeout(() => {
            const badge = document.getElementById(`read-badge-${id}`);
            if (badge) {
                badge.className = "inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-bold border bg-emerald-50 text-emerald-800 border-emerald-100";
                const icon = badge.querySelector('i');
                if (icon) icon.className = "fa-solid fa-eye";
                const text = badge.querySelector('.badge-text');
                if (text) text.textContent = "Sudah Dibaca";
            }
        }, 500);
    }
</script>
@endsection
@endsection
