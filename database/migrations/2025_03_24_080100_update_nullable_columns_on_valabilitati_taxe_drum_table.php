<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('valabilitati_taxe_drum', function (Blueprint $table): void {
            $table->string('tara')->nullable()->change();
            $table->decimal('suma', 10, 2)->nullable()->change();
            $table->string('moneda', 10)->nullable()->change();
            $table->date('data')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati_taxe_drum', function (Blueprint $table): void {
            $table->string('tara')->nullable(false)->change();
            $table->decimal('suma', 10, 2)->nullable(false)->change();
            $table->string('moneda', 10)->nullable(false)->change();
            $table->date('data')->nullable(false)->change();
        });
    }
};
