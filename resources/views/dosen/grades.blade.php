@extends('layouts.dosen')

@section('title', 'Rekap & Cetak Nilai - E-Learning STIKesMu Lhokseumawe')

@section('styles')
<style>
    @media print {
        /* Hide sidebar, header and actions */
        aside, header, nav, footer, button, select, label, form, .no-print {
            display: none !important;
        }
        body {
            background-color: #fff !important;
            color: #000 !important;
            font-size: 11px !important;
        }
        .print-area {
            width: 100% !important;
            position: absolute;
            left: 0;
            top: 0;
            margin: 0;
            padding: 0;
        }
        .print-header {
            display: block !important;
            margin-bottom: 20px;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
        }
    }
    .print-header {
        display: none;
    }
</style>
@endsection

@section('dosen-content')
<div class="space-y-6 print-area">
    
    <!-- Printable Header (Only visible on Print) -->
    <div class="print-header text-center">
        <h1 class="text-xl font-bold uppercase">STIKES Muhammadiyah Lhokseumawe</h1>
        <p class="text-xs">Jl. Kampus Muhammadiyah, Lhokseumawe, Aceh</p>
        <h2 class="text-base font-bold mt-3 border-t pt-2 uppercase">Laporan Rekapitulasi Nilai Hasil Ujian CBTMu</h2>
        @if ($selectedExam)
            <div class="grid grid-cols-2 text-left text-xs max-w-md mx-auto mt-4 border p-3 rounded-lg">
                <div><strong>Ujian:</strong> {{ $selectedExam->title }}</div>
                <div><strong>Mata Kuliah:</strong> {{ $selectedExam->course->name }}</div>
                <div><strong>Dosen Pengampu:</strong> {{ $selectedExam->dosen->name }}</div>
                <div><strong>Tanggal Cetak:</strong> {{ date('d-m-Y H:i') }} WIB</div>
            </div>
        @endif
    </div>

    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4 no-print">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Dosen &gt; Rekap &amp; Cetak Nilai</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 inline-block"></span>
                    Cetak &amp; Rekap Penilaian
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Cetak berkas kelengkapan administrasi ujian atau unduh rekapitulasi nilai akhir kelas.</p>
            </div>
            @if ($selectedExam)
            <div class="flex items-center gap-2">
                <button type="button" onclick="openExportModal()" class="rounded-none border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold text-white hover:bg-green-800 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                    <i class="fa-solid fa-print text-xs"></i>
                    <span>Cetak &amp; Export Berkas</span>
                </button>
                @if (count($grades) > 0)
                <a href="{{ route('dosen.grades.export', ['exam_id' => $examId]) }}" class="rounded-none border border-emerald-700 bg-white px-3.5 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                    <i class="fa-solid fa-file-excel text-xs"></i>
                    <span>Ekspor CSV</span>
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Exam Selector Panel (Flat SIAKAD style) -->
    <div class="border border-slate-300 bg-white p-4 rounded-none shadow-none no-print">
        <form method="GET" action="{{ route('dosen.grades.index') }}" class="space-y-3">
            <div>
                <label for="exam_id_select" class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih Sesi Ujian</label>
                <select name="exam_id" id="exam_id_select" onchange="this.form.submit()" class="w-full border border-slate-300 rounded-none px-3 py-2 text-xs text-slate-800 focus:border-green-600 focus:outline-none bg-white font-semibold">
                    <option value="" disabled selected>-- Pilih Sesi Ujian --</option>
                    @foreach ($exams as $e)
                        <option value="{{ $e->id }}" {{ $examId == $e->id ? 'selected' : '' }}>{{ $e->title }} - {{ $e->course->name }} ({{ $e->course->code }})</option>
                    @endforeach
                </select>
            </div>

            @if ($selectedExam)
            <div class="pt-2 border-t border-slate-200 flex flex-wrap items-center gap-3 text-xs text-slate-600">
                <span class="inline-flex items-center gap-1 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-none font-medium">
                    <i class="fa-solid fa-book text-slate-400 text-[10px]"></i> {{ $selectedExam->course->name }} ({{ $selectedExam->course->code }})
                </span>
                <span class="inline-flex items-center gap-1 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-none font-medium">
                    <i class="fa-solid fa-user-tie text-slate-400 text-[10px]"></i> {{ $selectedExam->dosen->name }}
                </span>
                <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-300 px-2.5 py-1 rounded-none font-bold">
                    <i class="fa-solid fa-clock text-emerald-600 text-[10px]"></i> Durasi: {{ $selectedExam->duration_minutes }} Menit
                </span>
            </div>
            @endif
        </form>
    </div>

    @if ($selectedExam)
        <!-- Statistics Summary Panels (Flat) -->
        @php
            $scores = $grades->pluck('score')->filter(fn($val) => $val !== null);
            $totalCount = $grades->count();
            $avgScore = $scores->count() > 0 ? $scores->average() : 0;
            $maxScore = $scores->count() > 0 ? $scores->max() : 0;
            $minScore = $scores->count() > 0 ? $scores->min() : 0;

            $hasEssayQuestions = $selectedExam->bankSoal
                ? $selectedExam->bankSoal->questions()->where('question_type', 'essai')->exists()
                : \App\Models\Question::where('course_id', $selectedExam->course_id)->where('question_type', 'essai')->exists();
        @endphp
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 no-print">
            <div class="border border-slate-300 bg-white p-3.5 rounded-none shadow-none">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Peserta Ujian</p>
                <p class="mt-0.5 text-xl font-bold text-slate-800 font-heading">{{ $totalCount }}</p>
            </div>
            <div class="border border-slate-300 bg-white p-3.5 rounded-none shadow-none">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nilai Rata-rata</p>
                <p class="mt-0.5 text-xl font-bold text-sky-700 font-heading">{{ number_format($avgScore, 2) }}</p>
            </div>
            <div class="border border-slate-300 bg-white p-3.5 rounded-none shadow-none">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nilai Tertinggi</p>
                <p class="mt-0.5 text-xl font-bold text-emerald-700 font-heading">{{ number_format($maxScore, 2) }}</p>
            </div>
            <div class="border border-slate-300 bg-white p-3.5 rounded-none shadow-none">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nilai Terendah</p>
                <p class="mt-0.5 text-xl font-bold text-rose-700 font-heading">{{ number_format($minScore, 2) }}</p>
            </div>
        </div>

        <!-- Grades Table Container (Flat SIAKAD style) -->
        <div class="border border-slate-300 bg-white rounded-none shadow-none overflow-hidden">
            <div class="p-3.5 bg-slate-100 border-b border-slate-300 flex items-center justify-between no-print">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider"><i class="fa-solid fa-ranking-star text-emerald-700 mr-1.5"></i>Hasil Rekapitulasi Nilai Akhir Mahasiswa</h3>
                
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openExportModal()" class="rounded-none border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition flex items-center gap-1 cursor-pointer shadow-none">
                        <i class="fa-solid fa-print text-[10px] text-slate-500"></i>
                        <span>Cetak &amp; Export</span>
                    </button>
                    @if (count($grades) > 0)
                    <a href="{{ route('dosen.grades.export', ['exam_id' => $examId]) }}" class="rounded-none border border-emerald-700 bg-emerald-700 px-3 py-1 text-xs font-semibold text-white hover:bg-emerald-800 transition flex items-center gap-1 cursor-pointer shadow-none">
                        <i class="fa-solid fa-file-excel text-[10px]"></i>
                        <span>Unduh CSV</span>
                    </a>
                    @endif
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-slate-800 border-collapse">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-300 text-slate-700 font-bold">
                            <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px] w-16 border-r border-slate-200">Peringkat</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-[11px] border-r border-slate-200">NIM / Username</th>
                            <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-[11px] border-r border-slate-200">Nama Mahasiswa</th>
                            <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px] border-r border-slate-200">Waktu Mulai</th>
                            <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px] border-r border-slate-200">Waktu Selesai</th>
                            <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px] border-r border-slate-200">Status</th>
                            @if ($hasEssayQuestions)
                            <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px] border-r border-slate-200 w-28 no-print">Koreksi</th>
                            @endif
                            <th class="px-4 py-3 text-right font-semibold uppercase tracking-wider text-[11px] w-32">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($grades as $index => $g)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 text-center font-bold text-slate-400 border-r border-slate-200">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-mono text-slate-600 border-r border-slate-200">{{ $g->user->username }}</td>
                            <td class="px-4 py-3 font-bold text-slate-800 border-r border-slate-200">{{ $g->user->name }}</td>
                            <td class="px-4 py-3 text-center font-mono text-slate-500 border-r border-slate-200">{{ $g->started_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-center font-mono text-slate-500 border-r border-slate-200">{{ $g->finished_at ? $g->finished_at->format('d/m/Y H:i') : '-' }}</td>
                            <td class="px-4 py-3 text-center border-r border-slate-200">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-none uppercase {{ $g->status === 'finished' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300 animate-pulse' }}">
                                    {{ $g->status === 'finished' ? 'Selesai' : 'Sedang Ujian' }}
                                </span>
                            </td>
                            @if ($hasEssayQuestions)
                            <td class="px-4 py-3 text-center border-r border-slate-200 no-print">
                                @if ($g->status === 'finished')
                                    @php
                                        $ungradedCount = \App\Models\StudentAnswer::where('student_exam_id', $g->id)
                                            ->whereNull('is_correct')
                                            ->whereHas('question', function($q) {
                                                $q->where('question_type', 'essai');
                                            })->count();
                                    @endphp
                                    @if ($ungradedCount > 0)
                                        <a href="{{ route('dosen.student-exams.koreksi-essay', ['id' => $g->id]) }}" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-1.5 px-2 rounded text-[10px] uppercase tracking-wider transition shadow-sm cursor-pointer flex items-center justify-center space-x-1 mx-auto w-24">
                                            <i class="fa-solid fa-triangle-exclamation text-[9px]"></i>
                                            <span>Koreksi ({{ $ungradedCount }})</span>
                                        </a>
                                    @else
                                        <a href="{{ route('dosen.student-exams.koreksi-essay', ['id' => $g->id]) }}" class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold py-1.5 px-2 rounded text-[10px] uppercase tracking-wider transition shadow-sm cursor-pointer flex items-center justify-center space-x-1 mx-auto w-24">
                                            <i class="fa-solid fa-circle-check text-[9px]"></i>
                                            <span>Selesai</span>
                                        </a>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic text-[10px]">Belum Selesai</span>
                                @endif
                            </td>
                            @endif
                            <td class="px-4 py-3 text-right font-mono font-bold text-sm {{ $g->score >= 70 ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $g->score !== null ? number_format($g->score, 2) : '0.00' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $hasEssayQuestions ? 8 : 7 }}" class="px-4 py-8 text-center text-slate-400">Belum ada mahasiswa yang mengerjakan ujian ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Signature area for printed reports -->
        <div class="hidden print:block mt-16 flex justify-between text-xs px-12">
            <div>
                <p>Mengetahui,</p>
                <p class="font-bold mt-16">Bagian Akademik STIKesMu</p>
            </div>
            <div class="text-right">
                <p>Lhokseumawe, {{ date('d-m-Y') }}</p>
                <p>Dosen Pengampu,</p>
                <p class="font-bold mt-16">{{ $selectedExam->dosen->name }}</p>
            </div>
        </div>
    @else
        <!-- No Exam Selected State -->
        <div class="border border-slate-300 bg-white p-12 rounded-none shadow-none text-center text-slate-400">
            <i class="fa-solid fa-square-poll-vertical text-4xl text-slate-300 mb-3 block"></i>
            Silakan pilih sesi ujian terlebih dahulu untuk melihat rekapitulasi dan menu cetak data penilaian.
        </div>
    @endif
</div>

<!-- Modal Pop-up Cetak & Export Berkas Administrasi (Flat SIAKAD style) -->
<div id="export-modal" class="fixed inset-0 z-50 bg-slate-900/40 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-none max-w-3xl w-full border border-slate-300 shadow-xl overflow-hidden transform scale-95 transition-all duration-200">
        <div class="px-5 py-3.5 border-b border-slate-300 flex justify-between items-center bg-slate-100">
            <div>
                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-print text-emerald-700"></i>
                    Cetak &amp; Ekspor Berkas Administrasi Ujian
                </h3>
                @if ($selectedExam)
                <p class="text-[11px] text-slate-500 mt-0.5">Sesi: <span class="font-semibold text-slate-700">{{ $selectedExam->title }}</span> ({{ $selectedExam->course->name }})</p>
                @endif
            </div>
            <button type="button" onclick="closeExportModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer p-1"><i class="fa-solid fa-xmark text-base"></i></button>
        </div>
        
        <div class="p-5 overflow-y-auto max-h-[75vh]">
            <table class="w-full text-xs text-slate-800 border-collapse border border-slate-300">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-300 text-slate-700 font-bold">
                        <th class="px-3 py-2.5 text-center w-10 border-r border-slate-300">No</th>
                        <th class="px-3 py-2.5 text-left border-r border-slate-300">Dokumen Administrasi</th>
                        <th class="px-3 py-2.5 text-left border-r border-slate-300">Fungsi &amp; Kegunaan</th>
                        <th class="px-3 py-2.5 text-center w-24 border-r border-slate-300">Format</th>
                        <th class="px-3 py-2.5 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <!-- 1. Berita Acara -->
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-3 py-3 text-center font-mono font-bold text-slate-400 border-r border-slate-200">1</td>
                        <td class="px-3 py-3 border-r border-slate-200 font-semibold text-slate-800">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-file-signature text-emerald-700"></i> Berita Acara Ujian
                            </span>
                        </td>
                        <td class="px-3 py-3 text-slate-500 border-r border-slate-200">Form Laporan resmi pelaksanaan ujian &amp; proctoring</td>
                        <td class="px-3 py-3 text-center border-r border-slate-200">
                            <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 font-mono text-[10px] font-bold">PDF / Print</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <button type="button" onclick="printBeritaAcara()" class="w-28 rounded-none border border-transparent bg-green-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-800 transition cursor-pointer text-center whitespace-nowrap">
                                Cetak PDF
                            </button>
                        </td>
                    </tr>

                    <!-- 2. Daftar Hadir -->
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-3 py-3 text-center font-mono font-bold text-slate-400 border-r border-slate-200">2</td>
                        <td class="px-3 py-3 border-r border-slate-200 font-semibold text-slate-800">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-clipboard-user text-emerald-700"></i> Daftar Hadir Peserta
                            </span>
                        </td>
                        <td class="px-3 py-3 text-slate-500 border-r border-slate-200">Lembar presensi fisik tanda tangan peserta ujian</td>
                        <td class="px-3 py-3 text-center border-r border-slate-200">
                            <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 font-mono text-[10px] font-bold">PDF / Print</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <button type="button" onclick="printDaftarHadir()" class="w-28 rounded-none border border-transparent bg-green-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-800 transition cursor-pointer text-center whitespace-nowrap">
                                Cetak Presensi
                            </button>
                        </td>
                    </tr>

                    <!-- 3. Kartu Peserta -->
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-3 py-3 text-center font-mono font-bold text-slate-400 border-r border-slate-200">3</td>
                        <td class="px-3 py-3 border-r border-slate-200 font-semibold text-slate-800">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-address-card text-emerald-700"></i> Kartu Peserta Ujian
                            </span>
                        </td>
                        <td class="px-3 py-3 text-slate-500 border-r border-slate-200">Kartu identitas &amp; nomor peserta ujian mahasiswa</td>
                        <td class="px-3 py-3 text-center border-r border-slate-200">
                            <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 font-mono text-[10px] font-bold">PDF / Print</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <button type="button" onclick="printKartuPeserta()" class="w-28 rounded-none border border-transparent bg-green-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-800 transition cursor-pointer text-center whitespace-nowrap">
                                Cetak Kartu
                            </button>
                        </td>
                    </tr>

                    <!-- 4. Daftar Peserta Ujian -->
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-3 py-3 text-center font-mono font-bold text-slate-400 border-r border-slate-200">4</td>
                        <td class="px-3 py-3 border-r border-slate-200 font-semibold text-slate-800">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-users text-emerald-700"></i> Peserta Ujian
                            </span>
                        </td>
                        <td class="px-3 py-3 text-slate-500 border-r border-slate-200">Lampiran pengikut ujian dan status pengerjaan</td>
                        <td class="px-3 py-3 text-center border-r border-slate-200">
                            <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 font-mono text-[10px] font-bold">PDF / Print</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <button type="button" onclick="printPesertaUjian()" class="w-28 rounded-none border border-transparent bg-green-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-800 transition cursor-pointer text-center whitespace-nowrap">
                                Cetak Peserta
                            </button>
                        </td>
                    </tr>

                    <!-- 5. Jadwal Pengawas -->
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-3 py-3 text-center font-mono font-bold text-slate-400 border-r border-slate-200">5</td>
                        <td class="px-3 py-3 border-r border-slate-200 font-semibold text-slate-800">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-calendar-check text-emerald-700"></i> Jadwal Pengawas
                            </span>
                        </td>
                        <td class="px-3 py-3 text-slate-500 border-r border-slate-200">Surat tugas &amp; daftar pengawas proctor ujian</td>
                        <td class="px-3 py-3 text-center border-r border-slate-200">
                            <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 font-mono text-[10px] font-bold">PDF / Print</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <button type="button" onclick="printJadwalPengawas()" class="w-28 rounded-none border border-transparent bg-green-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-800 transition cursor-pointer text-center whitespace-nowrap">
                                Cetak Jadwal
                            </button>
                        </td>
                    </tr>

                    <!-- 6. Rekap Excel -->
                    @if ($selectedExam && count($grades) > 0)
                    <tr class="hover:bg-slate-50 transition bg-emerald-50/30">
                        <td class="px-3 py-3 text-center font-mono font-bold text-emerald-600 border-r border-slate-200">6</td>
                        <td class="px-3 py-3 border-r border-slate-200 font-semibold text-slate-800">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-file-excel text-emerald-700"></i> Rekapitulasi Nilai (CSV)
                            </span>
                        </td>
                        <td class="px-3 py-3 text-slate-500 border-r border-slate-200">Master file rekap nilai akhir kelas untuk SIAKAD</td>
                        <td class="px-3 py-3 text-center border-r border-slate-200">
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 border border-emerald-300 font-mono text-[10px] font-bold">.CSV</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <a href="{{ route('dosen.grades.export', ['exam_id' => $examId]) }}" class="w-28 rounded-none border border-emerald-700 bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800 transition inline-block text-center whitespace-nowrap cursor-pointer">
                                Unduh CSV
                            </a>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-slate-300 bg-slate-100 flex justify-end">
            <button type="button" onclick="closeExportModal()" class="rounded-none border border-slate-300 bg-white px-4 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition cursor-pointer">
                Tutup Window
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openExportModal() {
        const modal = document.getElementById('export-modal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeExportModal() {
        const modal = document.getElementById('export-modal');
        if (modal) modal.classList.add('hidden');
    }
</script>

@if ($selectedExam)
<script>
    // Prepare variables for printable templates
    const examTitle = "{{ $selectedExam->title }}";
    const courseName = "{{ $selectedExam->course->name }}";
    const courseCode = "{{ $selectedExam->course->code }}";
    const dosenName = "{{ $selectedExam->dosen->name }}";
    
    // Students list from PHP
    const students = [
        @foreach ($grades as $g)
        {
            nim: "{{ $g->user->username }}",
            name: "{{ $g->user->name }}",
            status: "{{ $g->status }}",
            score: "{{ $g->score !== null ? number_format($g->score, 2) : '0.00' }}"
        },
        @endforeach
    ];

    // Helper for printing standard styles
    function openPrintWindow(htmlContent, title) {
        const win = window.open('', '_blank');
        win.document.write(`
            <html>
            <head>
                <title>${title}</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
                <style>
                    body { font-family: 'Arial', sans-serif; padding: 40px; color: #000; background-color: #fff; }
                    .kop-surat { border-bottom: 3px double #000; padding-bottom: 12px; margin-bottom: 30px; text-align: center; }
                    @media print {
                        body { padding: 0; }
                    }
                </style>
            </head>
            <body>
                <div class="kop-surat">
                    <h1 class="text-xl font-bold uppercase">STIKES MUHAMMADIYAH LHOKSEUMAWE</h1>
                    <p class="text-xs">Jl. Kampus Muhammadiyah, Lhokseumawe, Aceh - Telp: (0645) 123456</p>
                    <p class="text-[10px] text-gray-500">Website: stikesmu.ac.id | Email: info@stikesmu.ac.id</p>
                </div>
                ${htmlContent}
                
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(function() { window.close(); }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);
        win.document.close();
    }

    // 1. Kartu Peserta Ujian
    function printKartuPeserta() {
        if (students.length === 0) {
            showError('Tidak ada peserta terdaftar.');
            return;
        }

        let content = `
            <h2 class="text-center font-bold text-base mb-6 uppercase tracking-wider">KARTU PESERTA UJIAN CBTMu</h2>
            <div class="grid grid-cols-2 gap-6">
        `;

        students.forEach(s => {
            content += `
                <div class="border-2 border-gray-400 p-4 rounded-xl space-y-4">
                    <div class="flex items-center justify-between border-b pb-2">
                        <span class="font-black text-xs text-blue-900">STIKesMu CBTMu</span>
                        <span class="text-[10px] bg-green-100 text-green-800 font-bold px-2 py-0.5 rounded-full">KARTU UJIAN</span>
                    </div>
                    <table class="text-xs w-full">
                        <tr><td class="font-bold py-1 w-20">NIM</td><td>: ${s.nim}</td></tr>
                        <tr><td class="font-bold py-1">Nama</td><td class="font-bold">: ${s.name}</td></tr>
                        <tr><td class="font-bold py-1">Ujian</td><td>: ${examTitle}</td></tr>
                        <tr><td class="font-bold py-1">Mata Kuliah</td><td>: ${courseName}</td></tr>
                        <tr><td class="font-bold py-1">Ruang</td><td>: LAB CBTMu 1</td></tr>
                    </table>
                    <div class="border-t pt-2 flex justify-between items-center text-[9px] text-gray-400">
                        <span>Pengawas: ________________</span>
                        <span class="font-mono">${s.nim}-${s.score}</span>
                    </div>
                </div>
            `;
        });

        content += `</div>`;
        openPrintWindow(content, 'Cetak Kartu Peserta Ujian');
    }

    // 2. Daftar Hadir Ujian
    function printDaftarHadir() {
        let content = `
            <h2 class="text-center font-bold text-base uppercase tracking-wider mb-2">DAFTAR HADIR PESERTA UJIAN</h2>
            <div class="text-xs space-y-1 mb-6 max-w-md mx-auto border p-3 rounded-lg bg-gray-50">
                <div><strong>Nama Ujian:</strong> ${examTitle}</div>
                <div><strong>Mata Kuliah:</strong> ${courseName} (${courseCode})</div>
                <div><strong>Dosen Pengampu:</strong> ${dosenName}</div>
                <div><strong>Tanggal / Waktu:</strong> ${new Date().toLocaleDateString('id-ID')} WIB</div>
            </div>
            
            <table class="w-full border-collapse border border-gray-400 text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-400 py-2 px-3 text-center w-12">No</th>
                        <th class="border border-gray-400 py-2 px-3 w-32">NIM</th>
                        <th class="border border-gray-400 py-2 px-3">Nama Lengkap</th>
                        <th class="border border-gray-400 py-2 px-3 w-40 text-center" colspan="2">Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody>
        `;

        students.forEach((s, idx) => {
            const no = idx + 1;
            const signPos1 = no % 2 !== 0 ? `${no}. ....................` : '';
            const signPos2 = no % 2 === 0 ? `${no}. ....................` : '';
            content += `
                <tr>
                    <td class="border border-gray-400 py-2.5 px-3 text-center">${no}</td>
                    <td class="border border-gray-400 py-2.5 px-3 font-mono">${s.nim}</td>
                    <td class="border border-gray-400 py-2.5 px-3 font-bold">${s.name}</td>
                    <td class="border border-gray-400 py-2.5 px-3 w-20 text-left text-[10px] text-gray-500">${signPos1}</td>
                    <td class="border border-gray-400 py-2.5 px-3 w-20 text-left text-[10px] text-gray-500">${signPos2}</td>
                </tr>
            `;
        });

        if (students.length === 0) {
            content += `<tr><td colspan="5" class="border border-gray-400 py-4 text-center">Belum ada peserta terdaftar.</td></tr>`;
        }

        content += `
                </tbody>
            </table>
            
            <div class="mt-12 flex justify-between text-xs px-8">
                <div>
                    <p>Mengetahui,</p>
                    <p class="font-bold mt-16">Bagian Akademik</p>
                </div>
                <div class="text-right">
                    <p>Dosen Pengawas,</p>
                    <p class="font-bold mt-16">____________________</p>
                </div>
            </div>
        `;
        openPrintWindow(content, 'Cetak Daftar Hadir Ujian');
    }

    // 3. Berita Acara Pelaksanaan Ujian
    function printBeritaAcara() {
        const dateStr = new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        let content = `
            <h2 class="text-center font-bold text-base uppercase tracking-wider mb-8">BERITA ACARA PELAKSANAAN UJIAN CBTMu</h2>
            <p class="text-xs leading-relaxed mb-6">
                Pada hari ini, <strong>\${dateStr}</strong>, telah dilaksanakan ujian CBTMu di STIKES Muhammadiyah Lhokseumawe untuk:
            </p>
            
            <table class="text-xs w-full max-w-lg mb-8 space-y-2">
                <tr><td class="font-bold py-1 w-40">Mata Kuliah / Sesi</td><td>: ${courseName} (${courseCode}) / ${examTitle}</td></tr>
                <tr><td class="font-bold py-1">Dosen Pengampu</td><td>: ${dosenName}</td></tr>
                <tr><td class="font-bold py-1">Ruang / Lab</td><td>: Laboratorium Komputer CBTMu 1</td></tr>
                <tr><td class="font-bold py-1">Jumlah Peserta Hadir</td><td>: ${students.length} Orang</td></tr>
                <tr><td class="font-bold py-1">Jumlah Tidak Hadir</td><td>: 0 Orang</td></tr>
            </table>

            <h3 class="text-xs font-bold border-b pb-1 mb-3">Catatan Pelaksanaan Ujian:</h3>
            <div class="border-2 border-dashed border-gray-300 p-8 rounded-xl text-gray-400 text-xs italic mb-12">
                Catatan khusus mengenai server, gangguan jaringan, atau indikasi kecurangan mahasiswa (jika ada):
                <br><br><br>
            </div>

            <div class="flex justify-between text-xs px-8">
                <div>
                    <p>Dosen Pengawas,</p>
                    <p class="font-bold mt-16">____________________</p>
                </div>
                <div class="text-right">
                    <p>Penanggung Jawab Laboratorium,</p>
                    <p class="font-bold mt-16">____________________</p>
                </div>
            </div>
        `;
        openPrintWindow(content, 'Cetak Berita Acara Ujian');
    }

    // 4. Peserta Ujian
    function printPesertaUjian() {
        let content = `
            <h2 class="text-center font-bold text-base uppercase tracking-wider mb-6">DAFTAR PESERTA UJIAN RESMI</h2>
            <div class="text-xs space-y-1 mb-6 border p-3 rounded-lg bg-gray-50">
                <div><strong>Ujian:</strong> ${examTitle}</div>
                <div><strong>Mata Kuliah:</strong> ${courseName}</div>
                <div><strong>Jumlah Total:</strong> ${students.length} Mahasiswa</div>
            </div>
            
            <table class="w-full border-collapse border border-gray-400 text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-400 py-2 px-3 text-center w-12">No</th>
                        <th class="border border-gray-400 py-2 px-3 w-40">NIM</th>
                        <th class="border border-gray-400 py-2 px-3">Nama Lengkap</th>
                        <th class="border border-gray-400 py-2 px-3 w-32 text-center">Status Sesi</th>
                    </tr>
                </thead>
                <tbody>
        `;

        students.forEach((s, idx) => {
            content += `
                <tr>
                    <td class="border border-gray-400 py-2 px-3 text-center">${idx + 1}</td>
                    <td class="border border-gray-400 py-2 px-3 font-mono">${s.nim}</td>
                    <td class="border border-gray-400 py-2 px-3 font-bold">${s.name}</td>
                    <td class="border border-gray-400 py-2 px-3 text-center uppercase font-bold text-[10px]">${s.status}</td>
                </tr>
            `;
        });

        content += `
                </tbody>
            </table>
        `;
        openPrintWindow(content, 'Cetak Daftar Peserta Ujian');
    }

    // 5. Jadwal Pengawas
    function printJadwalPengawas() {
        let content = `
            <h2 class="text-center font-bold text-base uppercase tracking-wider mb-8">SURAT TUGAS & JADWAL PENGAWAS UJIAN</h2>
            <table class="w-full border-collapse border border-gray-400 text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-400 py-3 px-3 text-center w-12">No</th>
                        <th class="border border-gray-400 py-3 px-3">Mata Uji / Sesi</th>
                        <th class="border border-gray-400 py-3 px-3">Jadwal Sesi</th>
                        <th class="border border-gray-400 py-3 px-3">Ruang Lab</th>
                        <th class="border border-gray-400 py-3 px-3 w-48">Dosen Pengawas (Proktor)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-gray-400 py-4 px-3 text-center">1</td>
                        <td class="border border-gray-400 py-4 px-3">
                            <span class="font-bold">${examTitle}</span>
                            <span class="block text-[10px] text-gray-400">${courseName}</span>
                        </td>
                        <td class="border border-gray-400 py-4 px-3">${new Date().toLocaleDateString('id-ID')} WIB</td>
                        <td class="border border-gray-400 py-4 px-3 font-bold">LAB CBTMu 1</td>
                        <td class="border border-gray-400 py-4 px-3 font-bold text-gray-900">${dosenName}</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="mt-16 text-right text-xs px-12">
                <p>Lhokseumawe, ${new Date().toLocaleDateString('id-ID')}</p>
                <p>Ketua Panitia Ujian CBTMu,</p>
                <p class="font-bold mt-20">___________________________</p>
            </div>
        `;
        openPrintWindow(content, 'Cetak Jadwal Pengawas Ujian');
    }
</script>
@endif
@endsection
