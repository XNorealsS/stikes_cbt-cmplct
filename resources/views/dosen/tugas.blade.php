@extends('layouts.dosen')

@section('title', 'Tugas Kuliah - CBT STIKES Muhammadiyah Lhokseumawe')

@section('dosen-content')
<div class="space-y-8">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Dosen &gt; Tugas E-Learning</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Tugas E-Learning
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Buat tugas mandiri/kelompok, tentukan tenggat waktu, dan kelola pengumpulan jawaban mahasiswa.</p>
            </div>
            <button type="button" onclick="openAddModal()" class="rounded border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-green-800 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                <i class="fa-solid fa-file-circle-plus text-xs"></i>
                <span>Buat Tugas Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter & Statistics -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <form method="GET" action="{{ route('dosen.tugas.index') }}" class="grid grid-cols-1 sm:grid-cols-3 items-end gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Mata Kuliah</label>
                <select name="course_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary focus:border-secondary text-sm transition">
                    @foreach ($courses as $c)
                    <option value="{{ $c->id }}" {{ $courseId == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Tasks List -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse ($tugas as $t)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col justify-between space-y-4 hover:shadow-md transition">
            <div class="space-y-3">
                <div class="flex justify-between items-start">
                    <span class="inline-block px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-blue-55 text-primary border border-blue-100 uppercase font-mono">
                        {{ $t->classRoom ? $t->classRoom->name : 'Semua Kelas' }}
                    </span>
                    <span class="text-xs text-gray-400 font-bold">
                        Poin Maks: {{ $t->poin_nilai }}
                    </span>
                </div>
                <h2 class="text-xl font-bold text-gray-900">{{ $t->judul }}</h2>
                <p class="text-xs text-red-500 font-semibold"><i class="fa-regular fa-clock mr-1"></i>Deadline: {{ $t->deadline ? $t->deadline->format('d M Y H:i') : 'Tanpa Batas' }}</p>
                <p class="text-sm text-gray-600 line-clamp-3">{{ $t->deskripsi ?? 'Tidak ada petunjuk khusus.' }}</p>
            </div>

            <div class="pt-4 border-t border-gray-50 flex items-center justify-between gap-4">
                <a href="{{ route('dosen.tugas.submissions', $t->id) }}" class="flex-grow bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-4 rounded-xl text-xs uppercase tracking-wider transition text-center shadow-sm">
                    Koreksi Jawaban ({{ $t->submissions_count }} Kumpulan)
                </a>
                <div class="flex space-x-2">
                    <button type="button" onclick="openEditModal({{ json_encode($t) }})" class="bg-yellow-50 hover:bg-yellow-100 text-yellow-700 p-2.5 rounded-xl text-xs font-bold transition">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button type="button" onclick="confirmDelete({{ $t->id }}, '{{ $t->judul }}')" class="bg-red-50 hover:bg-red-100 text-red-700 p-2.5 rounded-xl text-xs font-bold transition">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-2 bg-white p-8 text-center text-gray-400 rounded-2xl border border-gray-100 shadow-sm">
            <i class="fa-solid fa-file-excel text-4xl mb-3 text-gray-300"></i>
            <p class="text-xs">Belum ada tugas kuliah yang Anda buat untuk mata kuliah ini.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Modal Tambah -->
<div id="add-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-file-circle-plus text-secondary mr-2"></i>Buat Tugas Kuliah</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="add-form" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="course_id" value="{{ $courseId }}">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kelas Tujuan</label>
                    <select name="class_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
                        <option value="">Semua Kelas</option>
                        @foreach ($classes as $cl)
                        <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Poin Nilai Maks</label>
                    <input type="number" name="poin_nilai" required min="0" max="100" value="100" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Tugas</label>
                <input type="text" name="judul" required placeholder="Contoh: Laporan Studi Kasus KDP" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Petunjuk / Deskripsi Tugas</label>
                <textarea name="deskripsi" rows="4" required placeholder="Tuliskan petunjuk pengerjaan tugas secara mendalam..." class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Rilis (Tayang)</label>
                    <input type="date" name="tanggal_tayang" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tenggat Waktu (Deadline)</label>
                    <input type="datetime-local" name="deadline" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-pen-to-square text-secondary mr-2"></i>Edit Informasi Tugas</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="edit-form" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-id">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Tugas</label>
                <input type="text" id="edit-judul" name="judul" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Poin Nilai Maks</label>
                <input type="number" id="edit-poin" name="poin_nilai" required min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Petunjuk / Deskripsi Tugas</label>
                <textarea id="edit-deskripsi" name="deskripsi" rows="4" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Rilis (Tayang)</label>
                    <input type="date" id="edit-tayang" name="tanggal_tayang" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tenggat Waktu (Deadline)</label>
                    <input type="datetime-local" id="edit-deadline" name="deadline" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status Keaktifan</label>
                <select id="edit-is_aktif" name="is_aktif" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-secondary hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    function openAddModal() {
        document.getElementById('add-modal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('add-modal').classList.add('hidden');
        document.getElementById('add-form').reset();
    }
    function openEditModal(t) {
        document.getElementById('edit-id').value = t.id;
        document.getElementById('edit-judul').value = t.judul;
        document.getElementById('edit-poin').value = t.poin_nilai;
        document.getElementById('edit-deskripsi').value = t.deskripsi;
        document.getElementById('edit-tayang').value = t.tanggal_tayang ? t.tanggal_tayang.substring(0, 10) : '';
        document.getElementById('edit-deadline').value = t.deadline ? t.deadline.substring(0, 16) : '';
        document.getElementById('edit-is_aktif').value = t.is_aktif ? '1' : '0';
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
        document.getElementById('edit-form').reset();
    }

    document.getElementById('add-form').addEventListener('submit', function(e) {
        e.preventDefault();
        axios.post('/dosen/e-learning/tugas', new FormData(this))
            .then(res => {
                if (res.data.success) {
                    closeAddModal();
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
                Swal.fire('Gagal', err.response.data.message || 'Gagal menyimpan tugas.', 'error');
            });
    });

    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        axios.post(`/dosen/e-learning/tugas/${id}`, new FormData(this))
            .then(res => {
                if (res.data.success) {
                    closeEditModal();
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
                Swal.fire('Gagal', err.response.data.message || 'Gagal mengubah tugas.', 'error');
            });
    });

    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Hapus Tugas?',
            text: `Apakah Anda yakin ingin menghapus tugas "${nama}"? Semua submission mahasiswa akan ikut terhapus!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/dosen/e-learning/tugas/${id}`)
                    .then(res => {
                        if (res.data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: res.data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => window.location.reload());
                        }
                    })
                    .catch(err => {
                        Swal.fire('Gagal', err.response.data.message || 'Gagal menghapus.', 'error');
                    });
            }
        });
    }
</script>
@endsection
@endsection
