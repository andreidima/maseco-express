<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturi_transportatori_plati_calupuri', function (Blueprint $table) {
            $table->id();
            $table->string('denumire_calup', 150);
            $table->date('data_plata')->nullable();
            $table->text('observatii')->nullable();
            $table->timestamps();
        });

        Schema::create('facturi_transportatori_plati_calupuri_comenzi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calup_id')->constrained('facturi_transportatori_plati_calupuri')->cascadeOnDelete();
            $table->unsignedInteger('comanda_id');
            $table->foreign('comanda_id')->references('id')->on('comenzi')->cascadeOnDelete();
            $table->timestamps();
            $table->unique('comanda_id', 'ft_calupuri_comenzi_unique');
        });

        Schema::create('facturi_transportatori_plati_calupuri_fisiere', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plata_calup_id');
            $table->foreign('plata_calup_id', 'ft_calupuri_fisiere_calup_fk')
                ->references('id')
                ->on('facturi_transportatori_plati_calupuri')
                ->cascadeOnDelete();
            $table->string('cale');
            $table->string('nume_original')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturi_transportatori_plati_calupuri_fisiere');
        Schema::dropIfExists('facturi_transportatori_plati_calupuri_comenzi');
        Schema::dropIfExists('facturi_transportatori_plati_calupuri');
    }
};
