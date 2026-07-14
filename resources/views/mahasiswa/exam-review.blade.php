@extends('layouts.mahasiswa')

@section('title', 'Pembahasan Ujian - CBT STIKES Muhammadiyah Lhokseumawe')

@section('mahasiswa-content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full space-y-8">
    
    <!-- Header Title -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Pembahasan Ujian</h1>
            <p class="text-sm text-gray-500 mt-1">Review detail jawaban Anda pada sesi ujian <strong class="text-gray-700">{{ $studentExam->exam->title }}</strong>.</p>
        </div>
        <a href="{{ route('mahasiswa.history') }}" class="bg-white hover:bg-gray-100 text-gray-700 font-bold py-2.5 px-5 rounded-xl border border-gray-300 text-sm transition shadow-sm flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left-long"></i>
            <span>Kembali ke Riwayat</span>
        </a>
    </div>

    <!-- Exam Score Summary Card -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6 relative overflow-hidden">
        <div class="space-y-1 relative z-10">
            <h2 class="text-xl font-bold text-gray-900">{{ $studentExam->exam->course->name }}</h2>
            <p class="text-xs text-gray-400">Diampu oleh: {{ $studentExam->exam->dosen->name }}</p>
            <p class="text-xs text-gray-500">Mulai: {{ $studentExam->started_at->format('d/m/Y H:i') }} | Selesai: {{ $studentExam->finished_at ? $studentExam->finished_at->format('d/m/Y H:i') : '-' }}</p>
        </div>
        <div class="text-center relative z-10 bg-gray-50 border p-4 rounded-xl min-w-[120px]">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest block">Nilai Ujian</span>
            <span class="font-mono font-black text-3xl mt-1 block {{ $studentExam->score >= 70 ? 'text-secondary' : 'text-red-600' }}">
                {{ number_format($studentExam->score, 2) }}
            </span>
        </div>
    </div>

    <!-- Questions Discussion List -->
    <div class="space-y-6">
        @foreach ($answers as $index => $ans)
        @php
            $q = $ans->question;
            $isCorrect = $ans->is_correct;
            $selected = $ans->selected_option;
            $correct = $q->correct_option;
            
            // Set border and background style based on correctness
            $cardBorder = 'border-gray-200 bg-white';
            if ($selected === null) {
                $cardBorder = 'border-yellow-200 bg-yellow-50/10';
            } elseif ($isCorrect) {
                $cardBorder = 'border-green-200 bg-green-50/10';
            } else {
                $cardBorder = 'border-red-200 bg-red-50/10';
            }
        @endphp
        
        <!-- Question Item Card -->
        <div class="border rounded-2xl p-6 md:p-8 shadow-sm space-y-4 {{ $cardBorder }}">
            
            <!-- Question text -->
            <div class="flex justify-between items-start gap-4">
                <div class="text-base md:text-lg font-bold text-gray-900 leading-relaxed flex items-start flex-1">
                    <span class="text-emerald-700 mr-2 select-none">{{ $index + 1 }}.</span>
                    <span class="flex-1 whitespace-pre-wrap">{!! nl2br(e($q->question_text)) !!}</span>
                </div>
                
                <!-- Status Badge -->
                @if ($selected === null)
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-700 uppercase tracking-wider flex-shrink-0">
                        Tidak Dijawab
                    </span>
                @elseif ($isCorrect)
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700 uppercase tracking-wider flex-shrink-0">
                        Benar
                    </span>
                @else
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700 uppercase tracking-wider flex-shrink-0">
                        Salah
                    </span>
                @endif
            </div>

            <!-- Optional Image -->
            @if ($q->question_image)
            <div class="my-4 max-w-md">
                <img src="{{ asset('storage/' . $q->question_image) }}" alt="Gambar Soal" class="rounded-xl border max-h-48 object-contain">
            </div>
            @endif

            <!-- Choices Options List -->
            <div class="space-y-2.5">
                @foreach (['A' => $q->option_a, 'B' => $q->option_b, 'C' => $q->option_c, 'D' => $q->option_d, 'E' => $q->option_e] as $opt => $text)
                @php
                    $optionBorder = 'border-gray-200 bg-white';
                    $badgeText = '';
                    
                    if ($opt === $correct) {
                        // Highlight the correct option in Green
                        $optionBorder = 'border-green-500 bg-green-50 text-green-800 font-bold';
                        $badgeText = '<span class="text-xs font-bold text-green-600 bg-green-100 px-2 py-0.5 rounded-full flex-shrink-0 ml-4"><i class="fa-solid fa-circle-check mr-1"></i>Kunci Jawaban</span>';
                    } elseif ($opt === $selected && !$isCorrect) {
                        // Highlight student wrong selection in Red
                        $optionBorder = 'border-red-500 bg-red-50 text-red-800 font-bold';
                        $badgeText = '<span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded-full flex-shrink-0 ml-4"><i class="fa-solid fa-circle-xmark mr-1"></i>Jawaban Anda</span>';
                    } elseif ($opt === $selected && $isCorrect) {
                        // Highlight student correct selection in Green with label
                        $badgeText = '<span class="text-xs font-bold text-green-600 bg-green-100 px-2 py-0.5 rounded-full flex-shrink-0 ml-4"><i class="fa-solid fa-circle-check mr-1"></i>Jawaban Anda (Benar)</span>';
                    }
                @endphp
                
                <div class="flex items-start p-3.5 border rounded-xl text-sm md:text-base {{ $optionBorder }}">
                    <span class="text-xs font-extrabold uppercase font-mono bg-gray-100 text-gray-500 px-2.5 py-1 rounded-lg border border-gray-200 mr-4 flex-shrink-0 mt-0.5 select-none">{{ $opt }}</span>
                    <span class="text-gray-800 flex-1 leading-relaxed">{{ $text }}</span>
                    {!! $badgeText !!}
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
