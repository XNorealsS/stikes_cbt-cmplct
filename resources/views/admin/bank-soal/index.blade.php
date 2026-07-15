@extends('layouts.admin')

@section('title', 'Manajemen User - CBT STIKES Muhammadiyah Lhokseumawe')

@section('admin-content')
<div class="space-y-6">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Admin &gt; Bank Soal</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Manajemen Bank Soal
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Buat, kelola, dan gunakan kembali kumpulan soal untuk berbagai jadwal ujian (CBTMu).</p>
            </div>
            <button type="button" onclick="openAddModal()" class="rounded border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-green-800 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Buat Bank Soal Baru</span>
            </button>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="border border-slate-200 bg-white p-4 rounded-lg shadow-none">
        <form method="GET" action="{{ route('dosen.questions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            <div>
                <label for="course_id" class="flex items-center gap-1.5 text-xs font-bold text-slate-600 uppercase mb-1.5">
                    <i class="fa-solid fa-book text-slate-400"></i> Mata Kuliah
                </label>
                <select name="course_id" id="course_id" onchange="this.form.submit()" class="w-full border border-slate-300 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-green-600 bg-white text-slate-800 cursor-pointer">
                    <option value="">-- Semua Mata Kuliah --</option>
                    @foreach ($courses as $c)
                        <option value="{{ $c->id }}" {{ $courseId == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-1 md:col-span-2">
                <label for="search" class="flex items-center gap-1.5 text-xs font-bold text-slate-600 uppercase mb-1.5">
                    <i class="fa-solid fa-magnifying-glass text-slate-400"></i> Cari Nama Bank Soal
                </label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-xs pointer-events-none"></i>
                    <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Ketik nama bank soal..." class="w-full pl-9 pr-4 py-2.5 border border-slate-300 rounded-md text-sm focus:outline-none focus:border-green-600 bg-white text-slate-800">
                </div>
            </div>
        </form>
    </div>

    <!-- Bank Soals List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($bankSoals as $bs)
            <div class="border border-slate-300 bg-white rounded-none p-5 shadow-none flex flex-col justify-between hover:border-slate-400 transition duration-150">
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-2 border-b border-slate-150">
                        <span class="px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300 rounded-none font-mono">
                            {{ $bs->course->code }}
                        </span>

                        <!-- Status Badge & Switch -->
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider {{ $bs->is_aktif ? 'text-emerald-700' : 'text-slate-400' }}" id="status-label-{{ $bs->id }}">
                                {{ $bs->is_aktif ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer" {{ $bs->is_aktif ? 'checked' : '' }} onchange="toggleBankSoalStatus({{ $bs->id }})">
                                <div class="w-8 h-4 bg-slate-200 peer-focus:outline-none rounded-none peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-white after:border-slate-300 after:border after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-700"></div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-slate-800 text-sm leading-snug hover:text-green-700 transition">
                            <a href="{{ route('dosen.bank-soal.show', $bs->id) }}">{{ $bs->nama }}</a>
                        </h3>
                        @if($bs->kode)
                            <span class="text-[10px] font-mono text-slate-400 block mt-0.5">Kode: {{ $bs->kode }}</span>
                        @endif
                        <p class="text-xs text-slate-500 mt-1.5 line-clamp-2">{{ $bs->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2">
                    <!-- Manage Questions Button with clear text and icon -->
                    <a href="{{ route('dosen.bank-soal.show', $bs->id) }}" class="rounded-none border border-transparent bg-green-700 hover:bg-green-800 text-white font-bold px-3 py-1.5 text-xs inline-flex items-center gap-1.5 transition cursor-pointer">
                        <i class="fa-solid fa-list-check text-[10px]"></i>
                        <span>Kelola Soal ({{ $bs->questions_count }})</span>
                    </a>

                    <!-- Explicit Edit & Delete Buttons -->
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="openEditModal({{ json_encode($bs) }})" class="rounded-none border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 font-semibold px-2.5 py-1.5 text-xs inline-flex items-center gap-1 transition cursor-pointer" title="Edit Metadata Bank Soal">
                            <i class="fa-solid fa-pen text-[10px] text-slate-500"></i>
                            <span>Edit</span>
                        </button>
                        <button type="button" onclick="confirmDelete({{ $bs->id }}, '{{ $bs->nama }}')" class="rounded-none border border-rose-300 bg-white hover:bg-rose-50 text-rose-700 font-semibold px-2.5 py-1.5 text-xs inline-flex items-center gap-1 transition cursor-pointer" title="Hapus Bank Soal">
                            <i class="fa-solid fa-trash-can text-[10px] text-rose-600"></i>
                            <span>Hapus</span>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full border border-slate-300 bg-white p-12 text-center text-slate-400">
                <i class="fa-solid fa-folder-open text-4xl text-slate-300 mb-3 block"></i>
                <h4 class="font-bold text-slate-700 text-sm">Belum Ada Bank Soal</h4>
                <p class="text-xs text-slate-500 mt-1">Gunakan tombol "+ Buat Bank Soal Baru" di atas untuk memulai.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal Tambah Bank Soal -->
<div id="add-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/80">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest"><i class="fa-solid fa-folder-plus text-primary mr-1.5"></i>Buat Bank Soal Baru</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-650 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="add-form" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Mata Kuliah</label>
                <select name="course_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-primary bg-white">
                    <option value="" disabled selected>-- Pilih Mata Kuliah --</option>
                    @foreach ($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nama Bank Soal</label>
                <input type="text" name="nama" required placeholder="Contoh: Kumpulan Soal Keperawatan Anak Ganjil" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Kode Internal (Opsional)</label>
                <input type="text" name="kode" placeholder="Contoh: ANK-01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Deskripsi / Catatan (Opsional)</label>
                <textarea name="deskripsi" rows="3" placeholder="Contoh: Soal-soal untuk ujian tengah semester dan kuis kelas A-C." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
            </div>
            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-full text-xs font-bold hover:bg-gray-50 transition cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 bg-primary hover:bg-emerald-850 text-white rounded-full text-xs font-bold transition shadow-sm cursor-pointer">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Bank Soal -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/80">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest"><i class="fa-solid fa-pen text-primary mr-1.5"></i>Edit Bank Soal</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-650 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="edit-form" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-id">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Mata Kuliah</label>
                <select id="edit-course_id" name="course_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-primary bg-white">
                    @foreach ($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nama Bank Soal</label>
                <input type="text" id="edit-nama" name="nama" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Kode Internal (Opsional)</label>
                <input type="text" id="edit-kode" name="kode" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Deskripsi / Catatan (Opsional)</label>
                <textarea id="edit-deskripsi" name="deskripsi" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
            </div>
            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-full text-xs font-bold hover:bg-gray-50 transition cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 bg-primary hover:bg-emerald-850 text-white rounded-full text-xs font-bold transition shadow-sm cursor-pointer">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Add Modal Functions
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

    // Edit Modal Functions
    function openEditModal(bs) {
        document.getElementById('edit-id').value = bs.id;
        document.getElementById('edit-course_id').value = bs.course_id;
        document.getElementById('edit-nama').value = bs.nama;
        document.getElementById('edit-kode').value = bs.kode || '';
        document.getElementById('edit-deskripsi').value = bs.deskripsi || '';

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

    // Submit Add
    document.getElementById('add-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const data = {
            course_id: this.querySelector('[name="course_id"]').value,
            nama: this.querySelector('[name="nama"]').value,
            kode: this.querySelector('[name="kode"]').value,
            deskripsi: this.querySelector('[name="deskripsi"]').value,
        };

        axios.post("{{ route('dosen.bank-soal.store') }}", data)
            .then(res => {
                if (res.data.success) {
                    closeAddModal();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.data.message,
                        confirmButtonColor: '#14532d'
                    }).then(() => {
                        window.location.reload();
                    });
                }
            })
            .catch(err => {
                const msg = err.response && err.response.data && err.response.data.message
                    ? err.response.data.message
                    : 'Terjadi kesalahan.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg, confirmButtonColor: '#14532d' });
            });
    });

    // Submit Edit
    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        const data = {
            course_id: document.getElementById('edit-course_id').value,
            nama: document.getElementById('edit-nama').value,
            kode: document.getElementById('edit-kode').value,
            deskripsi: document.getElementById('edit-deskripsi').value,
        };

        axios.put(`/dosen/bank-soal/${id}`, data)
            .then(res => {
                if (res.data.success) {
                    closeEditModal();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.data.message,
                        confirmButtonColor: '#14532d'
                    }).then(() => {
                        window.location.reload();
                    });
                }
            })
            .catch(err => {
                const msg = err.response && err.response.data && err.response.data.message
                    ? err.response.data.message
                    : 'Terjadi kesalahan.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg, confirmButtonColor: '#14532d' });
            });
    });

    // Toggle Active Status
    function toggleBankSoalStatus(id) {
        axios.post(`/dosen/bank-soal/${id}/toggle-aktif`)
            .then(res => {
                if (res.data.success) {
                    const label = document.getElementById(`status-label-${id}`);
                    label.textContent = res.data.is_aktif ? 'Aktif' : 'Nonaktif';

                    const toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    toast.fire({
                        icon: 'success',
                        title: res.data.message
                    });
                }
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal mengubah status.', confirmButtonColor: '#14532d' });
            });
    }

    // Confirm Delete
    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Hapus Bank Soal?',
            text: `Apakah Anda yakin ingin menghapus bank soal "${nama}"? Semua pertanyaan di dalamnya juga akan terhapus.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/dosen/bank-soal/${id}`)
                    .then(res => {
                        if (res.data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: res.data.message,
                                confirmButtonColor: '#14532d'
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    });
            }
        });
    }
</script>
@endsection
