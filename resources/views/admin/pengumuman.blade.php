@extends('layouts.admin')

@section('title', 'Pengumuman - CBT STIKES Muhammadiyah Lhokseumawe')

@section('admin-content')
<div class="space-y-8">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Admin &gt; Pengumuman Kampus</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Pengumuman Kampus &amp; Informasi
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola pengumuman yang ditampilkan pada beranda/dashboard dosen dan mahasiswa.</p>
            </div>
            <button type="button" onclick="openAddModal()" class="rounded border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-green-800 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Buat Pengumuman</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($pengumumans as $p)
        <div class="bg-white p-4 rounded-xl border border-gray-150 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div class="space-y-2">
                <div class="flex justify-between items-start gap-1">
                    <div class="flex flex-wrap gap-1">
                        <span class="inline-block px-2 py-0.5 text-[9px] font-bold bg-blue-50 text-blue-800 border border-blue-100 rounded uppercase">
                            Target: {{ $p->target }}
                        </span>
                        @if ($p->prodi)
                        <span class="inline-block px-2 py-0.5 text-[9px] font-bold bg-green-50 text-green-800 border border-green-100 rounded truncate max-w-[120px]" title="Prodi: {{ $p->prodi->nama }}">
                            {{ $p->prodi->nama }}
                        </span>
                        @endif
                    </div>
                    <span class="text-[10px] font-bold {{ $p->is_aktif ? 'text-green-600' : 'text-red-600' }}">
                        ● {{ $p->is_aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                
                <h3 class="text-sm font-bold text-gray-900 leading-snug" title="{{ $p->judul }}">{{ $p->judul }}</h3>
                <p class="text-[10px] text-gray-400">Oleh: {{ $p->user->name }} | {{ $p->created_at->format('d/m/Y H:i') }}</p>
                
                <div class="text-xs text-gray-650 leading-relaxed border-t border-gray-150 pt-2 whitespace-pre-line">
                    {!! $p->isi !!}
                </div>
            </div>
            
            <div class="mt-4 pt-2 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-450">
                <div class="flex flex-col">
                    <span>Mulai: {{ $p->tanggal_aktif ? $p->tanggal_aktif->format('d/m/Y') : 'Langsung' }}</span>
                    <span>Akhir: {{ $p->tanggal_expired ? $p->tanggal_expired->format('d/m/Y') : 'Selamanya' }}</span>
                </div>
                <div class="flex gap-1.5">
                    <button type="button" onclick="openEditModal({{ json_encode($p) }})" class="bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 text-yellow-800 px-2 py-1 rounded text-[10px] font-bold transition flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Edit</span>
                    </button>
                    <button type="button" onclick="confirmDelete({{ $p->id }}, '{{ $p->judul }}')" class="bg-red-50 hover:bg-red-100 border border-red-200 text-red-800 px-2 py-1 rounded text-[10px] font-bold transition flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-trash-can"></i>
                        <span>Hapus</span>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white p-8 text-center text-gray-400 rounded-xl border border-gray-100 shadow-sm">
            <i class="fa-solid fa-bullhorn text-4xl mb-3 text-gray-300"></i>
            <p class="text-xs">Tidak ada pengumuman yang diterbitkan.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Modal Tambah -->
<div id="add-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-bullhorn text-primary mr-2"></i>Buat Pengumuman</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="add-form" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Pengumuman</label>
                <input type="text" name="judul" required placeholder="Contoh: Jadwal Ujian UTS Semester Ganjil" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Target</label>
                    <select name="target" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                        <option value="semua">Semua Pengguna</option>
                        <option value="mahasiswa">Khusus Mahasiswa</option>
                        <option value="dosen">Khusus Dosen</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Program Studi (Opsional)</label>
                    <select name="prodi_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                        <option value="">Semua Program Studi</option>
                        @foreach ($prodis as $prodi)
                        <option value="{{ $prodi->id }}">{{ $prodi->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Isi Pengumuman</label>
                <textarea name="isi" rows="6" required placeholder="Tulis pengumuman di sini..." class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Aktif (Mulai)</label>
                    <input type="date" name="tanggal_aktif" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Expired (Selesai)</label>
                    <input type="date" name="tanggal_expired" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-semibold transition shadow-sm">Terbitkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-bullhorn text-primary mr-2"></i>Edit Pengumuman</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="edit-form" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-id">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Pengumuman</label>
                <input type="text" id="edit-judul" name="judul" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Target</label>
                    <select id="edit-target" name="target" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                        <option value="semua">Semua Pengguna</option>
                        <option value="mahasiswa">Khusus Mahasiswa</option>
                        <option value="dosen">Khusus Dosen</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Program Studi (Opsional)</label>
                    <select id="edit-prodi_id" name="prodi_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                        <option value="">Semua Program Studi</option>
                        @foreach ($prodis as $prodi)
                        <option value="{{ $prodi->id }}">{{ $prodi->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Isi Pengumuman</label>
                <textarea id="edit-isi" name="isi" rows="6" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Aktif (Mulai)</label>
                    <input type="date" id="edit-tanggal_aktif" name="tanggal_aktif" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Expired (Selesai)</label>
                    <input type="date" id="edit-tanggal_expired" name="tanggal_expired" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status Keaktifan</label>
                <select id="edit-is_aktif" name="is_aktif" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
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
    function openAddModal() {
        document.getElementById('add-modal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('add-modal').classList.add('hidden');
        document.getElementById('add-form').reset();
    }
    function openEditModal(p) {
        document.getElementById('edit-id').value = p.id;
        document.getElementById('edit-judul').value = p.judul;
        document.getElementById('edit-target').value = p.target;
        document.getElementById('edit-prodi_id').value = p.prodi_id || '';
        document.getElementById('edit-isi').value = p.isi;
        document.getElementById('edit-tanggal_aktif').value = p.tanggal_aktif ? p.tanggal_aktif.substring(0, 10) : '';
        document.getElementById('edit-tanggal_expired').value = p.tanggal_expired ? p.tanggal_expired.substring(0, 10) : '';
        document.getElementById('edit-is_aktif').value = p.is_aktif ? '1' : '0';
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
        document.getElementById('edit-form').reset();
    }

    document.getElementById('add-form').addEventListener('submit', function(e) {
        e.preventDefault();
        axios.post('/admin/pengumuman', new FormData(this))
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
                Swal.fire('Gagal', err.response.data.message || 'Gagal menyimpan.', 'error');
            });
    });

    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        axios.post(`/admin/pengumuman/${id}`, new FormData(this))
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
                Swal.fire('Gagal', err.response.data.message || 'Gagal memperbarui.', 'error');
            });
    });

    function confirmDelete(id, judul) {
        Swal.fire({
            title: 'Hapus Pengumuman?',
            text: `Apakah Anda yakin ingin menghapus "${judul}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/admin/pengumuman/${id}`)
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
