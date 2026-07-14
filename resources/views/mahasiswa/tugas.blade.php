@extends('layouts.mahasiswa')

@section('title', 'Tugas Kuliah - E-Learning STIKesMu')

@section('mahasiswa-content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Mahasiswa &gt; Tugas Kuliah</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Tugas Kuliah &amp; Evaluasi
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Daftar tugas mandiri dan terstruktur yang wajib dikerjakan.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse ($tugas as $t)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col justify-between space-y-4 hover:shadow-md transition">
            <div class="space-y-3">
                <div class="flex justify-between items-start">
                    <span class="inline-block px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-blue-50 text-primary border border-blue-100 uppercase font-mono">
                        {{ $t->course->code }}
                    </span>
                    <span class="text-xs font-bold font-mono {{ $t->my_submission ? 'text-green-600' : 'text-orange-600' }}">
                        {{ $t->my_submission ? 'Sudah Dikumpulkan' : 'Belum Dikumpulkan' }}
                    </span>
                </div>
                <h2 class="text-xl font-bold text-gray-900">{{ $t->judul }}</h2>
                <p class="text-xs text-gray-400">Batas Waktu: <strong class="text-red-600 font-semibold">{{ $t->deadline ? $t->deadline->format('d M Y H:i') : 'Tanpa Batas' }}</strong></p>
                <p class="text-sm text-gray-650">{{ $t->deskripsi ?? 'Tidak ada petunjuk khusus.' }}</p>

                @if ($t->my_submission)
                <div class="bg-green-50/50 p-4 rounded-xl border border-green-100 text-xs space-y-3.5 mt-4">
                    <div class="flex justify-between items-center border-b border-green-100 pb-1.5">
                        <h4 class="font-bold text-green-800">Catatan Pengumpulan Anda:</h4>
                        <div class="flex gap-1.5 font-mono text-[9px] font-extrabold uppercase">
                            @if ($t->my_submission->is_late)
                                <span class="bg-red-50 text-red-800 px-2 py-0.5 rounded border border-red-150">Terlambat</span>
                            @endif
                            @if ($t->my_submission->is_revision)
                                <span class="bg-blue-50 text-blue-800 px-2 py-0.5 rounded border border-blue-150">Revisi</span>
                            @endif
                        </div>
                    </div>
                    <p class="text-green-700 font-medium">{{ $t->my_submission->catatan ?? '-' }}</p>

                    @if(count($t->my_submission->files) > 0)
                        <div class="pt-1.5">
                            <span class="font-semibold text-gray-500 block mb-1">File Terlampir:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($t->my_submission->files as $file)
                                    <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="inline-flex items-center gap-1 bg-white border border-gray-200 hover:border-emerald-700 hover:text-emerald-800 px-2 py-1 rounded text-[10px] font-bold transition">
                                        <i class="fa-solid fa-file text-gray-400"></i>
                                        <span class="truncate max-w-[150px]">{{ $file->original_name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($t->my_submission->nilai !== null)
                    <div class="pt-2 border-t border-green-200/50 flex items-center justify-between text-sm">
                        <span class="font-bold text-green-800">Nilai:</span>
                        <span class="text-xl font-black text-green-700 font-mono">{{ $t->my_submission->nilai }} / {{ $t->poin_nilai }}</span>
                    </div>
                    @if ($t->my_submission->feedback_dosen)
                    <p class="text-[11px] text-gray-500 italic mt-1 border-t border-green-100 pt-1.5"><strong>Feedback Dosen:</strong> {{ $t->my_submission->feedback_dosen }}</p>
                    @endif
                    @else
                    <span class="inline-block text-[11px] text-gray-400 font-semibold italic mt-1">Belum dinilai oleh dosen</span>
                    @endif
                </div>
                @endif
            </div>

            <div class="pt-4 border-t border-gray-50">
                @if (!$t->my_submission || $t->my_submission->nilai === null)
                <button type="button" onclick="openSubmitModal({{ json_encode($t) }})" class="w-full bg-secondary bg-green-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs uppercase tracking-wider transition shadow-sm flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-file-arrow-up"></i>
                    <span>{{ $t->my_submission ? 'Revisi Jawaban' : 'Kirim Jawaban' }}</span>
                </button>
                @else
                <button disabled class="w-full bg-gray-100 text-gray-400 font-bold py-2.5 px-4 rounded-xl text-xs uppercase tracking-wider cursor-not-allowed flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Sudah Dinilai</span>
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-2 bg-white p-8 text-center text-gray-400 rounded-2xl border border-gray-100 shadow-sm">
            <i class="fa-solid fa-file-circle-exclamation text-4xl mb-3 text-gray-300"></i>
            <p class="text-xs">Belum ada tugas kuliah untuk kelas Anda.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Modal Submit Tugas -->
<div id="submit-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 id="tugas-judul" class="text-lg font-bold text-gray-900">Kirim Tugas</h3>
            <button onclick="closeSubmitModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="submit-form" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="tugas-id">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih File Jawaban (Bisa pilih lebih dari satu, Max: 10MB/file)</label>
                <input type="file" name="file_tugas[]" multiple class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary focus:border-secondary">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea name="catatan" rows="4" placeholder="Tulis catatan jika ada..." class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary focus:border-secondary text-sm transition"></textarea>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeSubmitModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">Kirim Tugas</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    function openSubmitModal(t) {
        document.getElementById('tugas-id').value = t.id;
        document.getElementById('tugas-judul').textContent = `Kirim Tugas: ${t.judul}`;
        document.getElementById('submit-modal').classList.remove('hidden');
        document.getElementById('submit-modal').classList.add('flex');
    }
    function closeSubmitModal() {
        const modal = document.getElementById('submit-modal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        document.getElementById('submit-form').reset();
    }

    document.getElementById('submit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('tugas-id').value;
        axios.post(`/mahasiswa/tugas/${id}/submit`, new FormData(this))
            .then(res => {
                if (res.data.success) {
                    closeSubmitModal();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => window.location.reload());
                }
            })
            .catch(err => {
                Swal.fire('Gagal', err.response.data.message || 'Gagal mengirim tugas.', 'error');
            });
    });
</script>
@endsection
@endsection
