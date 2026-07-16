<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Question;
use App\Models\Exam;
use App\Models\StudentExam;
use App\Models\StudentAnswer;
use App\Models\ActivityLog;
use App\Models\Ruang;
use App\Models\Sesi;
use App\Models\JenisUjian;
use App\Models\TahunAkademik;
use App\Services\QuestionImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DosenController extends Controller
{
    protected $importService;

    public function __construct(QuestionImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Dosen Dashboard
     */
    public function dashboard()
    {
        $courseCount = \App\Models\Course::count();
        $studentCount = \App\Models\User::where('role', 'mahasiswa')->count();
        $tugasCount = \App\Models\Tugas::where('user_id', auth()->id())->count();
        $examCount = \App\Models\Exam::where('dosen_id', auth()->id())->count();
        
        $activeExams = \App\Models\Exam::where('dosen_id', auth()->id())
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->get();

        return view('dosen.dashboard', compact('courseCount', 'studentCount', 'tugasCount', 'examCount', 'activeExams'));
    }

    /**
     * Jadwal Ujian index
     */
    public function jadwalUjian()
    {
        $bankSoals = \App\Models\BankSoal::with('course')
            ->where('dosen_id', auth()->id())
            ->where('is_aktif', true)
            ->get();
        $classes = \App\Models\ClassRoom::orderBy('name', 'asc')->get();
        $ruangs = Ruang::where('is_aktif', true)->orderBy('nama')->get();
        $sesis = Sesi::where('is_aktif', true)->orderBy('jam_mulai')->get();
        $jenisUjians = JenisUjian::where('is_aktif', true)->orderBy('kode')->get();
        $tahunAkademiks = TahunAkademik::orderBy('tahun_mulai', 'desc')->get();
        $exams = Exam::with(['course', 'bankSoal', 'classRoom', 'jenisUjian', 'ruang', 'sesi', 'tahunAkademik'])
            ->where('dosen_id', auth()->id())
            ->orderBy('start_time', 'desc')
            ->get();

        return view('dosen.exams', compact('bankSoals', 'classes', 'ruangs', 'sesis', 'jenisUjians', 'tahunAkademiks', 'exams'));
    }

    /**
     * Store a new Exam
     */
    public function examStore(Request $request)
    {
        $data = $request->validate([
            'bank_soal_id' => 'required|exists:bank_soals,id',
            'class_id' => 'nullable|exists:classes,id',
            'tahun_akademik_id' => 'nullable|exists:tahun_akademik,id',
            'jenis_ujian_id' => 'nullable|exists:jenis_ujians,id',
            'ruang_id' => 'nullable|exists:ruangs,id',
            'sesi_id' => 'nullable|exists:sesis,id',
            'exam_type' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'petunjuk' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:5',
            'total_questions' => 'required|integer|min:1',
            'passing_grade' => 'nullable|numeric|min:0|max:100',
            'is_random' => 'nullable|boolean',
        ]);

        $bankSoal = \App\Models\BankSoal::where('dosen_id', auth()->id())->findOrFail($data['bank_soal_id']);
        $data['course_id'] = $bankSoal->course_id;

        // Verify that there are enough questions in the bank soal
        $availableQuestionsCount = Question::where('bank_soal_id', $data['bank_soal_id'])->count();
        if ($availableQuestionsCount < $data['total_questions']) {
            return response()->json([
                'success' => false,
                'message' => "Soal tidak cukup. Soal tersedia: {$availableQuestionsCount}, sedangkan yang diminta: {$data['total_questions']}."
            ], 422);
        }

        $data['dosen_id'] = auth()->id();
        $data['token'] = strtoupper(Str::random(6));
        $data['is_random'] = $request->has('is_random') ? (bool)$request->input('is_random') : true;
        $data['passing_grade'] = $data['passing_grade'] ?? 60;

        $exam = Exam::create($data);

        ActivityLog::log('Tambah Sesi Ujian', "Membuat sesi ujian '{$exam->title}' dengan token: {$exam->token}.");

        return response()->json(['success' => true, 'message' => "Sesi ujian berhasil dibuat dengan Token: {$exam->token}"]);
    }

    /**
     * Update Sesi Ujian
     */
    public function examUpdate(Request $request, $id)
    {
        $exam = Exam::where('dosen_id', auth()->id())->findOrFail($id);

        $data = $request->validate([
            'bank_soal_id' => 'required|exists:bank_soals,id',
            'class_id' => 'nullable|exists:classes,id',
            'tahun_akademik_id' => 'nullable|exists:tahun_akademik,id',
            'jenis_ujian_id' => 'nullable|exists:jenis_ujians,id',
            'ruang_id' => 'nullable|exists:ruangs,id',
            'sesi_id' => 'nullable|exists:sesis,id',
            'exam_type' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'petunjuk' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:5',
            'total_questions' => 'required|integer|min:1',
            'passing_grade' => 'nullable|numeric|min:0|max:100',
            'is_random' => 'nullable|boolean',
        ]);

        $bankSoal = \App\Models\BankSoal::where('dosen_id', auth()->id())->findOrFail($data['bank_soal_id']);
        $data['course_id'] = $bankSoal->course_id;

        $availableQuestionsCount = Question::where('bank_soal_id', $data['bank_soal_id'])->count();
        if ($availableQuestionsCount < $data['total_questions']) {
            return response()->json([
                'success' => false,
                'message' => "Soal tidak cukup. Soal tersedia: {$availableQuestionsCount}, sedangkan yang diminta: {$data['total_questions']}."
            ], 422);
        }

        $data['is_random'] = $request->has('is_random') ? (bool)$request->input('is_random') : false;
        
        $exam->update($data);

        ActivityLog::log('Edit Sesi Ujian', "Mengubah sesi ujian '{$exam->title}'.");

        return response()->json(['success' => true, 'message' => 'Sesi ujian berhasil diperbarui.']);
    }

    /**
     * Delete Sesi Ujian
     */
    public function examDestroy($id)
    {
        $exam = Exam::where('dosen_id', auth()->id())->findOrFail($id);
        $title = $exam->title;
        $exam->delete();

        ActivityLog::log('Hapus Sesi Ujian', "Menghapus sesi ujian: {$title}.");

        return response()->json(['success' => true, 'message' => 'Sesi ujian berhasil dihapus.']);
    }

    /**
     * Regenerate Exam Token
     */
    public function examRegenerateToken($id)
    {
        $exam = Exam::where('dosen_id', auth()->id())->findOrFail($id);
        $oldToken = $exam->token;
        $exam->token = strtoupper(Str::random(6));
        $exam->save();

        ActivityLog::log('Perbarui Token', "Mengubah token sesi ujian '{$exam->title}' dari {$oldToken} menjadi {$exam->token}.");

        return response()->json(['success' => true, 'token' => $exam->token, 'message' => 'Token berhasil diperbarui.']);
    }

    /**
     * Rekap Nilai index
     */
    public function rekapNilai(Request $request)
    {
        $exams = Exam::with('course')
            ->where('dosen_id', auth()->id())
            ->orderBy('id', 'desc')
            ->get();

        // Smart UX auto-select: prioritize exam with ungraded essays
        $defaultExamId = null;
        foreach ($exams as $e) {
            $hasPending = $e->studentExams()
                ->whereHas('studentAnswers', function($query) {
                    $query->whereNull('is_correct')
                          ->whereHas('question', function($q) {
                              $q->where('question_type', 'essai');
                          });
                })->exists();
            if ($hasPending) {
                $defaultExamId = $e->id;
                break;
            }
        }

        if (!$defaultExamId) {
            $defaultExamId = $exams->first()?->id;
        }

        $examId = $request->query('exam_id', $defaultExamId);
        $selectedExam = null;
        $grades = [];

        if ($examId) {
            $selectedExam = Exam::with('course')->where('dosen_id', auth()->id())->findOrFail($examId);
            $grades = StudentExam::with('user')
                ->where('exam_id', $examId)
                ->orderBy('score', 'desc')
                ->get();
        }

        return view('dosen.grades', compact('exams', 'selectedExam', 'grades', 'examId'));
    }

    /**
     * Export grades to CSV
     */
    public function exportGradesCsv($examId)
    {
        $exam = Exam::with('course')->where('dosen_id', auth()->id())->findOrFail($examId);
        $grades = StudentExam::with('user')
            ->where('exam_id', $examId)
            ->orderBy('score', 'desc')
            ->get();

        $filename = "Rekap_Nilai_" . Str::slug($exam->title) . "_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Peringkat', 'NIM', 'Nama Mahasiswa', 'Sesi Ujian', 'Mulai', 'Selesai', 'Status', 'Nilai Akhir'];

        $callback = function() use($grades, $columns, $exam) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Campus Kop info in CSV
            fputcsv($file, ['STIKES MUHAMMADIYAH LHOKSEUMAWE']);
            fputcsv($file, ['LAPORAN HASIL UJIAN MAHASISWA']);
            fputcsv($file, ["Ujian: {$exam->title}"]);
            fputcsv($file, ["Mata Kuliah: {$exam->course->name} ({$exam->course->code})"]);
            fputcsv($file, []); // blank row
            
            fputcsv($file, $columns);

            foreach ($grades as $index => $grade) {
                fputcsv($file, [
                    $index + 1,
                    $grade->user->username,
                    $grade->user->name,
                    $exam->title,
                    $grade->started_at->format('d-m-Y H:i'),
                    $grade->finished_at ? $grade->finished_at->format('d-m-Y H:i') : '-',
                    strtoupper($grade->status),
                    $grade->score !== null ? number_format($grade->score, 2) : '0.00',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download Excel template for import
     */
    public function downloadExcelTemplate()
    {
        return app(\App\Http\Controllers\BankSoalController::class)->downloadExcelTemplate();
    }

    /**
     * Download Word DOCX template containing questions table
     */
    public function downloadWordTemplate()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'docx');
        
        $zip = new \ZipArchive();
        if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            
            // 1. [Content_Types].xml
            $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
              <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
              <Default Extension="xml" ContentType="application/xml"/>
              <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
            </Types>';
            $zip->addFromString('[Content_Types].xml', $contentTypes);

            // 2. _rels/.rels
            $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
              <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
            </Relationships>';
            $zip->addFromString('_rels/.rels', $rels);

            // 3. word/document.xml
            $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
              <w:body>
                <w:tbl>
                  <w:tr>
                    <w:tc><w:p><w:r><w:t>No</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>Pertanyaan</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>Pilihan A</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>Pilihan B</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>Pilihan C</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>Pilihan D</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>Pilihan E</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>Kunci</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>Kesulitan</w:t></w:r></w:p></w:tc>
                  </w:tr>
                  <w:tr>
                    <w:tc><w:p><w:r><w:t>1</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>Siapakah pendiri organisasi Muhammadiyah?</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>KH. Ahmad Dahlan</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>KH. Hasyim Asy\'ari</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>H.O.S. Cokroaminoto</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>Ki Hajar Dewantara</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>Jenderal Sudirman</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>A</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>mudah</w:t></w:r></w:p></w:tc>
                  </w:tr>
                  <w:tr>
                    <w:tc><w:p><w:r><w:t>2</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>Lambang Muhammadiyah adalah matahari dengan sinar sebanyak?</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>10 sinar</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>12 sinar</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>14 sinar</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>8 sinar</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>16 sinar</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>B</w:t></w:r></w:p></w:tc>
                    <w:tc><w:p><w:r><w:t>sedang</w:t></w:r></w:p></w:tc>
                  </w:tr>
                </w:tbl>
              </w:body>
            </w:document>';
            $zip->addFromString('word/document.xml', $documentXml);
            $zip->close();
        }

        return response()->download($tempFile, 'template_impor_soal_word.docx')->deleteFileAfterSend(true);
    }

    /**
     * Get essay answers for a specific student exam
     */
    public function getEssayAnswers($id)
    {
        $studentExam = StudentExam::with('user')->findOrFail($id);
        $exam = Exam::findOrFail($studentExam->exam_id);
        
        if (auth()->user()->role !== 'admin' && $exam->dosen_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $answers = StudentAnswer::with('question')
            ->where('student_exam_id', $studentExam->id)
            ->whereHas('question', function($query) {
                $query->where('question_type', 'essai');
            })
            ->get();

        return response()->json([
            'success' => true,
            'student' => $studentExam->user,
            'answers' => $answers
        ]);
    }

    /**
     * Grade essay answers and recalculate score
     */
    public function gradeEssay(Request $request, $id)
    {
        $studentExam = StudentExam::findOrFail($id);
        $exam = Exam::findOrFail($studentExam->exam_id);

        if (auth()->user()->role !== 'admin' && $exam->dosen_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'grades' => 'required|array',
            'grades.*.answer_id' => 'required|exists:student_answers,id',
            'grades.*.is_correct' => 'required|boolean'
        ]);

        \Illuminate\Support\Facades\DB::transaction(function() use ($request, $studentExam) {
            foreach ($request->input('grades') as $grade) {
                $ans = StudentAnswer::where('student_exam_id', $studentExam->id)
                    ->where('id', $grade['answer_id'])
                    ->first();
                if ($ans) {
                    $ans->is_correct = (bool)$grade['is_correct'];
                    $ans->save();
                }
            }

            // Recalculate score
            $allAnswers = StudentAnswer::where('student_exam_id', $studentExam->id)->get();
            $correctCount = $allAnswers->where('is_correct', true)->count();
            $totalCount = count($allAnswers);

            $score = $totalCount > 0 ? ($correctCount / $totalCount) * 100 : 0;
            $studentExam->score = $score;
            $studentExam->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Jawaban essay berhasil dinilai dan skor akhir dihitung ulang.'
        ]);
    }

    /**
     * Show dedicated essay correction page
     */
    public function showEssayCorrection($id)
    {
        $studentExam = StudentExam::with('user', 'exam.bankSoal')->findOrFail($id);
        $exam = $studentExam->exam;
        
        if (auth()->user()->role !== 'admin' && $exam->dosen_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }

        $answers = StudentAnswer::with('question')
            ->where('student_exam_id', $studentExam->id)
            ->whereHas('question', function($query) {
                $query->where('question_type', 'essai');
            })
            ->get();

        return view('dosen.koreksi-essay', compact('studentExam', 'exam', 'answers'));
    }

    /**
     * Store essay grades from dedicated correction page
     */
    public function storeEssayCorrection(Request $request, $id)
    {
        $studentExam = StudentExam::with('exam')->findOrFail($id);
        $exam = $studentExam->exam;
        
        if (auth()->user()->role !== 'admin' && $exam->dosen_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'grade' => 'required|array',
            'grade.*' => 'required|in:1,0,pending'
        ]);

        \Illuminate\Support\Facades\DB::transaction(function() use ($request, $studentExam) {
            foreach ($request->input('grade') as $answerId => $gradeVal) {
                $ans = StudentAnswer::where('student_exam_id', $studentExam->id)
                    ->where('id', $answerId)
                    ->first();
                if ($ans) {
                    if ($gradeVal === '1') {
                        $ans->is_correct = true;
                    } elseif ($gradeVal === '0') {
                        $ans->is_correct = false;
                    } else {
                        $ans->is_correct = null;
                    }
                    $ans->save();
                }
            }

            // Recalculate score
            $allAnswers = StudentAnswer::where('student_exam_id', $studentExam->id)->get();
            $correctCount = $allAnswers->where('is_correct', true)->count();
            $totalCount = count($allAnswers);

            $score = $totalCount > 0 ? ($correctCount / $totalCount) * 100 : 0;
            $studentExam->score = $score;
            $studentExam->save();
        });

        return redirect()->route('dosen.grades.index', ['exam_id' => $exam->id])
            ->with('success', 'Jawaban essay berhasil dinilai dan nilai akhir dihitung ulang.');
    }
}
