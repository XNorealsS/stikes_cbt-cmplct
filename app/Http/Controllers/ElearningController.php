<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Tugas;
use App\Models\TugasSubmission;
use App\Models\MateriView;
use App\Models\TugasSubmissionFile;
use App\Models\Notification;
use App\Models\User;
use App\Models\Course;
use App\Models\ClassRoom;
use App\Models\TahunAkademik;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ElearningController extends Controller
{
    // ==========================================
    // DOSEN — MATERI
    // ==========================================

    public function materiIndex(Request $request)
    {
        $courses = Course::orderBy('name')->get();
        $classes = ClassRoom::orderBy('name')->get();
        $tahunAkademik = TahunAkademik::orderBy('tahun_mulai', 'desc')->get();

        $courseId = $request->query('course_id', $courses->first()?->id);
        $classId  = $request->query('class_id');

        $query = Materi::with(['course', 'classRoom', 'tahunAkademik'])
            ->where('user_id', auth()->id())
            ->where('course_id', $courseId);

        if ($classId) $query->where('class_id', $classId);

        $materis = $query->orderBy('created_at', 'desc')->get();

        return view('dosen.materi', compact('courses', 'classes', 'tahunAkademik', 'materis', 'courseId', 'classId'));
    }

    public function materiStore(Request $request)
    {
        $data = $request->validate([
            'course_id'          => 'required|exists:courses,id',
            'class_id'           => 'nullable|exists:classes,id',
            'tahun_akademik_id'  => 'nullable|exists:tahun_akademik,id',
            'pertemuan_ke'       => 'nullable|integer|min:1',
            'judul'              => 'required|string|max:255',
            'deskripsi'          => 'nullable|string',
            'tipe'               => 'required|in:file,link,text',
            'link_url'           => 'nullable|url|required_if:tipe,link',
            'konten'             => 'nullable|string|required_if:tipe,text',
            'tanggal_tayang'     => 'nullable|date',
        ]);

        if ($request->tipe === 'file' && $request->hasFile('file_materi')) {
            $path = $request->file('file_materi')->store('materi', 'public');
            $data['file_path'] = $path;
        }

        $data['user_id'] = auth()->id();
        $data['is_aktif'] = true;

        $materi = Materi::create($data);
        ActivityLog::log('Upload Materi', "Mengunggah materi: {$materi->judul}.");

        // Send notifications if published immediately
        $today = now()->toDateString();
        if ($materi->is_aktif && (!$materi->tanggal_tayang || $materi->tanggal_tayang <= $today)) {
            $this->sendMateriNotification($materi);
        }

        return response()->json(['success' => true, 'message' => 'Materi berhasil diunggah.']);
    }

    public function materiUpdate(Request $request, $id)
    {
        $materi = Materi::where('user_id', auth()->id())->findOrFail($id);
        $wasActive = $materi->is_aktif;

        $data = $request->validate([
            'judul'          => 'required|string|max:255',
            'deskripsi'      => 'nullable|string',
            'pertemuan_ke'   => 'nullable|integer|min:1',
            'tanggal_tayang' => 'nullable|date',
            'is_aktif'       => 'nullable|boolean',
        ]);

        $materi->update($data);
        ActivityLog::log('Edit Materi', "Mengubah materi: {$materi->judul}.");

        // Send notifications if it wasn't active but now is published
        $today = now()->toDateString();
        if (!$wasActive && $materi->is_aktif && (!$materi->tanggal_tayang || $materi->tanggal_tayang <= $today)) {
            $this->sendMateriNotification($materi);
        }

        return response()->json(['success' => true, 'message' => 'Materi berhasil diperbarui.']);
    }

    public function materiDestroy($id)
    {
        $materi = Materi::where('user_id', auth()->id())->findOrFail($id);
        if ($materi->file_path) Storage::disk('public')->delete($materi->file_path);
        $judul = $materi->judul;
        $materi->delete();

        ActivityLog::log('Hapus Materi', "Menghapus materi: {$judul}.");
        return response()->json(['success' => true, 'message' => 'Materi berhasil dihapus.']);
    }

    public function materiProgress($id)
    {
        $materi = Materi::where('user_id', auth()->id())->findOrFail($id);
        $classId = $materi->class_id;

        $studentsQuery = User::where('role', 'mahasiswa');
        if ($classId) {
            $studentsQuery->where('class_id', $classId);
        }
        $students = $studentsQuery->orderBy('name')->get();

        $views = MateriView::where('materi_id', $id)
            ->pluck('viewed_at', 'user_id');

        $data = $students->map(function ($student) use ($views) {
            $viewedAt = isset($views[$student->id]) ? $views[$student->id] : null;
            return [
                'name' => $student->name,
                'nim' => $student->username,
                'viewed_at' => $viewedAt ? \Carbon\Carbon::parse($viewedAt)->format('d/m/Y H:i') . ' WIB' : null,
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    // ==========================================
    // DOSEN — TUGAS
    // ==========================================

    public function tugasIndex(Request $request)
    {
        $courses = Course::orderBy('name')->get();
        $classes = ClassRoom::orderBy('name')->get();
        $courseId = $request->query('course_id', $courses->first()?->id);

        $tugas = Tugas::with(['course', 'classRoom'])
            ->where('user_id', auth()->id())
            ->where('course_id', $courseId)
            ->withCount('submissions')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dosen.tugas', compact('courses', 'classes', 'tugas', 'courseId'));
    }

    public function tugasStore(Request $request)
    {
        $data = $request->validate([
            'course_id'         => 'required|exists:courses,id',
            'class_id'          => 'nullable|exists:classes,id',
            'tahun_akademik_id' => 'nullable|exists:tahun_akademik,id',
            'judul'             => 'required|string|max:255',
            'deskripsi'         => 'nullable|string',
            'poin_nilai'        => 'required|numeric|min:0|max:100',
            'deadline'          => 'nullable|date',
            'tanggal_tayang'    => 'nullable|date',
        ]);

        $data['user_id'] = auth()->id();
        $data['is_aktif'] = true;

        $tugas = Tugas::create($data);
        ActivityLog::log('Buat Tugas', "Membuat tugas: {$tugas->judul}.");

        return response()->json(['success' => true, 'message' => 'Tugas berhasil dibuat.']);
    }

    public function tugasUpdate(Request $request, $id)
    {
        $tugas = Tugas::where('user_id', auth()->id())->findOrFail($id);

        $data = $request->validate([
            'judul'          => 'required|string|max:255',
            'deskripsi'      => 'nullable|string',
            'poin_nilai'     => 'required|numeric|min:0|max:100',
            'deadline'       => 'nullable|date',
            'tanggal_tayang' => 'nullable|date',
            'is_aktif'       => 'nullable|boolean',
        ]);

        $tugas->update($data);
        ActivityLog::log('Edit Tugas', "Mengubah tugas: {$tugas->judul}.");

        return response()->json(['success' => true, 'message' => 'Tugas berhasil diperbarui.']);
    }

    public function tugasDestroy($id)
    {
        $tugas = Tugas::where('user_id', auth()->id())->findOrFail($id);
        $judul = $tugas->judul;
        $tugas->delete();

        ActivityLog::log('Hapus Tugas', "Menghapus tugas: {$judul}.");
        return response()->json(['success' => true, 'message' => 'Tugas berhasil dihapus.']);
    }

    public function tugasSubmissions($id)
    {
        $tugas = Tugas::with(['course', 'classRoom'])
            ->where('user_id', auth()->id())
            ->withCount('submissions')
            ->findOrFail($id);

        $submissions = TugasSubmission::with(['user', 'files'])
            ->where('tugas_id', $id)
            ->orderBy('submitted_at', 'desc')
            ->get();

        return view('dosen.tugas_submissions', compact('tugas', 'submissions'));
    }

    public function tugasNilai(Request $request, $id)
    {
        $submission = TugasSubmission::findOrFail($id);
        $data = $request->validate([
            'nilai'           => 'required|numeric|min:0|max:100',
            'feedback_dosen'  => 'nullable|string',
        ]);
        $submission->update($data);

        return response()->json(['success' => true, 'message' => 'Nilai berhasil disimpan.']);
    }

    // ==========================================
    // MAHASISWA — MATERI & TUGAS
    // ==========================================

    public function mahasiswaMateri()
    {
        $user   = auth()->user();
        $today  = now()->toDateString();
        $userId = $user->id;

        $materis = Materi::with(['course', 'classRoom', 'views' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->where('is_aktif', true)
            ->where(function ($q) use ($user) {
                $q->whereNull('class_id')->orWhere('class_id', $user->class_id);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_tayang')->orWhere('tanggal_tayang', '<=', $today);
            })
            ->orderBy('pertemuan_ke', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mahasiswa.materi', compact('materis'));
    }

    public function mahasiswaMateriDownload($id)
    {
        $user  = auth()->user();
        $materi = Materi::where('is_aktif', true)
            ->where(function ($q) use ($user) {
                $q->whereNull('class_id')->orWhere('class_id', $user->class_id);
            })
            ->findOrFail($id);

        if ($materi->file_path) {
            $this->recordView($materi->id);
            return Storage::disk('public')->download($materi->file_path);
        }

        return redirect()->back()->with('error', 'File tidak tersedia.');
    }

    public function mahasiswaMateriOpen($id)
    {
        $user = auth()->user();
        $materi = Materi::where('is_aktif', true)
            ->where(function ($q) use ($user) {
                $q->whereNull('class_id')->orWhere('class_id', $user->class_id);
            })
            ->findOrFail($id);

        if ($materi->tipe === 'link' && $materi->link_url) {
            $this->recordView($materi->id);
            return redirect()->away($materi->link_url);
        }

        return redirect()->back()->with('error', 'Link tidak valid.');
    }

    public function mahasiswaMateriView($id)
    {
        $user = auth()->user();
        $materi = Materi::where('is_aktif', true)
            ->where(function ($q) use ($user) {
                $q->whereNull('class_id')->orWhere('class_id', $user->class_id);
            })
            ->findOrFail($id);

        $this->recordView($materi->id);
        return response()->json(['success' => true]);
    }

    public function markNotificationsRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function mahasiswaTugas()
    {
        $user  = auth()->user();
        $today = now()->toDateString();
        $userId = $user->id;

        $tugas = Tugas::with(['course', 'classRoom'])
            ->where('is_aktif', true)
            ->where(function ($q) use ($user) {
                $q->whereNull('class_id')->orWhere('class_id', $user->class_id);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_tayang')->orWhere('tanggal_tayang', '<=', $today);
            })
            ->orderBy('deadline', 'asc')
            ->get()
            ->map(function ($t) use ($userId) {
                $t->my_submission = TugasSubmission::with('files')
                    ->where('tugas_id', $t->id)
                    ->where('user_id', $userId)
                    ->first();
                return $t;
            });

        return view('mahasiswa.tugas', compact('tugas'));
    }

    public function mahasiswaTugasSubmit(Request $request, $id)
    {
        $user  = auth()->user();
        $tugas = Tugas::findOrFail($id);

        $request->validate([
            'catatan'      => 'nullable|string',
            'file_tugas'   => 'nullable|array',
            'file_tugas.*' => 'file|max:10240',
        ]);

        $submission = TugasSubmission::where('tugas_id', $tugas->id)
            ->where('user_id', $user->id)
            ->first();

        $isRevision = false;
        if ($submission) {
            $isRevision = true;
            if ($submission->nilai !== null) {
                return response()->json(['success' => false, 'message' => 'Tugas sudah dinilai, tidak dapat direvisi.'], 403);
            }
        } else {
            $submission = new TugasSubmission([
                'tugas_id' => $tugas->id,
                'user_id'  => $user->id,
            ]);
        }

        $isLate = false;
        if ($tugas->deadline && now()->greaterThan($tugas->deadline)) {
            $isLate = true;
        }

        $submission->catatan      = $request->catatan;
        $submission->submitted_at = now();
        $submission->is_late      = $isLate;

        if ($isRevision) {
            $submission->is_revision = true;
            $submission->revised_at  = now();
        }

        $submission->save();

        if ($request->hasFile('file_tugas')) {
            if ($isRevision) {
                foreach ($submission->files as $oldFile) {
                    Storage::disk('public')->delete($oldFile->file_path);
                    $oldFile->delete();
                }
            }

            $files = $request->file('file_tugas');
            $firstPath = null;

            foreach ($files as $file) {
                $path = $file->store('tugas_submissions', 'public');
                if (!$firstPath) {
                    $firstPath = $path;
                }

                TugasSubmissionFile::create([
                    'tugas_submission_id' => $submission->id,
                    'file_path'           => $path,
                    'original_name'       => $file->getClientOriginalName(),
                ]);
            }

            $submission->update(['file_path' => $firstPath]);
        }

        return response()->json(['success' => true, 'message' => 'Tugas berhasil dikumpulkan.']);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    private function recordView($materiId)
    {
        MateriView::updateOrCreate(
            ['materi_id' => $materiId, 'user_id' => auth()->id()],
            ['viewed_at' => now()]
        );
    }

    protected function sendMateriNotification(Materi $materi)
    {
        $query = User::where('role', 'mahasiswa');
        if ($materi->class_id) {
            $query->where('class_id', $materi->class_id);
        }
        $students = $query->get();

        foreach ($students as $student) {
            Notification::create([
                'user_id' => $student->id,
                'title'   => 'Materi Baru Tersedia',
                'body'    => 'Materi baru "' . $materi->judul . '" untuk mata kuliah ' . $materi->course->name . ' telah dipublikasikan.',
                'is_read' => false,
            ]);
        }
    }
}
