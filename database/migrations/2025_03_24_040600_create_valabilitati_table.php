<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('valabilitati', function (Blueprint $table): void {
            $table->id();
            $table->string('numar_auto');
            $table->foreignId('sofer_id')->constrained('users')->cascadeOnDelete();
            $table->string('denumire');
            $table->date('data_inceput');
            $table->date('data_sfarsit')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valabilitati');
    }
};
