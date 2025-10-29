<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masini_documente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('masina_id')->constrained('masini')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('tara')->nullable();
            $table->date('data_expirare')->nullable();
            $table->string('email_notificare')->nullable();
            $table->boolean('notificare_60_trimisa')->default(false);
            $table->boolean('notificare_30_trimisa')->default(false);
            $table->boolean('notificare_1_trimisa')->default(false);
            $table->timestamps();

            $table->unique(['masina_id', 'document_type', 'tara']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masini_documente');
    }
};
