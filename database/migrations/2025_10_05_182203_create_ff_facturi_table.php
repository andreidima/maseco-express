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
        Schema::create('ff_facturi', function (Blueprint $table) {
            $table->id();
            $table->string('denumire_furnizor', 150);
            $table->string('numar_factura', 100);
            $table->date('data_factura');
            $table->date('data_scadenta');
            $table->decimal('suma', 12, 2);
            $table->string('moneda', 3);
            $table->string('departament_vehicul', 150)->nullable();
            $table->text('observatii')->nullable();
            $table->timestamps();

            $table->index('denumire_furnizor');
            $table->index('departament_vehicul');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ff_facturi');
    }
};
