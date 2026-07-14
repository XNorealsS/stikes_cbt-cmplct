<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\ExamApiController;
use App\Http\Controllers\TahunAkademikController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\UjianMasterController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\ElearningController;
use App\Http\Controllers\FeederController;
use App\Http\Controllers\BankSoalController;

// 1. Guest Routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 2. Authenticated Routes Group
Route::middleware(['auth'])->group(function () {

    // ==========================================
    // ADMIN ROUTES
    // ==========================================
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        // ---- DATA MASTER ----

        // Tahun Akademik
        Route::get('/tahun-akademik', [TahunAkademikController::class, 'index'])->name('tahun-akademik.index');
        Route::post('/tahun-akademik', [TahunAkademikController::class, 'store'])->name('tahun-akademik.store');
        Route::put('/tahun-akademik/{id}', [TahunAkademikController::class, 'update'])->name('tahun-akademik.update');
        Route::post('/tahun-akademik/{id}/set-aktif', [TahunAkademikController::class, 'setAktif'])->name('tahun-akademik.set-aktif');
        Route::delete('/tahun-akademik/{id}', [TahunAkademikController::class, 'destroy'])->name('tahun-akademik.destroy');

        // Program Studi
        Route::get('/prodi', [ProdiController::class, 'index'])->name('prodi.index');
        Route::post('/prodi', [ProdiController::class, 'store'])->name('prodi.store');
        Route::put('/prodi/{id}', [ProdiController::class, 'update'])->name('prodi.update');
        Route::delete('/prodi/{id}', [ProdiController::class, 'destroy'])->name('prodi.destroy');

        // User Management
        Route::get('/manajemen-user', [AdminController::class, 'usersIndex'])->name('users.index');
        Route::post('/manajemen-user', [AdminController::class, 'usersStore'])->name('users.store');
        Route::put('/manajemen-user/{id}', [AdminController::class, 'usersUpdate'])->name('users.update');
        Route::delete('/manajemen-user/{id}', [AdminController::class, 'usersDestroy'])->name('users.destroy');
        Route::post('/manajemen-user/import', [AdminController::class, 'usersImport'])->name('users.import');
        Route::get('/manajemen-user/template/{role}', [AdminController::class, 'usersImportTemplate'])->name('users.import-template');
        Route::post('/manajemen-user/{id}/reset-password', [AdminController::class, 'usersResetPassword'])->name('users.reset-password');

        // Course Master CRUD
        Route::get('/mata-kuliah', [AdminController::class, 'coursesIndex'])->name('courses.index');
        Route::post('/mata-kuliah', [AdminController::class, 'coursesStore'])->name('courses.store');
        Route::put('/mata-kuliah/{id}', [AdminController::class, 'coursesUpdate'])->name('courses.update');
        Route::delete('/mata-kuliah/{id}', [AdminController::class, 'coursesDestroy'])->name('courses.destroy');
        Route::post('/mata-kuliah/import', [AdminController::class, 'coursesImport'])->name('courses.import');

        // Bank Soal (Admin)
        Route::get('/bank-soal', [AdminController::class, 'bankSoalIndex'])->name('bank-soal.index');
        Route::post('/bank-soal', [AdminController::class, 'bankSoalStore'])->name('bank-soal.store');
        Route::put('/bank-soal/{id}', [AdminController::class, 'bankSoalUpdate'])->name('bank-soal.update');
        Route::delete('/bank-soal/{id}', [AdminController::class, 'bankSoalDestroy'])->name('bank-soal.destroy');
        Route::get('/bank-soal/{id}/preview', [AdminController::class, 'bankSoalPreview'])->name('bank-soal.preview');

        // Class Master CRUD
        Route::get('/kelas', [AdminController::class, 'classesIndex'])->name('classes.index');
        Route::post('/kelas', [AdminController::class, 'classesStore'])->name('classes.store');
        Route::put('/kelas/{id}', [AdminController::class, 'classesUpdate'])->name('classes.update');
        Route::delete('/kelas/{id}', [AdminController::class, 'classesDestroy'])->name('classes.destroy');
        Route::get('/kelas/{id}/students', [AdminController::class, 'classStudents'])->name('classes.students');

        // ---- MASTER UJIAN ----

        // Ruang Ujian
        Route::get('/ruang-ujian', [UjianMasterController::class, 'ruangIndex'])->name('ruang.index');
        Route::post('/ruang-ujian', [UjianMasterController::class, 'ruangStore'])->name('ruang.store');
        Route::put('/ruang-ujian/{id}', [UjianMasterController::class, 'ruangUpdate'])->name('ruang.update');
        Route::delete('/ruang-ujian/{id}', [UjianMasterController::class, 'ruangDestroy'])->name('ruang.destroy');

        // Sesi Ujian
        Route::get('/sesi-ujian', [UjianMasterController::class, 'sesiIndex'])->name('sesi.index');
        Route::post('/sesi-ujian', [UjianMasterController::class, 'sesiStore'])->name('sesi.store');
        Route::put('/sesi-ujian/{id}', [UjianMasterController::class, 'sesiUpdate'])->name('sesi.update');
        Route::delete('/sesi-ujian/{id}', [UjianMasterController::class, 'sesiDestroy'])->name('sesi.destroy');

        // Jenis Ujian
        Route::get('/jenis-ujian', [UjianMasterController::class, 'jenisUjianIndex'])->name('jenis-ujian.index');
        Route::post('/jenis-ujian', [UjianMasterController::class, 'jenisUjianStore'])->name('jenis-ujian.store');
        Route::put('/jenis-ujian/{id}', [UjianMasterController::class, 'jenisUjianUpdate'])->name('jenis-ujian.update');
        Route::delete('/jenis-ujian/{id}', [UjianMasterController::class, 'jenisUjianDestroy'])->name('jenis-ujian.destroy');

        // Pengumuman
        Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
        Route::post('/pengumuman', [PengumumanController::class, 'store'])->name('pengumuman.store');
        Route::put('/pengumuman/{id}', [PengumumanController::class, 'update'])->name('pengumuman.update');
        Route::delete('/pengumuman/{id}', [PengumumanController::class, 'destroy'])->name('pengumuman.destroy');

        // ---- PELAKSANAAN UJIAN ----

        // Exam Monitoring
        Route::get('/monitoring', [AdminController::class, 'monitoringIndex'])->name('monitoring.index');
        Route::get('/monitoring/{id}', [AdminController::class, 'monitoringDetail'])->name('monitoring.detail');
        Route::post('/monitoring/student-exam/{id}/reset', [AdminController::class, 'monitoringResetStudent'])->name('monitoring.reset');
        Route::post('/monitoring/student-exam/{id}/toggle-pending', [AdminController::class, 'monitoringTogglePending'])->name('monitoring.toggle-pending');
        Route::post('/monitoring/exam/{id}/adjust-time', [AdminController::class, 'monitoringAdjustTime'])->name('monitoring.adjust-time');

        // Jadwal Ujian CRUD (Sesi Ujian)
        Route::get('/jadwal-ujian', [AdminController::class, 'examsIndex'])->name('exams.index');
        Route::post('/jadwal-ujian', [AdminController::class, 'examsStore'])->name('exams.store');
        Route::put('/jadwal-ujian/{id}', [AdminController::class, 'examsUpdate'])->name('exams.update');
        Route::delete('/jadwal-ujian/{id}', [AdminController::class, 'examsDestroy'])->name('exams.destroy');
        Route::post('/jadwal-ujian/regenerate/{id}', [AdminController::class, 'examsRegenerateToken'])->name('exams.regenerate');

        // ---- LAPORAN & ANALISIS ----

        // Analisis Butir Soal
        Route::get('/analisis-soal', [AnalisisController::class, 'index'])->name('analisis.index');
        Route::post('/analisis-soal/{exam_id}/run', [AnalisisController::class, 'runAnalysis'])->name('analisis.run');
        Route::get('/analisis-soal/{exam_id}/export', [AnalisisController::class, 'exportExcel'])->name('analisis.export');

        // ---- PENGATURAN ----

        // Audit logs
        Route::get('/audit-system', [AdminController::class, 'auditLogsIndex'])->name('audit.index');

        // Integrasi Neo Feeder PDDIKTI
        Route::get('/feeder', [FeederController::class, 'index'])->name('feeder.index');
        Route::post('/feeder/test', [FeederController::class, 'testConnection'])->name('feeder.test');
        Route::post('/feeder/sync', [FeederController::class, 'sync'])->name('feeder.sync');
        Route::get('/feeder/logs', [FeederController::class, 'logs'])->name('feeder.logs');
        Route::get('/feeder/logs/{id}', [FeederController::class, 'logDetail'])->name('feeder.log-detail');
        Route::post('/feeder/peek', [FeederController::class, 'peek'])->name('feeder.peek');
    });

    // ==========================================
    // DOSEN ROUTES
    // ==========================================
    Route::middleware(['role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
        Route::get('/', [DosenController::class, 'dashboard'])->name('dashboard');

        // Bank Soal
        Route::get('/bank-soal', [BankSoalController::class, 'index'])->name('questions.index');
        Route::post('/bank-soal', [BankSoalController::class, 'store'])->name('bank-soal.store');
        Route::put('/bank-soal/{id}', [BankSoalController::class, 'update'])->name('bank-soal.update');
        Route::delete('/bank-soal/{id}', [BankSoalController::class, 'destroy'])->name('bank-soal.destroy');
        Route::post('/bank-soal/{id}/toggle-aktif', [BankSoalController::class, 'toggleActive'])->name('bank-soal.toggle-aktif');
        Route::get('/bank-soal/{id}', [BankSoalController::class, 'show'])->name('bank-soal.show');

        // Questions within Bank Soal (AJAX & Imports)
        Route::post('/bank-soal/{bank_soal_id}/questions', [BankSoalController::class, 'questionStore'])->name('questions.store');
        Route::put('/bank-soal/questions/{id}', [BankSoalController::class, 'questionUpdate'])->name('questions.update');
        Route::delete('/bank-soal/questions/{id}', [BankSoalController::class, 'questionDestroy'])->name('questions.destroy');
        Route::post('/bank-soal/{bank_soal_id}/questions/import', [BankSoalController::class, 'questionImport'])->name('questions.import');
        Route::get('/bank-soal/questions/template-excel', [BankSoalController::class, 'downloadExcelTemplate'])->name('questions.template-excel');
        Route::get('/bank-soal/questions/template-word', [BankSoalController::class, 'downloadWordTemplate'])->name('questions.template-word');
        Route::get('/bank-soal/questions/{id}/preview', [BankSoalController::class, 'questionPreview'])->name('questions.preview');

        // Sesi Ujian (Jadwal Ujian)
        Route::get('/jadwal-ujian', [DosenController::class, 'jadwalUjian'])->name('exams.index');
        Route::post('/jadwal-ujian', [DosenController::class, 'examStore'])->name('exams.store');
        Route::put('/jadwal-ujian/{id}', [DosenController::class, 'examUpdate'])->name('exams.update');
        Route::delete('/jadwal-ujian/{id}', [DosenController::class, 'examDestroy'])->name('exams.destroy');
        Route::post('/jadwal-ujian/regenerate/{id}', [DosenController::class, 'examRegenerateToken'])->name('exams.regenerate');

        // Rekap Nilai & Ekspor
        Route::get('/rekap-nilai', [DosenController::class, 'rekapNilai'])->name('grades.index');
        Route::get('/rekap-nilai/export/{exam_id}', [DosenController::class, 'exportGradesCsv'])->name('grades.export');

        // Analisis Butir Soal
        Route::get('/analisis-soal', [AnalisisController::class, 'dosenIndex'])->name('analisis.index');
        Route::post('/analisis-soal/{exam_id}/run', [AnalisisController::class, 'runAnalysis'])->name('analisis.run');
        Route::get('/analisis-soal/{exam_id}/export', [AnalisisController::class, 'exportExcel'])->name('analisis.export');

        // E-Learning Materi
        Route::get('/e-learning/materi', [ElearningController::class, 'materiIndex'])->name('materi.index');
        Route::post('/e-learning/materi', [ElearningController::class, 'materiStore'])->name('materi.store');
        Route::put('/e-learning/materi/{id}', [ElearningController::class, 'materiUpdate'])->name('materi.update');
        Route::delete('/e-learning/materi/{id}', [ElearningController::class, 'materiDestroy'])->name('materi.destroy');
        Route::get('/e-learning/materi/{id}/progress', [ElearningController::class, 'materiProgress'])->name('materi.progress');

        // E-Learning Tugas
        Route::get('/e-learning/tugas', [ElearningController::class, 'tugasIndex'])->name('tugas.index');
        Route::post('/e-learning/tugas', [ElearningController::class, 'tugasStore'])->name('tugas.store');
        Route::put('/e-learning/tugas/{id}', [ElearningController::class, 'tugasUpdate'])->name('tugas.update');
        Route::delete('/e-learning/tugas/{id}', [ElearningController::class, 'tugasDestroy'])->name('tugas.destroy');
        Route::get('/e-learning/tugas/{id}/submissions', [ElearningController::class, 'tugasSubmissions'])->name('tugas.submissions');
        Route::post('/e-learning/tugas/submissions/{id}/nilai', [ElearningController::class, 'tugasNilai'])->name('tugas.nilai');
    });

    // ==========================================
    // MAHASISWA ROUTES
    // ==========================================
    Route::middleware(['role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::get('/', [MahasiswaController::class, 'dashboard'])->name('dashboard');
        Route::get('/ujian', [MahasiswaController::class, 'ujianIndex'])->name('ujian.index');
        Route::get('/ruang-ujian/{id}', [MahasiswaController::class, 'examRoom'])->name('exam-room');
        Route::get('/riwayat-ujian', [MahasiswaController::class, 'examHistory'])->name('history');
        Route::get('/riwayat-ujian/review/{id}', [MahasiswaController::class, 'examReview'])->name('review');

        // E-Learning Mahasiswa
        Route::get('/materi', [ElearningController::class, 'mahasiswaMateri'])->name('materi.index');
        Route::get('/materi/{id}', [ElearningController::class, 'mahasiswaMateriShow'])->name('materi.show');
        Route::get('/materi/{id}/download', [ElearningController::class, 'mahasiswaMateriDownload'])->name('materi.download');
        Route::get('/materi/{id}/open', [ElearningController::class, 'mahasiswaMateriOpen'])->name('materi.open');
        Route::post('/materi/{id}/view', [ElearningController::class, 'mahasiswaMateriView'])->name('materi.view');
        Route::post('/notifications/mark-read', [ElearningController::class, 'markNotificationsRead'])->name('notifications.mark-read');
        Route::get('/tugas', [ElearningController::class, 'mahasiswaTugas'])->name('tugas.index');
        Route::post('/tugas/{id}/submit', [ElearningController::class, 'mahasiswaTugasSubmit'])->name('tugas.submit');
    });

    // ==========================================
    // SECURE INTERNAL EXAM API ENDPOINTS
    // ==========================================
    Route::middleware(['role:mahasiswa'])->prefix('api/v1/exam')->name('api.exam.')->group(function () {
        Route::post('/start', [ExamApiController::class, 'start'])->name('start');
        Route::post('/save-answer', [ExamApiController::class, 'saveAnswer'])->name('save-answer');
        Route::get('/timer-sync/{id}', [ExamApiController::class, 'timerSync'])->name('timer-sync');
        Route::post('/submit-final', [ExamApiController::class, 'submitFinal'])->name('submit-final');
        Route::post('/reset-session', [ExamApiController::class, 'resetSession'])->name('reset-session');
    });

});
