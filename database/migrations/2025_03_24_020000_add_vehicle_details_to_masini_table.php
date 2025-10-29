<?php

use App\Models\Masini\Masina;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('masini', function (Blueprint $table) {
            $table->string('marca_masina')->nullable()->after('descriere');
            $table->string('serie_sasiu')->nullable()->after('marca_masina');
        });

        Masina::query()->each(function (Masina $masina): void {
            $masina->syncDefaultDocuments();
        });
    }

    public function down(): void
    {
        Schema::table('masini', function (Blueprint $table) {
            $table->dropColumn(['marca_masina', 'serie_sasiu']);
        });
    }
};
