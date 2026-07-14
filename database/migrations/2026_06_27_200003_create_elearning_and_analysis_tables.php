<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // E-Learning: Materi Pembelajaran
        Schema::create('materis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // dosen
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->foreignId('tahun_akademik_id')->nullable()->constrained('tahun_akademik')->onDelete('set null');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['file', 'link', 'text'])->default('file');
            $table->string('file_path')->nullable();
            $table->string('link_url')->nullable();
            $table->text('konten')->nullable(); // untuk tipe text
            $table->date('tanggal_tayang')->nullable(); // kapan materi tampil ke mahasiswa
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        // E-Learning: Tugas
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // dosen
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->foreignId('tahun_akademik_id')->nullable()->constrained('tahun_akademik')->onDelete('set null');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->decimal('poin_nilai', 5, 2)->default(100);
            $table->dateTime('deadline')->nullable();
            $table->date('tanggal_tayang')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        // Pengumpulan Tugas Mahasiswa
        Schema::create('tugas_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // mahasiswa
            $table->string('file_path')->nullable();
            $table->text('catatan')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('feedback_dosen')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->timestamps();
        });

        // Analisis Butir Soal
        Schema::create('cbt_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->decimal('tingkat_kesukaran', 5, 4)->nullable(); // 0 - 1
            $table->decimal('daya_beda', 5, 4)->nullable(); // -1 to 1
            $table->decimal('reliabilitas', 5, 4)->nullable(); // Cronbach Alpha
            $table->json('distribusi_jawaban')->nullable(); // {"A": 10, "B": 5, ...}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_analyses');
        Schema::dropIfExists('tugas_submissions');
        Schema::dropIfExists('tugas');
        Schema::dropIfExists('materis');
    }
};
