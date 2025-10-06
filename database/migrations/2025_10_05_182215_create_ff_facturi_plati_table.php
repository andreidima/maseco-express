<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ff_facturi_plati', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('ff_facturi')->cascadeOnDelete();
            $table->foreignId('calup_id')->constrained('ff_plati_calupuri')->cascadeOnDelete();
            $table->timestamps();

            $table->unique('factura_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ff_facturi_plati');
    }
};
