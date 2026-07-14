<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add fields to prodis
        Schema::table('prodis', function (Blueprint $table) {
            $table->string('feeder_id', 36)->nullable()->unique()->after('id');
            $table->timestamp('feeder_synced_at')->nullable()->after('is_aktif');
        });

        // 2. Add fields to courses
        Schema::table('courses', function (Blueprint $table) {
            $table->string('feeder_id', 36)->nullable()->unique()->after('id');
            $table->timestamp('feeder_synced_at')->nullable()->after('is_praktikum');
        });

        // 3. Add fields to classes
        Schema::table('classes', function (Blueprint $table) {
            $table->string('feeder_id', 36)->nullable()->unique()->after('id');
            $table->string('feeder_semester_id', 20)->nullable()->after('angkatan');
            $table->timestamp('feeder_synced_at')->nullable()->after('wali_kelas_id');
        });

        // 4. Add fields to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('feeder_id', 36)->nullable()->unique()->after('id');
            $table->string('feeder_status', 10)->nullable()->after('status');
            $table->boolean('feeder_inactive')->default(false)->after('feeder_status');
            $table->timestamp('feeder_synced_at')->nullable()->after('feeder_inactive');
        });

        // 5. Add fields to tahun_akademik
        Schema::table('tahun_akademik', function (Blueprint $table) {
            $table->string('feeder_semester_id', 20)->nullable()->unique()->after('id');
        });

        // 6. Create sync_logs table
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sync_type', 50); // full, mahasiswa, dosen, kelas, prodi, semester
            $table->string('triggered_by', 50); // scheduler, manual_admin, artisan
            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->enum('status', ['running', 'success', 'failed'])->default('running');
            $table->integer('total_fetched')->default(0);
            $table->integer('total_inserted')->default(0);
            $table->integer('total_updated')->default(0);
            $table->integer('total_deactivated')->default(0);
            $table->integer('total_errors')->default(0);
            $table->text('error_log')->nullable();
            $table->text('notes')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');

        Schema::table('tahun_akademik', function (Blueprint $table) {
            $table->dropColumn('feeder_semester_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['feeder_id', 'feeder_status', 'feeder_inactive', 'feeder_synced_at']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['feeder_id', 'feeder_semester_id', 'feeder_synced_at']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['feeder_id', 'feeder_synced_at']);
        });

        Schema::table('prodis', function (Blueprint $table) {
            $table->dropColumn(['feeder_id', 'feeder_synced_at']);
        });
    }
};
