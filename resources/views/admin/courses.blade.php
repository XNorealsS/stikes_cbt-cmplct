@extends('layouts.admin')

@section('title', 'Mata Kuliah Master - CBT STIKES Muhammadiyah Lhokseumawe')

@section('admin-content')
<div class="space-y-8">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Admin &gt; Master Mata Kuliah</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Master Mata Kuliah
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola data mata kuliah yang diujikan dalam sistem CBT.</p>
            </div>
            <button type="button" onclick="openAddModal()" class="rounded border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-green-800 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Mata Kuliah</span>
            </button>
        </div>
    </div>

    <!-- Courses Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" style="min-width: 580px;">
                <thead>
                    <tr class="bg-green-700 text-xs font-bold text-white uppercase tracking-wider">
                        <th class="py-3 px-4">Kode</th>
                        <th class="py-3 px-4">Nama Mata Kuliah</th>
                        <th class="py-3 px-4">Deskripsi</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($courses as $course)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="py-3 px-4 font-mono font-bold text-green-700 text-xs whitespace-nowrap">{{ $course->code }}</td>
                        <td class="py-3 px-4 font-semibold text-gray-900">{{ $course->name }}</td>
                        <td class="py-3 px-4 text-gray-500 text-xs">{{ $course->description ?? '-' }}</td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5 flex-nowrap">
                                <button type="button" onclick="openEditModal({{ json_encode($course) }})" class="bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 px-2.5 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 whitespace-nowrap" title="Edit">
                                    <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                    <span>Edit</span>
                                </button>
                                <button type="button" onclick="confirmDelete({{ $course->id }}, '{{ $course->name }}')" class="bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 px-2.5 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 whitespace-nowrap" title="Hapus">
                                    <i class="fa-solid fa-trash-can text-[11px]"></i>
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-400">Tidak ada data mata kuliah.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Mata Kuliah -->
<div id="add-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-book-medical text-primary mr-2"></i>Tambah Mata Kuliah</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="add-form" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Mata Kuliah</label>
                <input type="text" name="code" required placeholder="Contoh: MK001" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Mata Kuliah</label>
                <input type="text" name="name" required placeholder="Contoh: Keperawatan Dasar" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
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

<!-- Modal Edit Mata Kuliah -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-book-open-reader text-primary mr-2"></i>Edit Mata Kuliah</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="edit-form" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-id">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Mata Kuliah</label>
                <input type="text" id="edit-code" name="code" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Mata Kuliah</label>
                <input type="text" id="edit-name" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi</label>
                <textarea id="edit-description" name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition"></textarea>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan</button>
            </div>
        </form>
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
    function openEditModal(course) {
        document.getElementById('edit-id').value = course.id;
        document.getElementById('edit-code').value = course.code;
        document.getElementById('edit-name').value = course.name;
        document.getElementById('edit-description').value = course.description || '';

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
        
        axios.post("{{ route('admin.courses.store') }}", formData)
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
                    : 'Gagal menambahkan mata kuliah.';
                showError(msg);
            });
    });

    // Edit Form Submit
    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        const code = document.getElementById('edit-code').value;
        const name = document.getElementById('edit-name').value;
        const description = document.getElementById('edit-description').value;

        axios.put(`/admin/mata-kuliah/${id}`, {
            code: code,
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
                : 'Gagal memperbarui mata kuliah.';
            showError(msg);
        });
    });

    // Delete confirm
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Mata Kuliah?',
            text: `Anda akan menghapus mata kuliah "${name}". Semua soal dan sesi ujian terkait juga akan terhapus!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/admin/mata-kuliah/${id}`)
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
                            : 'Gagal menghapus mata kuliah.';
                        showError(msg);
                    });
            }
        });
    }
</script>
@endsection
