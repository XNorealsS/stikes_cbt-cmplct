@extends('layouts.admin')

@section('title', 'Analisis Butir Soal - E-Learning STIKesMu')

@section('admin-content')
<div class="space-y-6">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Admin &gt; Analisis Butir Soal</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Analisis Butir Soal Ujian
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Evaluasi kualitas soal (tingkat kesukaran, daya beda, reliabilitas) setelah pelaksanaan ujian.</p>
            </div>
        </div>
    </div>

    <!-- Select Exam Form -->
    <div class="bg-white p-4 rounded-xl border border-gray-250 shadow-sm">
        <form method="GET" action="{{ route('admin.analisis.index') }}" class="flex flex-col sm:flex-row items-end gap-4">
            <div class="flex-grow w-full">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Pilih Sesi Ujian</label>
                <select name="exam_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary bg-white font-semibold text-gray-700">
                    <option value="">-- Pilih Sesi Ujian --</option>
                    @foreach ($exams as $exam)
                    <option value="{{ $exam->id }}" {{ $examId == $exam->id ? 'selected' : '' }}>
                        {{ $exam->title }} ({{ $exam->course->name }}) - {{ $exam->student_exams_count }} Peserta
                    </option>
                    @endforeach
                </select>
            </div>
            @if ($selectedExam)
            <div class="flex space-x-2 w-full sm:w-auto">
                <button type="button" onclick="runAnalysis({{ $selectedExam->id }})" class="bg-primary hover:bg-emerald-850 text-white font-bold py-2 px-4 rounded-lg text-xs uppercase tracking-wider transition shadow-sm w-full sm:w-auto cursor-pointer flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-arrows-spin"></i>
                    <span>Jalankan Analisis</span>
                </button>
                <a href="{{ route('admin.analisis.export', $selectedExam->id) }}" class="bg-secondary hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg text-xs uppercase tracking-wider transition shadow-sm text-center w-full sm:w-auto cursor-pointer flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Export Excel</span>
                </a>
            </div>
            @endif
        </form>
    </div>

    @if ($selectedExam)
    <!-- Header Stats (Flat Cards ala SIAKAD) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Stats Card 1 -->
        <div class="bg-white p-5 rounded-xl border border-gray-250 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Reliabilitas Ujian</span>
                <span class="text-2xl font-black text-primary leading-tight block">{{ $cronbachAlpha !== null ? number_format($cronbachAlpha, 4) : 'N/A' }}</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Cronbach Alpha (Ideal > 0.70)</span>
        </div>

        <!-- Stats Card 2 -->
        <div class="bg-white p-5 rounded-xl border border-gray-250 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Jumlah Soal</span>
                <span class="text-2xl font-black text-gray-800 leading-tight block">{{ count($analyses) }} Soal</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Total butir soal yang diujikan</span>
        </div>

        <!-- Stats Card 3 -->
        <div class="bg-white p-5 rounded-xl border border-gray-250 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Jumlah Peserta</span>
                <span class="text-2xl font-black text-gray-800 leading-tight block">{{ $selectedExam->student_exams_count }} Mahasiswa</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Mahasiswa terdaftar pengerjaan</span>
        </div>
    </div>

    <!-- Analysis Table (Flat Table style) -->
    <div class="bg-white rounded-xl border border-gray-250 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">
                        <th class="py-3 px-4 w-12 text-center border-r border-gray-200">No</th>
                        <th class="py-3 px-4 border-r border-gray-200">Butir Soal</th>
                        <th class="py-3 px-4 text-center border-r border-gray-200">Tingkat Kesukaran (TK)</th>
                        <th class="py-3 px-4 text-center border-r border-gray-200">Daya Beda (DB)</th>
                        <th class="py-3 px-4 text-center border-r border-gray-200">Benar / Salah</th>
                        <th class="py-3 px-4 text-center">Distribusi Jawaban</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-gray-200 text-gray-700">
                    @forelse ($analyses as $index => $a)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 text-center font-bold text-gray-400 border-r border-gray-200">{{ $index + 1 }}</td>
                        <td class="py-3 px-4 border-r border-gray-200 font-semibold">
                            <div class="line-clamp-2">{!! strip_tags($a['question_text']) !!}</div>
                        </td>
                        <td class="py-3 px-4 text-center border-r border-gray-200 space-y-1">
                            <span class="block font-mono font-bold text-gray-800">{{ number_format($a['tingkat_kesukaran'], 2) }}</span>
                            <span class="inline-block text-[9px] px-2 py-0.5 rounded-full font-bold uppercase {{ $a['tingkat_kesukaran'] <= 0.30 ? 'bg-red-50 text-red-800 border border-red-100' : ($a['tingkat_kesukaran'] <= 0.70 ? 'bg-yellow-50 text-yellow-800 border border-yellow-100' : 'bg-emerald-50 text-emerald-800 border border-emerald-100') }}">
                                {{ $a['kategori_tk'] }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center border-r border-gray-200 space-y-1">
                            <span class="block font-mono font-bold text-gray-800">{{ number_format($a['daya_beda'], 2) }}</span>
                            <span class="inline-block text-[9px] px-2 py-0.5 rounded-full font-bold uppercase {{ $a['daya_beda'] < 0.20 ? 'bg-red-50 text-red-800 border border-red-100' : 'bg-emerald-50 text-emerald-800 border border-emerald-100' }}">
                                {{ $a['kategori_db'] }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-mono border-r border-gray-200 font-semibold">
                            <span class="text-emerald-700 font-bold">{{ $a['jawaban_benar'] }}B</span> / 
                            <span class="text-red-700 font-bold">{{ $a['jawaban_salah'] }}S</span>
                        </td>
                        <td class="py-3 px-4 text-center font-mono">
                            <div class="flex flex-wrap justify-center gap-1">
                                @foreach ($a['distribusi'] as $opt => $count)
                                <span class="bg-gray-100 border border-gray-200 px-1.5 py-0.5 rounded text-[10px] font-bold text-gray-650">
                                    <strong>{{ $opt }}</strong>:{{ $count }}
                                </span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-gray-400 font-semibold">Silakan jalankan analisis terlebih dahulu.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function runAnalysis(examId) {
        Swal.fire({
            title: 'Jalankan Analisis?',
            text: 'Sistem akan menghitung ulang tingkat kesukaran, daya beda, dan reliabilitas Cronbach Alpha.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#14532d',
            cancelButtonColor: '#EF4444',
            confirmButtonText: 'Ya, Jalankan',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return axios.post(`/admin/analisis-soal/${examId}/run`)
                    .then(res => {
                        if (!res.data.success) throw new Error(res.data.message);
                        return res.data;
                    })
                    .catch(err => {
                        Swal.showValidationMessage(err.response.data.message || 'Gagal menjalankan analisis.');
                    });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Analisis Selesai',
                    text: result.value.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => window.location.reload());
            }
        });
    }
</script>
@endsection
