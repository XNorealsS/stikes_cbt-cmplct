<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\StudentExam;
use App\Models\StudentAnswer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExamApiController extends Controller
{
    /**
     * Start the exam session
     * POST /api/v1/exam/start
     */
    public function start(Request $request)
    {
        $data = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'token' => 'required|string',
        ]);

        $userId = auth()->id();
        $user = auth()->user();
        $exam = Exam::findOrFail($data['exam_id']);

        // Check class restriction
        if ($exam->class_id && $user->class_id !== $exam->class_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ujian ini tidak ditujukan untuk kelas Anda.'
            ], 422);
        }

        // 1. Verify token
        if (strtoupper(trim($data['token'])) !== strtoupper(trim($exam->token))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token ujian tidak valid. Silakan periksa kembali.'
            ], 422);
        }

        // 2. Verify scheduling window
        $now = now();
        if ($now->lessThan($exam->start_time)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ujian belum dimulai sesuai jadwal.'
            ], 422);
        }
        if ($now->greaterThan($exam->end_time)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal sesi ujian ini telah berakhir.'
            ], 422);
        }

        // 3. Check if user already has a session
        $existingSession = StudentExam::where('user_id', $userId)
            ->where('exam_id', $exam->id)
            ->first();

        if ($existingSession) {
            if ($existingSession->status === 'finished') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda sudah menyelesaikan ujian ini.'
                ], 422);
            }
            // If in progress, just return the active session id
            return response()->json([
                'status' => 'success',
                'session_id' => $existingSession->id
            ]);
        }

        // 4. Create new session and generate randomized question order once
        try {
            $sessionId = DB::transaction(function() use ($userId, $exam) {
                // Create student exam record
                $studentExam = StudentExam::create([
                    'user_id' => $userId,
                    'exam_id' => $exam->id,
                    'started_at' => now(),
                    'status' => 'progress',
                ]);

                // Fetch questions for this course
                $questions = Question::where('course_id', $exam->course_id)->get();

                // If the exam requires random order, shuffle questions, otherwise order by ID
                if ($exam->is_random) {
                    $questions = $questions->shuffle();
                }

                // Slice questions to meet the requested total_questions parameter
                $selectedQuestions = $questions->take($exam->total_questions);

                // Insert into student answers
                $order = 1;
                foreach ($selectedQuestions as $q) {
                    StudentAnswer::create([
                        'student_exam_id' => $studentExam->id,
                        'question_id' => $q->id,
                        'selected_option' => null,
                        'is_doubtful' => false,
                        'question_order' => $order++,
                    ]);
                }

                ActivityLog::log('Mulai Ujian', "Mahasiswa mulai mengerjakan ujian: '{$exam->title}' (Sesi ID: {$studentExam->id}).", $userId);

                return $studentExam->id;
            });

            return response()->json([
                'status' => 'success',
                'session_id' => $sessionId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memulai ujian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save an answer asynchronously
     * POST /api/v1/exam/save-answer
     */
    public function saveAnswer(Request $request)
    {
        $data = $request->validate([
            'student_exam_id' => 'required|exists:student_exams,id',
            'question_id' => 'required|exists:questions,id',
            'option_id' => 'nullable', // string A/B/C/D/E, number 1-5, or null
            'answer_text' => 'nullable|string',
            'is_doubtful' => 'nullable|boolean',
        ]);

        $userId = auth()->id();
        $studentExam = StudentExam::findOrFail($data['student_exam_id']);

        // Authorize student access
        if ($studentExam->user_id !== $userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized Access.'], 403);
        }

        // Prevent modifying finished or pending exams
        if ($studentExam->status === 'finished') {
            return response()->json(['status' => 'error', 'message' => 'Ujian ini telah selesai dikerjakan.'], 422);
        }
        if ($studentExam->status === 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Ujian Anda sedang ditangguhkan (pending) oleh Proktor/Admin.'], 422);
        }

        // Map option_id to A/B/C/D/E if it's sent as a number
        $selectedOption = null;
        if (!empty($data['option_id'])) {
            $opt = strtoupper($data['option_id']);
            if (in_array($opt, ['A', 'B', 'C', 'D', 'E'])) {
                $selectedOption = $opt;
            } else if (is_numeric($opt)) {
                $mapping = [1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E'];
                $selectedOption = $mapping[(int)$opt] ?? null;
            }
        }

        // Find the specific answer sheet record
        $answer = StudentAnswer::where('student_exam_id', $studentExam->id)
            ->where('question_id', $data['question_id'])
            ->firstOrFail();

        $answer->selected_option = $selectedOption;
        if (isset($data['answer_text'])) {
            $answer->answer_text = $data['answer_text'];
        }

        if (isset($data['is_doubtful'])) {
            $answer->is_doubtful = (bool)$data['is_doubtful'];
        }

        $answer->save();

        return response()->json([
            'status' => 'saved',
            'saved_at' => now()->toDateTimeString()
        ]);
    }

    /**
     * Sync countdown timer
     * GET /api/v1/exam/timer-sync/{id}
     */
    public function timerSync($id)
    {
        $userId = auth()->id();
        $studentExam = StudentExam::with('exam')->where('user_id', $userId)->findOrFail($id);

        if ($studentExam->status === 'finished') {
            return response()->json(['remaining_seconds' => 0]);
        }

        if ($studentExam->status === 'pending') {
            $startedAt = Carbon::parse($studentExam->started_at);
            $suspendedAt = Carbon::parse($studentExam->suspended_at ?? now());
            $elapsedSeconds = abs($suspendedAt->diffInSeconds($startedAt));
            $totalSeconds = $studentExam->exam->duration_minutes * 60;
            $remainingSeconds = max(0, $totalSeconds - $elapsedSeconds);
            return response()->json([
                'remaining_seconds' => $remainingSeconds,
                'is_pending' => true
            ]);
        }

        $startedAt = $studentExam->started_at;
        $elapsedSeconds = abs(now()->diffInSeconds($startedAt));
        $totalSeconds = $studentExam->exam->duration_minutes * 60;
        $remainingSeconds = $totalSeconds - $elapsedSeconds;

        if ($remainingSeconds <= 0 || now()->greaterThan($studentExam->exam->end_time)) {
            // Auto submit when time runs out
            $this->submitFinalProcess($studentExam);
            return response()->json(['remaining_seconds' => 0]);
        }

        return response()->json([
            'remaining_seconds' => $remainingSeconds,
            'is_pending' => false
        ]);
    }

    /**
     * Submit final exam answers
     * POST /api/v1/exam/submit-final
     */
    public function submitFinal(Request $request)
    {
        $data = $request->validate([
            'student_exam_id' => 'required|exists:student_exams,id',
            'force' => 'nullable|boolean',
        ]);

        $userId = auth()->id();
        $studentExam = StudentExam::findOrFail($data['student_exam_id']);

        if ($studentExam->user_id !== $userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized Access.'], 403);
        }

        if ($studentExam->status === 'finished') {
            return response()->json([
                'status' => 'submited',
                'score' => $studentExam->score
            ]);
        }

        $score = $this->submitFinalProcess($studentExam);

        return response()->json([
            'status' => 'submited',
            'score' => $score
        ]);
    }

    /**
     * Internal logic for scoring and finalizing student answers inside DB transaction
     */
    private function submitFinalProcess(StudentExam $studentExam): float
    {
        // Wrap final score calculations in DB transaction (PROJECT_CONTEXT Rule #3)
        return DB::transaction(function() use ($studentExam) {
            // Eager load questions to calculate values efficiently
            $answers = StudentAnswer::with('question')
                ->where('student_exam_id', $studentExam->id)
                ->get();

            $correctCount = 0;
            $totalCount = count($answers);

            foreach ($answers as $ans) {
                $q = $ans->question;
                if ($q->question_type === 'pg' || $q->question_type === 'benar_salah') {
                    if ($ans->selected_option !== null && strtoupper($ans->selected_option) === strtoupper($q->correct_option)) {
                        $ans->is_correct = true;
                        $correctCount++;
                    } else {
                        $ans->is_correct = false;
                    }
                } elseif ($q->question_type === 'isian') {
                    if ($ans->answer_text !== null && strcasecmp(trim($ans->answer_text), trim($q->correct_option)) === 0) {
                        $ans->is_correct = true;
                        $correctCount++;
                    } else {
                        $ans->is_correct = false;
                    }
                } elseif ($q->question_type === 'pg_kompleks') {
                    // PG kompleks logic: correct_option might be "A,B" or "A,C"
                    // selected_option could be comma separated too
                    if ($ans->selected_option !== null && strcasecmp(trim($ans->selected_option), trim($q->correct_option)) === 0) {
                        $ans->is_correct = true;
                        $correctCount++;
                    } else {
                        $ans->is_correct = false;
                    }
                } else {
                    // Essai or Menjodohkan (graded manually or default to false)
                    $ans->is_correct = false;
                }
                $ans->save();
            }

            $score = $totalCount > 0 ? ($correctCount / $totalCount) * 100 : 0;

            $studentExam->update([
                'finished_at' => now(),
                'score' => $score,
                'status' => 'finished'
            ]);

            ActivityLog::log('Kirim Jawaban Akhir', "Mahasiswa menyelesaikan ujian. Nilai: {$score}.", $studentExam->user_id);

            return $score;
        });
    }

    /**
     * Reset the exam session because student left the page/application
     * POST /api/v1/exam/reset-session
     */
    public function resetSession(Request $request)
    {
        $data = $request->validate([
            'student_exam_id' => 'required|exists:student_exams,id',
        ]);

        $userId = auth()->id();
        $studentExam = StudentExam::findOrFail($data['student_exam_id']);

        if ($studentExam->user_id !== $userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized Access.'], 403);
        }

        if ($studentExam->status === 'finished') {
            return response()->json(['status' => 'error', 'message' => 'Ujian ini telah selesai dikerjakan.'], 422);
        }

        try {
            DB::transaction(function() use ($studentExam, $userId) {
                // Delete student answers first
                StudentAnswer::where('student_exam_id', $studentExam->id)->delete();
                
                // Delete the exam session record
                $studentExam->delete();

                ActivityLog::log('Reset Ujian', "Ujian direset karena mahasiswa meninggalkan halaman/aplikasi (Sesi ID: {$studentExam->id}).", $userId);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Sesi ujian berhasil direset.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mereset sesi ujian: ' . $e->getMessage()
            ], 500);
        }
    }
}
