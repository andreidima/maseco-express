<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_gestiune_piese', function (Blueprint $table) {
            if (! Schema::hasColumn('service_gestiune_piese', 'cantitate_initiala')) {
                $table->decimal('cantitate_initiala', 10, 2)->nullable()->after('nr_bucati');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_gestiune_piese', function (Blueprint $table) {
            if (Schema::hasColumn('service_gestiune_piese', 'cantitate_initiala')) {
                $table->dropColumn('cantitate_initiala');
            }
        });
    }
};
