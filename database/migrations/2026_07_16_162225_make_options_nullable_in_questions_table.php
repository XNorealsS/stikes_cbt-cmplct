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
        Schema::table('questions', function (Blueprint $table) {
            $table->text('option_a')->nullable()->change();
            $table->text('option_b')->nullable()->change();
            $table->text('option_c')->nullable()->change();
            $table->text('option_d')->nullable()->change();
            $table->text('option_e')->nullable()->change();
            $table->text('correct_option')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->text('option_a')->nullable(false)->change();
            $table->text('option_b')->nullable(false)->change();
            $table->text('option_c')->nullable(false)->change();
            $table->text('option_d')->nullable(false)->change();
            $table->text('option_e')->nullable(false)->change();
            $table->char('correct_option', 1)->nullable(false)->change();
        });
    }
};
