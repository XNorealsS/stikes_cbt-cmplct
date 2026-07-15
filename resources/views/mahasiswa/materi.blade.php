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
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest pl-2.5 border-l-4 border-primary bg-slate-50 py-1">
                @if(is_numeric($pertemuan))
                    Pertemuan / Sesi {{ $pertemuan }}
                @else
                    Materi Umum
                @endif
            </h3>
            
            <div class="bg-white border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" style="min-width: 780px;">
                        <thead>
                            <tr class="bg-green-700 text-xs font-bold text-white uppercase tracking-wider">
                                <th class="py-3 px-4" style="min-width: 95px; width: 95px;">Kode</th>
                                <th class="py-3 px-4" style="min-width: 120px; width: 120px;">Status Baca</th>
                                <th class="py-3 px-4" style="min-width: 280px; width: 280px;">Judul Materi</th>
                                <th class="py-3 px-4" style="min-width: 110px; width: 110px;">Tipe</th>
                                <th class="py-3 px-4" style="min-width: 160px; width: 160px;">Dosen</th>
                                <th class="py-3 px-4 text-right" style="min-width: 165px; width: 165px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-100">
                            @foreach ($items as $materi)
                            @php
                                $isViewed = $materi->views->isNotEmpty();
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
                                <td class="py-4 px-6">
                                    <span id="read-badge-{{ $materi->id }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-bold border {{ $isViewed ? 'bg-emerald-50 text-emerald-800 border-emerald-100' : 'bg-amber-50 text-amber-800 border-amber-150 animate-pulse' }}">
                                        <i class="fa-solid {{ $isViewed ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                        <span class="badge-text">{{ $isViewed ? 'Sudah Dibaca' : 'Belum Dibaca' }}</span>
                                    </span>
                                </td>
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
                                    <span class="inline-flex items-center gap-1 text-xs capitalize">
                                        @if($materi->tipe === 'file')
                                            <i class="fa-solid fa-file-pdf text-red-500"></i> PDF
                                        @elseif($materi->tipe === 'link')
                                            <i class="fa-solid fa-link text-blue-500"></i> Link
                                        @else
                                            <i class="fa-solid fa-align-left text-gray-500"></i> Teks
                                        @endif
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-500 text-xs md:text-sm">{{ $materi->user->name }}</td>
                                <td class="py-4 px-6 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <!-- Lihat Detail Button -->
                                        <a href="{{ route('mahasiswa.materi.show', $materi->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 border border-slate-350 text-slate-700 text-[11px] font-bold transition hover:bg-slate-50 cursor-pointer rounded-sm">
                                            <i class="fa-solid fa-circle-info"></i> Detail
                                        </a>

                                        <!-- Tipe-specific Button -->
                                        @if ($materi->tipe === 'file' && $materi->file_path)
                                            <a href="{{ route('mahasiswa.materi.download', $materi->id) }}" onclick="markAsDownloaded({{ $materi->id }})" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-primary hover:bg-primary-900 text-white text-[11px] font-bold transition cursor-pointer rounded-sm">
                                                <i class="fa-solid fa-download"></i> Download
                                            </a>
                                        @elseif ($materi->tipe === 'link' && $materi->link_url)
                                            <a href="{{ route('mahasiswa.materi.open', $materi->id) }}" onclick="markAsDownloaded({{ $materi->id }})" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-primary hover:bg-primary-900 text-white text-[11px] font-bold transition cursor-pointer rounded-sm">
                                                <i class="fa-solid fa-external-link"></i> Buka Link
                                            </a>
                                        @else
                                            <a href="{{ route('mahasiswa.materi.show', $materi->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-primary hover:bg-primary-900 text-white text-[11px] font-bold transition cursor-pointer rounded-sm">
                                                <i class="fa-solid fa-book-open"></i> Baca
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white p-8 text-center text-gray-400 border border-gray-150 shadow-sm">
            <i class="fa-solid fa-book-bookmark text-4xl mb-3 text-gray-300"></i>
            <p class="text-xs font-semibold">Belum ada materi pembelajaran untuk kelas Anda.</p>
        </div>
        @endforelse
    </div>
</div>

@section('scripts')
<script>
    function markAsDownloaded(id) {
        setTimeout(() => {
            updateReadBadge(id);
        }, 500);
    }

    function updateReadBadge(id) {
        const badge = document.getElementById(`read-badge-${id}`);
        if (badge) {
            badge.className = "inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-bold border bg-emerald-50 text-emerald-800 border-emerald-100";
            const icon = badge.querySelector('i');
            if (icon) icon.className = "fa-solid fa-eye";
            const text = badge.querySelector('.badge-text');
            if (text) text.textContent = "Sudah Dibaca";
        }
    }
</script>
@endsection
@endsection
