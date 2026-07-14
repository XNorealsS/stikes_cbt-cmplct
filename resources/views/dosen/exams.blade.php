@extends('layouts.dosen')

@section('title', 'Jadwal Ujian - CBT STIKES Muhammadiyah Lhokseumawe')

@section('dosen-content')
<div class="space-y-5">
    <!-- Page Header -->
    <!-- Topbar & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800" style="font-family: var(--font-heading);">Jadwal Ujian</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola jadwal ujian yang Anda ampu.</p>
        </div>
        <button type="button" onclick="openAddModal()" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-900 text-white px-4 py-2 text-xs font-bold uppercase tracking-wider transition-all active:scale-95 cursor-pointer shadow-sm" style="border-radius: var(--radius-md);">
            <i class="fa-solid fa-plus"></i> Tambah Ujian
        </button>
    </div>

    <!-- Exams Card & Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr>
                        <th>Informasi Ujian</th>
                        <th>Detail Sesi &amp; Ruang</th>
                        <th class="text-center">Token</th>
                        <th>Durasi &amp; Soal</th>
                        <th>Waktu Aktif</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exams as $exam)
                    <tr>
                        <td>
                            <span class="font-bold text-slate-900 block text-[13px]">{{ $exam->title }}</span>
                            <div class="flex flex-wrap gap-1.5 mt-1.5">
                                <span class="badge {{ $exam->examTypeBadgeClass() }} text-[9px] uppercase py-0.5 px-1.5">{{ $exam->exam_type }}</span>
                                <span class="badge badge-primary text-[9px] py-0.5 px-1.5">
                                    PG: {{ $exam->passing_grade }}%
                                </span>
                                @if ($exam->classRoom)
                                    <span class="badge badge-neutral text-[9px] py-0.5 px-1.5">
                                        Kelas: {{ $exam->classRoom->name }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="font-semibold text-slate-700 block text-xs">{{ $exam->course->name }}</span>
                            <div class="text-[10px] text-slate-400 mt-1 space-y-0.5">
                                <div><span class="font-semibold text-slate-500">Ruang:</span> {{ $exam->ruang ? $exam->ruang->nama : '—' }}</div>
                                <div><span class="font-semibold text-slate-500">Sesi:</span> {{ $exam->sesi ? $exam->sesi->nama : '—' }}</div>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <span id="token-text-{{ $exam->id }}" class="font-mono font-bold bg-slate-50 border border-slate-200 text-slate-800 px-2.5 py-1 text-xs tracking-widest" style="border-radius: var(--radius-sm);">{{ $exam->token }}</span>
                                <button type="button" onclick="regenerateToken({{ $exam->id }}, '{{ $exam->title }}')" title="Regenerasi Token Baru" class="w-7 h-7 flex items-center justify-center bg-slate-50 hover:bg-slate-100 border border-slate-200 text-primary-700 hover:text-primary-800 transition active:scale-90" style="border-radius: var(--radius-sm);">
                                    <i class="fa-solid fa-arrows-rotate text-[10px]"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <span class="block text-slate-600 text-xs"><i class="fa-regular fa-clock mr-1 text-slate-400"></i>{{ $exam->duration_minutes }} Menit</span>
                            <span class="block text-[10px] text-slate-400 mt-1"><i class="fa-solid fa-list-check mr-1 text-slate-400"></i>{{ $exam->total_questions }} Soal ({{ $exam->is_random ? 'Acak' : 'Urut' }})</span>
                        </td>
                        <td class="text-[11px] text-slate-500 space-y-0.5">
                            <span class="block"><span class="font-semibold text-slate-600">Mulai:</span> {{ $exam->start_time->format('d/m/Y H:i') }}</span>
                            <span class="block"><span class="font-semibold text-slate-600">Selesai:</span> {{ $exam->end_time->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1">
                                <button type="button" onclick="openEditModal({{ json_encode($exam) }})" class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 text-[11px] font-bold transition duration-150" style="border-radius: var(--radius-sm);">
                                    <i class="fa-solid fa-pen-to-square text-[9px]"></i>
                                    <span>Edit</span>
                                </button>
                                <button type="button" onclick="confirmDelete({{ $exam->id }}, '{{ $exam->title }}')" class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 text-[11px] font-bold transition duration-150" style="border-radius: var(--radius-sm);">
                                    <i class="fa-solid fa-trash-can text-[9px]"></i>
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-14 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
                                    <i class="fa-regular fa-calendar-xmark text-xl text-slate-300"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-400">Belum ada sesi ujian</p>
                                    <p class="text-xs text-slate-300 mt-0.5">Jadwal ujian yang Anda buat akan tampil di sini.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Jadwal Ujian -->
<div id="add-modal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <form id="add-form" class="bg-white max-w-lg w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 flex flex-col max-h-[85vh]" style="border-radius: var(--radius-xl);">
        @csrf
        {{-- Fixed Header --}}
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 flex-shrink-0">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-calendar-check text-primary-700"></i>
                Buat Sesi Ujian Baru
            </h3>
            <button type="button" onclick="closeAddModal()" class="text-slate-400 hover:text-slate-650 w-7 h-7 flex items-center justify-center hover:bg-slate-100 transition" style="border-radius: var(--radius-sm);"><i class="fa-solid fa-xmark text-sm"></i></button>
        </div>

        {{-- Scrollable Body --}}
        <div class="p-6 space-y-4 overflow-y-auto flex-grow">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Pilih Bank Soal</label>
                <select name="bank_soal_id" required class="w-full">
                    <option value="" disabled selected>-- Pilih Bank Soal --</option>
                    @foreach ($bankSoals as $bs)
                        <option value="{{ $bs->id }}">{{ $bs->nama }} (MK: {{ $bs->course->name }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Tahun Akademik</label>
                    <select name="tahun_akademik_id" required class="w-full">
                        @foreach ($tahunAkademiks as $ta)
                            <option value="{{ $ta->id }}" {{ $ta->is_aktif ? 'selected' : '' }}>{{ $ta->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Jenis Ujian</label>
                    <select name="jenis_ujian_id" required class="w-full">
                        @foreach ($jenisUjians as $ju)
                            <option value="{{ $ju->id }}">{{ $ju->nama }} ({{ $ju->kode }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Ruang Ujian</label>
                    <select name="ruang_id" required class="w-full">
                        @foreach ($ruangs as $ru)
                            <option value="{{ $ru->id }}">{{ $ru->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Sesi Waktu</label>
                    <select name="sesi_id" required class="w-full">
                        @foreach ($sesis as $se)
                            <option value="{{ $se->id }}">{{ $se->nama }} ({{ substr($se->jam_mulai,0,5) }} - {{ substr($se->jam_selesai,0,5) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Tipe Ujian</label>
                    <select name="exam_type" required class="w-full">
                        <option value="UTS">UTS</option>
                        <option value="UAS">UAS</option>
                        <option value="KUIS">Kuis</option>
                        <option value="LATIHAN">Latihan Mandiri</option>
                        <option value="SELEKSI">Seleksi Masuk</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Passing Grade (KKM)</label>
                    <input type="number" step="0.01" name="passing_grade" value="60" required class="w-full">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Nama / Judul Ujian</label>
                <input type="text" name="title" required placeholder="Contoh: UTS KDP Ganjil" class="w-full">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Kelas Sasaran (Opsional)</label>
                <select name="class_id" class="w-full">
                    <option value="">-- Semua Kelas (Bisa Diakses Semua Kelas) --</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Waktu Mulai</label>
                    <input type="datetime-local" name="start_time" required class="w-full">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Waktu Selesai</label>
                    <input type="datetime-local" name="end_time" required class="w-full">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Durasi (Menit)</label>
                    <input type="number" name="duration_minutes" required min="5" placeholder="Contoh: 90" class="w-full">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Jumlah Soal</label>
                    <input type="number" name="total_questions" required min="1" placeholder="Contoh: 50" class="w-full">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Petunjuk Pengerjaan (Opsional)</label>
                <textarea name="petunjuk" rows="2" placeholder="Contoh: Berdoalah sebelum mengerjakan. Pilih satu jawaban paling tepat." class="w-full"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input id="add-is_random" name="is_random" type="checkbox" value="1" checked class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                <label for="add-is_random" class="text-xs font-semibold text-slate-600 cursor-pointer">Acak Urutan Soal (Sangat Direkomendasikan)</label>
            </div>
        </div>

        {{-- Fixed Footer --}}
        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2 bg-slate-50 flex-shrink-0">
            <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-slate-300 text-slate-600 text-xs font-semibold hover:bg-slate-100 transition cursor-pointer" style="border-radius: var(--radius-md);">Batal</button>
            <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-900 text-white text-xs font-bold transition cursor-pointer" style="border-radius: var(--radius-md);">Simpan</button>
        </div>
    </form>
</div>

<!-- Modal Edit Jadwal Ujian -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <form id="edit-form" class="bg-white max-w-lg w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 flex flex-col max-h-[85vh]" style="border-radius: var(--radius-xl);">
        @csrf
        @method('PUT')
        <input type="hidden" id="edit-id">

        {{-- Fixed Header --}}
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 flex-shrink-0">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-calendar-day text-primary-700"></i>
                Edit Sesi Ujian
            </h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-655 w-7 h-7 flex items-center justify-center hover:bg-slate-100 transition" style="border-radius: var(--radius-sm);"><i class="fa-solid fa-xmark text-sm"></i></button>
        </div>

        {{-- Scrollable Body --}}
        <div class="p-6 space-y-4 overflow-y-auto flex-grow">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Pilih Bank Soal</label>
                <select id="edit-bank_soal_id" name="bank_soal_id" required class="w-full bg-white">
                    <option value="" disabled>-- Pilih Bank Soal --</option>
                    @foreach ($bankSoals as $bs)
                        <option value="{{ $bs->id }}">{{ $bs->nama }} (MK: {{ $bs->course->name }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Tahun Akademik</label>
                    <select id="edit-tahun_akademik_id" name="tahun_akademik_id" required class="w-full bg-white">
                        @foreach ($tahunAkademiks as $ta)
                            <option value="{{ $ta->id }}">{{ $ta->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Jenis Ujian</label>
                    <select id="edit-jenis_ujian_id" name="jenis_ujian_id" required class="w-full bg-white">
                        @foreach ($jenisUjians as $ju)
                            <option value="{{ $ju->id }}">{{ $ju->nama }} ({{ $ju->kode }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Ruang Ujian</label>
                    <select id="edit-ruang_id" name="ruang_id" required class="w-full bg-white">
                        @foreach ($ruangs as $ru)
                            <option value="{{ $ru->id }}">{{ $ru->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Sesi Waktu</label>
                    <select id="edit-sesi_id" name="sesi_id" required class="w-full bg-white">
                        @foreach ($sesis as $se)
                            <option value="{{ $se->id }}">{{ $se->nama }} ({{ substr($se->jam_mulai,0,5) }} - {{ substr($se->jam_selesai,0,5) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Tipe Ujian</label>
                    <select id="edit-exam_type" name="exam_type" required class="w-full bg-white">
                        <option value="UTS">UTS</option>
                        <option value="UAS">UAS</option>
                        <option value="KUIS">Kuis</option>
                        <option value="LATIHAN">Latihan Mandiri</option>
                        <option value="SELEKSI">Seleksi Masuk</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Passing Grade (KKM)</label>
                    <input type="number" step="0.01" id="edit-passing_grade" name="passing_grade" required class="w-full">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Nama / Judul Ujian</label>
                <input type="text" id="edit-title" name="title" required class="w-full">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Kelas Sasaran (Opsional)</label>
                <select id="edit-class_id" name="class_id" class="w-full bg-white">
                    <option value="">-- Semua Kelas (Bisa Diakses Semua Kelas) --</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Waktu Mulai</label>
                    <input type="datetime-local" id="edit-start_time" name="start_time" required class="w-full">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Waktu Selesai</label>
                    <input type="datetime-local" id="edit-end_time" name="end_time" required class="w-full">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Durasi (Menit)</label>
                    <input type="number" id="edit-duration_minutes" name="duration_minutes" required min="5" class="w-full">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Jumlah Soal</label>
                    <input type="number" id="edit-total_questions" name="total_questions" required min="1" class="w-full">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Petunjuk Pengerjaan (Opsional)</label>
                <textarea id="edit-petunjuk" name="petunjuk" rows="2" class="w-full"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input id="edit-is_random" name="is_random" type="checkbox" value="1" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                <label for="edit-is_random" class="text-xs font-semibold text-slate-600 cursor-pointer">Acak Urutan Soal</label>
            </div>
        </div>

        {{-- Fixed Footer Edit --}}
        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2 bg-slate-50 flex-shrink-0">
            <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-300 text-slate-600 text-xs font-semibold hover:bg-slate-100 transition cursor-pointer" style="border-radius: var(--radius-md);">Batal</button>
            <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-900 text-white text-xs font-bold transition cursor-pointer" style="border-radius: var(--radius-md);">Simpan</button>
        </div>
    </form>
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
        document.getElementById('edit-class_id').value = exam.class_id || '';
        document.getElementById('edit-title').value = exam.title;
        document.getElementById('edit-tahun_akademik_id').value = exam.tahun_akademik_id || '';
        document.getElementById('edit-jenis_ujian_id').value = exam.jenis_ujian_id || '';
        document.getElementById('edit-ruang_id').value = exam.ruang_id || '';
        document.getElementById('edit-sesi_id').value = exam.sesi_id || '';
        document.getElementById('edit-exam_type').value = exam.exam_type || 'UTS';
        document.getElementById('edit-passing_grade').value = exam.passing_grade || '60';
        document.getElementById('edit-petunjuk').value = exam.petunjuk || '';
        
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
            tahun_akademik_id: this.querySelector('select[name="tahun_akademik_id"]').value,
            jenis_ujian_id: this.querySelector('select[name="jenis_ujian_id"]').value,
            ruang_id: this.querySelector('select[name="ruang_id"]').value,
            sesi_id: this.querySelector('select[name="sesi_id"]').value,
            exam_type: this.querySelector('select[name="exam_type"]').value,
            passing_grade: this.querySelector('input[name="passing_grade"]').value,
            title: this.querySelector('input[name="title"]').value,
            class_id: this.querySelector('select[name="class_id"]').value || null,
            start_time: this.querySelector('input[name="start_time"]').value,
            end_time: this.querySelector('input[name="end_time"]').value,
            duration_minutes: this.querySelector('input[name="duration_minutes"]').value,
            total_questions: this.querySelector('input[name="total_questions"]').value,
            petunjuk: this.querySelector('textarea[name="petunjuk"]').value,
            is_random: this.querySelector('input[name="is_random"]').checked ? 1 : 0
        };
        
        axios.post("{{ route('dosen.exams.store') }}", data)
            .then(res => {
                if (res.data.success) {
                    closeAddModal();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.data.message,
                        confirmButtonColor: '#0D47A1'
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
            tahun_akademik_id: document.getElementById('edit-tahun_akademik_id').value,
            jenis_ujian_id: document.getElementById('edit-jenis_ujian_id').value,
            ruang_id: document.getElementById('edit-ruang_id').value,
            sesi_id: document.getElementById('edit-sesi_id').value,
            exam_type: document.getElementById('edit-exam_type').value,
            passing_grade: document.getElementById('edit-passing_grade').value,
            title: document.getElementById('edit-title').value,
            class_id: document.getElementById('edit-class_id').value || null,
            start_time: document.getElementById('edit-start_time').value,
            end_time: document.getElementById('edit-end_time').value,
            duration_minutes: document.getElementById('edit-duration_minutes').value,
            total_questions: document.getElementById('edit-total_questions').value,
            petunjuk: document.getElementById('edit-petunjuk').value,
            is_random: document.getElementById('edit-is_random').checked ? 1 : 0
        };

        axios.put(`/dosen/jadwal-ujian/${id}`, data)
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
            confirmButtonColor: '#14532d',
            cancelButtonColor: '#EF4444',
            confirmButtonText: 'Ya, Perbarui',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post(`/dosen/jadwal-ujian/regenerate/${id}`)
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
            text: `Anda akan menghapus sesi ujian "${title}". Seluruh lembar jawaban dan nilai mahasiswa terkait akan ikut terhapus!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/dosen/jadwal-ujian/${id}`)
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
