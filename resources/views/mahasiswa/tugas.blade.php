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

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse ($tugas as $t)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-col justify-between hover:shadow-md transition duration-200">
            <div class="space-y-1.5">
                <div class="flex justify-between items-center gap-1.5 text-[9px]">
                    <span class="inline-block px-2 py-0.5 font-extrabold rounded bg-blue-50 text-primary border border-blue-100 uppercase font-mono truncate max-w-[80px]">
                        {{ $t->course->code }}
                    </span>
                    <span class="font-bold font-mono {{ $t->my_submission ? 'text-green-600' : 'text-orange-600' }}">
                        {{ $t->my_submission ? '✓ Terkumpul' : '✗ Belum' }}
                    </span>
                </div>
                
                <h3 class="text-sm font-bold text-gray-900 line-clamp-1 mt-2" title="{{ $t->judul }}">{{ $t->judul }}</h3>
                <span class="block text-[10px] text-gray-400">Dosen: {{ $t->user->name }}</span>
                
                <div class="text-[10px] text-slate-500 flex items-center gap-1">
                    <i class="fa-regular fa-clock text-slate-400"></i>
                    <span>Batas: <strong class="text-red-600 font-semibold">{{ $t->deadline ? $t->deadline->format('d M Y H:i') : 'Tanpa Batas' }}</strong></span>
                </div>
                
                <p class="text-xs text-gray-500 mt-2 line-clamp-2 min-h-[2.25rem] leading-relaxed">{{ $t->deskripsi ?? 'Tidak ada petunjuk khusus.' }}</p>

                @if ($t->my_submission)
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100 text-[10px] space-y-1.5 mt-3">
                    <div class="flex justify-between items-center border-b border-slate-150 pb-1">
                        <span class="font-bold text-slate-700">Status Anda:</span>
                        @if ($t->my_submission->nilai !== null)
                            <span class="font-black text-green-700 font-mono">Nilai: {{ $t->my_submission->nilai }}/{{ $t->poin_nilai }}</span>
                        @else
                            <span class="text-slate-400 italic font-medium">Belum dinilai</span>
                        @endif
                    </div>
                    
                    @if(count($t->my_submission->files) > 0)
                        <div class="pt-0.5">
                            <div class="flex flex-wrap gap-1">
                                @foreach($t->my_submission->files as $file)
                                    <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="inline-flex items-center gap-1 bg-white border border-gray-200 hover:border-emerald-700 hover:text-emerald-800 px-1.5 py-0.5 rounded text-[9px] font-bold transition">
                                        <i class="fa-solid fa-file text-gray-400 text-[8px]"></i>
                                        <span class="truncate max-w-[100px]">{{ $file->original_name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($t->my_submission->feedback_dosen)
                    <p class="text-[9px] text-gray-500 italic mt-1 border-t border-slate-150 pt-1"><strong>Feedback:</strong> {{ $t->my_submission->feedback_dosen }}</p>
                    @endif
                </div>
                @endif
            </div>

            <div class="pt-3 border-t border-gray-50 mt-4">
                @if (!$t->my_submission || $t->my_submission->nilai === null)
                <button type="button" onclick="openSubmitModal({{ json_encode($t) }})" class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-2 px-3 rounded-lg text-xs uppercase tracking-wider transition shadow-sm flex items-center justify-center space-x-1.5 cursor-pointer">
                    <i class="fa-solid fa-file-arrow-up text-[10px]"></i>
                    <span>{{ $t->my_submission ? 'Revisi' : 'Kirim Jawaban' }}</span>
                </button>
                @else
                <button disabled class="w-full bg-gray-100 text-gray-400 font-bold py-2 px-3 rounded-lg text-xs uppercase tracking-wider cursor-not-allowed flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                    <span>Selesai</span>
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white p-8 text-center text-gray-400 rounded-xl border border-gray-100 shadow-sm">
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
                <button type="submit" class="px-4 py-2 bg-secondary bg-green-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">Kirim Tugas</button>
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
