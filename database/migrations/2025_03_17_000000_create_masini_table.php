<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masini', function (Blueprint $table) {
            $table->id();
            $table->string('denumire');
            $table->string('numar_inmatriculare')->unique();
            $table->string('serie_sasiu')->nullable();
            $table->text('observatii')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masini');
    }
};
