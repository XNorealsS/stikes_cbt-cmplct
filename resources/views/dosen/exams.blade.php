@extends('layouts.dosen')

@section('title', 'Jadwal Ujian - CBT STIKES Muhammadiyah Lhokseumawe')

@section('dosen-content')
<div class="space-y-8">
    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Dosen &gt; Jadwal Ujian</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Jadwal &amp; Sesi Ujian
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Buat, kelola sesi ujian aktif, dan bagikan token ujian kepada mahasiswa.</p>
            </div>
            <button type="button" onclick="openAddModal()" class="rounded border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-green-800 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                <i class="fa-solid fa-calendar-plus text-xs"></i>
                <span>Buat Sesi Ujian</span>
            </button>
        </div>
    </div>

    <!-- Exams Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="py-4 px-6">Informasi Ujian</th>
                        <th class="py-4 px-6">Detail Sesi & Ruang</th>
                        <th class="py-4 px-6 text-center">Token</th>
                        <th class="py-4 px-6">Durasi & Soal</th>
                        <th class="py-4 px-6">Waktu Aktif</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($exams as $exam)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="py-4 px-6">
                            <span class="font-bold text-gray-900 block text-base">{{ $exam->title }}</span>
                            <div class="flex flex-wrap gap-1.5 mt-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $exam->examTypeBadgeClass() }}">{{ $exam->exam_type }}</span>
                                <span class="bg-blue-50 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded border border-blue-100">
                                    PG: {{ $exam->passing_grade }}%
                                </span>
                                @if ($exam->classRoom)
                                    <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded border border-emerald-200">
                                        Kelas: {{ $exam->classRoom->name }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-semibold text-gray-700 block">{{ $exam->course->name }}</span>
                            <div class="text-xs text-gray-400 mt-0.5 space-y-0.5">
                                <div><span class="font-bold text-gray-500">Ruang:</span> {{ $exam->ruang ? $exam->ruang->nama : '-' }}</div>
                                <div><span class="font-bold text-gray-500">Sesi:</span> {{ $exam->sesi ? $exam->sesi->nama : '-' }}</div>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <div class="inline-flex items-center space-x-2">
                                <span id="token-text-{{ $exam->id }}" class="font-mono font-black bg-gray-100 border text-gray-800 px-3 py-1.5 rounded-lg text-sm tracking-widest">{{ $exam->token }}</span>
                                <button type="button" onclick="regenerateToken({{ $exam->id }}, '{{ $exam->title }}')" title="Regenerasi Token Baru" class="text-primary hover:text-blue-800 transition">
                                    <i class="fa-solid fa-arrows-rotate text-sm"></i>
                                </button>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="block text-gray-600"><i class="fa-regular fa-clock mr-1.5 text-gray-400"></i>{{ $exam->duration_minutes }} Menit</span>
                            <span class="block text-xs text-gray-400 mt-0.5"><i class="fa-solid fa-list-check mr-1.5 text-gray-400"></i>{{ $exam->total_questions }} Soal ({{ $exam->is_random ? 'Acak' : 'Urut' }})</span>
                        </td>
                        <td class="py-4 px-6 text-xs text-gray-500">
                            <span class="block"><span class="font-bold text-gray-700">Mulai:</span> {{ $exam->start_time->format('d/m/Y H:i') }} WIB</span>
                            <span class="block mt-0.5"><span class="font-bold text-gray-700">Selesai:</span> {{ $exam->end_time->format('d/m/Y H:i') }} WIB</span>
                        </td>
                        <td class="py-4 px-6 text-right space-x-2">
                            <button type="button" onclick="openEditModal({{ json_encode($exam) }})" class="bg-yellow-50 hover:bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150 inline-flex items-center space-x-1">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>Edit</span>
                            </button>
                            <button type="button" onclick="confirmDelete({{ $exam->id }}, '{{ $exam->title }}')" class="bg-red-50 hover:bg-red-100 text-red-700 px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150 inline-flex items-center space-x-1">
                                <i class="fa-solid fa-trash-can"></i>
                                <span>Hapus</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-gray-400">Anda belum menjadwalkan ujian apapun.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Jadwal Ujian -->
<div id="add-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 my-8">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-calendar-check text-primary mr-2"></i>Buat Sesi Ujian Baru</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="add-form" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Bank Soal</label>
                <select name="bank_soal_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition bg-white">
                    <option value="" disabled selected>-- Pilih Bank Soal --</option>
                    @foreach ($bankSoals as $bs)
                        <option value="{{ $bs->id }}">{{ $bs->nama }} (MK: {{ $bs->course->name }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun Akademik</label>
                    <select name="tahun_akademik_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary text-sm transition bg-white">
                        @foreach ($tahunAkademiks as $ta)
                            <option value="{{ $ta->id }}" {{ $ta->is_aktif ? 'selected' : '' }}>{{ $ta->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Ujian</label>
                    <select name="jenis_ujian_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary text-sm transition bg-white">
                        @foreach ($jenisUjians as $ju)
                            <option value="{{ $ju->id }}">{{ $ju->nama }} ({{ $ju->kode }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Ruang Ujian</label>
                    <select name="ruang_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary text-sm transition bg-white">
                        @foreach ($ruangs as $ru)
                            <option value="{{ $ru->id }}">{{ $ru->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Sesi Waktu</label>
                    <select name="sesi_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary text-sm transition bg-white">
                        @foreach ($sesis as $se)
                            <option value="{{ $se->id }}">{{ $se->nama }} ({{ substr($se->jam_mulai,0,5) }} - {{ substr($se->jam_selesai,0,5) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe Ujian</label>
                    <select name="exam_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary text-sm transition bg-white">
                        <option value="UTS">UTS</option>
                        <option value="UAS">UAS</option>
                        <option value="KUIS">Kuis</option>
                        <option value="LATIHAN">Latihan Mandiri</option>
                        <option value="SELEKSI">Seleksi Masuk</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Passing Grade (KKM)</label>
                    <input type="number" step="0.01" name="passing_grade" value="60" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary text-sm transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama / Judul Ujian</label>
                <input type="text" name="title" required placeholder="Contoh: UTS KDP Ganjil" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kelas Sasaran (Opsional)</label>
                <select name="class_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition bg-white">
                    <option value="">-- Semua Kelas (Bisa Diakses Semua Kelas) --</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Waktu Mulai</label>
                    <input type="datetime-local" name="start_time" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Waktu Selesai</label>
                    <input type="datetime-local" name="end_time" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Durasi Pengerjaan (Menit)</label>
                    <input type="number" name="duration_minutes" required min="5" placeholder="Contoh: 90" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Soal Diujikan</label>
                    <input type="number" name="total_questions" required min="1" placeholder="Contoh: 50" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Petunjuk Pengerjaan Ujian (Opsional)</label>
                <textarea name="petunjuk" rows="3" placeholder="Contoh: Berdoalah sebelum mengerjakan. Pilih satu jawaban paling tepat." class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary text-sm transition"></textarea>
            </div>

            <div class="flex items-center mt-2">
                <input id="add-is_random" name="is_random" type="checkbox" value="1" checked class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                <label for="add-is_random" class="ml-2 block text-sm font-semibold text-gray-700">Acak Urutan Soal (Sangat Direkomendasikan)</label>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-semibold transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Jadwal Ujian -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 my-8">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-calendar-day text-primary mr-2"></i>Edit Sesi Ujian</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="edit-form" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-id">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Bank Soal</label>
                <select id="edit-bank_soal_id" name="bank_soal_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition bg-white">
                    <option value="" disabled>-- Pilih Bank Soal --</option>
                    @foreach ($bankSoals as $bs)
                        <option value="{{ $bs->id }}">{{ $bs->nama }} (MK: {{ $bs->course->name }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun Akademik</label>
                    <select id="edit-tahun_akademik_id" name="tahun_akademik_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary text-sm transition bg-white">
                        @foreach ($tahunAkademiks as $ta)
                            <option value="{{ $ta->id }}">{{ $ta->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Ujian</label>
                    <select id="edit-jenis_ujian_id" name="jenis_ujian_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary text-sm transition bg-white">
                        @foreach ($jenisUjians as $ju)
                            <option value="{{ $ju->id }}">{{ $ju->nama }} ({{ $ju->kode }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Ruang Ujian</label>
                    <select id="edit-ruang_id" name="ruang_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary text-sm transition bg-white">
                        @foreach ($ruangs as $ru)
                            <option value="{{ $ru->id }}">{{ $ru->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Sesi Waktu</label>
                    <select id="edit-sesi_id" name="sesi_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary text-sm transition bg-white">
                        @foreach ($sesis as $se)
                            <option value="{{ $se->id }}">{{ $se->nama }} ({{ substr($se->jam_mulai,0,5) }} - {{ substr($se->jam_selesai,0,5) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe Ujian</label>
                    <select id="edit-exam_type" name="exam_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary text-sm transition bg-white">
                        <option value="UTS">UTS</option>
                        <option value="UAS">UAS</option>
                        <option value="KUIS">Kuis</option>
                        <option value="LATIHAN">Latihan Mandiri</option>
                        <option value="SELEKSI">Seleksi Masuk</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Passing Grade (KKM)</label>
                    <input type="number" step="0.01" id="edit-passing_grade" name="passing_grade" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary text-sm transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama / Judul Ujian</label>
                <input type="text" id="edit-title" name="title" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kelas Sasaran (Opsional)</label>
                <select id="edit-class_id" name="class_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition bg-white">
                    <option value="">-- Semua Kelas (Bisa Diakses Semua Kelas) --</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Waktu Mulai</label>
                    <input type="datetime-local" id="edit-start_time" name="start_time" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Waktu Selesai</label>
                    <input type="datetime-local" id="edit-end_time" name="end_time" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Durasi Pengerjaan (Menit)</label>
                    <input type="number" id="edit-duration_minutes" name="duration_minutes" required min="5" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Soal Diujikan</label>
                    <input type="number" id="edit-total_questions" name="total_questions" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Petunjuk Pengerjaan Ujian (Opsional)</label>
                <textarea id="edit-petunjuk" name="petunjuk" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary text-sm transition"></textarea>
            </div>

            <div class="flex items-center mt-2">
                <input id="edit-is_random" name="is_random" type="checkbox" value="1" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                <label for="edit-is_random" class="ml-2 block text-sm font-semibold text-gray-700">Acak Urutan Soal</label>
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
