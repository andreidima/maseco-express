<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('valabilitati', function (Blueprint $table): void {
            $table->unsignedInteger('km_plecare')->nullable()->after('data_sfarsit');
            $table->unsignedInteger('km_sosire')->nullable()->after('km_plecare');
        });
    }

    public function down(): void
    {
        Schema::table('valabilitati', function (Blueprint $table): void {
            $table->dropColumn(['km_plecare', 'km_sosire']);
        });
    }
};
