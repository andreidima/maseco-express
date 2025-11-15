<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valabilitati_taxe_drum', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('valabilitate_id')
                ->constrained('valabilitati')
                ->cascadeOnDelete();
            $table->string('nume')->nullable();
            $table->string('tara');
            $table->decimal('suma', 10, 2);
            $table->string('moneda', 10);
            $table->date('data');
            $table->text('observatii')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valabilitati_taxe_drum');
    }
};
