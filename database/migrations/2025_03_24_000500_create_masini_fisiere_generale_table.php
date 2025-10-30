<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masini_fisiere_generale', function (Blueprint $table) {
            $table->id();
            $table->foreignId('masina_id')
                ->constrained('masini')
                ->cascadeOnDelete();
            $table->string('cale');
            $table->string('nume_original');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('dimensiune')->nullable();
            $table->unsignedBigInteger('uploaded_by_id')->nullable();
            $table->string('uploaded_by_name')->nullable();
            $table->string('uploaded_by_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masini_fisiere_generale');
    }
};
