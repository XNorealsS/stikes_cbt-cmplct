<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BankSoal;
use App\Models\Course;
use App\Models\JenisUjian;
use App\Models\Prodi;
use App\Models\Question;
use App\Models\Ruang;
use App\Models\Sesi;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\FeederService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Admin Dashboard index
     */
    public function dashboard()
    {
        $dosenCount = User::where('role', 'dosen')->count();
        $mahasiswaCount = User::where('role', 'mahasiswa')->count();
        $courseCount = Course::count();
        $classCount = \App\Models\ClassRoom::count();
        $logsCount = ActivityLog::count();
        $examCount = \App\Models\Exam::count();
        $questionCount = Question::count();
        $prodiCount = Prodi::where('is_aktif', true)->count();
        $tahunAktif = TahunAkademik::getAktif();
        $recentLogs = ActivityLog::with('user')->orderBy('created_at', 'desc')->take(10)->get();
        $activeExams = \App\Models\Exam::with(['course', 'dosen'])
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->count();

        return view('admin.dashboard', compact(
            'dosenCount', 'mahasiswaCount', 'courseCount', 'classCount', 'logsCount',
            'examCount', 'questionCount', 'prodiCount', 'tahunAktif',
            'activeExams', 'recentLogs'
        ));
    }

    /**
     * Users Management List
     */
    public function usersIndex(Request $request)
    {
        $role = $request->query('role', 'dosen'); // Default to dosen view
        $search = $request->query('search', '');

        $query = User::with('classRoom')->where('role', $role);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('nidn', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name', 'asc')->get();
        $classes = \App\Models\ClassRoom::orderBy('name', 'asc')->get();
        return view('admin.users', compact('users', 'role', 'classes', 'search'));
    }

    /**
     * Store a new User
     */
    public function usersStore(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users',
            'email'     => 'required|string|email|max:255|unique:users',
            'role'      => 'required|in:admin,dosen,mahasiswa',
            'class_id'  => 'nullable|exists:classes,id',
            'prodi_id'  => 'nullable|exists:prodis,id',
            'nim'       => 'nullable|string|max:50',
            'nidn'      => 'nullable|string|max:20',
            'angkatan'  => 'nullable|string|max:10',
            'no_hp'     => 'nullable|string|max:20',
            'jabatan'   => 'nullable|string|max:100',
            'password'  => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'username'  => $data['username'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'class_id'  => $data['class_id'] ?? null,
            'prodi_id'  => $data['prodi_id'] ?? null,
            'nim'       => $data['nim'] ?? null,
            'nidn'      => $data['nidn'] ?? null,
            'angkatan'  => $data['angkatan'] ?? null,
            'no_hp'     => $data['no_hp'] ?? null,
            'jabatan'   => $data['jabatan'] ?? null,
            'password'  => Hash::make($data['password']),
        ]);

        ActivityLog::log('Tambah Pengguna', "Menambahkan pengguna baru: {$user->name} ({$user->role}).");

        return response()->json(['success' => true, 'message' => 'Pengguna berhasil ditambahkan.']);
    }

    /**
     * Reset user password
     */
    public function usersResetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate(['password' => 'required|string|min:6']);
        $user->password = Hash::make($data['password']);
        $user->save();

        ActivityLog::log('Reset Password', "Reset password pengguna: {$user->name}.");
        return response()->json(['success' => true, 'message' => "Password {$user->name} berhasil direset."]);
    }

    /**
     * Import Users from CSV/Excel (simple CSV implementation)
     */
    public function usersImport(Request $request)
    {
        $request->validate([
            'role'        => 'required|in:dosen,mahasiswa',
            'import_file' => 'required|file|mimes:csv,txt',
        ]);

        $role = $request->input('role');
        $file = $request->file('import_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // skip header

        $imported = 0;
        $errors   = [];
        $row      = 1;

        while (($line = fgetcsv($handle)) !== false) {
            $row++;
            if (count($line) < 3) {
                $errors[] = "Baris {$row}: Format tidak valid.";
                continue;
            }

            try {
                $username = trim($line[0]);
                $name     = trim($line[1]);
                $email    = trim($line[2]);
                $password = isset($line[3]) ? trim($line[3]) : 'password123';

                if (User::where('username', $username)->exists()) {
                    $errors[] = "Baris {$row}: Username '{$username}' sudah ada.";
                    continue;
                }

                User::create([
                    'name'     => $name,
                    'username' => $username,
                    'email'    => $email,
                    'role'     => $role,
                    'password' => Hash::make($password),
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Baris {$row}: " . $e->getMessage();
            }
        }
        fclose($handle);

        ActivityLog::log('Import Pengguna', "Mengimpor {$imported} pengguna role {$role}.");
        return response()->json([
            'success' => true,
            'message' => "Berhasil mengimpor {$imported} pengguna.",
            'errors'  => $errors,
        ]);
    }

    /**
     * Download user import template
     */
    public function usersImportTemplate($role)
    {
        $filename = "template_import_{$role}.csv";
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];
        $callback = function () use ($role) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Username/NIM/NIDN', 'Nama Lengkap', 'Email', 'Password (opsional)']);
            if ($role === 'mahasiswa') {
                fputcsv($file, ['2023001', 'Nama Mahasiswa Contoh', 'mahasiswa@stikes.ac.id', 'password123']);
            } else {
                fputcsv($file, ['198001012010011001', 'Nama Dosen Contoh', 'dosen@stikes.ac.id', 'password123']);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Update an existing User
     */
    public function usersUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'class_id' => 'nullable|exists:classes,id',
            'password' => 'nullable|string|min:6',
        ]);

        $user->name = $data['name'];
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->class_id = $data['class_id'] ?? null;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        ActivityLog::log('Edit Pengguna', "Mengubah data pengguna: {$user->name}.");

        return response()->json(['success' => true, 'message' => 'Data pengguna berhasil diperbarui.']);
    }

    /**
     * Delete a User
     */
    public function usersDestroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak bisa menghapus akun Anda sendiri.'], 403);
        }

        $name = $user->name;
        $role = $user->role;
        $user->delete();

        ActivityLog::log('Hapus Pengguna', "Menghapus pengguna: {$name} ({$role}).");

        return response()->json(['success' => true, 'message' => 'Pengguna berhasil dihapus.']);
    }

    /**
     * Courses Master Management List
     */
    public function coursesIndex()
    {
        $courses = Course::with('prodi')->orderBy('code', 'asc')->get();
        $prodis = Prodi::where('is_aktif', true)->orderBy('nama')->get();
        return view('admin.courses', compact('courses', 'prodis'));
    }

    /**
     * Import courses from CSV
     */
    public function coursesImport(Request $request)
    {
        $request->validate(['import_file' => 'required|file|mimes:csv,txt']);
        $handle   = fopen($request->file('import_file')->getRealPath(), 'r');
        $header   = fgetcsv($handle);
        $imported = 0;
        $errors   = [];
        $row      = 1;

        while (($line = fgetcsv($handle)) !== false) {
            $row++;
            if (count($line) < 2) { $errors[] = "Baris {$row}: Format tidak valid."; continue; }
            try {
                $code = strtoupper(trim($line[0]));
                $name = trim($line[1]);
                if (Course::where('code', $code)->exists()) { $errors[] = "Baris {$row}: Kode '{$code}' sudah ada."; continue; }
                Course::create(['code' => $code, 'name' => $name, 'sks' => isset($line[2]) ? (int)$line[2] : 2]);
                $imported++;
            } catch (\Exception $e) { $errors[] = "Baris {$row}: " . $e->getMessage(); }
        }
        fclose($handle);
        ActivityLog::log('Import Mata Kuliah', "Mengimpor {$imported} mata kuliah.");
        return response()->json(['success' => true, 'message' => "Berhasil mengimpor {$imported} mata kuliah.", 'errors' => $errors]);
    }

    /**
     * Store a new Course
     */
    public function coursesStore(Request $request)
    {
        $data = $request->validate([
            'code'         => 'required|string|unique:courses|max:50',
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'prodi_id'     => 'nullable|exists:prodis,id',
            'sks'          => 'nullable|integer|min:1|max:10',
            'is_praktikum' => 'nullable|boolean',
        ]);

        $course = Course::create($data);

        ActivityLog::log('Tambah Mata Kuliah', "Menambahkan mata kuliah: {$course->name} ({$course->code}).");

        return response()->json(['success' => true, 'message' => 'Mata kuliah berhasil ditambahkan.']);
    }

    /**
     * Update a Course
     */
    public function coursesUpdate(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $data = $request->validate([
            'code'         => ['required', 'string', 'max:50', Rule::unique('courses')->ignore($course->id)],
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'prodi_id'     => 'nullable|exists:prodis,id',
            'sks'          => 'nullable|integer|min:1|max:10',
            'is_praktikum' => 'nullable|boolean',
        ]);

        $course->update($data);

        ActivityLog::log('Edit Mata Kuliah', "Mengubah data mata kuliah: {$course->name}.");

        return response()->json(['success' => true, 'message' => 'Mata kuliah berhasil diperbarui.']);
    }

    /**
     * Delete a Course
     */
    public function coursesDestroy($id)
    {
        $course = Course::findOrFail($id);
        $name = $course->name;
        $code = $course->code;
        $course->delete();

        ActivityLog::log('Hapus Mata Kuliah', "Menghapus mata kuliah: {$name} ({$code}).");

        return response()->json(['success' => true, 'message' => 'Mata kuliah berhasil dihapus.']);
    }

    // =========================================
    // BANK SOAL (Admin)
    // =========================================

    /**
     * Bank Soal index for Admin
     */
    public function bankSoalIndex(Request $request)
    {
        $courses      = Course::with('prodi')->withCount('questions')->orderBy('name', 'asc')->get();
        $courseId     = $request->query('course_id', $courses->first()?->id);
        $difficulty   = $request->query('difficulty', '');
        $questionType = $request->query('question_type', '');
        $category     = $request->query('category', '');
        $search       = $request->query('search', '');

        $query = Question::where('course_id', $courseId)->orderBy('id', 'desc');

        $bankSoals = BankSoal::with(['course'])
            ->withCount('questions')
            ->where('course_id', $courseId)
            ->get();

        if ($difficulty)   $query->where('difficulty', $difficulty);
        if ($questionType) $query->where('question_type', $questionType);
        if ($category)     $query->where('category', $category);
        if ($search)       $query->where('question_text', 'like', "%{$search}%");

        $questions    = $query->get();
        $questionCount = Question::where('course_id', $courseId)->count();
        $categories   = Question::where('course_id', $courseId)->whereNotNull('category')->distinct()->pluck('category');

        // Stats per difficulty
        $statsByDiff = Question::where('course_id', $courseId)
            ->selectRaw('difficulty, count(*) as total')
            ->groupBy('difficulty')
            ->pluck('total', 'difficulty');

        return view('admin.bank-soal.index', compact('courses', 'questions', 'courseId', 'difficulty', 'questionType', 'category', 'search', 'questionCount', 'categories', 'statsByDiff', 'bankSoals'));
    }

    /**
     * Preview a single question
     */
    public function bankSoalPreview($id)
    {
        $question = Question::with('matches')->findOrFail($id);
        return response()->json(['success' => true, 'question' => $question]);
    }

    /**
     * Store a new Question (Admin)
     */
    public function bankSoalStore(Request $request)
    {
        $data = $request->validate([
            'course_id'      => 'required|exists:courses,id',
            'category'       => 'nullable|string|max:255',
            'difficulty'     => 'required|in:mudah,sedang,sulit',
            'question_text'  => 'required|string',
            'option_a'       => 'required|string',
            'option_b'       => 'required|string',
            'option_c'       => 'required|string',
            'option_d'       => 'required|string',
            'option_e'       => 'required|string',
            'correct_option' => 'required|in:A,B,C,D,E',
        ]);

        $question = Question::create($data);
        ActivityLog::log('Tambah Soal (Admin)', "Admin menambahkan soal ID: {$question->id} untuk MK: {$data['course_id']}.");

        return response()->json(['success' => true, 'message' => 'Soal berhasil ditambahkan.']);
    }

    /**
     * Update an existing Question (Admin)
     */
    public function bankSoalUpdate(Request $request, $id)
    {
        $question = Question::findOrFail($id);

        $data = $request->validate([
            'course_id'      => 'required|exists:courses,id',
            'category'       => 'nullable|string|max:255',
            'difficulty'     => 'required|in:mudah,sedang,sulit',
            'question_text'  => 'required|string',
            'option_a'       => 'required|string',
            'option_b'       => 'required|string',
            'option_c'       => 'required|string',
            'option_d'       => 'required|string',
            'option_e'       => 'required|string',
            'correct_option' => 'required|in:A,B,C,D,E',
        ]);

        $question->update($data);
        ActivityLog::log('Edit Soal (Admin)', "Admin mengubah soal ID: {$question->id}.");

        return response()->json(['success' => true, 'message' => 'Soal berhasil diperbarui.']);
    }

    /**
     * Delete a Question (Admin)
     */
    public function bankSoalDestroy($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();
        ActivityLog::log('Hapus Soal (Admin)', "Admin menghapus soal ID: {$id}.");

        return response()->json(['success' => true, 'message' => 'Soal berhasil dihapus.']);
    }

    /**
     * Audit Log page
     */
    public function auditLogsIndex()
    {
        $logs = ActivityLog::with('user')->orderBy('created_at', 'desc')->paginate(50);
        return view('admin.audit', compact('logs'));
    }

    /**
     * Classes Master List
     */
    public function classesIndex()
    {
        $classes = \App\Models\ClassRoom::with('prodi', 'waliKelas')->withCount('users')->orderBy('name', 'asc')->get();
        $prodis  = Prodi::where('is_aktif', true)->orderBy('nama')->get();
        $dosens  = User::where('role', 'dosen')->orderBy('name')->get();
        return view('admin.classes', compact('classes', 'prodis', 'dosens'));
    }

    /**
     * Store new Class
     */
    public function classesStore(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|unique:classes|max:100',
            'description'   => 'nullable|string',
            'prodi_id'      => 'nullable|exists:prodis,id',
            'angkatan'      => 'nullable|string|max:10',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        $class = \App\Models\ClassRoom::create($data);

        ActivityLog::log('Tambah Kelas', "Menambahkan kelas baru: {$class->name}.");

        return response()->json(['success' => true, 'message' => 'Kelas berhasil ditambahkan.']);
    }

    /**
     * Update existing Class
     */
    public function classesUpdate(Request $request, $id)
    {
        $class = \App\Models\ClassRoom::findOrFail($id);

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:100', Rule::unique('classes')->ignore($class->id)],
            'description'   => 'nullable|string',
            'prodi_id'      => 'nullable|exists:prodis,id',
            'angkatan'      => 'nullable|string|max:10',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        $class->update($data);

        ActivityLog::log('Edit Kelas', "Mengubah data kelas: {$class->name}.");

        return response()->json(['success' => true, 'message' => 'Kelas berhasil diperbarui.']);
    }

    /**
     * Delete Class
     */
    public function classesDestroy($id)
    {
        $class = \App\Models\ClassRoom::findOrFail($id);
        $name = $class->name;
        $class->delete();

        ActivityLog::log('Hapus Kelas', "Menghapus kelas: {$name}.");

        return response()->json(['success' => true, 'message' => 'Kelas berhasil dihapus.']);
    }

    /**
     * Feeder Integration View
     */
    public function feederIndex(FeederService $feederService)
    {
        $config = $feederService->getConfig();
        return view('admin.feeder', compact('config'));
    }

    /**
     * Save Feeder Configuration
     */
    public function feederSaveConfig(Request $request, FeederService $feederService)
    {
        $data = $request->validate([
            'url' => 'required|url',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $feederService->saveConfig($data);

        ActivityLog::log('Konfigurasi Feeder', "Mengubah konfigurasi Web Service Neo Feeder.");

        return response()->json(['success' => true, 'message' => 'Konfigurasi Feeder berhasil disimpan.']);
    }

    /**
     * Test Connection to Feeder
     */
    public function feederTestConnection(Request $request, FeederService $feederService)
    {
        $data = $request->validate([
            'url' => 'required|url',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        try {
            $token = $feederService->testConnection($data['url'], $data['username'], $data['password']);
            return response()->json(['success' => true, 'message' => 'Koneksi Sukses! Berhasil mendapatkan token.', 'token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Run Sync Now
     */
    public function feederSyncNow(Request $request, FeederService $feederService)
    {
        $data = $request->validate([
            'type' => 'required|in:courses,dosen,mahasiswa',
        ]);
        try {
            $type = $data['type'];
            if ($type === 'courses') {
                $result = $feederService->syncCourses();
                $feederService->updateLastSync('courses');
                ActivityLog::log('Sinkronisasi Feeder', "Sinkronisasi mata kuliah selesai.");
                $msg = "Mata kuliah berhasil disinkronkan. Terimpor baru: {$result['imported']}, Diperbarui: {$result['updated']}.";
            } elseif ($type === 'dosen') {
                $result = $feederService->syncDosen();
                $feederService->updateLastSync('dosen');
                ActivityLog::log('Sinkronisasi Feeder', "Sinkronisasi dosen selesai.");
                $msg = "Dosen berhasil disinkronkan. Terimpor baru: {$result['imported']}, Diperbarui: {$result['updated']}.";
            } else {
                $result = $feederService->syncMahasiswa();
                $feederService->updateLastSync('mahasiswa');
                ActivityLog::log('Sinkronisasi Feeder', "Sinkronisasi mahasiswa selesai.");
                $msg = "Mahasiswa berhasil disinkronkan. Terimpor baru: {$result['imported']}, Diperbarui: {$result['updated']}.";
            }

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get list of students in a class
     */
    public function classStudents($id)
    {
        $class = \App\Models\ClassRoom::findOrFail($id);
        $students = User::where('class_id', $id)->where('role', 'mahasiswa')->orderBy('name', 'asc')->get();
        return response()->json([
            'success' => true,
            'class_name' => $class->name,
            'students' => $students
        ]);
    }

    /**
     * Monitoring index - lists all exams
     */
    public function monitoringIndex()
    {
        $exams = \App\Models\Exam::with(['course', 'dosen'])
            ->withCount([
                'studentExams as total_started' => function($q) { $q->where('status', 'progress'); },
                'studentExams as total_finished' => function($q) { $q->where('status', 'finished'); },
                'studentExams as total_pending' => function($q) { $q->where('status', 'pending'); }
            ])
            ->orderBy('start_time', 'desc')
            ->get();

        return view('admin.monitoring', compact('exams'));
    }

    /**
     * Monitoring detail - lists students status for a specific exam
     */
    public function monitoringDetail($id)
    {
        $exam = \App\Models\Exam::with(['course', 'dosen'])->findOrFail($id);

        // Fetch students (filter by class if exam has class_id)
        $query = User::where('role', 'mahasiswa');
        if ($exam->class_id) {
            $query->where('class_id', $exam->class_id);
        }

        $students = $query->with('classRoom')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function($student) use ($exam) {
                // Find student exam session if exists
                $session = \App\Models\StudentExam::where('user_id', $student->id)
                    ->where('exam_id', $exam->id)
                    ->first();

                $student->exam_session = $session;
                return $student;
            });

        return view('admin.monitoring_detail', compact('exam', 'students'));
    }

    /**
     * Reset a student's exam attempt
     */
    public function monitoringResetStudent($id)
    {
        $session = \App\Models\StudentExam::findOrFail($id);
        $studentName = $session->user->name;
        $examTitle = $session->exam->title;

        \Illuminate\Support\Facades\DB::transaction(function() use ($session) {
            \App\Models\StudentAnswer::where('student_exam_id', $session->id)->delete();
            $session->delete();
        });

        ActivityLog::log('Reset Ujian', "Mereset sesi ujian mahasiswa: {$studentName} pada ujian '{$examTitle}'.");

        return response()->json(['success' => true, 'message' => "Ujian {$studentName} berhasil di-reset."]);
    }

    /**
     * Toggle student exam pending/suspend status
     */
    public function monitoringTogglePending($id)
    {
        $session = \App\Models\StudentExam::findOrFail($id);
        $studentName = $session->user->name;

        if ($session->status === 'progress') {
            $session->status = 'pending';
            $session->suspended_at = now();
            $session->save();

            ActivityLog::log('Tangguhkan Ujian', "Menangguhkan ujian mahasiswa: {$studentName}.");
            return response()->json(['success' => true, 'message' => "Ujian {$studentName} ditangguhkan (pending)."]);
        } elseif ($session->status === 'pending') {
            // Calculate suspended duration
            $suspendedAt = \Carbon\Carbon::parse($session->suspended_at ?? now());
            $suspendedSeconds = now()->diffInSeconds($suspendedAt);

            // Shift started_at forward
            $session->started_at = \Carbon\Carbon::parse($session->started_at)->addSeconds($suspendedSeconds);
            $session->status = 'progress';
            $session->suspended_at = null;
            $session->save();

            ActivityLog::log('Lanjutkan Ujian', "Melanjutkan ujian mahasiswa: {$studentName}.");
            return response()->json(['success' => true, 'message' => "Ujian {$studentName} dilanjutkan (aktif kembali)."]);
        }

        return response()->json(['success' => false, 'message' => "Status sesi ujian tidak dapat diubah."], 400);
    }

    /**
     * Adjust duration / end time of an exam session
     */
    public function monitoringAdjustTime(Request $request, $id)
    {
        $exam = \App\Models\Exam::findOrFail($id);

        $data = $request->validate([
            'duration_minutes' => 'required|integer|min:1',
            'end_time' => 'required|date|after:start_time',
        ]);

        $exam->duration_minutes = $data['duration_minutes'];
        $exam->end_time = $data['end_time'];
        $exam->save();

        ActivityLog::log('Atur Waktu Ujian', "Mengatur waktu ujian '{$exam->title}': durasi {$exam->duration_minutes} menit, selesai {$exam->end_time}.");

        return response()->json(['success' => true, 'message' => "Waktu ujian berhasil diperbarui."]);
    }

    /**
     * Admin Sesi Ujian (Jadwal Ujian) index
     */
    public function examsIndex()
    {
        $bankSoals     = \App\Models\BankSoal::with(['course', 'dosen'])->where('is_aktif', true)->get();
        $dosens        = User::where('role', 'dosen')->orderBy('name', 'asc')->get();
        $classes       = \App\Models\ClassRoom::orderBy('name', 'asc')->get();
        $ruangs        = Ruang::where('is_aktif', true)->orderBy('nama')->get();
        $sesis         = Sesi::where('is_aktif', true)->orderBy('jam_mulai')->get();
        $jenisUjians   = JenisUjian::where('is_aktif', true)->orderBy('kode')->get();
        $tahunAkademik = TahunAkademik::orderBy('tahun_mulai', 'desc')->get();
        $exams = \App\Models\Exam::with(['course', 'bankSoal', 'dosen', 'classRoom', 'jenisUjian', 'ruang', 'sesi', 'tahunAkademik'])
            ->orderBy('start_time', 'desc')->get();

        return view('admin.exams', compact('bankSoals', 'dosens', 'classes', 'ruangs', 'sesis', 'jenisUjians', 'tahunAkademik', 'exams'));
    }

    /**
     * Store new Exam (Admin)
     */
    public function examsStore(Request $request)
    {
        $data = $request->validate([
            'bank_soal_id'      => 'required|exists:bank_soals,id',
            'dosen_id'          => 'required|exists:users,id',
            'class_id'          => 'nullable|exists:classes,id',
            'tahun_akademik_id' => 'nullable|exists:tahun_akademik,id',
            'jenis_ujian_id'    => 'nullable|exists:jenis_ujians,id',
            'ruang_id'          => 'nullable|exists:ruangs,id',
            'sesi_id'           => 'nullable|exists:sesis,id',
            'exam_type'         => 'nullable|string|max:50',
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'petunjuk'          => 'nullable|string',
            'start_time'        => 'required|date',
            'end_time'          => 'required|date|after:start_time',
            'duration_minutes'  => 'required|integer|min:5',
            'total_questions'   => 'required|integer|min:1',
            'passing_grade'     => 'nullable|numeric|min:0|max:100',
            'is_random'         => 'nullable|boolean',
        ]);

        $bankSoal = \App\Models\BankSoal::findOrFail($data['bank_soal_id']);
        $data['course_id'] = $bankSoal->course_id;

        $availableQuestionsCount = Question::where('bank_soal_id', $data['bank_soal_id'])->count();
        if ($availableQuestionsCount < $data['total_questions']) {
            return response()->json([
                'success' => false,
                'message' => "Soal tidak cukup. Soal tersedia: {$availableQuestionsCount}, sedangkan yang diminta: {$data['total_questions']}."
            ], 422);
        }

        $data['token']    = strtoupper(\Illuminate\Support\Str::random(6));
        $data['is_random']     = $request->has('is_random') ? (bool)$request->input('is_random') : true;
        $data['passing_grade'] = $data['passing_grade'] ?? 60;

        $exam = \App\Models\Exam::create($data);

        ActivityLog::log('Tambah Sesi Ujian (Admin)', "Membuat sesi ujian '{$exam->title}' [{$exam->exam_type}] oleh Admin dengan token: {$exam->token}.");

        return response()->json(['success' => true, 'message' => "Sesi ujian berhasil dibuat dengan Token: {$exam->token}"]);
    }

    /**
     * Update Exam (Admin)
     */
    public function examsUpdate(Request $request, $id)
    {
        $exam = \App\Models\Exam::findOrFail($id);

        $data = $request->validate([
            'bank_soal_id'      => 'required|exists:bank_soals,id',
            'dosen_id'          => 'required|exists:users,id',
            'class_id'          => 'nullable|exists:classes,id',
            'tahun_akademik_id' => 'nullable|exists:tahun_akademik,id',
            'jenis_ujian_id'    => 'nullable|exists:jenis_ujians,id',
            'ruang_id'          => 'nullable|exists:ruangs,id',
            'sesi_id'           => 'nullable|exists:sesis,id',
            'exam_type'         => 'nullable|string|max:50',
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'petunjuk'          => 'nullable|string',
            'start_time'        => 'required|date',
            'end_time'          => 'required|date|after:start_time',
            'duration_minutes'  => 'required|integer|min:5',
            'total_questions'   => 'required|integer|min:1',
            'passing_grade'     => 'nullable|numeric|min:0|max:100',
            'is_random'         => 'nullable|boolean',
        ]);

        $bankSoal = \App\Models\BankSoal::findOrFail($data['bank_soal_id']);
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

        ActivityLog::log('Edit Sesi Ujian (Admin)', "Admin mengubah sesi ujian '{$exam->title}'.");

        return response()->json(['success' => true, 'message' => 'Sesi ujian berhasil diperbarui.']);
    }

    /**
     * Delete Exam (Admin)
     */
    public function examsDestroy($id)
    {
        $exam = \App\Models\Exam::findOrFail($id);
        $title = $exam->title;
        $exam->delete();

        ActivityLog::log('Hapus Sesi Ujian (Admin)', "Menghapus sesi ujian oleh Admin: {$title}.");

        return response()->json(['success' => true, 'message' => 'Sesi ujian berhasil dihapus.']);
    }

    /**
     * Regenerate Exam Token (Admin)
     */
    public function examsRegenerateToken($id)
    {
        $exam = \App\Models\Exam::findOrFail($id);
        $oldToken = $exam->token;
        $exam->token = strtoupper(\Illuminate\Support\Str::random(6));
        $exam->save();

        ActivityLog::log('Perbarui Token (Admin)', "Mengubah token sesi ujian '{$exam->title}' oleh Admin dari {$oldToken} menjadi {$exam->token}.");

        return response()->json(['success' => true, 'token' => $exam->token, 'message' => 'Token berhasil diperbarui.']);
    }
}
