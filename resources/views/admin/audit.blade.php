@extends('layouts.admin')

@section('title', 'Audit System Log - CBT STIKES Muhammadiyah Lhokseumawe')

@section('admin-content')
<div class="space-y-8">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Admin &gt; Audit System Log</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Audit System Log &amp; Rekam Jejak
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Daftar rekam jejak aktivitas operasional pengguna dalam sistem CBT.</p>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="py-4 px-6">Identitas Pengguna</th>
                        <th class="py-4 px-6">Username (NIM/NIP)</th>
                        <th class="py-4 px-6">Aktivitas</th>
                        <th class="py-4 px-6">Deskripsi Rinci</th>
                        <th class="py-4 px-6">Alamat IP</th>
                        <th class="py-4 px-6">Tanggal & Waktu</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($logs as $log)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="py-4 px-6 font-semibold text-gray-900">{{ $log->user ? $log->user->name : 'System' }}</td>
                        <td class="py-4 px-6 font-mono text-gray-600">{{ $log->user ? $log->user->username : '-' }}</td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ Str::contains($log->activity, 'Hapus') ? 'bg-red-50 text-red-700' : (Str::contains($log->activity, 'Tambah') ? 'bg-green-50 text-green-700' : 'bg-blue-50 text-blue-700') }}">
                                {{ $log->activity }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-gray-500">{{ $log->description }}</td>
                        <td class="py-4 px-6 font-mono text-xs text-gray-400">{{ $log->ip_address }}</td>
                        <td class="py-4 px-6 text-gray-400 text-xs font-medium">{{ $log->created_at->format('d-m-Y H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-gray-400">Belum ada log aktivitas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Links -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
