@extends('layouts.app')

@section('title', 'Ruang Ujian - CBTMu')

@section('no-nav', true)

@section('styles')
<style>
    /* Prevent text copying and context menus to deter cheating */
    .unselectable {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    
    /* Option Label custom transition */
    .option-label {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Modal open scroll lock */
    .overflow-hidden {
        overflow: hidden;
    }
</style>
@endsection

@section('content')
<!-- Top Header Banner (Fully Responsive) -->
<header class="bg-white border-b border-neutral-300 py-2 sm:py-3 px-4 sm:px-6 sticky top-0 z-30 no-print select-none shadow-sm min-h-16 h-auto flex items-center justify-between gap-2">
    <div class="flex items-center space-x-2 sm:space-x-3 min-w-0">
        <img src="https://siakad.stikeslhokseumawe.ac.id/logo.png" alt="Logo STIKesMu" class="h-7 w-7 sm:h-8 sm:w-8 object-contain flex-shrink-0">
        <div class="min-w-0">
            <span class="font-bold text-xs sm:text-sm text-[#0F172A] block font-heading truncate max-w-[130px] sm:max-w-none">{{ $exam->title }}</span>
            <span class="text-[9px] sm:text-[10px] text-[#64748B] block -mt-0.5 truncate max-w-[130px] sm:max-w-none">Mata Uji: {{ $exam->course->name }} ({{ $exam->course->code }})</span>
        </div>
    </div>
    
    <!-- Sisa Waktu & Digital Clock -->
    <div class="flex items-center space-x-2 sm:space-x-4 flex-shrink-0">
        <div class="hidden md:flex font-mono text-[10px] font-bold text-[#64748B] bg-[#F1F5F9] border border-[#CBD5E1] px-2.5 py-1 rounded-sm items-center space-x-1.5">
            <i class="fa-regular fa-user text-slate-400"></i>
            <span>{{ auth()->user()->name }}</span>
        </div>
        
        <div class="flex items-center space-x-1.5 sm:space-x-2">
            <i class="fa-regular fa-clock text-[#64748B] text-xs sm:text-sm"></i>
            <span id="countdown-timer-header" class="font-mono font-bold text-lg sm:text-2xl text-[#0F172A] tracking-wider">00:00:00</span>
        </div>
    </div>
</header>

<!-- Main Workspace -->
<div class="max-w-7xl mx-auto px-4 py-6 flex-grow w-full unselectable" oncontextmenu="return false;">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        
        <!-- Left Side: Card Question (Spans 3 cols on desktop) -->
        <div class="lg:col-span-3 space-y-6">
            
            <!-- White Floating Exam Card (Flat Style) -->
            <div class="bg-white border border-[#CBD5E1] shadow-sm overflow-hidden min-h-[60vh] flex flex-col justify-between">
                
                <!-- Card Header -->
                <div class="px-4 sm:px-6 py-3 border-b border-[#CBD5E1] flex justify-between items-center gap-2 select-none bg-[#F8FAFC]">
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold text-[#64748B] uppercase tracking-wider">NOMOR:</span>
                        <span id="current-question-badge" class="bg-[#14532D] text-white font-extrabold px-3 py-1.5 rounded-sm text-xs sm:text-sm font-mono shadow-sm">
                            1
                        </span>
                    </div>

                    <!-- Font Adjuster Controls & Mobile Question Menu Button -->
                    <div class="flex items-center space-x-2.5">
                        <!-- Font Adjuster Buttons -->
                        <div class="flex items-center space-x-1">
                            <button type="button" onclick="adjustFontSize(-1)" class="w-8 h-8 border border-[#CBD5E1] text-[#64748B] flex items-center justify-center hover:bg-[#F1F5F9] transition font-black cursor-pointer bg-white rounded-sm" title="Perkecil Ukuran Soal">
                                <i class="fa-solid fa-minus text-[10px]"></i>
                            </button>
                            <button type="button" onclick="adjustFontSize(1)" class="w-8 h-8 border border-[#CBD5E1] text-[#64748B] flex items-center justify-center hover:bg-[#F1F5F9] transition font-black cursor-pointer bg-white rounded-sm" title="Perbesar Ukuran Soal">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                            </button>
                        </div>

                        <!-- Mobile "Soal" Toggle Button with Stack Icon (Only Visible on Mobile/Tablet) -->
                        <button type="button" onclick="toggleMobileQuestionGrid(true)" class="lg:hidden h-8 px-3 border border-[#14532D] text-[#14532D] hover:bg-[#14532D] hover:text-white transition rounded-sm text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-sm">
                            <i class="fa-solid fa-layer-group text-[11px]"></i>
                            <span>Soal</span>
                        </button>
                    </div>
                </div>

                <!-- Question Content Container -->
                <div class="p-4 sm:p-8 flex-grow flex flex-col justify-center">
                    <div class="bg-white min-h-[30vh] flex flex-col justify-between">
                        
                        @foreach ($answers as $index => $ans)
                        @php
                            $q = $ans->question;
                        @endphp
                        <!-- Single Question Item -->
                        <div id="question-card-{{ $index + 1 }}" class="question-card {{ $index === 0 ? '' : 'hidden' }} space-y-6" data-question-id="{{ $q->id }}" data-ans-id="{{ $ans->id }}">
                            
                            <!-- Question Text -->
                            <div class="question-text-content text-base font-medium text-[#334155] leading-relaxed flex items-start">
                                <span class="text-[#14532D] font-extrabold mr-2 select-none">{{ $index + 1 }}.</span>
                                <span class="flex-1 whitespace-pre-wrap">{!! nl2br(e($q->question_text)) !!}</span>
                            </div>

                            <!-- Optional Image -->
                            @if ($q->question_image)
                            <div class="my-4 max-w-lg">
                                <img src="{{ asset('storage/' . $q->question_image) }}" alt="Gambar Soal" class="rounded-sm border border-neutral-300 max-h-60 object-contain shadow-sm">
                            </div>
                            @endif

                            <!-- Options List based on Question Type (Comfortable padding for mobile tap targets) -->
                            @if ($q->question_type === 'pg' || $q->question_type === 'benar_salah')
                            <div class="space-y-3 mt-6">
                                @foreach (['A' => $q->option_a, 'B' => $q->option_b, 'C' => $q->option_c, 'D' => $q->option_d, 'E' => $q->option_e] as $opt => $text)
                                @if ($q->question_type === 'benar_salah' && !in_array($opt, ['A', 'B'])) @continue @endif
                                @php
                                    $isSelected = ($ans->selected_option === $opt);
                                @endphp
                                <label class="option-label flex items-start space-x-4 py-4 px-4.5 border-2 {{ $isSelected ? 'bg-[#E6F4EA] border-[#1C6B3A] font-semibold text-[#0B3D24] shadow-sm' : 'border-[#CBD5E1] hover:bg-[#F3FAF5] text-[#334155]' }} rounded-sm cursor-pointer transition relative">
                                    <input type="radio" 
                                           name="q-{{ $q->id }}" 
                                           value="{{ $opt }}" 
                                           {{ $isSelected ? 'checked' : '' }} 
                                           onchange="saveStudentAnswer({{ $ans->id }}, {{ $q->id }}, '{{ $opt }}')"
                                           class="hidden">
                                    
                                    <!-- Badge Circle (Optimized for finger touch targeting on mobile) -->
                                    <span class="option-badge flex h-9 w-9 sm:h-8 sm:w-8 items-center justify-center rounded-full text-xs font-bold transition duration-150 select-none flex-shrink-0 mt-0.5 {{ $isSelected ? 'bg-[#14532D] text-white shadow-md scale-105' : 'bg-[#F1F5F9] text-[#64748B] border border-[#CBD5E1]' }}">
                                        {{ $opt }}
                                    </span>
                                    
                                    <!-- Option text -->
                                    <span class="option-text-content text-sm text-slate-700 flex-1 leading-relaxed select-text mt-1">{{ $text }}</span>
                                </label>
                                @endforeach
                            </div>
                            @elseif ($q->question_type === 'pg_kompleks')
                            <div class="space-y-3 mt-6">
                                @foreach (['A' => $q->option_a, 'B' => $q->option_b, 'C' => $q->option_c, 'D' => $q->option_d, 'E' => $q->option_e] as $opt => $text)
                                @php
                                    $isSelected = in_array($opt, explode(',', $ans->selected_option ?? ''));
                                @endphp
                                <label class="option-label flex items-start space-x-4 py-4 px-4.5 border-2 {{ $isSelected ? 'bg-[#E6F4EA] border-[#1C6B3A] font-semibold text-[#0B3D24] shadow-sm' : 'border-[#CBD5E1] hover:bg-[#F3FAF5] text-[#334155]' }} rounded-sm cursor-pointer transition relative">
                                    <input type="checkbox" 
                                           name="q-{{ $q->id }}[]" 
                                           value="{{ $opt }}" 
                                           {{ $isSelected ? 'checked' : '' }} 
                                           onchange="saveStudentAnswerKompleks({{ $ans->id }}, {{ $q->id }})"
                                           class="hidden">
                                    
                                        <!-- Badge Circle -->
                                        <span class="option-badge flex h-9 w-9 sm:h-8 sm:w-8 items-center justify-center rounded-full text-xs font-bold transition duration-150 select-none flex-shrink-0 mt-0.5 {{ $isSelected ? 'bg-[#14532D] text-white shadow-md scale-105' : 'bg-[#F1F5F9] text-[#64748B] border border-[#CBD5E1]' }}">
                                            {{ $opt }}
                                        </span>
                                    
                                    <!-- Option text -->
                                    <span class="option-text-content text-sm text-slate-700 flex-1 leading-relaxed select-text mt-1">{{ $text }}</span>
                                </label>
                                @endforeach
                            </div>
                            @elseif ($q->question_type === 'isian')
                            <div class="mt-6">
                                <label class="block text-xs font-bold text-[#64748B] uppercase tracking-widest mb-2">Jawaban Singkat:</label>
                                <input type="text" 
                                       id="text-ans-{{ $q->id }}"
                                       value="{{ $ans->answer_text }}" 
                                       onchange="saveStudentAnswerText({{ $ans->id }}, {{ $q->id }})"
                                       placeholder="Ketik jawaban singkat Anda di sini..."
                                       class="w-full border border-[#CBD5E1] rounded-sm px-4 py-3 focus:outline-none focus:ring-1 focus:ring-[#14532D] text-sm transition">
                            </div>
                            @elseif ($q->question_type === 'essai')
                            <div class="mt-6">
                                <label class="block text-xs font-bold text-[#64748B] uppercase tracking-widest mb-2">Jawaban Essai / Penjelasan:</label>
                                <textarea id="text-ans-{{ $q->id }}"
                                          rows="6"
                                          onchange="saveStudentAnswerText({{ $ans->id }}, {{ $q->id }})"
                                          placeholder="Tuliskan penjelasan lengkap Anda di sini..."
                                          class="w-full border border-[#CBD5E1] rounded-sm px-4 py-3 focus:outline-none focus:ring-1 focus:ring-[#14532D] text-sm transition">{{ $ans->answer_text }}</textarea>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Navigation Footer (Larger buttons on mobile for easy clicking) -->
                <div class="px-3 sm:px-6 py-4 bg-[#F8FAFC] border-t border-[#CBD5E1] flex flex-row justify-between items-center gap-2 select-none">
                    <!-- Back Button -->
                    <button type="button" id="btn-prev" onclick="prevQuestion()" class="h-12 sm:h-10 bg-white border border-[#CBD5E1] hover:bg-[#F8FAFC] text-slate-700 font-bold px-3 sm:px-5 rounded-sm text-xs sm:text-sm transition flex items-center justify-center space-x-1.5 shadow-sm disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer whitespace-nowrap">
                        <i class="fa-solid fa-arrow-left text-slate-500"></i>
                        <span id="btn-prev-text"><span class="hidden sm:inline">Sebelumnya</span><span class="sm:hidden">Sebelum</span></span>
                    </button>

                    <!-- Doubtful Button -->
                    <button type="button" id="btn-doubtful" onclick="toggleDoubtful()" class="h-12 sm:h-10 bg-yellow-500 hover:bg-yellow-600 text-white font-bold px-3 sm:px-5 rounded-sm text-xs sm:text-sm transition flex items-center justify-center space-x-1.5 shadow-sm border border-yellow-600 cursor-pointer whitespace-nowrap">
                        <i class="fa-regular fa-square-minus"></i>
                        <span id="doubtful-btn-text"><span class="hidden sm:inline">Ragu-Ragu</span><span class="sm:hidden">Ragu</span></span>
                    </button>

                    <!-- Next Button -->
                    <button type="button" id="btn-next" onclick="nextQuestion()" class="h-12 sm:h-10 bg-[#14532D] hover:bg-[#0B3D24] text-white font-bold px-3 sm:px-5 rounded-sm text-xs sm:text-sm transition flex items-center justify-center space-x-1.5 shadow-sm cursor-pointer whitespace-nowrap">
                        <span id="btn-next-text"><span class="hidden sm:inline">Berikutnya</span><span class="sm:hidden">Lanjut</span></span>
                        <i id="btn-next-icon" class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>

                <!-- Keyboard shortcuts hint in footer -->
                <div class="hidden sm:flex justify-center items-center space-x-6 py-2.5 px-6 border-t border-neutral-200 text-[11px] text-[#64748B] select-none bg-[#F8FAFC]">
                    <div class="flex items-center space-x-1.5">
                        <kbd class="px-1.5 py-0.5 border border-neutral-300 bg-white rounded-sm font-mono shadow-sm">←</kbd>
                        <kbd class="px-1.5 py-0.5 border border-neutral-300 bg-white rounded-sm font-mono shadow-sm">→</kbd>
                        <span>Navigasi Soal</span>
                    </div>
                    <div class="flex items-center space-x-1.5">
                        <kbd class="px-1.5 py-0.5 border border-neutral-300 bg-white rounded-sm font-mono shadow-sm">A</kbd>
                        <span>-</span>
                        <kbd class="px-1.5 py-0.5 border border-neutral-300 bg-white rounded-sm font-mono shadow-sm">E</kbd>
                        <span>Pilih Opsi</span>
                    </div>
                    <div class="flex items-center space-x-1.5">
                        <kbd class="px-1.5 py-0.5 border border-neutral-300 bg-white rounded-sm font-mono shadow-sm">R</kbd>
                        <span>Tandai Ragu</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right Side: Desktop Question Navigation (Spans 1 col on desktop, hidden on mobile) -->
        <div class="hidden lg:block lg:col-span-1 bg-white p-6 border border-[#CBD5E1] shadow-sm sticky top-24">
            <div class="flex items-center space-x-2 text-slate-800 border-b border-[#CBD5E1] pb-3 mb-4 select-none">
                <i class="fa-solid fa-table-cells text-[#14532D]"></i>
                <h3 class="text-xs font-extrabold uppercase tracking-wider font-heading">Navigasi Soal</h3>
            </div>
            
            <!-- Numbers Grid (Centered and formatted properly to avoid mobile stretching) -->
            <div class="grid grid-cols-5 gap-3 justify-items-center items-center max-h-[45vh] lg:max-h-[55vh] overflow-y-auto pr-1">
                @foreach ($answers as $index => $ans)
                @php
                    $bgColor = 'bg-white border border-[#CBD5E1] text-[#334155] hover:bg-slate-50';
                    if ($ans->selected_option !== null || $ans->answer_text !== null) {
                        $bgColor = $ans->is_doubtful 
                            ? 'bg-yellow-500 text-white border border-yellow-600 hover:bg-yellow-600 shadow-sm' 
                            : 'bg-[#14532D] text-white border border-[#14532D] shadow-sm';
                    }
                @endphp
                <button type="button" 
                        id="desktop-grid-btn-{{ $index + 1 }}" 
                        onclick="jumpToQuestion({{ $index + 1 }});" 
                        class="grid-number-btn w-10 h-10 flex-shrink-0 rounded-sm text-xs font-bold font-mono transition duration-150 flex items-center justify-center relative {{ $bgColor }} {{ $index === 0 ? 'ring-2 ring-offset-2 ring-[#14532D]' : '' }}">
                    <span class="block text-xs font-black">{{ $index + 1 }}</span>
                </button>
                @endforeach
            </div>
            
            <!-- Legend (Responsive Grid) -->
            <div class="mt-6 border-t border-[#CBD5E1] pt-4 grid grid-cols-3 gap-2 text-[9px] font-bold uppercase text-slate-505 tracking-wider select-none text-center">
                <div class="flex items-center space-x-1 justify-center bg-[#F3FAF5] py-1.5 border border-neutral-100 rounded-sm">
                    <span class="inline-block h-2.5 w-2.5 rounded-sm bg-[#14532D]"></span>
                    <span class="text-[#14532d]">Terjawab</span>
                </div>
                <div class="flex items-center space-x-1 justify-center bg-yellow-50 py-1.5 border border-neutral-100 rounded-sm">
                    <span class="inline-block h-2.5 w-2.5 rounded-sm bg-yellow-500"></span>
                    <span class="text-yellow-700">Ragu</span>
                </div>
                <div class="flex items-center space-x-1 justify-center bg-white py-1.5 border border-neutral-350 rounded-sm">
                    <span class="inline-block h-2.5 w-2.5 rounded-sm bg-white border border-neutral-300"></span>
                    <span class="text-neutral-500">Belum Isi</span>
                </div>
            </div>
            
            <!-- Submit Button (Large touch target) -->
            <div class="mt-6">
                <button type="button" onclick="confirmSubmitExam()" class="w-full h-12 bg-[#DC2626] hover:bg-[#B91C1C] text-white font-bold py-2.5 px-4 rounded-sm text-xs uppercase tracking-wider transition shadow-sm flex items-center justify-center space-x-2 cursor-pointer">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Selesaikan Ujian</span>
                </button>
            </div>
        </div>
        
    </div>
</div>

<!-- Question Grid Modal (Clean and Responsive Modal Popup on Mobile/Tablet) -->
<div id="mobile-question-grid-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <!-- Backdrop Overlay -->
        <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" onclick="toggleMobileQuestionGrid(false)"></div>

        <!-- Modal Card Content -->
        <div class="inline-block bg-white rounded-sm text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-sm sm:max-w-md border border-[#CBD5E1] z-10">
            <!-- Header -->
            <div class="bg-neutral-50 px-5 py-4 border-b border-[#CBD5E1] flex justify-between items-center select-none">
                <div class="flex items-center space-x-2 text-slate-800">
                    <i class="fa-solid fa-table-cells text-[#14532D]"></i>
                    <h3 class="text-xs font-extrabold uppercase tracking-wider font-heading" id="modal-title">Daftar Soal Ujian</h3>
                </div>
                <button type="button" onclick="toggleMobileQuestionGrid(false)" class="text-slate-400 hover:text-slate-650 transition cursor-pointer bg-transparent border-none">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-5 space-y-4">
                <!-- Centered responsive numbers grid for easy clicking (With scroll capability for 40+ questions) -->
                <div class="grid grid-cols-5 gap-3 justify-items-center items-center max-h-[45vh] overflow-y-auto pr-1">
                    @foreach ($answers as $index => $ans)
                    @php
                        $bgColor = 'bg-white border border-[#CBD5E1] text-[#334155] hover:bg-slate-50';
                        if ($ans->selected_option !== null || $ans->answer_text !== null) {
                            $bgColor = $ans->is_doubtful 
                                ? 'bg-yellow-500 text-white border border-yellow-600 hover:bg-yellow-600 shadow-sm' 
                                : 'bg-[#14532D] text-white border border-[#14532D] shadow-sm';
                        }
                    @endphp
                    <button type="button" 
                            id="grid-btn-{{ $index + 1 }}" 
                            onclick="jumpToQuestion({{ $index + 1 }}); toggleMobileQuestionGrid(false);" 
                            class="grid-number-btn w-10 h-10 flex-shrink-0 rounded-sm text-xs font-bold font-mono transition duration-150 flex items-center justify-center relative {{ $bgColor }} {{ $index === 0 ? 'ring-2 ring-offset-2 ring-[#14532D]' : '' }}">
                        <span class="block text-xs font-black">{{ $index + 1 }}</span>
                    </button>
                    @endforeach
                </div>

                <!-- Legend -->
                <div class="mt-4 border-t border-[#CBD5E1] pt-4 grid grid-cols-3 gap-2 text-[9px] font-bold uppercase text-slate-505 tracking-wider select-none text-center">
                    <div class="flex items-center space-x-1 justify-center bg-[#F3FAF5] py-1.5 border border-neutral-100 rounded-sm">
                        <span class="inline-block h-2.5 w-2.5 rounded-sm bg-[#14532D]"></span>
                        <span class="text-[#14532d]">Terjawab</span>
                    </div>
                    <div class="flex items-center space-x-1 justify-center bg-yellow-50 py-1.5 border border-neutral-100 rounded-sm">
                        <span class="inline-block h-2.5 w-2.5 rounded-sm bg-yellow-500"></span>
                        <span class="text-yellow-700">Ragu</span>
                    </div>
                    <div class="flex items-center space-x-1 justify-center bg-white py-1.5 border border-neutral-350 rounded-sm">
                        <span class="inline-block h-2.5 w-2.5 rounded-sm bg-white border border-neutral-300"></span>
                        <span class="text-neutral-500">Belum Isi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const studentExamId = {{ $studentExam->id }};
    const examId = {{ $exam->id }};
    const totalQuestions = {{ count($answers) }};
    let currentIdx = 1; 
    let remainingSeconds = Math.floor({{ $remainingSeconds }});
    let isSubmitting = false;
    let isResetting = false;

    // Set page unload indicators
    window.addEventListener('beforeunload', (e) => {
        if (!isSubmitting && !isResetting) {
            e.preventDefault();
            e.returnValue = 'Apakah Anda yakin ingin meninggalkan halaman ujian? Sesi Anda akan ditangguhkan!';
        }
    });

    // Reset session and kick student out on cheating detection
    function resetExamSessionAndKickOut(triggerType) {
        if (isSubmitting || isResetting) return;
        isResetting = true;
        
        axios.post('/api/v1/exam/reset-session', {
            student_exam_id: studentExamId,
            reason: triggerType
        })
        .then(res => {
            Swal.fire({
                icon: 'warning',
                title: 'Koneksi Terputus / Pelanggaran Terdeteksi',
                text: 'Sesi ujian Anda telah ditangguhkan secara aman karena Anda beralih halaman atau tab browser. Silakan minta proktor/dosen untuk me-reset status Anda.',
                allowOutsideClick: false,
                confirmButtonText: 'Kembali ke Dashboard',
                confirmButtonColor: '#DC2626'
            }).then(() => {
                window.location.href = '/mahasiswa';
            });
        })
        .catch(err => {
            console.error("Failed to suspend exam session:", err);
            window.location.href = '/mahasiswa';
        });
    }

    // Visibility change and focus loss listeners
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            resetExamSessionAndKickOut('visibilitychange');
        }
    });

    window.addEventListener('blur', () => {
        resetExamSessionAndKickOut('blur');
    });

    // Beacon API for unload/close tab detection
    window.addEventListener('pagehide', (e) => {
        if (isSubmitting || isResetting) return;
        
        const url = '/api/v1/exam/reset-session';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const data = new FormData();
        data.append('student_exam_id', studentExamId);
        data.append('_token', csrfToken);
        
        navigator.sendBeacon(url, data);
    });

    // Font size controls
    let currentFontSize = 16;
    function adjustFontSize(delta) {
        currentFontSize = Math.max(12, Math.min(24, currentFontSize + delta));
        document.querySelectorAll('.question-text-content, .option-text-content').forEach(el => {
            el.style.fontSize = currentFontSize + 'px';
        });
    }

    // Toggle Mobile Question Grid Modal
    function toggleMobileQuestionGrid(show) {
        const modal = document.getElementById('mobile-question-grid-modal');
        if (modal) {
            if (show) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            } else {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }
    }
    
    // Store doubts status map in JS
    const questionsStatus = {
        @foreach ($answers as $index => $ans)
            {{ $index + 1 }}: {
                ansId: {{ $ans->id }},
                questionId: {{ $ans->question_id }},
                hasAnswer: {{ ($ans->selected_option !== null || $ans->answer_text !== null) ? 'true' : 'false' }},
                selectedOption: '{{ $ans->selected_option }}',
                isDoubtful: {{ $ans->is_doubtful ? 'true' : 'false' }}
            },
        @endforeach
    };

    // Initialize UI on load
    updateNavigationButtons();
    startTimer();
    syncTimerWithServer(); // background sync

    // Keyboard shortcuts keydown listener
    document.addEventListener('keydown', function(e) {
        if (isSubmitting || isResetting) return;
        
        const activeEl = document.activeElement;
        if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA')) {
            return;
        }

        const key = e.key.toUpperCase();
        if (key === 'ARROWLEFT') {
            prevQuestion();
        } else if (key === 'ARROWRIGHT') {
            nextQuestion();
        } else if (key === 'R') {
            toggleDoubtful();
        } else if (['A', 'B', 'C', 'D', 'E'].includes(key)) {
            const currentCard = document.getElementById('question-card-' + currentIdx);
            if (currentCard) {
                const radioInput = currentCard.querySelector('input[type="radio"][value="' + key + '"]');
                if (radioInput) {
                    radioInput.checked = true;
                    radioInput.dispatchEvent(new Event('change'));
                }
                const checkboxInput = currentCard.querySelector('input[type="checkbox"][value="' + key + '"]');
                if (checkboxInput) {
                    checkboxInput.checked = !checkboxInput.checked;
                    checkboxInput.dispatchEvent(new Event('change'));
                }
            }
        }
    });

    // Navigation functions
    function updateNavigationButtons() {
        // Prev button disable state
        document.getElementById('btn-prev').disabled = (currentIdx === 1);
        document.getElementById('btn-prev').classList.toggle('opacity-40', currentIdx === 1);
        document.getElementById('btn-prev').classList.toggle('cursor-not-allowed', currentIdx === 1);

        // Next button text / icon safely
        const nextText = document.getElementById('btn-next-text');
        const nextIcon = document.getElementById('btn-next-icon');
        if (currentIdx === totalQuestions) {
            if (nextText) nextText.innerHTML = '<span class="hidden sm:inline">Selesai</span><span class="sm:hidden">Selesai</span>';
            if (nextIcon) nextIcon.className = 'fa-solid fa-circle-check';
        } else {
            if (nextText) nextText.innerHTML = '<span class="hidden sm:inline">Berikutnya</span><span class="sm:hidden">Lanjut</span>';
            if (nextIcon) nextIcon.className = 'fa-solid fa-arrow-right';
        }

        // Update current indicator & current-question-badge
        const badgeEl = document.getElementById('current-question-badge');
        if (badgeEl) badgeEl.textContent = currentIdx;

        // Highlight active grid number
        document.querySelectorAll('.grid-number-btn').forEach(btn => {
            btn.classList.remove('ring-2', 'ring-offset-2', 'ring-[#14532D]');
        });
        const activeGridBtn = document.getElementById(`grid-btn-${currentIdx}`);
        const activeDesktopGridBtn = document.getElementById(`desktop-grid-btn-${currentIdx}`);
        if (activeGridBtn) activeGridBtn.classList.add('ring-2', 'ring-offset-2', 'ring-[#14532D]');
        if (activeDesktopGridBtn) activeDesktopGridBtn.classList.add('ring-2', 'ring-offset-2', 'ring-[#14532D]');

        // Update Doubtful state button styling
        const status = questionsStatus[currentIdx];
        const btnDoubtful = document.getElementById('btn-doubtful');
        const doubtfulTextSpan = document.getElementById('doubtful-btn-text');
        if (status.isDoubtful) {
            btnDoubtful.className = 'h-12 sm:h-10 bg-yellow-500 hover:bg-yellow-600 text-white font-bold px-3 sm:px-5 rounded-sm text-xs sm:text-sm transition flex items-center justify-center space-x-1.5 shadow-sm border border-yellow-600 cursor-pointer whitespace-nowrap';
            if (doubtfulTextSpan) doubtfulTextSpan.innerHTML = '<span class="hidden sm:inline">Ragu-Ragu</span><span class="sm:hidden">Ragu</span>';
        } else {
            btnDoubtful.className = 'h-12 sm:h-10 bg-white hover:bg-slate-50 text-slate-700 font-bold px-3 sm:px-5 rounded-sm border border-slate-350 text-xs sm:text-sm transition flex items-center justify-center space-x-1.5 shadow-sm cursor-pointer whitespace-nowrap';
            if (doubtfulTextSpan) doubtfulTextSpan.innerHTML = '<span class="hidden sm:inline">Ragu-Ragu</span><span class="sm:hidden">Ragu</span>';
        }
    }

    function prevQuestion() {
        if (currentIdx > 1) {
            hideQuestion(currentIdx);
            currentIdx--;
            showQuestion(currentIdx);
            updateNavigationButtons();
            
            // Scroll smoothly to question top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function nextQuestion() {
        if (currentIdx < totalQuestions) {
            hideQuestion(currentIdx);
            currentIdx++;
            showQuestion(currentIdx);
            updateNavigationButtons();
            
            // Scroll smoothly to question top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            confirmSubmitExam();
        }
    }

    function jumpToQuestion(idx) {
        hideQuestion(currentIdx);
        currentIdx = idx;
        showQuestion(currentIdx);
        updateNavigationButtons();
        
        // Scroll smoothly to question top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Colors updates helper for both mobile grid and desktop grid panels
    function updateGridButtonColor(idx) {
        const status = questionsStatus[idx];
        const gridBtn = document.getElementById(`grid-btn-${idx}`);
        const desktopGridBtn = document.getElementById(`desktop-grid-btn-${idx}`);

        const updateBtn = (btn) => {
            if (!btn) return;
            btn.className = 'grid-number-btn w-10 h-10 flex-shrink-0 rounded-sm text-xs font-bold font-mono transition duration-150 flex items-center justify-center relative';

            if (status.hasAnswer) {
                if (status.isDoubtful) {
                    btn.classList.add('bg-yellow-500', 'text-white', 'border', 'border-yellow-600', 'hover:bg-yellow-600', 'shadow-sm');
                } else {
                    btn.classList.add('bg-[#14532D]', 'text-white', 'border', 'border-[#14532D]', 'hover:bg-[#0B3D24]', 'shadow-sm');
                }
            } else {
                btn.classList.add('bg-white', 'text-[#334155]', 'border', 'border-[#CBD5E1]', 'hover:bg-slate-50');
            }

            if (idx === currentIdx) {
                btn.classList.add('ring-2', 'ring-offset-2', 'ring-[#14532D]');
            }
        };

        updateBtn(gridBtn);
        updateBtn(desktopGridBtn);
    }

    function showQuestion(idx) {
        const el = document.getElementById('question-card-' + idx);
        if (el) el.classList.remove('hidden');
    }

    // Adjust font size on load if saved/default
    adjustFontSize(0);

    function hideQuestion(idx) {
        const el = document.getElementById('question-card-' + idx);
        if (el) el.classList.add('hidden');
    }

    // Save Answer (AJAX call to /api/v1/exam/save-answer)
    function saveStudentAnswer(ansId, questionId, option) {
        const status = questionsStatus[currentIdx];
        status.hasAnswer = true;
        status.selectedOption = option;

        // Visual feedback immediately
        updateGridButtonColor(currentIdx);

        // Highlight selected label container and badges
        const currentCard = document.getElementById('question-card-' + currentIdx);
        if (currentCard) {
            currentCard.querySelectorAll('.option-label').forEach(lbl => {
                lbl.className = 'option-label flex items-start space-x-4 py-4 px-4.5 border-2 border-[#CBD5E1] hover:bg-[#F3FAF5] text-[#334155] rounded-sm cursor-pointer transition relative';
                const badge = lbl.querySelector('.option-badge');
                if (badge) {
                    badge.className = 'option-badge flex h-9 w-9 sm:h-8 sm:w-8 items-center justify-center rounded-full text-xs font-bold transition duration-150 select-none bg-[#F1F5F9] text-[#64748B] border border-[#CBD5E1] flex-shrink-0 mt-0.5';
                }
            });
            // Find checked option label
            const checkedInput = currentCard.querySelector('input[value="' + option + '"]');
            if (checkedInput) {
                const label = checkedInput.closest('.option-label');
                if (label) {
                    label.className = 'option-label flex items-start space-x-4 py-4 px-4.5 border-2 bg-[#E6F4EA] border-[#1C6B3A] font-semibold text-[#0B3D24] rounded-sm cursor-pointer transition relative shadow-sm';
                    const badge = label.querySelector('.option-badge');
                    if (badge) {
                        badge.className = 'option-badge flex h-9 w-9 sm:h-8 sm:w-8 items-center justify-center rounded-full text-xs font-bold transition duration-150 select-none bg-[#14532D] text-white shadow-md scale-105 flex-shrink-0 mt-0.5';
                    }
                }
            }
        }

        axios.post('/api/v1/exam/save-answer', {
            student_exam_id: studentExamId,
            question_id: questionId,
            option_id: option,
            is_doubtful: status.isDoubtful
        })
        .then(res => {
            console.log("Answer saved:", res.data.saved_at);
        })
        .catch(err => {
            console.error("Failed to save answer:", err);
        });
    }

    // Save Text / Essay Answer
    function saveStudentAnswerText(ansId, questionId) {
        const inputVal = document.getElementById('text-ans-' + questionId).value;
        const status = questionsStatus[currentIdx];
        status.hasAnswer = (inputVal.trim() !== "");
        status.selectedOption = null;

        updateGridButtonColor(currentIdx);

        axios.post('/api/v1/exam/save-answer', {
            student_exam_id: studentExamId,
            question_id: questionId,
            answer_text: inputVal,
            is_doubtful: status.isDoubtful
        })
        .then(res => {
            console.log("text answer saved:", res.data.saved_at);
        })
        .catch(err => {
            console.error("Failed to save text answer:", err);
        });
    }

    // Save Complex Multiple Choice (PG Kompleks)
    function saveStudentAnswerKompleks(ansId, questionId) {
        const currentCard = document.getElementById('question-card-' + currentIdx);
        if (!currentCard) return;

        const checkedBoxes = currentCard.querySelectorAll('input[type="checkbox"]:checked');
        
        let selectedOptions = [];
        checkedBoxes.forEach(cb => {
            selectedOptions.push(cb.value);
        });
        
        const optionStr = selectedOptions.sort().join(',');
        const status = questionsStatus[currentIdx];
        status.hasAnswer = (selectedOptions.length > 0);
        status.selectedOption = optionStr;

        updateGridButtonColor(currentIdx);

        // Update CSS labels
        currentCard.querySelectorAll('.option-label').forEach(lbl => {
            const cb = lbl.querySelector('input[type="checkbox"]');
            const badge = lbl.querySelector('.option-badge');
            if (cb && cb.checked) {
                lbl.className = 'option-label flex items-start space-x-4 py-4 px-4.5 border-2 bg-[#E6F4EA] border-[#1C6B3A] font-semibold text-[#0B3D24] rounded-sm cursor-pointer transition relative shadow-sm';
                if (badge) badge.className = 'option-badge flex h-9 w-9 sm:h-8 sm:w-8 items-center justify-center rounded-full text-xs font-bold transition duration-150 select-none bg-[#14532D] text-white shadow-md scale-105 flex-shrink-0 mt-0.5';
            } else {
                lbl.className = 'option-label flex items-start space-x-4 py-4 px-4.5 border-2 border-[#CBD5E1] hover:bg-[#F3FAF5] text-[#334155] rounded-sm cursor-pointer transition relative';
                if (badge) badge.className = 'option-badge flex h-9 w-9 sm:h-8 sm:w-8 items-center justify-center rounded-full text-xs font-bold transition duration-150 select-none bg-[#F1F5F9] text-[#64748B] border border-[#CBD5E1] flex-shrink-0 mt-0.5';
            }
        });

        axios.post('/api/v1/exam/save-answer', {
            student_exam_id: studentExamId,
            question_id: questionId,
            option_id: optionStr,
            is_doubtful: status.isDoubtful
        })
        .then(res => {
            console.log("Complex answer saved:", res.data.saved_at);
        })
        .catch(err => {
            console.error("Failed to save complex answer:", err);
        });
    }

    // Toggle Doubtful
    function toggleDoubtful() {
        const status = questionsStatus[currentIdx];
        status.isDoubtful = !status.isDoubtful;

        updateNavigationButtons();
        updateGridButtonColor(currentIdx);

        axios.post('/api/v1/exam/save-answer', {
            student_exam_id: studentExamId,
            question_id: status.questionId,
            option_id: status.selectedOption || null,
            is_doubtful: status.isDoubtful
        })
        .then(res => {
            console.log("Doubt status saved:", res.data.saved_at);
        })
        .catch(err => {
            console.error(err);
        });
    }

    // Countdown Timer JS with dynamic rose/amber coloring
    function startTimer() {
        const timerDisplay = document.getElementById('countdown-timer-header');
        if (!timerDisplay) return;
        
        const interval = setInterval(() => {
            if (remainingSeconds <= 0) {
                clearInterval(interval);
                timerDisplay.textContent = "00:00:00";
                timerDisplay.className = "font-mono font-bold text-lg sm:text-2xl text-[#DC2626]";
                autoSubmitExam();
                return;
            }

            remainingSeconds--;
            
            const secondsInt = Math.floor(remainingSeconds);
            const hrs = Math.floor(secondsInt / 3600);
            const mins = Math.floor((secondsInt % 3600) / 60);
            const secs = secondsInt % 60;

            const pad = (n) => n < 10 ? '0' + n : n;
            timerDisplay.textContent = `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
            
            if (remainingSeconds < 60) {
                timerDisplay.className = "font-mono font-bold text-lg sm:text-2xl text-[#DC2626] animate-pulse";
            } else if (remainingSeconds < 300) {
                timerDisplay.className = "font-mono font-bold text-lg sm:text-2xl text-[#D97706]";
            } else {
                timerDisplay.className = "font-mono font-bold text-lg sm:text-2xl text-[#0F172A]";
            }
        }, 1000);
    }

    // Background sync timer with server every 30 seconds
    function syncTimerWithServer() {
        setInterval(() => {
            if (remainingSeconds <= 0) return;
            
            axios.get(`/api/v1/exam/timer-sync/${studentExamId}`)
                .then(res => {
                    remainingSeconds = parseInt(res.data.remaining_seconds) || 0;
                    console.log("Timer synchronized. Remaining:", remainingSeconds, "secs");
                })
                .catch(err => {
                    console.warn("Timer sync failed:", err);
                });
        }, 30000);
    }

    // Confirm final exam submission
    function confirmSubmitExam() {
        let unanswered = 0;
        for (const idx in questionsStatus) {
            if (!questionsStatus[idx].hasAnswer) {
                unanswered++;
            }
        }

        let alertText = 'Apakah Anda yakin ingin menyelesaikan ujian? Anda tidak dapat mengubah jawaban lagi!';
        if (unanswered > 0) {
            alertText = `Terdapat ${unanswered} soal yang BELUM dijawab. Apakah Anda yakin ingin menyelesaikan ujian?`;
        }

        Swal.fire({
            title: 'Selesaikan Ujian?',
            text: alertText,
            icon: unanswered > 0 ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: '#14532D',
            cancelButtonColor: '#DC2626',
            confirmButtonText: 'Ya, Submit',
            cancelButtonText: 'Kembali Periksa',
            reverseButtons: true,
            customClass: { popup: 'rounded-sm' }
        }).then((result) => {
            if (result.isConfirmed) {
                submitFinalExam();
            }
        });
    }

    // Submit final exam answers
    function submitFinalExam() {
        isSubmitting = true;
        Swal.fire({
            title: 'Menyimpan Jawaban',
            text: 'Mengkalkulasi nilai Anda secara instan...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        axios.post('/api/v1/exam/submit-final', {
            student_exam_id: studentExamId,
            force: false
        })
        .then(res => {
            Swal.close();
            if (res.data.status === 'submited') {
                Swal.fire({
                    icon: 'success',
                    title: 'Ujian Selesai',
                    text: 'Nilai ujian Anda berhasil disimpan.',
                    confirmButtonColor: '#14532d',
                    customClass: { popup: 'rounded-sm' }
                }).then(() => {
                    window.location.href = '/mahasiswa/riwayat-ujian';
                });
            }
        })
        .catch(err => {
            isSubmitting = false;
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Gagal mengirimkan jawaban ujian.',
                customClass: { popup: 'rounded-sm' }
            });
        });
    }

    // Auto submit when time runs out
    function autoSubmitExam() {
        Swal.fire({
            title: 'Waktu Habis!',
            text: 'Waktu ujian telah berakhir. Lembar jawaban Anda akan otomatis disimpan.',
            icon: 'info',
            allowOutsideClick: false,
            confirmButtonText: 'OK',
            confirmButtonColor: '#14532d',
            customClass: { popup: 'rounded-sm' }
        }).then(() => {
            submitFinalExam();
        });
    }
</script>
@endsection
