<?php

namespace App\Http\Controllers;

use App\Models\BankSoal;
use App\Models\Course;
use App\Models\Question;
use App\Models\ActivityLog;
use App\Services\QuestionImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BankSoalController extends Controller
{
    protected $importService;

    public function __construct(QuestionImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Display a listing of the bank soals.
     */
    public function index(Request $request)
    {
        $courses = Course::orderBy('name', 'asc')->get();
        $courseId = $request->query('course_id', '');
        $search = $request->query('search', '');

        $query = BankSoal::with('course')->withCount('questions')
            ->where('dosen_id', auth()->id())
            ->orderBy('id', 'desc');

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        if ($search) {
            $query->where('nama', 'like', "%{$search}%");
        }

        $bankSoals = $query->get();

        return view('dosen.bank-soal.index', compact('courses', 'bankSoals', 'courseId', 'search'));
    }

    /**
     * Store a newly created bank soal.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'nullable|string|max:50',
            'course_id' => 'required|exists:courses,id',
            'deskripsi' => 'nullable|string',
        ]);

        $data['dosen_id'] = auth()->id();
        $data['is_aktif'] = true;

        $bankSoal = BankSoal::create($data);

        ActivityLog::log('Tambah Bank Soal', "Dosen membuat bank soal baru: '{$bankSoal->nama}'.");

        return response()->json(['success' => true, 'message' => 'Bank Soal berhasil dibuat.']);
    }

    /**
     * Update the specified bank soal.
     */
    public function update(Request $request, $id)
    {
        $bankSoal = BankSoal::where('dosen_id', auth()->id())->findOrFail($id);

        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'nullable|string|max:50',
            'course_id' => 'required|exists:courses,id',
            'deskripsi' => 'nullable|string',
        ]);

        $bankSoal->update($data);

        ActivityLog::log('Edit Bank Soal', "Dosen memperbarui bank soal ID: {$bankSoal->id}.");

        return response()->json(['success' => true, 'message' => 'Bank Soal berhasil diperbarui.']);
    }

    /**
     * Remove the specified bank soal from storage.
     */
    public function destroy($id)
    {
        $bankSoal = BankSoal::where('dosen_id', auth()->id())->findOrFail($id);
        $nama = $bankSoal->nama;
        $bankSoal->delete();

        ActivityLog::log('Hapus Bank Soal', "Dosen menghapus bank soal '{$nama}'.");

        return response()->json(['success' => true, 'message' => 'Bank Soal berhasil dihapus.']);
    }

    /**
     * Toggle active status of bank soal.
     */
    public function toggleActive(Request $request, $id)
    {
        $bankSoal = BankSoal::where('dosen_id', auth()->id())->findOrFail($id);
        $bankSoal->is_aktif = !$bankSoal->is_aktif;
        $bankSoal->save();

        $statusStr = $bankSoal->is_aktif ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLog::log('Toggle Status Bank Soal', "Mengubah status bank soal '{$bankSoal->nama}' menjadi {$statusStr}.");

        return response()->json(['success' => true, 'is_aktif' => $bankSoal->is_aktif, 'message' => "Bank soal berhasil {$statusStr}."]);
    }

    /**
     * Show detail of questions in the bank soal.
     */
    public function show(Request $request, $id)
    {
        $bankSoal = BankSoal::with('course')->where('dosen_id', auth()->id())->findOrFail($id);
        
        $difficulty = $request->query('difficulty', '');
        $questionType = $request->query('question_type', '');
        $category = $request->query('category', '');
        $search = $request->query('search', '');

        $query = Question::where('bank_soal_id', $id)->orderBy('id', 'desc');

        if ($difficulty) $query->where('difficulty', $difficulty);
        if ($questionType) $query->where('question_type', $questionType);
        if ($category) $query->where('category', $category);
        if ($search) $query->where('question_text', 'like', "%{$search}%");

        $questions = $query->get();
        $categories = Question::where('bank_soal_id', $id)->whereNotNull('category')->distinct()->pluck('category');

        return view('dosen.bank-soal.show', compact('bankSoal', 'questions', 'difficulty', 'questionType', 'category', 'search', 'categories'));
    }

    /**
     * Store a question within a bank soal.
     */
    public function questionStore(Request $request, $bank_soal_id)
    {
        $bankSoal = BankSoal::where('dosen_id', auth()->id())->findOrFail($bank_soal_id);

        $data = $request->validate([
            'question_type' => 'nullable|string|in:pg,pg_kompleks,essai,isian,menjodohkan,benar_salah',
            'category' => 'nullable|string|max:255',
            'difficulty' => 'required|in:mudah,sedang,sulit',
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'option_e' => 'required|string',
            'correct_option' => 'required|in:A,B,C,D,E',
            'bobot' => 'nullable|numeric|min:0',
            'explanation' => 'nullable|string',
        ]);

        $data['bank_soal_id'] = $bankSoal->id;
        $data['course_id'] = $bankSoal->course_id;
        $data['question_type'] = $data['question_type'] ?? 'pg';

        $question = Question::create($data);

        ActivityLog::log('Tambah Soal', "Menambahkan soal baru ID: {$question->id} ke bank soal ID: {$bankSoal->id}.");

        return response()->json(['success' => true, 'message' => 'Soal berhasil ditambahkan.']);
    }

    /**
     * Update an existing question.
     */
    public function questionUpdate(Request $request, $id)
    {
        $question = Question::findOrFail($id);
        $bankSoal = BankSoal::where('dosen_id', auth()->id())->findOrFail($question->bank_soal_id);

        $data = $request->validate([
            'question_type' => 'nullable|string|in:pg,pg_kompleks,essai,isian,menjodohkan,benar_salah',
            'category' => 'nullable|string|max:255',
            'difficulty' => 'required|in:mudah,sedang,sulit',
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'option_e' => 'required|string',
            'correct_option' => 'required|in:A,B,C,D,E',
            'bobot' => 'nullable|numeric|min:0',
            'explanation' => 'nullable|string',
        ]);

        $data['question_type'] = $data['question_type'] ?? 'pg';
        $question->update($data);

        ActivityLog::log('Edit Soal', "Mengubah soal ID: {$question->id} di bank soal ID: {$bankSoal->id}.");

        return response()->json(['success' => true, 'message' => 'Soal berhasil diperbarui.']);
    }

    /**
     * Delete a question.
     */
    public function questionDestroy($id)
    {
        $question = Question::findOrFail($id);
        $bankSoal = BankSoal::where('dosen_id', auth()->id())->findOrFail($question->bank_soal_id);
        $question->delete();

        ActivityLog::log('Hapus Soal', "Menghapus soal ID: {$id} dari bank soal ID: {$bankSoal->id}.");

        return response()->json(['success' => true, 'message' => 'Soal berhasil dihapus.']);
    }

    /**
     * Import questions.
     */
    public function questionImport(Request $request, $bank_soal_id)
    {
        $bankSoal = BankSoal::where('dosen_id', auth()->id())->findOrFail($bank_soal_id);

        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('import_file');
        $tempPath = $file->getRealPath();

        $result = $this->importService->importExcel($tempPath, $bankSoal->course_id, 'Excel Import', $bankSoal->id);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => "Berhasil mengimpor {$result['count']} soal."
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor soal.',
                'errors' => $result['errors']
            ], 422);
        }
    }

    /**
     * Download Excel template (.xlsx).
     */
    public function downloadExcelTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Impor Soal');

        // Headers
        $headers = [
            'Pertanyaan / Teks Soal',
            'Tingkat Kesulitan (mudah/sedang/sulit)',
            'Pilihan A',
            'Pilihan B',
            'Pilihan C',
            'Pilihan D',
            'Pilihan E (Opsional)',
            'Kunci Jawaban (A/B/C/D/E)',
            'Pembahasan / Catatan (Opsional)'
        ];

        // Fill Header Row via fromArray
        $sheet->fromArray([$headers], null, 'A1');

        // Header Styling (STIKesMu Dark Green)
        $headerRange = 'A1:I1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '14532D'],
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Sample Data Rows
        $samples = [
            [
                'Siapakah pelopor utama keperawatan modern yang dikenal dengan julukan The Lady with the Lamp?',
                'mudah',
                'Florence Nightingale',
                'Clara Barton',
                'Dorothea Dix',
                'Mary Seacole',
                'Virginia Henderson',
                'A',
                'Florence Nightingale meletakkan dasar-dasar keperawatan modern saat Perang Krimea.'
            ],
            [
                'Berapakah batas rentang normal tekanan darah sistolik pada orang dewasa dalam kondisi istirahat?',
                'sedang',
                '90 - 120 mmHg',
                '120 - 140 mmHg',
                '140 - 160 mmHg',
                '160 - 180 mmHg',
                '60 - 80 mmHg',
                'A',
                'Sistolik normal dewasa adalah kurang dari 120 mmHg (rentang 90-120 mmHg).'
            ],
            [
                'Pemeriksaan spesifik yang paling tepat untuk konfirmasi diagnosis pasti malaria adalah:',
                'sulit',
                'Uji Kromatografi Cepat (RDT)',
                'Pemeriksaan Mikroskopis Tetes Tebal & Apusan Darah',
                'Hitung Jenis Leukosit (Diff Count)',
                'Pemeriksaan Laju Endap Darah (LED)',
                'Tes Serologi IgG/IgM',
                'B',
                'Baku emas (gold standard) diagnosis malaria adalah ditemukannya parasit plasmodium pada apusan darah.'
            ],
        ];

        // Fill Sample Data Rows via fromArray
        $sheet->fromArray($samples, null, 'A2');

        // Auto Column Widths
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_Impor_Soal_STIKesMu.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Download Word template.
     */
    public function downloadWordTemplate()
    {
        $path = public_path('templates/template_soal.docx');
        if (file_exists($path)) {
            return response()->download($path);
        }
        return response()->json(['success' => false, 'message' => 'Template tidak ditemukan.'], 404);
    }

    /**
     * Preview single question.
     */
    public function questionPreview($id)
    {
        $question = Question::with('matches')->findOrFail($id);
        $bankSoal = BankSoal::where('dosen_id', auth()->id())->findOrFail($question->bank_soal_id);
        return response()->json(['success' => true, 'question' => $question]);
    }
}
