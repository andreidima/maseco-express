<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masini_documente_fisiere', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('masini_documente')->cascadeOnDelete();
            $table->string('cale');
            $table->string('nume_fisier');
            $table->string('nume_original');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('dimensiune')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masini_documente_fisiere');
    }
};
