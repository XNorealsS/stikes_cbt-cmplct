@extends('layouts.dosen')

@section('title', 'Koreksi Pengumpulan Tugas - E-Learning STIKesMu')

@section('dosen-content')
<div class="space-y-8">
    <div class="flex items-center space-x-4">
        <a href="{{ route('dosen.tugas.index') }}" class="bg-gray-150 text-gray-700 p-2.5 rounded-xl hover:bg-gray-200 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Koreksi Pengumpulan Tugas</h1>
            <p class="text-sm text-gray-500 mt-1">Tugas: <strong class="text-gray-750 font-bold">{{ $tugas->judul }}</strong> ({{ $tugas->course->name }})</p>
        </div>
    </div>

    <!-- Submissions Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="py-4 px-6">Nama Mahasiswa / NIM</th>
                        <th class="py-4 px-6">Waktu Pengumpulan</th>
                        <th class="py-4 px-6">Catatan Mahasiswa</th>
                        <th class="py-4 px-6">Nilai</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($submissions as $sub)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900 block text-base">{{ $sub->user->name }}</span>
                                <div class="flex gap-1 font-mono text-[8px] font-extrabold uppercase">
                                    @if ($sub->is_late)
                                        <span class="bg-red-50 text-red-800 px-1.5 py-0.5 rounded border border-red-150">Terlambat</span>
                                    @endif
                                    @if ($sub->is_revision)
                                        <span class="bg-blue-50 text-blue-800 px-1.5 py-0.5 rounded border border-blue-150 font-mono">Revisi</span>
                                    @endif
                                </div>
                            </div>
                            <span class="text-xs text-gray-450 font-mono block mt-0.5">{{ $sub->user->username }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="text-gray-700 font-semibold block">{{ $sub->submitted_at ? $sub->submitted_at->format('d/m/Y H:i') : '-' }} WIB</span>
                        </td>
                        <td class="py-4 px-6">
                            <p class="text-gray-600 max-w-xs truncate text-xs md:text-sm">{{ $sub->catatan ?? '-' }}</p>
                        </td>
                        <td class="py-4 px-6 font-mono">
                            @if ($sub->nilai !== null)
                            <span class="text-lg font-black text-green-700">{{ $sub->nilai }}</span> <span class="text-xs text-gray-400">/ {{ $tugas->poin_nilai }}</span>
                            @else
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-orange-50 text-orange-700 border border-orange-100">Belum Dinilai</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right space-y-2">
                            <div class="flex flex-wrap justify-end gap-1 mb-2">
                                @forelse($sub->files as $file)
                                    <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-2.5 py-1 rounded text-[10px] font-bold transition inline-flex items-center space-x-1" title="{{ $file->original_name }}">
                                        <i class="fa-solid fa-cloud-arrow-down"></i>
                                        <span class="max-w-[100px] truncate">{{ $file->original_name }}</span>
                                    </a>
                                @empty
                                    @if ($sub->file_path)
                                    <a href="{{ asset('storage/' . $sub->file_path) }}" target="_blank" class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-2.5 py-1 rounded text-[10px] font-bold transition inline-flex items-center space-x-1">
                                        <i class="fa-solid fa-cloud-arrow-down"></i>
                                        <span>Unduh File</span>
                                    </a>
                                    @endif
                                @endforelse
                            </div>
                            <button type="button" onclick="openGradeModal({{ json_encode($sub) }}, '{{ $sub->user->name }}')" class="bg-secondary hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center space-x-1">
                                <i class="fa-solid fa-star"></i>
                                <span>{{ $sub->nilai !== null ? 'Re-Nilai' : 'Nilai' }}</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">Belum ada mahasiswa yang mengumpulkan tugas ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Input Nilai -->
<div id="grade-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-star text-secondary mr-2"></i>Koreksi Tugas Mahasiswa</h3>
            <button onclick="closeGradeModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="grade-form" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="sub-id">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Mahasiswa</label>
                <input type="text" id="sub-student-name" disabled class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nilai Tugas (Maks: {{ $tugas->poin_nilai }})</label>
                <input type="number" name="nilai" id="sub-nilai" required min="0" max="{{ $tugas->poin_nilai }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Feedback / Catatan Dosen (Opsional)</label>
                <textarea name="feedback_dosen" id="sub-feedback" rows="3" placeholder="Tuliskan saran perbaikan..." class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition"></textarea>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeGradeModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan Nilai</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    function openGradeModal(sub, name) {
        document.getElementById('sub-id').value = sub.id;
        document.getElementById('sub-student-name').value = name;
        document.getElementById('sub-nilai').value = sub.nilai || '';
        document.getElementById('sub-feedback').value = sub.feedback_dosen || '';
        document.getElementById('grade-modal').classList.remove('hidden');
    }
    function closeGradeModal() {
        document.getElementById('grade-modal').classList.add('hidden');
        document.getElementById('grade-form').reset();
    }

    document.getElementById('grade-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('sub-id').value;
        axios.post(`/dosen/e-learning/tugas/submissions/${id}/nilai`, new FormData(this))
            .then(res => {
                if (res.data.success) {
                    closeGradeModal();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => window.location.reload());
                }
            })
            .catch(err => {
                Swal.fire('Gagal', err.response.data.message || 'Gagal menyimpan nilai.', 'error');
            });
    });
</script>
@endsection
@endsection
