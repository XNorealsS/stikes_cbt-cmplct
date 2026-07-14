<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\StudentAnswer;
use App\Models\StudentExam;
use App\Models\Question;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AnalisisController extends Controller
{
    /**
     * Admin: Analisis Butir Soal list
     */
    public function index(Request $request)
    {
        $exams = Exam::with(['course', 'jenisUjian'])
            ->withCount('studentExams')
            ->orderBy('start_time', 'desc')
            ->get();

        $examId = $request->query('exam_id', $exams->first()?->id);
        $selectedExam = null;
        $analyses = [];
        $cronbachAlpha = null;

        if ($examId) {
            $selectedExam = Exam::with(['course', 'jenisUjian'])->findOrFail($examId);
            $analyses = $this->getAnalysisData($examId);
            $cronbachAlpha = $this->calculateCronbachAlpha($examId);
        }

        return view('admin.analisis', compact('exams', 'selectedExam', 'analyses', 'examId', 'cronbachAlpha'));
    }

    /**
     * Dosen: Analisis Butir Soal (only own exams)
     */
    public function dosenIndex(Request $request)
    {
        $exams = Exam::with(['course', 'jenisUjian'])
            ->where('dosen_id', auth()->id())
            ->withCount('studentExams')
            ->orderBy('start_time', 'desc')
            ->get();

        $examId = $request->query('exam_id', $exams->first()?->id);
        $selectedExam = null;
        $analyses = [];
        $cronbachAlpha = null;

        if ($examId) {
            $selectedExam = Exam::with(['course', 'jenisUjian'])
                ->where('dosen_id', auth()->id())
                ->findOrFail($examId);

            $analyses = $this->getAnalysisData($examId);
            $cronbachAlpha = $this->calculateCronbachAlpha($examId);
        }

        return view('dosen.analisis', compact('exams', 'selectedExam', 'analyses', 'examId', 'cronbachAlpha'));
    }

    /**
     * Run / re-run analysis for an exam
     */
    public function runAnalysis($examId)
    {
        $exam = Exam::findOrFail($examId);

        $finishedSessions = StudentExam::where('exam_id', $examId)->where('status', 'finished')->count();

        if ($finishedSessions < 5) {
            return response()->json([
                'success' => false,
                'message' => "Minimal 5 mahasiswa harus menyelesaikan ujian untuk analisis. Saat ini: {$finishedSessions} mahasiswa.",
            ], 422);
        }

        $analyses = $this->getAnalysisData($examId);
        $cronbachAlpha = $this->calculateCronbachAlpha($examId);

        ActivityLog::log('Analisis Soal', "Menjalankan analisis butir soal untuk ujian: {$exam->title}.");

        return response()->json([
            'success'       => true,
            'message'       => 'Analisis selesai.',
            'analyses'      => $analyses,
            'cronbachAlpha' => $cronbachAlpha,
        ]);
    }

    /**
     * Export analisis ke CSV
     */
    public function exportExcel($examId)
    {
        $exam = Exam::with('course')->findOrFail($examId);
        $analyses = $this->getAnalysisData($examId);
        $cronbach = $this->calculateCronbachAlpha($examId);

        $filename = 'analisis_soal_' . \Illuminate\Support\Str::slug($exam->title) . '_' . date('Ymd') . '.csv';

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($analyses, $exam, $cronbach) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['STIKES MUHAMMADIYAH LHOKSEUMAWE']);
            fputcsv($file, ['ANALISIS BUTIR SOAL']);
            fputcsv($file, ["Ujian: {$exam->title}"]);
            fputcsv($file, ["Mata Kuliah: {$exam->course->name} ({$exam->course->code})"]);
            fputcsv($file, ["Reliabilitas (Cronbach Alpha): " . number_format($cronbach ?? 0, 4)]);
            fputcsv($file, []);

            fputcsv($file, ['No', 'Pertanyaan', 'Tingkat Kesukaran', 'Kategori TK', 'Daya Beda', 'Kategori DB', 'Benar', 'Salah', 'Tidak Jawab']);

            foreach ($analyses as $i => $a) {
                fputcsv($file, [
                    $i + 1,
                    mb_substr(strip_tags($a['question_text']), 0, 80),
                    number_format($a['tingkat_kesukaran'], 4),
                    $a['kategori_tk'],
                    number_format($a['daya_beda'], 4),
                    $a['kategori_db'],
                    $a['jawaban_benar'],
                    $a['jawaban_salah'],
                    $a['tidak_jawab'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Core analysis calculation
     */
    private function getAnalysisData(int $examId): array
    {
        $finishedSessions = StudentExam::where('exam_id', $examId)
            ->where('status', 'finished')
            ->get();

        $totalPeserta = $finishedSessions->count();
        if ($totalPeserta === 0) return [];

        // Get all question IDs from this exam's student answers
        $questionIds = StudentAnswer::whereIn('student_exam_id', $finishedSessions->pluck('id'))
            ->distinct('question_id')
            ->pluck('question_id');

        $questions = Question::whereIn('id', $questionIds)->get()->keyBy('id');

        $analyses = [];

        // Sort sessions by total score for upper/lower group split
        $sessionScores = $finishedSessions->map(function ($s) use ($finishedSessions) {
            $answers = StudentAnswer::where('student_exam_id', $s->id)->get();
            $correct = $answers->where('is_correct', true)->count();
            return ['session_id' => $s->id, 'correct' => $correct];
        })->sortByDesc('correct')->values();

        $cutoff = (int)($totalPeserta * 0.27);
        $upperGroup = $sessionScores->take($cutoff)->pluck('session_id');
        $lowerGroup = $sessionScores->reverse()->take($cutoff)->pluck('session_id');

        foreach ($questionIds as $qId) {
            $question = $questions->get($qId);
            if (!$question) continue;

            $allAnswers = StudentAnswer::where('question_id', $qId)
                ->whereIn('student_exam_id', $finishedSessions->pluck('id'))
                ->get();

            $benar      = $allAnswers->where('is_correct', true)->count();
            $salah      = $allAnswers->where('is_correct', false)->whereNotNull('selected_option')->count();
            $tidakJawab = $allAnswers->whereNull('selected_option')->count();

            $p = $totalPeserta > 0 ? $benar / $totalPeserta : 0; // Tingkat Kesukaran

            // Daya beda (D = (BA/JA) - (BB/JB))
            $ba = StudentAnswer::where('question_id', $qId)->whereIn('student_exam_id', $upperGroup)->where('is_correct', true)->count();
            $bb = StudentAnswer::where('question_id', $qId)->whereIn('student_exam_id', $lowerGroup)->where('is_correct', true)->count();
            $d  = $cutoff > 0 ? ($ba - $bb) / $cutoff : 0;

            // Distribusi jawaban
            $distribusi = [];
            foreach (['A', 'B', 'C', 'D', 'E'] as $opt) {
                $distribusi[$opt] = $allAnswers->where('selected_option', $opt)->count();
            }

            // Kategori
            $kategoriTk = match(true) {
                $p <= 0.30  => 'Sulit',
                $p <= 0.70  => 'Sedang',
                default     => 'Mudah',
            };

            $kategoriDb = match(true) {
                $d < 0.00   => 'Sangat Buruk',
                $d < 0.20   => 'Buruk',
                $d < 0.40   => 'Cukup',
                $d < 0.70   => 'Baik',
                default     => 'Sangat Baik',
            };

            $analyses[] = [
                'question_id'        => $qId,
                'question_text'      => $question->question_text,
                'tingkat_kesukaran'  => round($p, 4),
                'kategori_tk'        => $kategoriTk,
                'daya_beda'          => round($d, 4),
                'kategori_db'        => $kategoriDb,
                'jawaban_benar'      => $benar,
                'jawaban_salah'      => $salah,
                'tidak_jawab'        => $tidakJawab,
                'distribusi'         => $distribusi,
            ];
        }

        return $analyses;
    }

    /**
     * Cronbach Alpha reliability coefficient
     */
    private function calculateCronbachAlpha(int $examId): ?float
    {
        $sessions = StudentExam::where('exam_id', $examId)->where('status', 'finished')->get();
        $n = $sessions->count();
        if ($n < 2) return null;

        // Get all answers grouped by session
        $sessionAnswers = [];
        foreach ($sessions as $s) {
            $answers = StudentAnswer::where('student_exam_id', $s->id)->get();
            $sessionAnswers[$s->id] = $answers->map(fn($a) => $a->is_correct ? 1 : 0)->toArray();
        }

        // Get question count
        $k = count(reset($sessionAnswers));
        if ($k < 2) return null;

        // Variance of each item
        $itemVariances = [];
        $questionCount = $k;

        // Pivot: index by question position
        $matrix = [];
        foreach ($sessionAnswers as $sid => $scores) {
            foreach ($scores as $qi => $score) {
                $matrix[$qi][] = $score;
            }
        }

        foreach ($matrix as $qi => $scores) {
            $mean = array_sum($scores) / count($scores);
            $variance = array_sum(array_map(fn($s) => ($s - $mean) ** 2, $scores)) / count($scores);
            $itemVariances[$qi] = $variance;
        }

        // Total score variance
        $totalScores = array_map(fn($a) => array_sum($a), $sessionAnswers);
        $meanTotal = array_sum($totalScores) / count($totalScores);
        $totalVariance = array_sum(array_map(fn($s) => ($s - $meanTotal) ** 2, $totalScores)) / count($totalScores);

        if ($totalVariance == 0) return null;

        $sumItemVariances = array_sum($itemVariances);
        $alpha = ($questionCount / ($questionCount - 1)) * (1 - ($sumItemVariances / $totalVariance));

        return round($alpha, 4);
    }
}
