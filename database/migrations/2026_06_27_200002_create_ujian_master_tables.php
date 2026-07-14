<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ruang Ujian (Lab Komputer)
        Schema::create('ruangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // e.g. "Lab Komputer A"
            $table->integer('kapasitas')->default(30);
            $table->string('lokasi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        // Sesi Waktu Ujian
        Schema::create('sesis', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // e.g. "Sesi 1"
            $table->time('jam_mulai'); // e.g. "08:00:00"
            $table->time('jam_selesai'); // e.g. "10:00:00"
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        // Jenis Ujian
        Schema::create('jenis_ujians', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique(); // UTS, UAS, KUIS, dll.
            $table->string('nama'); // Ujian Tengah Semester
            $table->decimal('bobot_nilai', 5, 2)->default(100); // persentase bobot dalam nilai akhir
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        // Update exams table: add ruang, sesi, jenis_ujian, petunjuk, etc.
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('tahun_akademik_id')->nullable()->after('class_id')->constrained('tahun_akademik')->onDelete('set null');
            $table->foreignId('jenis_ujian_id')->nullable()->after('tahun_akademik_id')->constrained('jenis_ujians')->onDelete('set null');
            $table->foreignId('ruang_id')->nullable()->after('jenis_ujian_id')->constrained('ruangs')->onDelete('set null');
            $table->foreignId('sesi_id')->nullable()->after('ruang_id')->constrained('sesis')->onDelete('set null');
            $table->text('petunjuk')->nullable()->after('description'); // petunjuk pengerjaan ujian
        });

        // Update questions: add question_type support
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('question_type', ['pg', 'pg_kompleks', 'essai', 'isian', 'menjodohkan', 'benar_salah'])->default('pg')->after('course_id');
            $table->decimal('bobot', 5, 2)->default(1)->after('correct_option'); // bobot nilai soal
            $table->text('explanation')->nullable()->after('bobot'); // penjelasan jawaban
        });

        // Soal Menjodohkan — kiri-kanan pairs
        Schema::create('question_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->integer('urutan');
            $table->text('item_kiri');
            $table->string('item_kiri_image')->nullable();
            $table->text('item_kanan');
            $table->string('item_kanan_image')->nullable();
            $table->timestamps();
        });

        // Pengumuman
        Schema::create('pengumumans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // penulis
            $table->string('judul');
            $table->text('isi');
            $table->foreignId('prodi_id')->nullable()->constrained('prodis')->onDelete('set null'); // null = semua prodi
            $table->enum('target', ['semua', 'mahasiswa', 'dosen'])->default('semua');
            $table->date('tanggal_aktif')->nullable();
            $table->date('tanggal_expired')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumumans');
        Schema::dropIfExists('question_matches');
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['question_type', 'bobot', 'explanation']);
        });
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['tahun_akademik_id', 'jenis_ujian_id', 'ruang_id', 'sesi_id']);
            $table->dropColumn(['tahun_akademik_id', 'jenis_ujian_id', 'ruang_id', 'sesi_id', 'petunjuk']);
        });
        Schema::dropIfExists('jenis_ujians');
        Schema::dropIfExists('sesis');
        Schema::dropIfExists('ruangs');
    }
};
