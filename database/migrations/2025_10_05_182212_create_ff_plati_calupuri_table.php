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
        Schema::create('ff_plati_calupuri', function (Blueprint $table) {
            $table->id();
            $table->string('denumire_calup', 150);
            $table->date('data_plata')->nullable();
            $table->text('observatii')->nullable();
            $table->timestamps();
            $table->index('data_plata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ff_plati_calupuri');
    }
};
