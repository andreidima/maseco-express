<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masini_mementouri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('masina_id')->constrained('masini')->cascadeOnDelete();
            $table->string('email_notificari')->nullable();
            $table->string('telefon_notificari')->nullable();
            $table->text('observatii')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masini_mementouri');
    }
};
