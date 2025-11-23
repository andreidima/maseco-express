<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valabilitate_cursa_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('valabilitate_cursa_id')
                ->constrained('valabilitati_curse')
                ->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('original_name');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['valabilitate_cursa_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valabilitate_cursa_images');
    }
};
