<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add pertemuan_ke to materis
        Schema::table('materis', function (Blueprint $table) {
            $table->integer('pertemuan_ke')->nullable()->after('tahun_akademik_id');
        });

        // 2. Create materi_views table
        Schema::create('materi_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materi_id')->constrained('materis')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('viewed_at');
            $table->timestamps();
        });

        // 3. Create notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // 4. Update tugas_submissions: add is_revision, revised_at, and is_late
        Schema::table('tugas_submissions', function (Blueprint $table) {
            $table->boolean('is_revision')->default(false)->after('feedback_dosen');
            $table->dateTime('revised_at')->nullable()->after('is_revision');
            $table->boolean('is_late')->default(false)->after('revised_at');
        });

        // 5. Create tugas_submission_files table
        Schema::create('tugas_submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_submission_id')->constrained('tugas_submissions')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_submission_files');

        Schema::table('tugas_submissions', function (Blueprint $table) {
            $table->dropColumn(['is_revision', 'revised_at', 'is_late']);
        });

        Schema::dropIfExists('notifications');
        Schema::dropIfExists('materi_views');

        Schema::table('materis', function (Blueprint $table) {
            $table->dropColumn('pertemuan_ke');
        });
    }
};
