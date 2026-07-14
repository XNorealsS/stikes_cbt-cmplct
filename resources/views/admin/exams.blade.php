@extends('layouts.admin')

@section('title', 'Jadwal Ujian Master - SIAKAD STIKesMu')

@section('admin-content')
<div class="space-y-4">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Admin &gt; Jadwal Ujian</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Manajemen Jadwal Ujian
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Buat, sunting, hapus, dan atur seluruh sesi pelaksanaan ujian di sistem.</p>
            </div>
            <button type="button" onclick="openAddModal()" class="rounded border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-green-800 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                <i class="fa-solid fa-calendar-plus text-xs"></i>
                <span>Buat Sesi Ujian</span>
            </button>
        </div>
    </div>

    <!-- Exams Table Container (SIAKAD style) -->
    <div class="border border-slate-200 bg-white rounded-lg shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-slate-800 border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 font-bold">
                        <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-[11px]">Nama Sesi Ujian</th>
                        <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-[11px]">Mata Kuliah</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px]">Token</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px]">Durasi &amp; Soal</th>
                        <th class="px-4 py-3 text-center font-semibold uppercase tracking-wider text-[11px]">Waktu Aktif</th>
                        <th class="px-4 py-3 text-right font-semibold uppercase tracking-wider text-[11px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($exams as $exam)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-800 block">{{ $exam->title }}</span>
                            <div class="space-y-0.5 mt-0.5">
                                <span class="text-[10px] text-slate-400 block">Dosen: {{ $exam->dosen->name }}</span>
                                @if ($exam->classRoom)
                                    <span class="inline-block bg-emerald-50 text-emerald-800 text-[9px] font-bold px-1.5 py-0.5 border border-emerald-200 rounded">
                                        Kelas: {{ $exam->classRoom->name }}
                                    </span>
                                @else
                                    <span class="inline-block bg-slate-100 text-slate-600 text-[9px] font-bold px-1.5 py-0.5 border border-slate-200 rounded">
                                        Kelas: Semua Kelas
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-semibold text-slate-700 block">{{ $exam->course->name }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $exam->course->code }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex items-center space-x-1.5">
                                <span id="token-text-{{ $exam->id }}" class="font-mono font-bold bg-emerald-50 border border-emerald-300 text-emerald-800 px-2.5 py-0.5 rounded text-[11px] tracking-wider">{{ $exam->token }}</span>
                                <button type="button" onclick="regenerateToken({{ $exam->id }}, '{{ $exam->title }}')" title="Regenerasi Token Baru" class="text-emerald-700 hover:text-emerald-900 transition cursor-pointer p-1">
                                    <i class="fa-solid fa-arrows-rotate text-xs"></i>
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="block text-slate-700 font-medium"><i class="fa-regular fa-clock mr-1 text-slate-400"></i>{{ $exam->duration_minutes }} Menit</span>
                            <span class="block text-[10px] text-slate-400 mt-0.5"><i class="fa-solid fa-list-check mr-1 text-slate-400"></i>{{ $exam->total_questions }} Soal ({{ $exam->is_random ? 'Acak' : 'Urut' }})</span>
                        </td>
                        <td class="px-4 py-3 text-center font-mono text-[10px] text-slate-500">
                            <span class="block"><span class="font-bold text-slate-700">Mulai:</span> {{ \Carbon\Carbon::parse($exam->start_time)->format('d/m/Y H:i') }}</span>
                            <span class="block mt-0.5"><span class="font-bold text-slate-700">Tutup:</span> {{ \Carbon\Carbon::parse($exam->end_time)->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-1.5">
                            <button type="button" onclick="openEditModal({{ json_encode($exam) }})" class="rounded border border-amber-300 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100 transition inline-flex items-center gap-1 shadow-none cursor-pointer">
                                <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                <span>Edit</span>
                            </button>
                            <button type="button" onclick="confirmDelete({{ $exam->id }}, '{{ $exam->title }}')" class="rounded border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 hover:bg-red-100 transition inline-flex items-center gap-1 shadow-none cursor-pointer">
                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                                <span>Hapus</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400">Tidak ada sesi jadwal ujian yang terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Jadwal Ujian (SIAKAD style) -->
<div id="add-modal" class="fixed inset-0 z-50 bg-slate-900/40 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-lg w-full border border-slate-200 shadow-lg overflow-hidden transform scale-95 transition-all duration-200">
        <div class="px-5 py-3.5 border-b border-slate-200 flex justify-between items-center bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800"><i class="fa-solid fa-calendar-check text-emerald-700 mr-2"></i>Buat Sesi Ujian Baru</h3>
            <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark text-base"></i></button>
        </div>
        <form id="add-form" class="flex flex-col max-h-[85vh]">
            @csrf
            <div class="p-6 space-y-4 overflow-y-auto">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Bank Soal</label>
                    <select name="bank_soal_id" required class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none bg-white">
                        <option value="" disabled selected>-- Pilih Bank Soal --</option>
                        @foreach ($bankSoals as $bs)
                            <option value="{{ $bs->id }}">{{ $bs->nama }} (MK: {{ $bs->course->name }} &bull; Dosen: {{ $bs->dosen->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Dosen Pengampu</label>
                    <select name="dosen_id" required class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none bg-white">
                        <option value="" disabled selected>-- Pilih Dosen Pengampu --</option>
                        @foreach ($dosens as $d)
                            <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->username }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Kelas Sasaran (Opsional)</label>
                    <select name="class_id" class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none bg-white">
                        <option value="">-- Semua Kelas (Bisa Diakses Semua Kelas) --</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama / Judul Ujian</label>
                    <input type="text" name="title" required placeholder="Contoh: Ujian Tengah Semester (UTS)" class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Waktu Mulai</label>
                        <input type="datetime-local" name="start_time" required class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Waktu Selesai</label>
                        <input type="datetime-local" name="end_time" required class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Durasi Pengerjaan (Menit)</label>
                        <input type="number" name="duration_minutes" required min="5" placeholder="Contoh: 90" class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Jumlah Soal Diujikan</label>
                        <input type="number" name="total_questions" required min="1" placeholder="Contoh: 50" class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center pt-1">
                    <input id="add-is_random" name="is_random" type="checkbox" value="1" checked class="h-4 w-4 text-green-600 focus:ring-green-500 border-slate-300 rounded">
                    <label for="add-is_random" class="ml-2 block text-xs font-semibold text-slate-700 uppercase">Acak Urutan Soal</label>
                </div>
            </div>

            <div class="px-6 py-4 flex justify-end gap-2 border-t border-slate-200 bg-slate-50 flex-shrink-0">
                <button type="button" onclick="closeAddModal()" class="rounded border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer">Batal</button>
                <button type="submit" class="rounded border border-transparent bg-green-700 px-4 py-1.5 text-xs font-semibold text-white hover:bg-green-800 transition cursor-pointer">Simpan Sesi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Jadwal Ujian (SIAKAD style) -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-slate-900/40 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-lg w-full border border-slate-200 shadow-lg overflow-hidden transform scale-95 transition-all duration-200">
        <div class="px-5 py-3.5 border-b border-slate-200 flex justify-between items-center bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800"><i class="fa-solid fa-calendar-day text-emerald-700 mr-2"></i>Edit Sesi Ujian</h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark text-base"></i></button>
        </div>
        <form id="edit-form" class="flex flex-col max-h-[85vh]">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-4 overflow-y-auto">
                <input type="hidden" id="edit-id">
            
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Pilih Bank Soal</label>
                    <select id="edit-bank_soal_id" name="bank_soal_id" required class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none bg-white">
                        <option value="" disabled>-- Pilih Bank Soal --</option>
                        @foreach ($bankSoals as $bs)
                            <option value="{{ $bs->id }}">{{ $bs->nama }} (MK: {{ $bs->course->name }} &bull; Dosen: {{ $bs->dosen->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Dosen Pengampu</label>
                    <select id="edit-dosen_id" name="dosen_id" required class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none bg-white">
                        @foreach ($dosens as $d)
                            <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->username }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Kelas Sasaran (Opsional)</label>
                    <select id="edit-class_id" name="class_id" class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none bg-white">
                        <option value="">-- Semua Kelas (Bisa Diakses Semua Kelas) --</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama / Judul Ujian</label>
                    <input type="text" id="edit-title" name="title" required class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Waktu Mulai</label>
                        <input type="datetime-local" id="edit-start_time" name="start_time" required class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Waktu Selesai</label>
                        <input type="datetime-local" id="edit-end_time" name="end_time" required class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Durasi Pengerjaan (Menit)</label>
                        <input type="number" id="edit-duration_minutes" name="duration_minutes" required min="5" class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Jumlah Soal Diujikan</label>
                        <input type="number" id="edit-total_questions" name="total_questions" required min="1" class="w-full border border-slate-300 px-3 py-2 text-xs text-slate-800 rounded focus:border-green-600 focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center pt-1">
                    <input id="edit-is_random" name="is_random" type="checkbox" value="1" class="h-4 w-4 text-green-600 focus:ring-green-500 border-slate-300 rounded">
                    <label for="edit-is_random" class="ml-2 block text-xs font-semibold text-slate-700 uppercase">Acak Urutan Soal</label>
                </div>
            </div>

            <div class="px-6 py-4 flex justify-end gap-2 border-t border-slate-200 bg-slate-50 flex-shrink-0">
                <button type="button" onclick="closeEditModal()" class="rounded border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition cursor-pointer">Batal</button>
                <button type="submit" class="rounded border border-transparent bg-green-700 px-4 py-1.5 text-xs font-semibold text-white hover:bg-green-800 transition cursor-pointer">Simpan Perubahan</button>
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
    function openEditModal(exam) {
        document.getElementById('edit-id').value = exam.id;
        document.getElementById('edit-bank_soal_id').value = exam.bank_soal_id || '';
        document.getElementById('edit-dosen_id').value = exam.dosen_id;
        document.getElementById('edit-class_id').value = exam.class_id || '';
        document.getElementById('edit-title').value = exam.title;
        
        // Format dates for datetime-local inputs (YYYY-MM-DDTHH:MM)
        const formatDatetime = (dateStr) => {
            const date = new Date(dateStr);
            const pad = (n) => n < 10 ? '0' + n : n;
            return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) + 'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
        };
        
        document.getElementById('edit-start_time').value = formatDatetime(exam.start_time);
        document.getElementById('edit-end_time').value = formatDatetime(exam.end_time);
        document.getElementById('edit-duration_minutes').value = exam.duration_minutes;
        document.getElementById('edit-total_questions').value = exam.total_questions;
        document.getElementById('edit-is_random').checked = exam.is_random;

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
        
        const data = {
            bank_soal_id: this.querySelector('select[name="bank_soal_id"]').value,
            dosen_id: this.querySelector('select[name="dosen_id"]').value,
            class_id: this.querySelector('select[name="class_id"]').value || null,
            title: this.querySelector('input[name="title"]').value,
            start_time: this.querySelector('input[name="start_time"]').value,
            end_time: this.querySelector('input[name="end_time"]').value,
            duration_minutes: this.querySelector('input[name="duration_minutes"]').value,
            total_questions: this.querySelector('input[name="total_questions"]').value,
            is_random: this.querySelector('input[name="is_random"]').checked ? 1 : 0
        };
        
        axios.post("{{ route('admin.exams.store') }}", data)
            .then(res => {
                if (res.data.success) {
                    closeAddModal();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.data.message,
                        confirmButtonColor: '#1A4731'
                    }).then(() => {
                        window.location.reload();
                    });
                }
            })
            .catch(err => {
                const msg = err.response && err.response.data && err.response.data.message 
                    ? err.response.data.message 
                    : 'Gagal membuat sesi ujian.';
                showError(msg);
            });
    });

    // Edit Form Submit
    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        
        const data = {
            bank_soal_id: document.getElementById('edit-bank_soal_id').value,
            dosen_id: document.getElementById('edit-dosen_id').value,
            class_id: document.getElementById('edit-class_id').value || null,
            title: document.getElementById('edit-title').value,
            start_time: document.getElementById('edit-start_time').value,
            end_time: document.getElementById('edit-end_time').value,
            duration_minutes: document.getElementById('edit-duration_minutes').value,
            total_questions: document.getElementById('edit-total_questions').value,
            is_random: document.getElementById('edit-is_random').checked ? 1 : 0
        };

        axios.put(`/admin/jadwal-ujian/${id}`, data)
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
                    : 'Gagal memperbarui sesi ujian.';
                showError(msg);
            });
    });

    // Regenerate Token
    function regenerateToken(id, title) {
        Swal.fire({
            title: 'Perbarui Token?',
            text: `Apakah Anda yakin ingin mengganti token ujian untuk "${title}"? Token lama tidak akan bisa digunakan lagi.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1A4731',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Perbarui',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post(`/admin/jadwal-ujian/regenerate/${id}`)
                    .then(res => {
                        if (res.data.success) {
                            document.getElementById(`token-text-${id}`).textContent = res.data.token;
                            showSuccess(res.data.message);
                        }
                    })
                    .catch(err => {
                        showError('Gagal memperbarui token.');
                    });
            }
        });
    }

    // Delete confirm
    function confirmDelete(id, title) {
        Swal.fire({
            title: 'Hapus Sesi Ujian?',
            text: `Anda akan menghapus sesi ujian "${title}" (Admin). Seluruh lembar jawaban dan nilai mahasiswa terkait akan ikut terhapus!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/admin/jadwal-ujian/${id}`)
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
                            : 'Gagal menghapus sesi ujian.';
                        showError(msg);
                    });
            }
        });
    }
</script>
@endsection
