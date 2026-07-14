@extends('layouts.dosen')

@section('title', 'Tugas Kuliah - E-Learning STIKesMu')

@section('dosen-content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Dosen &gt; Tugas E-Learning</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Kelola Tugas E-Learning
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Buat tugas mandiri/kelompok, tentukan tenggat waktu, dan kelola pengumpulan jawaban mahasiswa.</p>
            </div>
            <button type="button" onclick="openAddModal()" class="rounded-sm border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-green-800 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                <i class="fa-solid fa-file-circle-plus text-xs"></i>
                <span>Buat Tugas Baru</span>
            </button>
        </div>
    </div>

    <!-- Unified Table & Filter Card -->
    <div class="bg-white border border-gray-150 shadow-sm overflow-hidden">
        
        <!-- Table Header & Filter Row -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 border-b border-gray-150 bg-slate-50/50">
            <div>
                <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest flex items-center">
                    <i class="fa-solid fa-list-check text-primary mr-2"></i> Daftar Tugas Kuliah
                </h3>
            </div>
            <div>
                <form method="GET" action="{{ route('dosen.tugas.index') }}" class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mata Kuliah:</span>
                    <select name="course_id" onchange="this.form.submit()" class="border border-slate-300 rounded-sm px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary text-xs bg-white font-semibold transition cursor-pointer text-slate-700">
                        @foreach ($courses as $c)
                        <option value="{{ $c->id }}" {{ $courseId == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-150">
                        <th class="py-4 px-6 w-36">Kelas Tujuan</th>
                        <th class="py-4 px-6">Judul & Petunjuk Tugas</th>
                        <th class="py-4 px-6 w-24 text-center">Poin Maks</th>
                        <th class="py-4 px-6 w-44">Tenggat Waktu</th>
                        <th class="py-4 px-6 w-36 text-center">Jawaban Masuk</th>
                        <th class="py-4 px-6 text-right w-56">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($tugas as $t)
                    <tr class="hover:bg-gray-50/70 transition duration-150">
                        <td class="py-4 px-6">
                            <span class="inline-block px-2.5 py-0.5 text-[9px] font-extrabold rounded-full bg-blue-50 text-primary border border-blue-100 uppercase font-mono">
                                {{ $t->classRoom ? $t->classRoom->name : 'Semua Kelas' }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="space-y-0.5">
                                <span class="font-bold text-gray-900 block leading-snug">{{ $t->judul }}</span>
                                @if($t->deskripsi)
                                    <p class="text-xs text-gray-400 line-clamp-1 font-normal leading-relaxed">{{ $t->deskripsi }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="py-4 px-6 text-center font-bold text-slate-700 font-mono">
                            {{ $t->poin_nilai }}
                        </td>
                        <td class="py-4 px-6">
                            <span class="text-xs font-semibold {{ $t->deadline && now()->greaterThan($t->deadline) ? 'text-red-600' : 'text-slate-650' }} flex items-center gap-1">
                                <i class="fa-regular fa-clock text-[10px]"></i>
                                {{ $t->deadline ? $t->deadline->format('d M Y H:i') : 'Tanpa Batas' }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-bold rounded border {{ $t->submissions_count > 0 ? 'bg-emerald-50 text-emerald-800 border-emerald-100' : 'bg-slate-50 text-slate-500 border border-slate-100' }}">
                                <i class="fa-solid fa-file-invoice text-[9px]"></i> {{ $t->submissions_count }} Kumpulan
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <div class="inline-flex items-center gap-1.5 justify-end">
                                <a href="{{ route('dosen.tugas.submissions', $t->id) }}" class="h-8 inline-flex items-center justify-center gap-1.5 px-3 bg-primary hover:bg-primary-900 text-white text-[11px] font-bold transition rounded-sm uppercase tracking-wide">
                                    <i class="fa-solid fa-list-check text-[9px]"></i> Koreksi
                                </a>
                                <button type="button" onclick="openEditModal({{ json_encode($t) }})" class="h-8 w-8 inline-flex items-center justify-center border border-slate-300 text-slate-700 hover:bg-slate-50 transition rounded-sm cursor-pointer shadow-sm">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button" onclick="confirmDelete({{ $t->id }}, '{{ $t->judul }}')" class="h-8 w-8 inline-flex items-center justify-center border border-red-200 text-red-600 hover:bg-red-50 transition rounded-sm cursor-pointer shadow-sm">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-gray-450">
                            <i class="fa-solid fa-file-excel text-4xl mb-3 text-gray-300 block"></i>
                            <p class="text-xs font-semibold">Belum ada tugas kuliah yang dibuat untuk mata kuliah ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah (Flat) -->
<div id="add-modal" class="fixed inset-0 z-50 bg-black/55 hidden flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeAddModal()"></div>

    <!-- Modal Content -->
    <div class="bg-white border border-slate-200 w-full max-w-lg shadow-2xl relative z-10 overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider"><i class="fa-solid fa-file-circle-plus text-primary mr-2"></i>Buat Tugas Kuliah</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-650 cursor-pointer border-0 bg-transparent"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="add-form" class="p-6 space-y-4 text-xs">
            @csrf
            <input type="hidden" name="course_id" value="{{ $courseId }}">
            
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-bold text-slate-500 uppercase tracking-wider">Kelas Tujuan</label>
                    <select name="class_id" class="w-full border border-gray-350 rounded-sm px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary text-sm transition bg-white">
                        <option value="">Semua Kelas</option>
                        @foreach ($classes as $cl)
                        <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-500 uppercase tracking-wider">Poin Nilai Maks</label>
                    <input type="number" name="poin_nilai" required min="0" max="100" value="100" class="w-full border border-gray-355 rounded-sm px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary text-sm transition">
                </div>
            </div>

            <div class="space-y-1">
                <label class="block font-bold text-slate-500 uppercase tracking-wider">Judul Tugas</label>
                <input type="text" name="judul" required placeholder="Contoh: Laporan Studi Kasus KDP" class="w-full border border-gray-355 rounded-sm px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary text-sm transition">
            </div>

            <div class="space-y-1">
                <label class="block font-bold text-slate-500 uppercase tracking-wider">Petunjuk / Deskripsi Tugas</label>
                <textarea name="deskripsi" rows="4" required placeholder="Tuliskan petunjuk pengerjaan tugas secara mendalam..." class="w-full border border-gray-355 rounded-sm px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary text-sm transition"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-bold text-slate-500 uppercase tracking-wider">Tanggal Rilis (Tayang)</label>
                    <input type="date" name="tanggal_tayang" class="w-full border border-gray-355 rounded-sm px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary text-sm transition">
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-500 uppercase tracking-wider">Tenggat Waktu (Deadline)</label>
                    <input type="datetime-local" name="deadline" class="w-full border border-gray-355 rounded-sm px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary text-sm transition">
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-2.5 border-t border-gray-150">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-sm text-xs font-bold hover:bg-slate-50 transition cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-900 text-white rounded-sm text-xs font-bold transition shadow-sm uppercase tracking-wider cursor-pointer">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit (Flat) -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-black/55 hidden flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeEditModal()"></div>

    <!-- Modal Content -->
    <div class="bg-white border border-slate-200 w-full max-w-lg shadow-2xl relative z-10 overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider"><i class="fa-solid fa-pen-to-square text-primary mr-2"></i>Edit Informasi Tugas</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-655 cursor-pointer border-0 bg-transparent"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="edit-form" class="p-6 space-y-4 text-xs">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-id">
            
            <div class="space-y-1">
                <label class="block font-bold text-slate-500 uppercase tracking-wider">Judul Tugas</label>
                <input type="text" id="edit-judul" name="judul" required class="w-full border border-gray-355 rounded-sm px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary text-sm transition">
            </div>

            <div class="space-y-1">
                <label class="block font-bold text-slate-500 uppercase tracking-wider">Poin Nilai Maks</label>
                <input type="number" id="edit-poin" name="poin_nilai" required min="0" max="100" class="w-full border border-gray-355 rounded-sm px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary text-sm transition">
            </div>

            <div class="space-y-1">
                <label class="block font-bold text-slate-500 uppercase tracking-wider">Petunjuk / Deskripsi Tugas</label>
                <textarea id="edit-deskripsi" name="deskripsi" rows="4" required class="w-full border border-gray-355 rounded-sm px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary text-sm transition"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-bold text-slate-500 uppercase tracking-wider">Tanggal Rilis (Tayang)</label>
                    <input type="date" id="edit-tayang" name="tanggal_tayang" class="w-full border border-gray-355 rounded-sm px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary text-sm transition">
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-500 uppercase tracking-wider">Tenggat Waktu (Deadline)</label>
                    <input type="datetime-local" id="edit-deadline" name="deadline" class="w-full border border-gray-355 rounded-sm px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary text-sm transition">
                </div>
            </div>

            <div class="space-y-1">
                <label class="block font-bold text-slate-500 uppercase tracking-wider">Status Keaktifan</label>
                <select id="edit-is_aktif" name="is_aktif" required class="w-full border border-gray-355 rounded-sm px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary text-sm transition bg-white">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <div class="pt-4 flex justify-end space-x-2.5 border-t border-gray-150">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-sm text-xs font-bold hover:bg-slate-50 transition cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-900 text-white rounded-sm text-xs font-bold transition shadow-sm uppercase tracking-wider cursor-pointer">Simpan</button>
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
