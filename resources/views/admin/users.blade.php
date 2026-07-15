@extends('layouts.admin')

@section('title', 'Manajemen User - CBT STIKES Muhammadiyah Lhokseumawe')

@section('admin-content')
<div class="space-y-8">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Admin &gt; Data Pengguna</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Manajemen Data Pengguna
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola data dosen, mahasiswa, dan administrator sistem.</p>
            </div>
            <button type="button" onclick="openAddModal()" class="rounded border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-green-800 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Pengguna</span>
            </button>
        </div>
    </div>

    <!-- Role Filter Tabs -->
    <div class="border-b border-gray-200 overflow-x-auto scrollbar-none">
        <nav class="flex space-x-4 sm:space-x-8 whitespace-nowrap px-1">
            <a href="{{ route('admin.users.index', ['role' => 'dosen', 'search' => request('search')]) }}" class="border-b-2 py-4 px-1 text-sm font-semibold transition duration-200 {{ $role === 'dosen' ? 'border-green-700 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="fa-solid fa-user-tie mr-2"></i>Dosen
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'mahasiswa', 'search' => request('search')]) }}" class="border-b-2 py-4 px-1 text-sm font-semibold transition duration-200 {{ $role === 'mahasiswa' ? 'border-green-700 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="fa-solid fa-user-graduate mr-2"></i>Mahasiswa
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'admin', 'search' => request('search')]) }}" class="border-b-2 py-4 px-1 text-sm font-semibold transition duration-200 {{ $role === 'admin' ? 'border-green-700 text-green-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="fa-solid fa-user-shield mr-2"></i>Administrator
            </a>
        </nav>
    </div>

    @if(request('search'))
        <div class="bg-blue-50 border border-blue-100 text-blue-800 px-4 py-3 rounded-xl text-xs flex justify-between items-center font-semibold">
            <span>Menampilkan hasil pencarian untuk: <strong class="text-blue-950">"{{ request('search') }}"</strong></span>
            <a href="{{ route('admin.users.index', ['role' => $role]) }}" class="text-blue-600 hover:text-blue-800 hover:underline">Hapus Pencarian</a>
        </div>
    @endif
 
    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" style="min-width: 720px;">
                <thead>
                    <tr class="bg-green-700 text-xs font-bold text-white uppercase tracking-wider">
                        <th class="py-3 px-4">Identitas Pengguna</th>
                        <th class="py-3 px-4">Username (NIM/NIP)</th>
                        <th class="py-3 px-4">Email</th>
                        @if ($role === 'mahasiswa')
                        <th class="py-3 px-4">Kelas</th>
                        @endif
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($users as $user)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="py-3 px-4 font-bold text-gray-900">{{ $user->name }}</td>
                        <td class="py-3 px-4 font-mono text-gray-600">{{ $user->username }}</td>
                        <td class="py-3 px-4 text-gray-500">{{ $user->email }}</td>
                        @if ($role === 'mahasiswa')
                        <td class="py-3 px-4 text-gray-500 font-semibold">{{ $user->classRoom->name ?? '-' }}</td>
                        @endif
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-blue-50 text-primary uppercase whitespace-nowrap">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5 flex-nowrap">
                                <button type="button" onclick="openEditModal({{ json_encode($user) }})" class="bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 px-2.5 py-1.5 rounded-lg text-xs font-bold transition duration-150 inline-flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>
                                @if ($user->id !== auth()->id())
                                <button type="button" onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')" class="bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 px-2.5 py-1.5 rounded-lg text-xs font-bold transition duration-150 inline-flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Hapus</span>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">Tidak ada data pengguna.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengguna -->
<div id="add-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-user-plus text-primary mr-2"></i>Tambah Pengguna</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="add-form" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="role" value="{{ $role }}">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Username (NIM/NIP)</label>
                <input type="text" name="username" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            @if ($role === 'mahasiswa')
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kelas</label>
                <select name="class_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kata Sandi</label>
                <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition" placeholder="Minimal 6 karakter">
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pengguna -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-user-pen text-primary mr-2"></i>Edit Pengguna</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="edit-form" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-id">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" id="edit-name" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Username (NIM/NIP)</label>
                <input type="text" id="edit-username" name="username" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" id="edit-email" name="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            @if ($role === 'mahasiswa')
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kelas</label>
                <select id="edit-class-id" name="class_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kata Sandi Baru (Opsional)</label>
                <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition" placeholder="Kosongkan jika tidak diubah">
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
    function openEditModal(user) {
        document.getElementById('edit-id').value = user.id;
        document.getElementById('edit-name').value = user.name;
        document.getElementById('edit-username').value = user.username;
        document.getElementById('edit-email').value = user.email;
        if (user.role === 'mahasiswa' && document.getElementById('edit-class-id')) {
            document.getElementById('edit-class-id').value = user.class_id || '';
        }

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
        
        axios.post("{{ route('admin.users.store') }}", formData)
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
                    : 'Gagal menambahkan pengguna.';
                showError(msg);
            });
    });

    // Edit Form Submit
    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        const name = document.getElementById('edit-name').value;
        const username = document.getElementById('edit-username').value;
        const email = document.getElementById('edit-email').value;
        const password = this.querySelector('input[name="password"]').value;
        
        let class_id = null;
        if (document.getElementById('edit-class-id')) {
            class_id = document.getElementById('edit-class-id').value;
        }

        axios.put(`/admin/manajemen-user/${id}`, {
            name: name,
            username: username,
            email: email,
            password: password,
            class_id: class_id
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
                : 'Gagal memperbarui data pengguna.';
            showError(msg);
        });
    });

    // Delete confirm
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Pengguna?',
            text: `Anda akan menghapus pengguna "${name}". Aksi ini tidak dapat dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/admin/manajemen-user/${id}`)
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
                            : 'Gagal menghapus pengguna.';
                        showError(msg);
                    });
            }
        });
    }
</script>
@endsection
