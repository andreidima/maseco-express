<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('valabilitati_curse', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('valabilitate_id')->constrained('valabilitati')->cascadeOnDelete();
            $table->string('incarcare_localitate')->nullable();
            $table->string('incarcare_cod_postal')->nullable();
            $table->string('descarcare_localitate')->nullable();
            $table->string('descarcare_cod_postal')->nullable();
            $table->dateTime('data_cursa')->nullable();
            $table->text('observatii')->nullable();
            $table->timestamps();

            $table->index('data_cursa');
            $table->index('incarcare_localitate');
            $table->index('descarcare_localitate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valabilitati_curse');
    }
};
