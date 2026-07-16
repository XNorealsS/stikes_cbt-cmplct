@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.dosen')

@section('title', 'Detail Bank Soal - E-Learning STIKesMu')

@section(auth()->user()->role === 'admin' ? 'admin-content' : 'dosen-content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center bg-white p-4 rounded-xl border border-gray-250 shadow-sm">
        <h1 class="text-xl font-black text-primary tracking-tight">Detail Bank Soal</h1>
        <a href="{{ auth()->user()->role === 'admin' ? route('admin.bank-soal.index') : route('dosen.questions.index') }}" class="bg-red-600 hover:bg-red-750 text-white font-bold py-1.5 px-4 rounded-lg text-xs uppercase tracking-wider transition flex items-center space-x-1.5 shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Metadata Panel -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Action Box -->
        <div class="bg-white p-6 rounded-xl border border-gray-250 shadow-sm flex flex-col justify-between">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Nama Bank Soal</span>
                <span class="text-sm font-black text-gray-900 leading-tight block">{{ $bankSoal->nama }}</span>
                @if($bankSoal->deskripsi)
                    <p class="text-xs text-gray-500 mt-2">{{ $bankSoal->deskripsi }}</p>
                @endif
            </div>

            <div class="pt-4 flex flex-wrap gap-2 border-t mt-4">
                <button type="button" onclick="openImportModal()" class="flex-grow bg-green-700 text-white font-bold py-2 px-3 rounded-lg text-[10px] uppercase tracking-wider transition shadow-sm cursor-pointer">
                    <i class="fa-solid fa-file-import mr-1"></i> Impor Massal Soal
                </button>
                <button type="button" onclick="openAddModal()" class="flex-grow bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg text-[10px] uppercase tracking-wider transition shadow-sm cursor-pointer">
                    <i class="fa-solid fa-plus mr-1"></i> Tambah Soal Manual
                </button>
            </div>
        </div>

        <!-- Metadata Info Grid -->
        @php
            $difficultyEasy = $questions->where('difficulty', 'mudah')->count();
            $difficultyMedium = $questions->where('difficulty', 'sedang')->count();
            $difficultyHard = $questions->where('difficulty', 'sulit')->count();
        @endphp
        <div class="bg-white rounded-xl border border-gray-250 shadow-sm overflow-hidden lg:col-span-2">
            <table class="w-full text-left text-xs border-collapse">
                <tbody>
                    <tr class="border-b border-gray-200">
                        <td class="py-2.5 px-4 bg-gray-50 font-bold text-gray-500 w-32 border-r">Kode Bank Soal</td>
                        <td class="py-2.5 px-4 font-mono font-bold text-primary">{{ $bankSoal->kode ?? '-' }}</td>
                        <td class="py-2.5 px-4 bg-gray-50 font-bold text-gray-500 w-32 border-r border-l">Total Soal</td>
                        <td class="py-2.5 px-4 font-bold text-gray-650">{{ $questions->count() }} Soal</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-2.5 px-4 bg-gray-50 font-bold text-gray-500 border-r">Mata Kuliah</td>
                        <td class="py-2.5 px-4 font-bold text-gray-800">{{ $bankSoal->course->name }} ({{ $bankSoal->course->code }})</td>
                        <td class="py-2.5 px-4 bg-gray-50 font-bold text-gray-500 border-r border-l">Kesulitan Mudah</td>
                        <td class="py-2.5 px-4 text-green-700 font-bold">{{ $difficultyEasy }} Soal</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-2.5 px-4 bg-gray-50 font-bold text-gray-500 border-r">Dosen Pematur</td>
                        <td class="py-2.5 px-4 font-semibold text-gray-700">{{ $bankSoal->dosen->name }}</td>
                        <td class="py-2.5 px-4 bg-gray-50 font-bold text-gray-500 border-r border-l">Kesulitan Sedang</td>
                        <td class="py-2.5 px-4 text-yellow-750 font-bold">{{ $difficultyMedium }} Soal</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-4 bg-gray-50 font-bold text-gray-500 border-r">Status</td>
                        <td class="py-2.5 px-4">
                            @if($bankSoal->is_aktif)
                                <span class="px-2.5 py-0.5 text-[9px] font-extrabold rounded-full bg-emerald-50 text-emerald-800 border border-emerald-100 uppercase">Aktif</span>
                            @else
                                <span class="px-2.5 py-0.5 text-[9px] font-extrabold rounded-full bg-red-50 text-red-800 border border-red-100 uppercase">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-4 bg-gray-50 font-bold text-gray-500 border-r border-l">Kesulitan Sulit</td>
                        <td class="py-2.5 px-4 text-red-700 font-bold">{{ $difficultyHard }} Soal</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Questions list -->
    <div class="space-y-4">
        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-widest border-b pb-2"><i class="fa-solid fa-list-ol mr-1"></i> I. Soal Pilihan Ganda</h3>
        
        <div class="space-y-4">
            @forelse ($questions as $index => $q)
                <div class="bg-white border border-gray-250 rounded-xl p-6 shadow-sm relative hover:border-primary/50 transition">
                    <!-- Top Info row -->
                    <div class="flex justify-between items-start border-b pb-3 mb-3">
                        <div class="flex items-center space-x-2">
                            <span class="font-black text-gray-900 text-xs bg-gray-100 h-6 w-6 rounded-full flex items-center justify-center">
                                {{ $index + 1 }}
                            </span>
                            <span class="px-2.5 py-0.5 text-[9px] font-bold rounded bg-gray-100 text-gray-700 uppercase tracking-wide border border-gray-200">
                                {{ $q->difficulty }}
                            </span>
                            @if ($q->category)
                                <span class="px-2.5 py-0.5 text-[9px] font-bold rounded bg-blue-50 text-primary uppercase tracking-wide border border-blue-100">
                                    {{ $q->category }}
                                </span>
                            @endif
                        </div>
                        <div class="flex space-x-1.5">
                            <button type="button" onclick="previewQuestion({{ $q->id }})" class="bg-blue-50 hover:bg-blue-100 text-primary border border-blue-200 font-bold px-2.5 py-1 rounded text-[10px] transition uppercase">
                                <i class="fa-solid fa-eye mr-1"></i> Preview
                            </button>
                            <button type="button" onclick="openEditModal({{ json_encode($q) }})" class="bg-yellow-50 hover:bg-yellow-100 text-yellow-750 border border-yellow-250 font-bold px-2.5 py-1 rounded text-[10px] transition uppercase">
                                <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                            </button>
                            <button type="button" onclick="confirmDelete({{ $q->id }}, {{ $index + 1 }})" class="bg-red-50 hover:bg-red-100 text-red-750 border border-red-250 font-bold px-2.5 py-1 rounded text-[10px] transition uppercase">
                                <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                            </button>
                        </div>
                    </div>

                    <!-- Question Content -->
                    <div class="space-y-4 text-xs text-gray-800 leading-relaxed font-semibold">
                        <div class="prose max-w-none">
                            {!! nl2br(e($q->question_text)) !!}
                        </div>

                        <!-- Options List -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                            <div class="p-3 border rounded-xl flex items-center space-x-2.5 {{ $q->correct_option === 'A' ? 'bg-emerald-50 border-emerald-500/50 text-emerald-800' : 'bg-slate-50 border-slate-100' }}">
                                <span class="h-5 w-5 rounded-full flex items-center justify-center font-bold text-[10px] {{ $q->correct_option === 'A' ? 'bg-emerald-600 text-white' : 'bg-white border text-gray-500' }}">A</span>
                                <span class="flex-1 truncate">{{ $q->option_a }}</span>
                            </div>
                            <div class="p-3 border rounded-xl flex items-center space-x-2.5 {{ $q->correct_option === 'B' ? 'bg-emerald-50 border-emerald-500/50 text-emerald-800' : 'bg-slate-50 border-slate-100' }}">
                                <span class="h-5 w-5 rounded-full flex items-center justify-center font-bold text-[10px] {{ $q->correct_option === 'B' ? 'bg-emerald-600 text-white' : 'bg-white border text-gray-500' }}">B</span>
                                <span class="flex-1 truncate">{{ $q->option_b }}</span>
                            </div>
                            <div class="p-3 border rounded-xl flex items-center space-x-2.5 {{ $q->correct_option === 'C' ? 'bg-emerald-50 border-emerald-500/50 text-emerald-800' : 'bg-slate-50 border-slate-100' }}">
                                <span class="h-5 w-5 rounded-full flex items-center justify-center font-bold text-[10px] {{ $q->correct_option === 'C' ? 'bg-emerald-600 text-white' : 'bg-white border text-gray-500' }}">C</span>
                                <span class="flex-1 truncate">{{ $q->option_c }}</span>
                            </div>
                            <div class="p-3 border rounded-xl flex items-center space-x-2.5 {{ $q->correct_option === 'D' ? 'bg-emerald-50 border-emerald-500/50 text-emerald-800' : 'bg-slate-50 border-slate-100' }}">
                                <span class="h-5 w-5 rounded-full flex items-center justify-center font-bold text-[10px] {{ $q->correct_option === 'D' ? 'bg-emerald-600 text-white' : 'bg-white border text-gray-500' }}">D</span>
                                <span class="flex-1 truncate">{{ $q->option_d }}</span>
                            </div>
                            <div class="p-3 border rounded-xl flex items-center space-x-2.5 {{ $q->correct_option === 'E' ? 'bg-emerald-50 border-emerald-500/50 text-emerald-800' : 'bg-slate-50 border-slate-100' }}">
                                <span class="h-5 w-5 rounded-full flex items-center justify-center font-bold text-[10px] {{ $q->correct_option === 'E' ? 'bg-emerald-600 text-white' : 'bg-white border text-gray-500' }}">E</span>
                                <span class="flex-1 truncate">{{ $q->option_e }}</span>
                            </div>
                        </div>

                        <!-- Explanation info -->
                        @if ($q->explanation)
                            <div class="bg-gray-50 border p-3 rounded-lg text-[11px] text-gray-550 italic mt-2.5">
                                <span class="font-bold text-gray-700 block not-italic mb-0.5">Pembahasan Soal:</span>
                                {{ $q->explanation }}
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border p-12 text-center text-gray-400">
                    <i class="fa-solid fa-list-check text-4xl mb-3 text-gray-250 block"></i>
                    Belum ada pertanyaan terdaftar di bank soal ini.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Tambah Soal Manual -->
<div id="add-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 my-8">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest"><i class="fa-solid fa-plus text-primary mr-1.5"></i>Tambah Soal Manual</h3>
            <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="add-form" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Tingkat Kesulitan</label>
                    <select name="difficulty" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary bg-white">
                        <option value="mudah">Mudah</option>
                        <option value="sedang" selected>Sedang</option>
                        <option value="sulit">Sulit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Kategori / Topik</label>
                    <input type="text" name="category" placeholder="Contoh: Keperawatan Dasar" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Teks Pertanyaan</label>
                <textarea name="question_text" rows="4" required placeholder="Masukkan pertanyaan ujian..." class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
            </div>

            <!-- Options inputs -->
            <div class="space-y-3">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Pilihan Jawaban</label>
                
                <div class="flex items-center space-x-2">
                    <span class="bg-gray-100 border text-gray-600 font-bold h-7 w-7 rounded-full flex items-center justify-center text-xs">A</span>
                    <input type="text" name="option_a" required placeholder="Jawaban A" class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="flex items-center space-x-2">
                    <span class="bg-gray-100 border text-gray-600 font-bold h-7 w-7 rounded-full flex items-center justify-center text-xs">B</span>
                    <input type="text" name="option_b" required placeholder="Jawaban B" class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="flex items-center space-x-2">
                    <span class="bg-gray-100 border text-gray-600 font-bold h-7 w-7 rounded-full flex items-center justify-center text-xs">C</span>
                    <input type="text" name="option_c" required placeholder="Jawaban C" class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="flex items-center space-x-2">
                    <span class="bg-gray-100 border text-gray-600 font-bold h-7 w-7 rounded-full flex items-center justify-center text-xs">D</span>
                    <input type="text" name="option_d" required placeholder="Jawaban D" class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="flex items-center space-x-2">
                    <span class="bg-gray-100 border text-gray-600 font-bold h-7 w-7 rounded-full flex items-center justify-center text-xs">E</span>
                    <input type="text" name="option_e" required placeholder="Jawaban E" class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Kunci Jawaban</label>
                    <select name="correct_option" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary bg-white font-bold text-emerald-800">
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Pembahasan Soal (Opsional)</label>
                    <input type="text" name="explanation" placeholder="Penjelasan singkat jawaban yang benar..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-full text-xs font-bold hover:bg-gray-50 transition cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 bg-primary hover:bg-emerald-850 text-white rounded-full text-xs font-bold transition shadow-sm cursor-pointer">Simpan Soal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Soal -->
<div id="edit-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 my-8">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest"><i class="fa-solid fa-pen-to-square text-primary mr-1.5"></i>Edit Soal</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="edit-form" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-id">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Tingkat Kesulitan</label>
                    <select id="edit-difficulty" name="difficulty" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary bg-white">
                        <option value="mudah">Mudah</option>
                        <option value="sedang">Sedang</option>
                        <option value="sulit">Sulit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Kategori / Topik</label>
                    <input type="text" id="edit-category" name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Teks Pertanyaan</label>
                <textarea id="edit-question_text" name="question_text" rows="4" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
            </div>

            <!-- Options inputs -->
            <div class="space-y-3">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Pilihan Jawaban</label>
                
                <div class="flex items-center space-x-2">
                    <span class="bg-gray-100 border text-gray-600 font-bold h-7 w-7 rounded-full flex items-center justify-center text-xs">A</span>
                    <input type="text" id="edit-option_a" name="option_a" required class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="flex items-center space-x-2">
                    <span class="bg-gray-100 border text-gray-600 font-bold h-7 w-7 rounded-full flex items-center justify-center text-xs">B</span>
                    <input type="text" id="edit-option_b" name="option_b" required class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="flex items-center space-x-2">
                    <span class="bg-gray-100 border text-gray-600 font-bold h-7 w-7 rounded-full flex items-center justify-center text-xs">C</span>
                    <input type="text" id="edit-option_c" name="option_c" required class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="flex items-center space-x-2">
                    <span class="bg-gray-100 border text-gray-600 font-bold h-7 w-7 rounded-full flex items-center justify-center text-xs">D</span>
                    <input type="text" id="edit-option_d" name="option_d" required class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="flex items-center space-x-2">
                    <span class="bg-gray-100 border text-gray-600 font-bold h-7 w-7 rounded-full flex items-center justify-center text-xs">E</span>
                    <input type="text" id="edit-option_e" name="option_e" required class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Kunci Jawaban</label>
                    <select id="edit-correct_option" name="correct_option" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary bg-white font-bold text-emerald-800">
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Pembahasan Soal (Opsional)</label>
                    <input type="text" id="edit-explanation" name="explanation" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-full text-xs font-bold hover:bg-gray-50 transition cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 bg-primary hover:bg-emerald-850 text-white rounded-full text-xs font-bold transition shadow-sm cursor-pointer">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Impor Massal -->
<!-- Modal Impor Soal Massal (SIAKAD Flat Style) -->
<div id="import-modal" class="fixed inset-0 z-50 bg-slate-900/40 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-none max-w-2xl w-full border border-slate-300 shadow-xl overflow-hidden transform scale-95 transition-all duration-200">
        <!-- Modal Header -->
        <div class="px-5 py-3.5 border-b border-slate-300 flex justify-between items-center bg-slate-100">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-file-import text-emerald-700"></i>
                Impor Soal Massal dari Excel / Word
            </h3>
            <button type="button" onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer p-1">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <form id="import-form" class="p-5 space-y-4 max-h-[80vh] overflow-y-auto">
            @csrf

            <!-- Step 1: Unduh Template Resmi -->
            <div class="border border-emerald-200 bg-emerald-50/50 p-3.5 rounded-none space-y-2">
                <div class="flex items-start gap-2">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center bg-emerald-700 text-white text-[10px] font-bold">1</span>
                    <div>
                        <h4 class="text-xs font-bold text-emerald-900">Unduh Format Template Resmi</h4>
                        <p class="text-[11px] text-emerald-800 mt-0.5">Silakan unduh template resmi di bawah ini yang telah dilengkapi contoh baris &amp; struktur kolom yang rapi.</p>
                    </div>
                </div>
                <div class="pt-1 flex flex-wrap gap-2">
                    <button type="button" onclick="downloadExcelTemplate(event)" class="rounded-none border border-transparent bg-green-700 hover:bg-green-800 text-white text-xs font-semibold px-3 py-1.5 transition flex items-center gap-1.5 cursor-pointer shadow-none">
                        <i class="fa-solid fa-file-excel text-xs"></i>
                        <span>Unduh Template Excel (.xlsx)</span>
                    </button>
                </div>
            </div>

            <!-- Step 2: Panduan Struktur Kolom -->
            <div class="border border-slate-300 bg-white p-3.5 rounded-none space-y-2 text-xs text-slate-700">
                <div class="flex items-start gap-2">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center bg-slate-700 text-white text-[10px] font-bold">2</span>
                    <h4 class="text-xs font-bold text-slate-800">Panduan Struktur Kolom Excel</h4>
                </div>
                <div class="overflow-x-auto pt-1">
                    <table class="w-full text-[11px] border-collapse border border-slate-300">
                        <thead>
                            <tr class="bg-slate-100 font-bold text-slate-700">
                                <th class="border border-slate-300 p-1.5 text-center w-12">Kolom</th>
                                <th class="border border-slate-300 p-1.5 text-left">Nama Header</th>
                                <th class="border border-slate-300 p-1.5 text-left">Keterangan / Nilai Valid</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr><td class="p-1.5 border text-center font-mono font-bold">A</td><td class="p-1.5 border font-semibold text-slate-800">Pertanyaan / Teks Soal</td><td class="p-1.5 border text-slate-600">Teks pertanyaan utama (Wajib)</td></tr>
                            <tr><td class="p-1.5 border text-center font-mono font-bold">B</td><td class="p-1.5 border font-semibold text-slate-800">Tingkat Kesulitan</td><td class="p-1.5 border font-bold text-emerald-700 font-mono">mudah / sedang / sulit</td></tr>
                            <tr><td class="p-1.5 border text-center font-mono font-bold">C - G</td><td class="p-1.5 border font-semibold text-slate-800">Pilihan A s/d E</td><td class="p-1.5 border text-slate-600">Jawaban A, B, C, D, E (A-D wajib, E opsional)</td></tr>
                            <tr><td class="p-1.5 border text-center font-mono font-bold">H</td><td class="p-1.5 border font-semibold text-slate-800">Kunci Jawaban</td><td class="p-1.5 border font-bold text-emerald-700 font-mono">A / B / C / D / E</td></tr>
                            <tr><td class="p-1.5 border text-center font-mono font-bold">I</td><td class="p-1.5 border font-semibold text-slate-800">Pembahasan</td><td class="p-1.5 border text-slate-500">Penjelasan kunci jawaban (Opsional)</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Step 3: Pilih File Excel & Upload -->
            <div class="space-y-3 pt-1">
                <div class="flex items-start gap-2">
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center bg-slate-700 text-white text-[10px] font-bold">3</span>
                    <h4 class="text-xs font-bold text-slate-800">Unggah Berkas Excel (.xlsx)</h4>
                </div>
                <input type="hidden" name="file_type" value="excel">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih File Excel (.xlsx / .xls / .csv)</label>
                    <input type="file" name="import_file" accept=".xlsx,.xls,.csv" required class="w-full border border-slate-300 rounded-none px-3 py-2 text-xs focus:border-green-600 bg-white text-slate-800">
                </div>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-300">
                <button type="button" onclick="closeImportModal()" class="rounded-none border border-slate-300 bg-white px-4 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btn-import-submit" class="rounded-none border border-transparent bg-green-700 px-5 py-1.5 text-xs font-semibold text-white hover:bg-green-800 transition cursor-pointer shadow-none">
                    <i class="fa-solid fa-file-import mr-1"></i> Mulai Impor Soal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Preview Soal -->
<div id="preview-modal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 my-8">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest"><i class="fa-solid fa-eye text-primary mr-1.5"></i>Preview Detail Soal</h3>
            <button onclick="closePreviewModal()" class="text-gray-400 hover:text-gray-650 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto text-xs text-gray-800">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Teks Soal</span>
                <div id="preview-text" class="bg-gray-50 border p-3 rounded-lg font-semibold leading-relaxed whitespace-pre-wrap"></div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tingkat Kesulitan</span>
                    <span id="preview-difficulty" class="font-bold capitalize"></span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Kategori / Topik</span>
                    <span id="preview-category" class="font-bold"></span>
                </div>
            </div>

            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2.5">Opsi & Kunci Jawaban</span>
                <div class="space-y-2" id="preview-options"></div>
            </div>

            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Pembahasan</span>
                <div id="preview-explanation" class="bg-gray-50 border p-3 rounded-lg italic text-gray-600 whitespace-pre-wrap"></div>
            </div>

            <div class="pt-4 flex justify-end border-t border-gray-100">
                <button type="button" onclick="closePreviewModal()" class="px-5 py-2.5 bg-primary hover:bg-emerald-850 text-white rounded-full text-xs font-bold transition shadow-sm cursor-pointer">Tutup Preview</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showError(msg) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: msg,
            confirmButtonColor: '#14532d'
        });
    }

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
    function openEditModal(q) {
        document.getElementById('edit-id').value = q.id;
        document.getElementById('edit-difficulty').value = q.difficulty;
        document.getElementById('edit-category').value = q.category || '';
        document.getElementById('edit-question_text').value = q.question_text;
        document.getElementById('edit-option_a').value = q.option_a;
        document.getElementById('edit-option_b').value = q.option_b;
        document.getElementById('edit-option_c').value = q.option_c;
        document.getElementById('edit-option_d').value = q.option_d;
        document.getElementById('edit-option_e').value = q.option_e;
        document.getElementById('edit-correct_option').value = q.correct_option;
        document.getElementById('edit-explanation').value = q.explanation || '';

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

    // Import Modal Functions
    function openImportModal() {
        const modal = document.getElementById('import-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.querySelector('div').classList.remove('scale-95');
            modal.querySelector('div').classList.add('scale-100');
        }, 10);
    }

    function closeImportModal() {
        const modal = document.getElementById('import-modal');
        modal.querySelector('div').classList.remove('scale-100');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.getElementById('import-form').reset();
        }, 200);
    }

    // Template downloads
    function downloadExcelTemplate(e) {
        e.preventDefault();
        window.location.href = "{{ route('dosen.questions.template-excel') }}";
    }

    function downloadWordTemplate(e) {
        e.preventDefault();
        window.location.href = "{{ route('dosen.questions.template-word') }}";
    }

    // Submit Add Question
    document.getElementById('add-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        axios.post("/dosen/bank-soal/{{ $bankSoal->id }}/questions", formData)
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
                    : 'Gagal menambahkan soal.';
                showError(msg);
            });
    });

    // Submit Edit Question
    document.getElementById('edit-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        const formData = {
            difficulty: document.getElementById('edit-difficulty').value,
            category: document.getElementById('edit-category').value,
            question_text: document.getElementById('edit-question_text').value,
            option_a: document.getElementById('edit-option_a').value,
            option_b: document.getElementById('edit-option_b').value,
            option_c: document.getElementById('edit-option_c').value,
            option_d: document.getElementById('edit-option_d').value,
            option_e: document.getElementById('edit-option_e').value,
            correct_option: document.getElementById('edit-correct_option').value,
            explanation: document.getElementById('edit-explanation').value,
        };

        axios.put(`/dosen/bank-soal/questions/${id}`, formData)
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
                    : 'Gagal memperbarui soal.';
                showError(msg);
            });
    });

    // Import Form Submit
    document.getElementById('import-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btnSubmit = document.getElementById('btn-import-submit');
        const originalText = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `<i class="fa-solid fa-circle-notch animate-spin mr-2"></i> Mengimpor...`;
        
        const formData = new FormData(this);

        axios.post("/dosen/bank-soal/{{ $bankSoal->id }}/questions/import", formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(res => {
            closeImportModal();
            Swal.fire({
                icon: 'success',
                title: 'Impor Berhasil',
                text: res.data.message,
                confirmButtonColor: '#14532d',
            }).then(() => {
                window.location.reload();
            });
        })
        .catch(err => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalText;
            const msg = err.response && err.response.data && err.response.data.message 
                ? err.response.data.message 
                : 'Gagal mengimpor soal.';
            showError(msg);
        });
    });

    // Delete confirm
    function confirmDelete(id, num) {
        Swal.fire({
            title: 'Hapus Soal?',
            text: `Anda akan menghapus soal nomor ${num}. Tindakan ini tidak dapat dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/dosen/bank-soal/questions/${id}`)
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
                            : 'Gagal menghapus soal.';
                        showError(msg);
                    });
            }
        });
    }

    // Preview Question
    function previewQuestion(id) {
        axios.get(`/dosen/bank-soal/questions/${id}/preview`)
            .then(res => {
                if (res.data.success) {
                    const q = res.data.question;
                    document.getElementById('preview-text').textContent = q.question_text;
                    document.getElementById('preview-difficulty').textContent = q.difficulty;
                    document.getElementById('preview-category').textContent = q.category || 'Tanpa Kategori';
                    document.getElementById('preview-explanation').textContent = q.explanation || 'Tidak ada pembahasan.';
                    
                    const optionsContainer = document.getElementById('preview-options');
                    optionsContainer.innerHTML = '';
                    
                    const opts = [
                        { label: 'A', text: q.option_a },
                        { label: 'B', text: q.option_b },
                        { label: 'C', text: q.option_c },
                        { label: 'D', text: q.option_d },
                        { label: 'E', text: q.option_e }
                    ];
                    
                    opts.forEach(opt => {
                        const isCorrect = q.correct_option === opt.label;
                        const card = document.createElement('div');
                        card.className = `p-2.5 border rounded-lg flex items-center space-x-2 ${isCorrect ? 'bg-emerald-50 border-emerald-400 text-emerald-800' : 'bg-slate-50 border-slate-100'}`;
                        
                        const badge = document.createElement('span');
                        badge.className = `h-5 w-5 rounded-full flex items-center justify-center font-bold text-[10px] ${isCorrect ? 'bg-emerald-600 text-white' : 'bg-white border text-gray-500'}`;
                        badge.textContent = opt.label;
                        
                        const text = document.createElement('span');
                        text.className = 'flex-1 truncate font-semibold';
                        text.textContent = opt.text;
                        
                        card.appendChild(badge);
                        card.appendChild(text);
                        optionsContainer.appendChild(card);
                    });

                    const modal = document.getElementById('preview-modal');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    setTimeout(() => {
                        modal.querySelector('div').classList.remove('scale-95');
                        modal.querySelector('div').classList.add('scale-100');
                    }, 10);
                }
            });
    }

    function closePreviewModal() {
        const modal = document.getElementById('preview-modal');
        modal.querySelector('div').classList.remove('scale-100');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 200);
    }
</script>
@endsection
