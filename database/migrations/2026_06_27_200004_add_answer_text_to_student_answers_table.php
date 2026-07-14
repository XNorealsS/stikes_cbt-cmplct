<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_answers', function (Blueprint $table) {
            $table->text('answer_text')->nullable()->after('selected_option'); // holds essay or fill-in-the-blank text answers
            $table->decimal('nilai_dosen', 5, 2)->nullable()->after('is_correct'); // manual grading for essay
            $table->text('feedback_dosen')->nullable()->after('nilai_dosen');
        });
    }

    public function down(): void
    {
        Schema::table('student_answers', function (Blueprint $table) {
            $table->dropColumn(['answer_text', 'nilai_dosen', 'feedback_dosen']);
        });
    }
};
