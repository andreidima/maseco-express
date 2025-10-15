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
        Schema::create('service_gestiune_piese', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')
                ->constrained('service_ff_facturi')
                ->cascadeOnDelete();
            $table->string('denumire');
            $table->string('cod')->nullable();
            $table->decimal('nr_bucati', 12, 2)->nullable();
            $table->decimal('pret', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_gestiune_piese');
    }
};
