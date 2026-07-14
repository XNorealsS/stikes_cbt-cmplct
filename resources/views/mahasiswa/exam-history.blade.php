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
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="py-4 px-6">Nama Ujian</th>
                        <th class="py-4 px-6">Mata Kuliah</th>
                        <th class="py-4 px-6">Tanggal Mulai</th>
                        <th class="py-4 px-6">Selesai Pengerjaan</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-center">Nilai Akhir</th>
                        <th class="py-4 px-6 text-right">Pembahasan</th>
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
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-green-50 text-green-700 uppercase">
                                {{ $h->status }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="font-mono font-black text-lg {{ $h->score >= 70 ? 'text-secondary' : 'text-red-600' }}">
                                {{ number_format($h->score, 2) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <a href="{{ route('mahasiswa.review', ['id' => $h->id]) }}" class="bg-blue-50 hover:bg-blue-100 text-primary px-3 py-2 rounded-lg text-xs font-bold transition duration-150 inline-flex items-center space-x-1.5 shadow-sm">
                                <i class="fa-solid fa-book-open"></i>
                                <span>Lihat Pembahasan</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-400">
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
