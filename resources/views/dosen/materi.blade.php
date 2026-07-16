@extends('layouts.dosen')

@section('title', 'Materi E-Learning - E-Learning STIKesMu')

@section('dosen-content')
<div class="space-y-8">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Dosen &gt; Materi E-Learning</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Materi E-Learning
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Unggah dan bagikan slide presentasi, e-book, video, atau tautan pembelajaran untuk mahasiswa.</p>
            </div>
            <button type="button" onclick="openAddModal()" class="rounded border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-green-800 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                <span>Unggah Materi Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter & Statistics -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <form method="GET" action="{{ route('dosen.materi.index') }}" class="grid grid-cols-1 sm:grid-cols-3 items-end gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Mata Kuliah</label>
                <select name="course_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary focus:border-secondary text-sm transition">
                    @foreach ($courses as $c)
                    <option value="{{ $c->id }}" {{ $courseId == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Filter Kelas</label>
                <select name="class_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary focus:border-secondary text-sm transition">
                    <option value="">Semua Kelas</option>
                    @foreach ($classes as $cl)
                    <option value="{{ $cl->id }}" {{ $classId == $cl->id ? 'selected' : '' }}>{{ $cl->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Materials Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" style="min-width: 680px;">
                <thead>
                    <tr class="bg-green-700 text-xs font-bold text-white uppercase tracking-wider">
                        <th class="py-3 px-4">Informasi Materi</th>
                        <th class="py-3 px-4">Tipe Materi</th>
                        <th class="py-3 px-4">Ditujukan Ke</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($materis as $materi)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4">
                            <span class="font-bold text-gray-900 block text-base">
                                @if($materi->pertemuan_ke)
                                    <span class="bg-emerald-55 text-emerald-800 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded border border-emerald-150 mr-1.5 font-mono">Sesi {{ $materi->pertemuan_ke }}</span>
                                @endif
                                {{ $materi->judul }}
                            </span>
                            <span class="text-xs text-gray-450 block line-clamp-1 mt-0.5">{{ $materi->deskripsi ?? 'Tidak ada deskripsi' }}</span>
                        </td>
                        <td class="py-3 px-4 capitalize">
                            @if ($materi->tipe === 'file')
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100 whitespace-nowrap">
                                <i class="fa-solid fa-file-pdf"></i> File
                            </span>
                            @elseif ($materi->tipe === 'link')
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-100 whitespace-nowrap">
                                <i class="fa-solid fa-link"></i> Tautan
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-semibold bg-slate-50 text-slate-700 border border-slate-100 whitespace-nowrap">
                                <i class="fa-solid fa-align-left"></i> Teks
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="block font-semibold text-gray-700 whitespace-nowrap">{{ $materi->classRoom ? $materi->classRoom->name : 'Semua Kelas' }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if ($materi->is_aktif)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-150 whitespace-nowrap">
                                Tayang
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-150 whitespace-nowrap">
                                Draft
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5 flex-nowrap">
                                <button type="button" onclick="checkProgress({{ $materi->id }}, '{{ $materi->judul }}')" class="bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 px-2.5 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 whitespace-nowrap cursor-pointer" title="Lihat Progress Membaca">
                                    <i class="fa-solid fa-users-viewfinder text-[11px]"></i>
                                    <span>Progress</span>
                                </button>
                                <button type="button" onclick="openEditModal({{ json_encode($materi) }})" class="bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 px-2.5 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 whitespace-nowrap cursor-pointer" title="Edit">
                                    <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                    <span>Edit</span>
                                </button>
                                <button type="button" onclick="confirmDelete({{ $materi->id }}, '{{ $materi->judul }}')" class="bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 px-2.5 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 whitespace-nowrap cursor-pointer" title="Hapus">
                                    <i class="fa-solid fa-trash-can text-[11px]"></i>
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">Tidak ada materi terdaftar untuk kriteria ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div id="add-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-file-arrow-up text-secondary mr-2"></i>Unggah Materi Pembelajaran</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="add-form" class="p-6 space-y-4" enctype="multipart/form-data">
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
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun Akademik</label>
                    <select name="tahun_akademik_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
                        @foreach ($tahunAkademik as $ta)
                        <option value="{{ $ta->id }}" {{ $ta->is_aktif ? 'selected' : '' }}>{{ $ta->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Materi</label>
                    <input type="text" name="judul" required placeholder="Contoh: Asuhan Keperawatan Dasar Hipovolemia" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Pertemuan Ke</label>
                    <input type="number" name="pertemuan_ke" min="1" placeholder="Contoh: 1" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Singkat (Opsional)</label>
                <textarea name="deskripsi" rows="2" placeholder="Tulis rincian singkat isi materi..." class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition"></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe Konten</label>
                <select name="tipe" id="add-tipe" onchange="toggleTipeInput('add')" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
                    <option value="file">Dokumen / File Upload</option>
                    <option value="link">Tautan Luar (URL)</option>
                    <option value="text">Materi Tekstual</option>
                </select>
            </div>

            <div id="add-file-section" class="tipe-section">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih File (PDF, DOCX, PPTX, MP4 - Max: 20MB)</label>
                <input type="file" name="file_materi" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary">
            </div>

            <div id="add-link-section" class="tipe-section hidden">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tautan / Link URL</label>
                <input type="url" name="link_url" placeholder="https://example.com/slides" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
            </div>

            <div id="add-text-section" class="tipe-section hidden">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Isi Materi Tekstual</label>
                <textarea name="konten" rows="5" placeholder="Tuliskan isi materi Anda di sini..." class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition"></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai Tayang (Opsional)</label>
                <input type="date" name="tanggal_tayang" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
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
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-pen-to-square text-secondary mr-2"></i>Edit Informasi Materi</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="edit-form" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-id">
            
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Materi</label>
                    <input type="text" id="edit-judul" name="judul" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Pertemuan Ke</label>
                    <input type="number" id="edit-pertemuan_ke" name="pertemuan_ke" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Singkat</label>
                <textarea id="edit-deskripsi" name="deskripsi" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition"></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai Tayang</label>
                <input type="date" id="edit-tanggal_tayang" name="tanggal_tayang" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status Penayangan</label>
                <select id="edit-is_aktif" name="is_aktif" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-secondary text-sm transition">
                    <option value="1">Aktif / Publikasikan</option>
                    <option value="0">Draft</option>
                </select>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-secondary bg-green-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Progress Mahasiswa -->
<div id="progress-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div>
                <h3 class="text-base font-bold text-gray-900"><i class="fa-solid fa-users-viewfinder text-secondary mr-2"></i>Progress Membaca Mahasiswa</h3>
                <p class="text-[11px] text-emerald-700 font-bold mt-0.5" id="progress-materi-title">Materi</p>
            </div>
            <button onclick="closeProgressModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div class="px-6 pb-6 max-h-96 overflow-y-auto pt-0">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="uppercase tracking-wider border-b border-gray-150">
                        <th class="py-3 px-4 sticky top-0 bg-green-700 text-white font-bold z-10">Nama Mahasiswa</th>
                        <th class="py-3 px-4 sticky top-0 bg-green-700 text-white font-bold z-10">NIM</th>
                        <th class="py-3 px-4 text-center sticky top-0 bg-green-700 text-white font-bold z-10">Status</th>
                        <th class="py-3 px-4 text-right sticky top-0 bg-green-700 text-white font-bold z-10">Waktu Lihat</th>
                    </tr>
                </thead>
                <tbody id="progress-table-body" class="divide-y divide-gray-100">
                    <!-- Dynamically Loaded -->
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end bg-gray-50/50">
            <button type="button" onclick="closeProgressModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-xs font-semibold transition">Tutup</button>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function toggleTipeInput(prefix) {
        const tipe = document.getElementById(`${prefix}-tipe`).value;
        document.getElementById(`${prefix}-file-section`).classList.add('hidden');
        document.getElementById(`${prefix}-link-section`).classList.add('hidden');
        document.getElementById(`${prefix}-text-section`).classList.add('hidden');

        if (tipe === 'file') {
            document.getElementById(`${prefix}-file-section`).classList.remove('hidden');
        } else if (tipe === 'link') {
            document.getElementById(`${prefix}-link-section`).classList.remove('hidden');
        } else if (tipe === 'text') {
            document.getElementById(`${prefix}-text-section`).classList.remove('hidden');
        }
    }

    function openAddModal() {
        document.getElementById('add-modal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('add-modal').classList.add('hidden');
        document.getElementById('add-form').reset();
        toggleTipeInput('add');
    }
    function openEditModal(materi) {
        document.getElementById('edit-id').value = materi.id;
        document.getElementById('edit-judul').value = materi.judul;
        document.getElementById('edit-pertemuan_ke').value = materi.pertemuan_ke || '';
        document.getElementById('edit-deskripsi').value = materi.deskripsi || '';
        document.getElementById('edit-tanggal_tayang').value = materi.tanggal_tayang ? materi.tanggal_tayang.substring(0, 10) : '';
        document.getElementById('edit-is_aktif').value = materi.is_aktif ? '1' : '0';
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
        document.getElementById('edit-form').reset();
    }

    function checkProgress(id, title) {
        document.getElementById('progress-materi-title').textContent = title;
        const tbody = document.getElementById('progress-table-body');
        tbody.innerHTML = `<tr><td colspan="4" class="text-center py-8 text-gray-400"><i class="fa-solid fa-circle-notch animate-spin text-lg mr-2"></i>Memuat progress...</td></tr>`;
        
        document.getElementById('progress-modal').classList.remove('hidden');
        
        axios.get(`/dosen/e-learning/materi/${id}/progress`)
            .then(res => {
                if (res.data.success) {
                    let html = '';
                    res.data.data.forEach(item => {
                        html += `
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-2.5 px-4 font-bold text-gray-800">${item.name}</td>
                                <td class="py-2.5 px-4 font-mono text-gray-650">${item.nim}</td>
                                <td class="py-2.5 px-4 text-center">
                                    ${item.viewed_at 
                                        ? '<span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-100">Sudah Dilihat</span>' 
                                        : '<span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-50 text-amber-800 border border-amber-150">Belum Dilihat</span>'}
                                </td>
                                <td class="py-2.5 px-4 text-right text-gray-500 font-mono">${item.viewed_at || '-'}</td>
                            </tr>
                        `;
                    });
                    if (res.data.data.length === 0) {
                        html = `<tr><td colspan="4" class="text-center py-6 text-gray-400">Tidak ada mahasiswa terdaftar di kelas ini.</td></tr>`;
                    }
                    tbody.innerHTML = html;
                }
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-6 text-red-650 font-bold">Gagal memuat progress mahasiswa.</td></tr>`;
            });
    }

    function closeProgressModal() {
        document.getElementById('progress-modal').classList.add('hidden');
    }

    document.getElementById('add-form').addEventListener('submit', function(e) {
        e.preventDefault();
        axios.post('/dosen/e-learning/materi', new FormData(this))
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
                Swal.fire('Gagal', err.response.data.message || 'Gagal menyimpan materi.', 'error');
            });
    });

    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        axios.post(`/dosen/e-learning/materi/${id}`, new FormData(this))
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
                Swal.fire('Gagal', err.response.data.message || 'Gagal mengubah materi.', 'error');
            });
    });

    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Hapus Materi?',
            text: `Apakah Anda yakin ingin menghapus materi "${nama}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/dosen/e-learning/materi/${id}`)
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
