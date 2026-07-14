<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\StudentExam;
use App\Models\StudentAnswer;
use App\Models\Materi;
use App\Models\Tugas;
use App\Models\TugasSubmission;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MahasiswaController extends Controller
{
    /**
     * Mahasiswa Dashboard index
     */
    public function dashboard()
    {
        $userId = auth()->id();
        
        $classId = auth()->user()->class_id;
        $today = now()->toDateString();
        
        // Active exams: current time falls within start and end time,
        // matches student's class (or open to all),
        // and the student has not completed it yet.
        $activeExams = Exam::with(['course', 'dosen'])
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->where(function($query) use ($classId) {
                $query->whereNull('class_id')
                      ->orWhere('class_id', $classId);
            })
            ->whereDoesntHave('studentExams', function($query) use ($userId) {
                $query->where('user_id', $userId)->where('status', 'finished');
            })
            ->get();

        // Check if there is an in-progress session they can resume
        $activeSession = StudentExam::with('exam.course')
            ->where('user_id', $userId)
            ->where('status', 'progress')
            ->first();

        // If they have an active session, check if time has already run out.
        // If it ran out, we will auto-finish it on load.
        if ($activeSession) {
            $exam = $activeSession->exam;
            $startedAt = $activeSession->started_at;
            $elapsedSeconds = abs(now()->diffInSeconds($startedAt));
            $totalSeconds = $exam->duration_minutes * 60;
            $remainingSeconds = $totalSeconds - $elapsedSeconds;

            if ($remainingSeconds <= 0 || now()->greaterThan($exam->end_time)) {
                // Time's up! Force submit.
                // We will handle this gracefully.
                $this->forceSubmitSession($activeSession);
                $activeSession = null;
            }
        }

        // Fetch 3 latest active materials for the student's class
        $latestMateris = Materi::with(['course', 'user'])
            ->where('is_aktif', true)
            ->where(function ($q) use ($classId) {
                $q->whereNull('class_id')->orWhere('class_id', $classId);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_tayang')->orWhere('tanggal_tayang', '<=', $today);
            })
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Fetch 3 upcoming active tasks that the student hasn't submitted yet
        $upcomingTugas = Tugas::with(['course', 'user'])
            ->where('is_aktif', true)
            ->where(function ($q) use ($classId) {
                $q->whereNull('class_id')->orWhere('class_id', $classId);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_tayang')->orWhere('tanggal_tayang', '<=', $today);
            })
            ->where('deadline', '>=', now())
            ->whereDoesntHave('submissions', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->orderBy('deadline', 'asc')
            ->take(3)
            ->get();

        // Compute stats for Mahasiswa dashboard
        $courseCount = \App\Models\Course::count();
        $uncompletedTasksCount = \App\Models\Tugas::where('is_aktif', true)
            ->where(function ($q) use ($classId) {
                $q->whereNull('class_id')->orWhere('class_id', $classId);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_tayang')->orWhere('tanggal_tayang', '<=', $today);
            })
            ->whereDoesntHave('submissions', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->count();

        $examsTodayCount = \App\Models\Exam::where(function($query) use ($classId) {
                $query->whereNull('class_id')
                      ->orWhere('class_id', $classId);
            })
            ->whereDate('start_time', '<=', now()->toDateString())
            ->whereDate('end_time', '>=', now()->toDateString())
            ->count();

        $avgScore = \App\Models\StudentExam::where('user_id', $userId)
            ->where('status', 'finished')
            ->whereNotNull('score')
            ->avg('score') ?? 0;

        return view('mahasiswa.dashboard', compact('activeExams', 'activeSession', 'latestMateris', 'upcomingTugas', 'courseCount', 'uncompletedTasksCount', 'examsTodayCount', 'avgScore'));
    }

    /**
     * Exam Room view
     */
    public function examRoom($id)
    {
        $userId = auth()->id();
        $studentExam = StudentExam::with(['exam.course', 'exam.dosen'])
            ->where('user_id', $userId)
            ->findOrFail($id);

        // If it's already finished, they cannot enter again.
        if ($studentExam->status === 'finished') {
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Ujian ini telah selesai dikerjakan.');
        }

        if ($studentExam->status === 'pending') {
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Ujian Anda sedang ditangguhkan (pending) oleh Proktor/Admin.');
        }

        $exam = $studentExam->exam;
        
        // Check if the overall exam scheduling window is closed
        if (now()->greaterThan($exam->end_time)) {
            $this->forceSubmitSession($studentExam);
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Waktu ujian telah berakhir (jadwal selesai).');
        }

        // Calculate remaining seconds
        $startedAt = $studentExam->started_at;
        $elapsedSeconds = abs(now()->diffInSeconds($startedAt));
        $totalSeconds = $exam->duration_minutes * 60;
        $remainingSeconds = $totalSeconds - $elapsedSeconds;

        if ($remainingSeconds <= 0) {
            $this->forceSubmitSession($studentExam);
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Waktu pengerjaan ujian telah habis.');
        }

        // Get student answers ordered by the predetermined question_order
        // Eager load questions to avoid N+1 queries! (Rule #1 of PROJECT_CONTEXT)
        $answers = StudentAnswer::with('question')
            ->where('student_exam_id', $studentExam->id)
            ->orderBy('question_order', 'asc')
            ->get();

        return view('mahasiswa.exam-room', compact('studentExam', 'exam', 'answers', 'remainingSeconds'));
    }

    /**
     * Exam History list
     */
    public function examHistory()
    {
        $userId = auth()->id();
        $history = StudentExam::with(['exam.course', 'exam.dosen'])
            ->where('user_id', $userId)
            ->where('status', 'finished')
            ->orderBy('finished_at', 'desc')
            ->get();

        return view('mahasiswa.exam-history', compact('history'));
    }

    /**
     * Discuss past exam questions
     */
    public function examReview($id)
    {
        $userId = auth()->id();
        $studentExam = StudentExam::with(['exam.course', 'exam.dosen'])
            ->where('user_id', $userId)
            ->where('status', 'finished')
            ->findOrFail($id);

        // Fetch answers with associated questions to avoid N+1 query
        $answers = StudentAnswer::with('question')
            ->where('student_exam_id', $studentExam->id)
            ->orderBy('question_order', 'asc')
            ->get();

        return view('mahasiswa.exam-review', compact('studentExam', 'answers'));
    }

    private function forceSubmitSession(StudentExam $studentExam)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($studentExam) {
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
                    if ($ans->selected_option !== null && strcasecmp(trim($ans->selected_option), trim($q->correct_option)) === 0) {
                        $ans->is_correct = true;
                        $correctCount++;
                    } else {
                        $ans->is_correct = false;
                    }
                } else {
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
        });
    }
}
