@extends('layouts.mahasiswa')

@section('title', 'Riwayat Ujian - CBT STIKES Muhammadiyah Lhokseumawe')

@section('mahasiswa-content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full space-y-8">
    
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Mahasiswa &gt; Riwayat Ujian</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Riwayat Ujian CBTMu
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Daftar lembar nilai dan pembahasan ujian yang telah selesai Anda ikuti.</p>
            </div>
            <a href="{{ route('mahasiswa.dashboard') }}" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                <i class="fa-solid fa-house text-xs"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>

    <!-- History Card / Table -->
    <div class="bg-white border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" style="min-width: 800px;">
                <thead>
                    <tr class="bg-green-700 text-xs font-bold text-white uppercase tracking-wider">
                        <th class="py-3 px-4" style="min-width: 220px; width: 220px;">Nama Ujian</th>
                        <th class="py-3 px-4" style="min-width: 240px; width: 240px;">Mata Kuliah</th>
                        <th class="py-3 px-4" style="min-width: 140px; width: 140px;">Tanggal Mulai</th>
                        <th class="py-3 px-4" style="min-width: 140px; width: 140px;">Selesai Pengerjaan</th>
                        <th class="py-3 px-4 text-center" style="min-width: 100px; width: 100px;">Status</th>
                        <th class="py-3 px-4 text-center" style="min-width: 100px; width: 100px;">Nilai Akhir</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($history as $h)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="py-4 px-6">
                            <span class="font-bold text-gray-900 block text-base">{{ $h->exam->title }}</span>
                            <span class="text-xs text-gray-400">Dosen: {{ $h->exam->dosen->name }}</span>
                        </td>
                        <td class="py-4 px-6 text-gray-600 font-medium">{{ $h->exam->course->name }} ({{ $h->exam->course->code }})</td>
                        <td class="py-4 px-6 text-xs text-gray-500">{{ $h->started_at->format('d-m-Y H:i') }}</td>
                        <td class="py-4 px-6 text-xs text-gray-500">{{ $h->finished_at ? $h->finished_at->format('d-m-Y H:i') : '-' }}</td>
                        <td class="py-4 px-6 text-center">
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded bg-green-50 text-green-700 border border-green-150 uppercase">
                                {{ $h->status }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="font-mono font-black text-lg {{ $h->score >= 70 ? 'text-secondary' : 'text-red-600' }}">
                                {{ number_format($h->score, 2) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-gray-400">
                            <i class="fa-solid fa-clock-rotate-left text-4xl mb-3 text-gray-300 block"></i>
                            Anda belum menyelesaikan ujian apapun.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
