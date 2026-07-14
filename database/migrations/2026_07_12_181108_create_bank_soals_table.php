<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_soals', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode')->nullable();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('dosen_id')->constrained('users')->onDelete('cascade');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('bank_soal_id')->nullable()->constrained('bank_soals')->onDelete('cascade');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('bank_soal_id')->nullable()->constrained('bank_soals')->onDelete('set null');
        });

        // Backfill logic
        $fallbackDosen = DB::table('users')->where('role', 'dosen')->first() ?? DB::table('users')->first();
        $fallbackDosenId = $fallbackDosen ? $fallbackDosen->id : null;

        if ($fallbackDosenId) {
            $coursesInQuestions = DB::table('questions')->select('course_id')->distinct()->get();

            foreach ($coursesInQuestions as $row) {
                $courseId = $row->course_id;
                $course = DB::table('courses')->where('id', $courseId)->first();
                if (!$course) continue;

                $dosens = DB::table('exams')
                    ->where('course_id', $courseId)
                    ->select('dosen_id')
                    ->distinct()
                    ->pluck('dosen_id')
                    ->toArray();

                if (empty($dosens)) {
                    $dosens = [$fallbackDosenId];
                }

                foreach ($dosens as $dosenId) {
                    $bankSoalId = DB::table('bank_soals')->insertGetId([
                        'nama' => "Bank Soal Lama - " . $course->name,
                        'kode' => $course->code,
                        'course_id' => $courseId,
                        'dosen_id' => $dosenId,
                        'deskripsi' => 'Dibuat otomatis oleh migrasi untuk menampung soal-soal lama.',
                        'is_aktif' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('questions')
                        ->where('course_id', $courseId)
                        ->whereNull('bank_soal_id')
                        ->update(['bank_soal_id' => $bankSoalId]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['bank_soal_id']);
            $table->dropColumn('bank_soal_id');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['bank_soal_id']);
            $table->dropColumn('bank_soal_id');
        });

        Schema::dropIfExists('bank_soals');
    }
};
