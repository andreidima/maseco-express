<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comenzi_fisiere', function (Blueprint $table) {
            $table->unsignedTinyInteger('este_factura')
                ->default(0)
                ->after('validat');
        });

        Schema::table('comenzi_fisiere_istoric', function (Blueprint $table) {
            $table->unsignedTinyInteger('este_factura')
                ->default(0)
                ->after('validat');
        });
    }

    public function down(): void
    {
        Schema::table('comenzi_fisiere_istoric', function (Blueprint $table) {
            $table->dropColumn('este_factura');
        });

        Schema::table('comenzi_fisiere', function (Blueprint $table) {
            $table->dropColumn('este_factura');
        });
    }
};
