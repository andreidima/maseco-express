<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->decimal('alte_taxe', 10, 2)->nullable()->after('km_flash_plin');
            $table->decimal('fuel_tax', 10, 2)->nullable()->after('alte_taxe');
            $table->decimal('suma_incasata', 10, 2)->nullable()->after('fuel_tax');
            $table->decimal('daily_contribution_incasata', 10, 2)->nullable()->after('suma_incasata');
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati_curse', function (Blueprint $table): void {
            $table->dropColumn([
                'alte_taxe',
                'fuel_tax',
                'suma_incasata',
                'daily_contribution_incasata',
            ]);
        });
    }
};
