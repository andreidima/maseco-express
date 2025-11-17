<?php

use App\Models\ValabilitateCursaGrup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valabilitati_cursa_grupuri', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('valabilitate_id')
                ->constrained('valabilitati')
                ->cascadeOnDelete();
            $table->string('nume');
            $table->string('format_documente');
            $table->decimal('suma_incasata', 12, 2)->nullable();
            $table->decimal('suma_calculata', 12, 2)->nullable();
            $table->date('data_factura')->nullable();
            $table->string('numar_factura')->nullable();
            $table->string('culoare_hex', 20)->default(ValabilitateCursaGrup::DEFAULT_COLOR);
            $table->timestamps();
        });

        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->foreignId('cursa_grup_id')
                ->nullable()
                ->after('valabilitate_id')
                ->constrained('valabilitati_cursa_grupuri')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('cursa_grup_id');
        });

        Schema::dropIfExists('valabilitati_cursa_grupuri');
    }
};
