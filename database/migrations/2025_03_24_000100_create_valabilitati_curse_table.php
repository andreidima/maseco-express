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
        Schema::create('valabilitati_curse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('valabilitate_id')->constrained('valabilitati')->cascadeOnDelete();
            $table->string('localitate_plecare');
            $table->string('localitate_sosire')->nullable();
            $table->timestampTz('plecare_la')->nullable();
            $table->timestampTz('sosire_la')->nullable();
            $table->unsignedInteger('km_bord')->nullable();
            $table->text('observatii')->nullable();
            $table->timestamps();

            $table->index('localitate_plecare');
            $table->index('localitate_sosire');
            $table->index('plecare_la');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valabilitati_curse');
    }
};
