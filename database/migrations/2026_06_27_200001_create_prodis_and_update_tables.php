<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prodis', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique(); // e.g. "S1-KEP"
            $table->string('nama'); // e.g. "S1 Ilmu Keperawatan"
            $table->enum('jenjang', ['D3', 'D4', 'S1', 'S2', 'Profesi'])->default('S1');
            $table->string('akreditasi')->nullable(); // A, B, C, Baik, dll.
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        // Add prodi_id to courses
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('prodi_id')->nullable()->after('id')->constrained('prodis')->onDelete('set null');
            $table->integer('sks')->default(2)->after('description');
            $table->boolean('is_praktikum')->default(false)->after('sks');
        });

        // Add prodi_id and other fields to classes
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('prodi_id')->nullable()->after('id')->constrained('prodis')->onDelete('set null');
            $table->string('angkatan')->nullable()->after('name'); // e.g. "2023", "2024"
            $table->foreignId('wali_kelas_id')->nullable()->after('angkatan')->constrained('users')->onDelete('set null');
        });

        // Extend users table for mahasiswa & dosen profiles
        Schema::table('users', function (Blueprint $table) {
            $table->string('nim')->nullable()->after('email');   // untuk mahasiswa
            $table->string('nidn')->nullable()->after('nim');   // untuk dosen
            $table->foreignId('prodi_id')->nullable()->after('nidn')->constrained('prodis')->onDelete('set null');
            $table->string('photo')->nullable()->after('prodi_id'); // path foto profil
            $table->string('angkatan')->nullable()->after('photo'); // tahun angkatan mahasiswa
            $table->enum('status', ['aktif', 'cuti', 'do', 'lulus'])->default('aktif')->after('angkatan');
            $table->string('no_hp')->nullable()->after('status');
            $table->date('tanggal_lahir')->nullable()->after('no_hp');
            $table->string('alamat')->nullable()->after('tanggal_lahir');
            $table->string('jabatan')->nullable()->after('alamat'); // jabatan dosen
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nim', 'nidn', 'prodi_id', 'photo', 'angkatan', 'status', 'no_hp', 'tanggal_lahir', 'alamat', 'jabatan']);
        });
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['prodi_id', 'wali_kelas_id']);
            $table->dropColumn(['prodi_id', 'angkatan', 'wali_kelas_id']);
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['prodi_id']);
            $table->dropColumn(['prodi_id', 'sks', 'is_praktikum']);
        });
        Schema::dropIfExists('prodis');
    }
};
