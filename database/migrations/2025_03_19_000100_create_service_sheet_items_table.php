<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_sheet_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_sheet_id')->constrained('service_sheets')->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->string('description');
            $table->string('quantity')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_sheet_items');
    }
};
