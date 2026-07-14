@extends('layouts.admin')

@section('title', 'Tahun Akademik - CBT STIKES Muhammadiyah Lhokseumawe')

@section('admin-content')
<div class="space-y-8">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Admin &gt; Tahun Akademik</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Tahun Akademik
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola tahun akademik aktif dan semester untuk pelaksanaan ujian.</p>
            </div>
            <button type="button" onclick="openAddModal()" class="rounded border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-green-800 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Tahun Akademik</span>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="py-4 px-6">Tahun Akademik</th>
                        <th class="py-4 px-6">Tahun Mulai</th>
                        <th class="py-4 px-6">Semester</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($tahunAkademik as $ta)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="py-4 px-6 font-semibold text-gray-900">{{ $ta->nama }}</td>
                        <td class="py-4 px-6 font-mono">{{ $ta->tahun_mulai }}</td>
                        <td class="py-4 px-6 capitalize">{{ $ta->semester }}</td>
                        <td class="py-4 px-6 text-center">
                            @if ($ta->is_aktif)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                Tidak Aktif
                            </span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right space-x-2">
                            @if (!$ta->is_aktif)
                            <button type="button" onclick="setAktif({{ $ta->id }}, '{{ $ta->nama }}')" class="bg-green-50 hover:bg-green-100 text-green-700 px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150 inline-flex items-center space-x-1">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Aktifkan</span>
                            </button>
                            @endif
                            <button type="button" onclick="openEditModal({{ json_encode($ta) }})" class="bg-yellow-50 hover:bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150 inline-flex items-center space-x-1">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>Edit</span>
                            </button>
                            @if (!$ta->is_aktif)
                            <button type="button" onclick="confirmDelete({{ $ta->id }}, '{{ $ta->nama }}')" class="bg-red-50 hover:bg-red-100 text-red-700 px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150 inline-flex items-center space-x-1">
                                <i class="fa-solid fa-trash-can"></i>
                                <span>Hapus</span>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">Tidak ada data tahun akademik.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div id="add-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-calendar-plus text-primary mr-2"></i>Tambah Tahun Akademik</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="add-form" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun Mulai</label>
                <input type="number" name="tahun_mulai" required placeholder="Contoh: 2025" min="2000" max="2099" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Semester</label>
                <select name="semester" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                    <option value="ganjil">Ganjil</option>
                    <option value="genap">Genap</option>
                </select>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-calendar-check text-primary mr-2"></i>Edit Tahun Akademik</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="edit-form" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-id">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun Mulai</label>
                <input type="number" id="edit-tahun_mulai" name="tahun_mulai" required min="2000" max="2099" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Semester</label>
                <select id="edit-semester" name="semester" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                    <option value="ganjil">Ganjil</option>
                    <option value="genap">Genap</option>
                </select>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan</button>
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
