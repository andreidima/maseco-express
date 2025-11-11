<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('valabilitati', function (Blueprint $table) {
            $table->id();
            $table->foreignId('masina_id')->nullable()->constrained('masini')->nullOnDelete();
            $table->string('referinta')->nullable();
            $table->timestampTz('prima_cursa')->nullable();
            $table->timestampTz('ultima_cursa')->nullable();
            $table->unsignedInteger('total_curse')->default(0);
            $table->timestamps();

            $table->index(['masina_id', 'prima_cursa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valabilitati');
    }
};
