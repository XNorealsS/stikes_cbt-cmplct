@extends('layouts.mahasiswa')

@section('title', $materi->judul . ' - E-Learning STIKesMu')

@section('mahasiswa-content')
@php
    $youtubeEmbedUrl = null;
    $youtubeThumbnail = null;
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
            $youtubeEmbedUrl = "https://www.youtube.com/embed/" . $videoId;
            $youtubeThumbnail = "https://img.youtube.com/vi/" . $videoId . "/hqdefault.jpg";
        }
    }
@endphp

<style>
    /* Custom Reading Themes */
    .theme-light {
        background-color: #ffffff !important;
        color: #1e293b !important;
        border-color: #e2e8f0 !important;
    }
    .theme-sepia {
        background-color: #fbf5eb !important;
        color: #433422 !important;
        border-color: #e8dcc4 !important;
    }
    .theme-dark {
        background-color: #18191a !important;
        color: #e4e6eb !important;
        border-color: #2f3031 !important;
    }

    /* Font Scale Classes */
    .font-size-xs { font-size: 13px !important; line-height: 1.6 !important; }
    .font-size-sm { font-size: 15px !important; line-height: 1.6 !important; }
    .font-size-base { font-size: 17px !important; line-height: 1.7 !important; }
    .font-size-lg { font-size: 20px !important; line-height: 1.8 !important; }
    .font-size-xl { font-size: 24px !important; line-height: 1.8 !important; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Mahasiswa &gt; Materi &gt; Detail</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Detail Materi Pembelajaran
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Ruang baca nyaman materi kuliah Anda.</p>
            </div>
            <div>
                <a href="{{ route('mahasiswa.materi.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 text-xs font-semibold transition rounded-sm cursor-pointer shadow-none">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    <!-- 2-Column Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Column: Content Preview (Col-span 8) -->
        <div class="lg:col-span-8 space-y-4 order-2 lg:order-1">
            
            @if ($materi->tipe === 'text')
                <!-- Placeholder / Option to Read -->
                <div class="bg-white border border-gray-150 p-12 flex flex-col items-center justify-center space-y-4 shadow-sm" id="text-placeholder">
                    <div class="h-16 w-16 bg-emerald-50 text-emerald-800 border border-emerald-100 flex items-center justify-center text-3xl">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div class="text-center space-y-1">
                        <h4 class="font-bold text-slate-800 text-sm">Materi Tekstual Tersedia</h4>
                        <p class="text-xs text-slate-400">Tekan tombol di bawah ini untuk membuka halaman baca nyaman.</p>
                    </div>
                    <button type="button" onclick="showTextContent()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary-900 text-white text-xs font-bold transition uppercase tracking-wider rounded-sm cursor-pointer shadow-sm">
                        <i class="fa-solid fa-book-open"></i> Buka & Baca Materi
                    </button>
                </div>

                <!-- Comfortable Reading Pane -->
                <div class="hidden space-y-4" id="text-content-area">
                    <!-- Reading Mode Toolbar -->
                    <div class="flex flex-wrap items-center justify-between gap-4 bg-slate-100 p-3 border border-slate-200 text-xs select-none">
                        <span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest flex items-center gap-1.5">
                            <i class="fa-solid fa-glasses text-sm text-primary"></i> Ruang Membaca Nyaman
                        </span>
                        
                        <div class="flex flex-wrap items-center gap-4">
                            <!-- Font Size Adjuster -->
                            <div class="flex items-center gap-1 bg-white p-1 border border-slate-250 rounded-sm">
                                <button type="button" onclick="changeFontSize(-1)" class="w-8 h-7 hover:bg-slate-50 text-xs font-black flex items-center justify-center cursor-pointer transition select-none" title="Perkecil Font">A-</button>
                                <div class="w-px h-4 bg-slate-200"></div>
                                <button type="button" onclick="resetFontSize()" class="px-2 h-7 hover:bg-slate-50 text-[10px] font-bold flex items-center justify-center cursor-pointer transition select-none" title="Reset Ukuran">Normal</button>
                                <div class="w-px h-4 bg-slate-200"></div>
                                <button type="button" onclick="changeFontSize(1)" class="w-8 h-7 hover:bg-slate-50 text-xs font-black flex items-center justify-center cursor-pointer transition select-none" title="Perbesar Font">A+</button>
                            </div>
                            
                            <!-- Theme Selector -->
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="setReadingTheme('light')" class="w-6 h-6 rounded-full bg-white border-2 border-slate-400 hover:scale-110 transition cursor-pointer shadow-sm" title="Tema Terang"></button>
                                <button type="button" onclick="setReadingTheme('sepia')" class="w-6 h-6 rounded-full bg-[#fbf5eb] border-2 border-[#e8dcc4] hover:scale-110 transition cursor-pointer shadow-sm" title="Tema Sepia (Hangat)"></button>
                                <button type="button" onclick="setReadingTheme('dark')" class="w-6 h-6 rounded-full bg-[#18191a] border-2 border-[#2f3031] hover:scale-110 transition cursor-pointer shadow-sm" title="Tema Gelap"></button>
                            </div>

                            <!-- Close / Collapse Button -->
                            <button type="button" onclick="hideTextContent()" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-800 text-white rounded-sm text-[10px] font-bold transition flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-eye-slash"></i> Tutup
                            </button>
                        </div>
                    </div>

                    <!-- Readable Text Container -->
                    <div class="border border-slate-200 p-1 bg-slate-100">
                        <div id="reading-content-box" class="theme-light font-size-base text-gray-800 bg-white border border-slate-200 p-8 sm:p-12 whitespace-pre-line font-serif transition-all duration-300 leading-relaxed">
                            {{ $materi->konten }}
                        </div>
                    </div>
                </div>

            @elseif ($materi->tipe === 'file' && $materi->file_path)
                <!-- PDF Embedded Preview -->
                <div class="bg-white border border-gray-150 p-2 shadow-sm space-y-2">
                    <div class="bg-slate-100 p-1 border border-slate-200">
                        <iframe src="{{ asset('storage/' . $materi->file_path) }}#toolbar=0&navpanes=0" class="w-full h-[650px] border-0 bg-white" type="application/pdf">
                            <div class="p-8 text-center text-slate-500 text-xs">
                                Browser Anda tidak mendukung preview PDF langsung. Silakan <a href="{{ route('mahasiswa.materi.download', $materi->id) }}" class="text-blue-600 hover:underline">Unduh File PDF</a> untuk membacanya.
                            </div>
                        </iframe>
                    </div>
                </div>

            @elseif ($materi->tipe === 'link' && $materi->link_url)
                <!-- Video/Link Preview Section -->
                @if ($youtubeEmbedUrl)
                    <div class="bg-white border border-gray-150 p-2 shadow-sm">
                        <div class="border border-slate-200 bg-slate-900 shadow-inner">
                            <div class="aspect-video w-full">
                                <iframe class="w-full h-full" src="{{ $youtubeEmbedUrl }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white border border-gray-150 p-8 shadow-sm text-center space-y-4">
                        <div class="h-16 w-16 bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-3xl mx-auto">
                            <i class="fa-solid fa-globe"></i>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-slate-800 text-sm">Tautan Eksternal Tersedia</h4>
                            <p class="text-xs text-slate-400">Materi ini berupa tautan web yang mengarah ke situs eksternal.</p>
                        </div>
                        <div class="text-xs font-mono text-blue-600 bg-slate-50 p-3 border border-slate-150 break-all select-all">
                            {{ $materi->link_url }}
                        </div>
                    </div>
                @endif
            @endif

        </div>

        <!-- Right Column: Detail Metadata & Notes (Col-span 4) -->
        <div class="lg:col-span-4 space-y-6 order-1 lg:order-2">
            
            <!-- Metadata Card -->
            <div class="bg-white border border-gray-150 p-6 shadow-sm space-y-4">
                
                <!-- Badge and Date -->
                <div class="flex justify-between items-center text-xs pb-3 border-b border-slate-100">
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 bg-slate-100 border border-slate-200 text-slate-700 capitalize rounded-sm">
                        @if($materi->tipe === 'file')
                            <i class="fa-solid fa-file-pdf text-red-500"></i> PDF Document
                        @elseif($materi->tipe === 'link')
                            <i class="fa-solid fa-link text-blue-500"></i> Tautan Link
                        @else
                            <i class="fa-solid fa-align-left text-gray-500"></i> Teks Bacaan
                        @endif
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium">Rilis: {{ $materi->tanggal_tayang ? $materi->tanggal_tayang->format('d M Y') : 'Langsung' }}</span>
                </div>

                <!-- Title -->
                <div class="space-y-1">
                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Judul Materi</span>
                    <h2 class="text-base font-bold text-slate-900 leading-snug">{{ $materi->judul }}</h2>
                </div>

                <div class="h-px bg-slate-100"></div>

                <!-- Specs -->
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between items-start gap-2">
                        <span class="text-slate-400 font-bold uppercase text-[9px] tracking-wider mt-0.5">Mata Kuliah:</span>
                        <div class="text-right">
                            <span class="px-1.5 py-0.5 text-[9px] font-extrabold rounded-full bg-blue-50 text-primary border border-blue-100 font-mono uppercase inline-block mb-1">
                                {{ $materi->course->code }}
                            </span>
                            <span class="block font-bold text-slate-800">{{ $materi->course->name }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center gap-2">
                        <span class="text-slate-400 font-bold uppercase text-[9px] tracking-wider">Dosen Pengampu:</span>
                        <span class="font-bold text-slate-800 text-right">{{ $materi->user->name }}</span>
                    </div>

                    <div class="flex justify-between items-center gap-2">
                        <span class="text-slate-400 font-bold uppercase text-[9px] tracking-wider">Pertemuan Ke:</span>
                        <span class="font-bold text-slate-800 text-right">
                            @if(is_numeric($materi->pertemuan_ke))
                                Sesi / Pertemuan {{ $materi->pertemuan_ke }}
                            @else
                                Umum
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Main Actions (If PDF/Link) -->
                @if ($materi->tipe === 'file' && $materi->file_path)
                    <div class="pt-3">
                        <a href="{{ route('mahasiswa.materi.download', $materi->id) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary-900 text-white text-xs font-bold transition uppercase tracking-wider rounded-sm cursor-pointer shadow-sm">
                            <i class="fa-solid fa-download"></i> Unduh File PDF
                        </a>
                    </div>
                @elseif ($materi->tipe === 'link' && $materi->link_url)
                    <div class="pt-3">
                        <a href="{{ route('mahasiswa.materi.open', $materi->id) }}" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary-900 text-white text-xs font-bold transition uppercase tracking-wider rounded-sm cursor-pointer shadow-sm">
                            <i class="fa-solid fa-external-link"></i> Buka Tautan Baru
                        </a>
                    </div>
                @endif

            </div>

            <!-- Lecturer Notes / Description Memo Card -->
            <div class="bg-amber-50/40 border-l-4 border-amber-500 p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-amber-100 pb-2">
                    <div class="flex items-center gap-2">
                        <div class="h-7 w-7 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold text-amber-800 uppercase tracking-wider">Catatan Dosen</span>
                            <p class="text-[9px] text-amber-600 font-medium">Oleh: {{ $materi->user->name }}</p>
                        </div>
                    </div>
                    <div class="text-[9px] bg-amber-100 text-amber-800 font-bold px-1.5 py-0.5 uppercase tracking-wider font-mono">
                        Note
                    </div>
                </div>
                
                <div class="text-slate-700 text-xs leading-relaxed whitespace-pre-line font-sans pl-1 italic">
                    <i class="fa-solid fa-quote-left text-amber-350 text-base mr-1.5 float-left"></i>
                    {{ $materi->deskripsi ?? 'Tidak ada catatan khusus dari dosen pengampu untuk materi ini.' }}
                </div>
            </div>

        </div>

    </div>
</div>

<script>
    // State of the font size (xs, sm, base, lg, xl)
    const fontSizes = ['font-size-xs', 'font-size-sm', 'font-size-base', 'font-size-lg', 'font-size-xl'];
    let currentSizeIndex = 2; // Default is base

    function changeFontSize(direction) {
        const box = document.getElementById('reading-content-box');
        
        // Remove current size class
        box.classList.remove(fontSizes[currentSizeIndex]);

        // Calculate new index
        currentSizeIndex += direction;
        if (currentSizeIndex < 0) currentSizeIndex = 0;
        if (currentSizeIndex >= fontSizes.length) currentSizeIndex = fontSizes.length - 1;

        // Add new size class
        box.classList.add(fontSizes[currentSizeIndex]);
    }

    function resetFontSize() {
        const box = document.getElementById('reading-content-box');
        box.classList.remove(fontSizes[currentSizeIndex]);
        currentSizeIndex = 2;
        box.classList.add(fontSizes[currentSizeIndex]);
    }

    // Set Theme Color (light, sepia, dark)
    function setReadingTheme(theme) {
        const box = document.getElementById('reading-content-box');
        
        // Clear previous theme classes
        box.classList.remove('theme-light', 'theme-sepia', 'theme-dark');

        // Add selected theme class
        if (theme === 'light') {
            box.classList.add('theme-light');
        } else if (theme === 'sepia') {
            box.classList.add('theme-sepia');
        } else if (theme === 'dark') {
            box.classList.add('theme-dark');
        }
    }

    function showTextContent() {
        document.getElementById('text-placeholder').classList.add('hidden');
        document.getElementById('text-content-area').classList.remove('hidden');
        
        // Automatically scroll to the top of the reading container
        setTimeout(() => {
            document.getElementById('text-content-area').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }

    function hideTextContent() {
        document.getElementById('text-placeholder').classList.remove('hidden');
        document.getElementById('text-content-area').classList.add('hidden');
    }
</script>
@endsection
