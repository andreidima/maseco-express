<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('valabilitati_alimentari', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('valabilitate_id')->constrained('valabilitati')->cascadeOnDelete();
            $table->dateTime('data_ora_alimentare');
            $table->decimal('litrii', 10, 2);
            $table->decimal('pret_pe_litru', 12, 4);
            $table->decimal('total_pret', 12, 4);
            $table->text('observatii')->nullable();
            $table->timestamps();

            $table->index('data_ora_alimentare');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valabilitati_alimentari');
    }
};
