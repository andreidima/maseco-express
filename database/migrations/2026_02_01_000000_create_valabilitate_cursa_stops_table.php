<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('valabilitate_cursa_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('valabilitate_cursa_id')->constrained('valabilitati_curse')->cascadeOnDelete();
            $table->enum('type', ['incarcare', 'descarcare']);
            $table->string('cod_postal')->nullable();
            $table->string('localitate');
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valabilitate_cursa_stops');
    }
};
