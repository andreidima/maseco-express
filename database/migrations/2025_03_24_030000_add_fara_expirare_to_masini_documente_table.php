<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('masini_documente', function (Blueprint $table) {
            $table->boolean('fara_expirare')->default(false)->nullable()->after('data_expirare');
        });
    }

    public function down(): void
    {
        Schema::table('masini_documente', function (Blueprint $table) {
            $table->dropColumn('fara_expirare');
        });
    }
};
