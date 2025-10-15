<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_ff_facturi_fisiere', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')
                ->constrained('service_ff_facturi')
                ->cascadeOnDelete();
            $table->string('cale');
            $table->string('nume_original')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_ff_facturi_fisiere');
    }
};
