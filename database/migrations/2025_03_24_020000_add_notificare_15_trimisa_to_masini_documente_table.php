<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('masini_documente', function (Blueprint $table) {
            $table->boolean('notificare_15_trimisa')->default(false)->after('notificare_30_trimisa');
        });
    }

    public function down(): void
    {
        Schema::table('masini_documente', function (Blueprint $table) {
            $table->dropColumn('notificare_15_trimisa');
        });
    }
};
