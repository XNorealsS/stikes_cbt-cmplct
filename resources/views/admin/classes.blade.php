@extends('layouts.admin')

@section('title', 'Data Kelas Master - CBT STIKES Muhammadiyah Lhokseumawe')

@section('admin-content')
<div class="space-y-8">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Admin &gt; Master Kelas</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Master Kelas Akademik
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola data kelas untuk pengelompokan mahasiswa.</p>
            </div>
            <button type="button" onclick="openAddModal()" class="rounded border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-green-800 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Kelas</span>
            </button>
        </div>
    </div>

    <!-- Classes Table -->
    <div class="bg-white  border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="py-4 px-6 w-1/3">Nama Kelas</th>
                        <th class="py-4 px-6">Deskripsi</th>
                        <th class="py-4 px-6 w-1/6 text-center">Jumlah Mahasiswa</th>
                        <th class="py-4 px-6 text-right w-1/4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($classes as $class)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="py-4 px-6 font-semibold text-gray-900">{{ $class->name }}</td>
                        <td class="py-4 px-6 text-gray-500 text-xs md:text-sm">{{ $class->description ?? '-' }}</td>
                        <td class="py-4 px-6 text-center font-bold text-gray-700">
                            <span class="px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 text-xs">
                                {{ $class->users_count ?? $class->users->count() }} Orang
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right space-x-2">
                            <button type="button" onclick="viewStudents({{ $class->id }})" class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150 inline-flex items-center space-x-1">
                                <i class="fa-solid fa-users"></i>
                                <span>Siswa</span>
                            </button>
                            <button type="button" onclick="openEditModal({{ json_encode($class) }})" class="bg-yellow-50 hover:bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150 inline-flex items-center space-x-1">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>Edit</span>
                            </button>
                            <button type="button" onclick="confirmDelete({{ $class->id }}, '{{ $class->name }}')" class="bg-red-50 hover:bg-red-100 text-red-700 px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150 inline-flex items-center space-x-1">
                                <i class="fa-solid fa-trash-can"></i>
                                <span>Hapus</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-400">Tidak ada data kelas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Kelas -->
<div id="add-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-school text-primary mr-2"></i>Tambah Kelas</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="add-form" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Kelas</label>
                <input type="text" name="name" required placeholder="Contoh: Tingkat I - Kelas A" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi (Opsional)</label>
                <textarea name="description" rows="3" placeholder="Keterangan singkat..." class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition"></textarea>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Kelas -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-pen-to-square text-primary mr-2"></i>Edit Kelas</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="edit-form" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-id">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Kelas</label>
                <input type="text" id="edit-name" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi (Opsional)</label>
                <textarea id="edit-description" name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition"></textarea>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail Mahasiswa -->
<div id="students-modal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-[16px] max-w-2xl w-full shadow-lg overflow-hidden transform scale-95 transition-all duration-200">
        <div class="px-6 py-4 border-b border-neutral-300 flex justify-between items-center bg-white">
            <h3 class="text-base font-semibold text-neutral-900"><i class="fa-solid fa-users text-[#14532D] mr-2"></i>Daftar Mahasiswa - <span id="modal-class-name">-</span></h3>
            <button onclick="closeStudentsModal()" class="w-8 h-8 rounded-full hover:bg-neutral-100 flex items-center justify-center text-neutral-500 cursor-pointer"><i class="fa-solid fa-xmark text-base"></i></button>
        </div>
        <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
            <div class="overflow-hidden border border-neutral-300 rounded-[12px] bg-white">
                <table class="w-full text-left text-xs text-neutral-700">
                    <thead>
                        <tr class="bg-neutral-50 border-b border-neutral-300">
                            <th class="px-4 py-3 font-semibold text-neutral-500 uppercase tracking-wider">Nama</th>
                            <th class="px-4 py-3 font-semibold text-neutral-500 uppercase tracking-wider font-mono">NIM</th>
                            <th class="px-4 py-3 font-semibold text-neutral-500 uppercase tracking-wider">Email</th>
                        </tr>
                    </thead>
                    <tbody id="modal-students-body" class="divide-y divide-neutral-300">
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-neutral-500">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-300 flex justify-end">
            <button type="button" onclick="closeStudentsModal()" class="px-5 h-10 rounded-[12px] border border-[#14532D] text-[#14532D] text-xs font-semibold hover:bg-[#F3FAF5] active:scale-[0.98] transition cursor-pointer">Tutup</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Add Modal functions
    function openAddModal() {
        const modal = document.getElementById('add-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.querySelector('div').classList.remove('scale-95');
            modal.querySelector('div').classList.add('scale-100');
        }, 10);
    }
    
    function closeAddModal() {
        const modal = document.getElementById('add-modal');
        modal.querySelector('div').classList.remove('scale-100');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.getElementById('add-form').reset();
        }, 200);
    }

    // Edit Modal functions
    function openEditModal(classroom) {
        document.getElementById('edit-id').value = classroom.id;
        document.getElementById('edit-name').value = classroom.name;
        document.getElementById('edit-description').value = classroom.description || '';

        const modal = document.getElementById('edit-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.querySelector('div').classList.remove('scale-95');
            modal.querySelector('div').classList.add('scale-100');
        }, 10);
    }

    function closeEditModal() {
        const modal = document.getElementById('edit-modal');
        modal.querySelector('div').classList.remove('scale-100');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.getElementById('edit-form').reset();
        }, 200);
    }

    // Add Form Submit
    document.getElementById('add-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        axios.post("{{ route('admin.classes.store') }}", formData)
            .then(res => {
                if (res.data.success) {
                    closeAddModal();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                }
            })
            .catch(err => {
                const msg = err.response && err.response.data && err.response.data.message 
                    ? err.response.data.message 
                    : 'Gagal menambahkan kelas.';
                showError(msg);
            });
    });

    // Edit Form Submit
    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        const name = document.getElementById('edit-name').value;
        const description = document.getElementById('edit-description').value;

        axios.put(`/admin/kelas/${id}`, {
            name: name,
            description: description
        })
        .then(res => {
            if (res.data.success) {
                closeEditModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.reload();
                });
            }
        })
        .catch(err => {
            const msg = err.response && err.response.data && err.response.data.message 
                ? err.response.data.message 
                : 'Gagal memperbarui kelas.';
            showError(msg);
        });
    });

    // Delete confirm
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Kelas?',
            text: `Anda akan menghapus kelas "${name}". Mahasiswa di kelas ini akan kehilangan asosiasi kelas mereka!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/admin/kelas/${id}`)
                    .then(res => {
                        if (res.data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: res.data.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    })
                    .catch(err => {
                        const msg = err.response && err.response.data && err.response.data.message 
                            ? err.response.data.message 
                            : 'Gagal menghapus kelas.';
                        showError(msg);
                    });
            }
        });
    }

    function viewStudents(classId) {
        // Show modal and loading state
        const modal = document.getElementById('students-modal');
        document.getElementById('modal-class-name').textContent = 'Loading...';
        document.getElementById('modal-students-body').innerHTML = `
            <tr>
                <td colspan="3" class="px-3 py-4 text-center text-slate-400">
                    <i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Memuat data...
                </td>
            </tr>
        `;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.querySelector('div').classList.remove('scale-95');
            modal.querySelector('div').classList.add('scale-100');
        }, 10);

        axios.get(`/admin/kelas/${classId}/students`)
            .then(res => {
                if (res.data.success) {
                    document.getElementById('modal-class-name').textContent = res.data.class_name;
                    const students = res.data.students;
                    let html = '';
                    if (students.length === 0) {
                        html = `<tr><td colspan="3" class="px-3 py-4 text-center text-slate-400">Tidak ada mahasiswa terdaftar di kelas ini.</td></tr>`;
                    } else {
                        students.forEach(s => {
                            html += `
                                <tr class="hover:bg-slate-50">
                                    <td class="border-r border-slate-100 px-3 py-2 font-semibold text-slate-700">${s.name}</td>
                                    <td class="border-r border-slate-100 px-3 py-2 font-mono text-slate-500">${s.username}</td>
                                    <td class="px-3 py-2 text-slate-500">${s.email}</td>
                                </tr>
                            `;
                        });
                    }
                    document.getElementById('modal-students-body').innerHTML = html;
                }
            })
            .catch(err => {
                showError('Gagal memuat daftar mahasiswa.');
                closeStudentsModal();
            });
    }

    function closeStudentsModal() {
        const modal = document.getElementById('students-modal');
        modal.querySelector('div').classList.remove('scale-100');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 200);
    }
</script>
@endsection
