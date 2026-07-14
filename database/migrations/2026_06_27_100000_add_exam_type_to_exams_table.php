<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->string('exam_type')->default('UTS')->after('title');
            $table->decimal('passing_grade', 5, 2)->default(70.00)->after('is_random');
            $table->text('description')->nullable()->after('passing_grade');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['exam_type', 'passing_grade', 'description']);
        });
    }
};
