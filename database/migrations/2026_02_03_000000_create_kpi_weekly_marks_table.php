<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kpi_weekly_marks', function (Blueprint $table) {
            $table->id();
            $table->date('week_start_date')->index();
            $table->unsignedBigInteger('rated_user_id')->index();
            $table->unsignedBigInteger('rated_by_user_id')->index();
            $table->unsignedTinyInteger('mark')->nullable();
            $table->timestamps();

            $table->unique(['week_start_date', 'rated_user_id', 'rated_by_user_id'], 'kpi_weekly_marks_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_weekly_marks');
    }
};

