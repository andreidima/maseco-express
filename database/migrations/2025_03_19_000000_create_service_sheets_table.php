<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('masina_id')->constrained('service_masini')->cascadeOnDelete();
            $table->unsignedInteger('km_bord');
            $table->date('data_service');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_sheets');
    }
};
