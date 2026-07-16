@extends('layouts.dosen')

@section('title', 'Koreksi Essay - E-Learning STIKesMu')

@section('dosen-content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Dosen &gt; Rekap Nilai &gt; Koreksi Essay</p>
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Koreksi Jawaban Essay
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Berikan penilaian benar/salah secara manual untuk jawaban essay mahasiswa.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dosen.grades.index', ['exam_id' => $exam->id]) }}" class="rounded-none border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
                <button type="button" onclick="document.getElementById('essay-correction-form').submit()" class="rounded-none border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-green-800 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <span>Simpan Penilaian</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Info Panel: Grid of 2 Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Card 1: Informasi Mahasiswa -->
        <div class="bg-white border border-slate-200 rounded-none shadow-none overflow-hidden">
            <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 flex items-center gap-2">
                <span class="p-1 bg-emerald-50 text-emerald-700 rounded">
                    <i class="fa-solid fa-user text-xs"></i>
                </span>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Informasi Mahasiswa</span>
            </div>
            <div class="p-4">
                <table class="w-full text-xs text-slate-700 border-none">
                    <tr class="align-middle">
                        <td class="w-28 py-1.5 font-semibold text-slate-450">Nama Lengkap</td>
                        <td class="w-4 py-1.5 text-slate-400 font-bold text-center">:</td>
                        <td class="py-1.5 text-slate-800 font-bold">{{ $studentExam->user->name }}</td>
                    </tr>
                    <tr class="align-middle">
                        <td class="py-1.5 font-semibold text-slate-450">NIM / Username</td>
                        <td class="py-1.5 text-slate-400 font-bold text-center">:</td>
                        <td class="py-1.5">
                            <span class="bg-slate-100 text-slate-700 font-mono font-bold px-2 py-0.5 rounded text-[10px] border border-slate-200">
                                {{ $studentExam->user->username }}
                            </span>
                        </td>
                    </tr>
                    <tr class="align-middle">
                        <td class="py-1.5 font-semibold text-slate-450">Program Studi</td>
                        <td class="py-1.5 text-slate-400 font-bold text-center">:</td>
                        <td class="py-1.5 text-slate-750 font-semibold">{{ $studentExam->user->prodi->name ?? '-' }}</td>
                    </tr>
                    <tr class="align-middle">
                        <td class="py-1.5 font-semibold text-slate-450">Angkatan</td>
                        <td class="py-1.5 text-slate-400 font-bold text-center">:</td>
                        <td class="py-1.5 text-slate-750 font-semibold">{{ $studentExam->user->angkatan ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Card 2: Informasi Sesi Ujian -->
        <div class="bg-white border border-slate-200 rounded-none shadow-none overflow-hidden">
            <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 flex items-center gap-2">
                <span class="p-1 bg-sky-50 text-sky-700 rounded">
                    <i class="fa-solid fa-file-signature text-xs"></i>
                </span>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Detail Sesi Ujian</span>
            </div>
            <div class="p-4">
                <table class="w-full text-xs text-slate-700 border-none">
                    <tr class="align-middle">
                        <td class="w-28 py-1.5 font-semibold text-slate-450">Nama Ujian</td>
                        <td class="w-4 py-1.5 text-slate-400 font-bold text-center">:</td>
                        <td class="py-1.5 text-slate-800 font-bold">{{ $exam->title }}</td>
                    </tr>
                    <tr class="align-middle">
                        <td class="py-1.5 font-semibold text-slate-450">Mata Kuliah</td>
                        <td class="py-1.5 text-slate-400 font-bold text-center">:</td>
                        <td class="py-1.5 text-slate-750 font-semibold">{{ $exam->course->name ?? '-' }}</td>
                    </tr>
                    <tr class="align-middle">
                        <td class="py-1.5 font-semibold text-slate-450">Waktu Mulai</td>
                        <td class="py-1.5 text-slate-400 font-bold text-center">:</td>
                        <td class="py-1.5 text-slate-750 font-mono">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar-check text-slate-450"></i>
                                <span>{{ $studentExam->started_at->format('d/m/Y H:i') }} WIB</span>
                            </span>
                        </td>
                    </tr>
                    <tr class="align-middle">
                        <td class="py-1.5 font-semibold text-slate-450">Waktu Selesai</td>
                        <td class="py-1.5 text-slate-400 font-bold text-center">:</td>
                        <td class="py-1.5 text-slate-750 font-mono">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-regular fa-clock text-slate-450"></i>
                                <span>{{ $studentExam->finished_at ? $studentExam->finished_at->format('d/m/Y H:i') : '-' }} WIB</span>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Essay Correction Form -->
    <form id="essay-correction-form" action="{{ route('dosen.student-exams.store-koreksi-essay', ['id' => $studentExam->id]) }}" method="POST" class="space-y-6 pb-12">
        @csrf
        <div class="space-y-6">
            @forelse ($answers as $index => $ans)
            @php
                $q = $ans->question;
            @endphp
            <div class="border border-slate-300 bg-white shadow-none rounded-none overflow-hidden">
                <!-- Question Card Header -->
                <div class="px-5 py-3.5 bg-slate-100 border-b border-slate-300 flex justify-between items-center">
                    <span class="bg-slate-200 border border-slate-300 text-slate-700 font-bold h-6 w-6 rounded-full flex items-center justify-center text-xs">
                        {{ $index + 1 }}
                    </span>
                    <span class="text-[10px] bg-slate-200 border px-2 py-0.5 font-bold rounded uppercase tracking-wider text-slate-700">
                        Kesulitan: {{ $q->difficulty }}
                    </span>
                </div>

                <div class="p-5 space-y-4">
                    <!-- Question Text Block -->
                    <div class="bg-emerald-50/20 p-4 border-l-4 border-green-700 space-y-1 rounded-r-md">
                        <span class="text-[9px] font-black text-green-800 uppercase tracking-widest block"><i class="fa-solid fa-circle-question mr-1"></i>Pertanyaan Soal:</span>
                        <div class="text-sm font-semibold text-slate-900 leading-relaxed">
                            {!! nl2br(e($q->question_text)) !!}
                        </div>
                    </div>

                    <!-- Comparison Grid (Side-by-Side on desktop/tablet, Stacked on mobile) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Student's Response Card -->
                        <div class="border border-slate-200 rounded-lg overflow-hidden flex flex-col bg-white">
                            <div class="px-4 py-2.5 bg-slate-100 border-b border-slate-200 flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-user-pen text-slate-500 text-xs"></i> Jawaban Mahasiswa
                                </span>
                                @if (!$ans->answer_text)
                                    <span class="text-[8px] bg-rose-100 text-rose-700 font-bold px-2 py-0.5 rounded border border-rose-200 uppercase tracking-wider">Tidak Menjawab</span>
                                @endif
                            </div>
                            <div class="p-4 flex-grow bg-slate-50/30 min-h-[120px] flex flex-col justify-between">
                                @if ($ans->answer_text)
                                    <div class="bg-white p-4 rounded border border-slate-200 shadow-sm text-slate-800 font-sans leading-relaxed text-xs flex-grow whitespace-pre-wrap">{{ trim($ans->answer_text) }}</div>
                                @else
                                    <div class="bg-rose-50/20 border border-rose-150 p-4 rounded text-rose-700 italic text-center text-xs flex-grow flex items-center justify-center">
                                        <i class="fa-solid fa-circle-exclamation mr-1.5 text-xs text-rose-500"></i> Mahasiswa tidak menuliskan jawaban.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Reference Answer Card -->
                        <div class="border border-amber-200 rounded-lg overflow-hidden flex flex-col bg-white">
                            <div class="px-4 py-2.5 bg-amber-50 border-b border-amber-200 flex items-center">
                                <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-key text-amber-600 text-xs"></i> Kunci Jawaban (Referensi Dosen)
                                </span>
                            </div>
                            <div class="p-4 flex-grow bg-amber-50/5 min-h-[120px] flex flex-col">
                                <div class="bg-white p-4 rounded border border-amber-200 shadow-sm text-slate-800 font-sans leading-relaxed text-xs flex-grow whitespace-pre-wrap font-semibold">{{ trim($q->correct_option) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Grading Section -->
                    <div class="pt-3 border-t border-slate-200 flex flex-col lg:flex-row lg:items-center gap-3 justify-between bg-slate-50/50 p-3.5">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5"><i class="fa-solid fa-star-half-stroke text-slate-400 text-xs"></i>Penilaian Jawaban:</span>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                            <label class="inline-flex items-center justify-center gap-2 cursor-pointer py-2 px-4 border border-slate-200 bg-white rounded hover:bg-emerald-50 hover:border-emerald-300 transition flex-1 sm:flex-initial">
                                <input type="radio" name="grade[{{ $ans->id }}]" value="1" {{ $ans->is_correct === true ? 'checked' : '' }} class="w-4 h-4 text-emerald-650 focus:ring-emerald-500 border-slate-300">
                                <span class="text-xs font-bold text-emerald-700">Benar (100% Poin)</span>
                            </label>
                            <label class="inline-flex items-center justify-center gap-2 cursor-pointer py-2 px-4 border border-slate-200 bg-white rounded hover:bg-rose-50 hover:border-rose-300 transition flex-1 sm:flex-initial">
                                <input type="radio" name="grade[{{ $ans->id }}]" value="0" {{ $ans->is_correct === false ? 'checked' : '' }} class="w-4 h-4 text-rose-650 focus:ring-rose-500 border-slate-300">
                                <span class="text-xs font-bold text-rose-700">Salah (0 Poin)</span>
                            </label>
                            <label class="inline-flex items-center justify-center gap-2 cursor-pointer py-2 px-4 border border-slate-200 bg-white rounded hover:bg-slate-100 transition flex-1 sm:flex-initial">
                                <input type="radio" name="grade[{{ $ans->id }}]" value="pending" {{ $ans->is_correct === null ? 'checked' : '' }} class="w-4 h-4 text-slate-500 focus:ring-slate-400 border-slate-300">
                                <span class="text-xs font-semibold text-slate-650">Belum Dinilai</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="border border-slate-300 bg-white p-8 text-center text-slate-400 rounded-none">
                Tidak ada jawaban essay untuk mahasiswa ini.
            </div>
            @endforelse
        </div>

        <!-- Action Buttons (Static) -->
        <div class="border border-slate-300 bg-slate-50 p-5 rounded-none flex flex-col md:flex-row items-center justify-between gap-4 mt-6 print:hidden">
            <div class="text-xs text-slate-500 text-center md:text-left">
                Menilai jawaban untuk: <span class="font-bold text-slate-700">{{ $studentExam->user->name }}</span> ({{ $studentExam->user->username }})
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                <a href="{{ route('dosen.grades.index', ['exam_id' => $exam->id]) }}" class="rounded-none border border-slate-300 bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition cursor-pointer text-center">
                    Batal / Kembali
                </a>
                <button type="submit" class="rounded-none border border-transparent bg-green-700 px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-white hover:bg-green-800 transition cursor-pointer shadow-none text-center flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-save"></i>
                    <span>Simpan Penilaian &amp; Hitung Nilai</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
