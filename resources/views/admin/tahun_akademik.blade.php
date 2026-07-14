@extends('layouts.admin')

@section('title', 'Tahun Akademik - CBT STIKES Muhammadiyah Lhokseumawe')

@section('admin-content')
<div class="space-y-5">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-header-title">
                <span class="title-bar"></span>
                Tahun Akademik
            </h1>
            <p class="page-header-subtitle">Kelola tahun akademik aktif dan semester untuk pelaksanaan ujian CBT.</p>
        </div>
        <button type="button" onclick="openAddModal()" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-900 text-white px-4 py-2 text-xs font-bold uppercase tracking-wider transition-all active:scale-95 cursor-pointer shadow-sm" style="border-radius: var(--radius-md);">
            <i class="fa-solid fa-plus"></i>
            Tambah Tahun Akademik
        </button>
    </div>

    <!-- Table Container -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr>
                        <th>Tahun Akademik</th>
                        <th>Tahun Mulai</th>
                        <th>Semester</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tahunAkademik as $ta)
                    <tr>
                        <td class="font-semibold text-slate-900">{{ $ta->nama }}</td>
                        <td class="font-mono text-slate-600">{{ $ta->tahun_mulai }}</td>
                        <td class="capitalize text-slate-600">{{ $ta->semester }}</td>
                        <td class="text-center">
                            @if ($ta->is_aktif)
                            <span class="badge badge-success">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                Aktif
                            </span>
                            @else
                            <span class="badge badge-neutral">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if (!$ta->is_aktif)
                                <button type="button" onclick="setAktif({{ $ta->id }}, '{{ $ta->nama }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-50 hover:bg-primary-100 text-primary-700 text-[11px] font-bold transition-colors" style="border-radius: var(--radius-sm);">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i> Aktifkan
                                </button>
                                @endif
                                <button type="button" onclick="openEditModal({{ json_encode($ta) }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-[11px] font-bold transition-colors" style="border-radius: var(--radius-sm);">
                                    <i class="fa-solid fa-pen-to-square text-[10px]"></i> Edit
                                </button>
                                @if (!$ta->is_aktif)
                                <button type="button" onclick="confirmDelete({{ $ta->id }}, '{{ $ta->nama }}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 text-[11px] font-bold transition-colors" style="border-radius: var(--radius-sm);">
                                    <i class="fa-solid fa-trash-can text-[10px]"></i> Hapus
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <i class="fa-regular fa-calendar-xmark text-2xl text-slate-300"></i>
                                <p class="text-sm text-slate-400 font-medium">Belum ada data tahun akademik</p>
                                <p class="text-xs text-slate-400">Klik "Tambah Tahun Akademik" untuk memulai.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div id="add-modal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white max-w-md w-full overflow-hidden" style="border-radius: var(--radius-xl); box-shadow: var(--shadow-xl);">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-calendar-plus text-primary-700"></i>
                Tambah Tahun Akademik
            </h3>
            <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600 w-7 h-7 flex items-center justify-center hover:bg-slate-100 transition" style="border-radius: var(--radius-sm);">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="add-form" class="flex flex-col max-h-[85vh]">
            @csrf
            <div class="p-6 space-y-4 overflow-y-auto">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Tahun Mulai</label>
                    <input type="number" name="tahun_mulai" required placeholder="Contoh: 2025" min="2000" max="2099" class="w-full">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Semester</label>
                    <select name="semester" required class="w-full">
                        <option value="ganjil">Ganjil</option>
                        <option value="genap">Genap</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 flex justify-end gap-2 border-t border-slate-100 bg-slate-50 flex-shrink-0">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-slate-300 text-slate-600 text-xs font-semibold hover:bg-slate-100 transition cursor-pointer" style="border-radius: var(--radius-md);">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-900 text-white text-xs font-bold transition cursor-pointer" style="border-radius: var(--radius-md);">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white max-w-md w-full overflow-hidden" style="border-radius: var(--radius-xl); box-shadow: var(--shadow-xl);">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-calendar-check text-primary-700"></i>
                Edit Tahun Akademik
            </h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 w-7 h-7 flex items-center justify-center hover:bg-slate-100 transition" style="border-radius: var(--radius-sm);">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="edit-form" class="flex flex-col max-h-[85vh]">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-4 overflow-y-auto">
                <input type="hidden" id="edit-id">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Tahun Mulai</label>
                    <input type="number" id="edit-tahun_mulai" name="tahun_mulai" required min="2000" max="2099" class="w-full">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Semester</label>
                    <select id="edit-semester" name="semester" required class="w-full">
                        <option value="ganjil">Ganjil</option>
                        <option value="genap">Genap</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 flex justify-end gap-2 border-t border-slate-100 bg-slate-50 flex-shrink-0">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-300 text-slate-600 text-xs font-semibold hover:bg-slate-100 transition cursor-pointer" style="border-radius: var(--radius-md);">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-900 text-white text-xs font-bold transition cursor-pointer" style="border-radius: var(--radius-md);">Simpan</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    // Modal Helpers
    function openAddModal() {
        document.getElementById('add-modal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('add-modal').classList.add('hidden');
        document.getElementById('add-form').reset();
    }
    function openEditModal(ta) {
        document.getElementById('edit-id').value = ta.id;
        document.getElementById('edit-tahun_mulai').value = ta.tahun_mulai;
        document.getElementById('edit-semester').value = ta.semester;
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
        document.getElementById('edit-form').reset();
    }

    // Submit Add
    document.getElementById('add-form').addEventListener('submit', function(e) {
        e.preventDefault();
        axios.post('/admin/tahun-akademik', new FormData(this))
            .then(res => {
                if (res.data.success) {
                    closeAddModal();
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
                Swal.fire('Gagal', err.response.data.message || 'Gagal menambahkan data.', 'error');
            });
    });

    // Submit Edit
    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        axios.post(`/admin/tahun-akademik/${id}`, new FormData(this))
            .then(res => {
                if (res.data.success) {
                    closeEditModal();
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
                Swal.fire('Gagal', err.response.data.message || 'Gagal mengubah data.', 'error');
            });
    });

    // Set Aktif
    function setAktif(id, nama) {
        Swal.fire({
            title: 'Aktifkan Tahun Akademik?',
            text: `Apakah Anda yakin ingin mengaktifkan tahun akademik "${nama}"? Tahun akademik lainnya akan dinonaktifkan.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#14532d',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Ya, Aktifkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post(`/admin/tahun-akademik/${id}/set-aktif`)
                    .then(res => {
                        if (res.data.success) {
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
                        Swal.fire('Gagal', 'Gagal menetapkan status aktif.', 'error');
                    });
            }
        });
    }

    // Delete
    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Hapus Tahun Akademik?',
            text: `Apakah Anda yakin ingin menghapus "${nama}"? Tindakan ini tidak bisa dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/admin/tahun-akademik/${id}`)
                    .then(res => {
                        if (res.data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: res.data.message,
                                showConfirmButton: false,
                                timer: 1500
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
