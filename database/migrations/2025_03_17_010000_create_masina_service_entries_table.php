<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masina_service_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('masina_id')->constrained('masini')->cascadeOnDelete();
            $table->foreignId('gestiune_piesa_id')->nullable()->constrained('gestiune_piese')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tip');
            $table->string('denumire_interventie')->nullable();
            $table->string('cod_piesa')->nullable();
            $table->string('denumire_piesa')->nullable();
            $table->decimal('cantitate', 10, 2)->nullable();
            $table->date('data_montaj')->nullable();
            $table->string('nume_mecanic')->nullable();
            $table->string('nume_utilizator')->nullable();
            $table->text('observatii')->nullable();
            $table->timestamps();

            $table->index(['tip', 'data_montaj']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masina_service_entries');
    }
};
