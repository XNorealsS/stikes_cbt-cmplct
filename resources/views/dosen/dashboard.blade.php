@extends('layouts.dosen')

@section('title', 'Dosen Dashboard - E-Learning STIKesMu')

@section('dosen-content')
<div class="space-y-6">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Dosen &gt; Beranda Dosen</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Beranda Dosen
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola data bank soal mata kuliah, atur jadwal sesi ujian aktif, dan cetak lembar administrasi penilaian.</p>
            </div>
        </div>
    </div>

    <!-- Stats Grid: 4 columns on desktop, 2 on tablet, 1 on mobile -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <div class="bg-white p-5 rounded-xl border border-gray-255 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Mata Kuliah Terdaftar</span>
                <span class="text-2xl font-black text-primary leading-tight block">{{ $courseCount }}</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Mata kuliah yang diampu</span>
        </div>

        <!-- Card 2 -->
        <div class="bg-white p-5 rounded-xl border border-gray-255 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Mahasiswa Aktif di Kelas</span>
                <span class="text-2xl font-black text-gray-800 leading-tight block">{{ $studentCount }}</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Mahasiswa terdaftar di sistem</span>
        </div>

        <!-- Card 3 -->
        <div class="bg-white p-5 rounded-xl border border-gray-255 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tugas Berjalan</span>
                <span class="text-2xl font-black text-gray-800 leading-tight block">{{ $tugasCount }}</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Tugas aktif yang diberikan</span>
        </div>

        <!-- Card 4 -->
        <div class="bg-white p-5 rounded-xl border border-gray-255 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Sesi Ujian Aktif</span>
                <span class="text-2xl font-black text-gray-800 leading-tight block">{{ $examCount }}</span>
            </div>
            <span class="block text-[10px] text-gray-400 font-semibold mt-2 border-t pt-2">Sesi ujian yang dijadwalkan</span>
        </div>
    </div>

    <!-- Active Exams List (Flat Table) -->
    <div class="bg-white rounded-xl border border-gray-255 shadow-sm overflow-hidden">
        <div class="p-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-widest flex items-center">
                <span class="h-2 w-2 bg-green-500 rounded-full animate-ping mr-2"></span> Ujian Sedang Berjalan
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">
                        <th class="py-3 px-4 border-r border-gray-200">Nama Ujian</th>
                        <th class="py-3 px-4 border-r border-gray-200">Mata Kuliah</th>
                        <th class="py-3 px-4 text-center border-r border-gray-200">Token</th>
                        <th class="py-3 px-4 text-center border-r border-gray-200">Jumlah Soal</th>
                        <th class="py-3 px-4 border-r border-gray-200">Durasi Sesi</th>
                        <th class="py-3 px-4">Batas Akhir</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-gray-200 text-gray-700">
                    @forelse ($activeExams as $exam)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-gray-900 border-r border-gray-200">{{ $exam->title }}</td>
                        <td class="py-3 px-4 font-semibold text-gray-700 border-r border-gray-200">{{ $exam->course->name }} ({{ $exam->course->code }})</td>
                        <td class="py-3 px-4 text-center border-r border-gray-200">
                            <span class="font-mono font-bold bg-gray-100 border border-gray-200 text-gray-800 px-2 py-0.5 rounded text-[11px] tracking-wider">{{ $exam->token }}</span>
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-gray-650 border-r border-gray-200">{{ $exam->total_questions }} Soal</td>
                        <td class="py-3 px-4 text-gray-500 border-r border-gray-200">{{ $exam->duration_minutes }} Menit</td>
                        <td class="py-3 px-4 font-medium text-gray-500">{{ $exam->end_time->format('d-m-Y H:i') }} WIB</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-gray-400 font-semibold">Tidak ada sesi ujian aktif saat ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
