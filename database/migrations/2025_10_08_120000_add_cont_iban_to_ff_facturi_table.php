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
        Schema::table('ff_facturi', function (Blueprint $table) {
            $table->string('cont_iban', 255)->nullable()->after('moneda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ff_facturi', function (Blueprint $table) {
            $table->dropColumn('cont_iban');
        });
    }
};
