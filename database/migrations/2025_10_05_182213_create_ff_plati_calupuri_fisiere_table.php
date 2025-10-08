<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ff_plati_calupuri_fisiere', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plata_calup_id')->constrained('ff_plati_calupuri')->cascadeOnDelete();
            $table->string('cale');
            $table->string('nume_original')->nullable();
            $table->timestamps();
        });

        if (Schema::hasColumn('ff_plati_calupuri', 'fisier_pdf')) {
            $records = DB::table('ff_plati_calupuri')
                ->whereNotNull('fisier_pdf')
                ->select(['id', 'fisier_pdf'])
                ->get();

            foreach ($records as $record) {
                DB::table('ff_plati_calupuri_fisiere')->insert([
                    'plata_calup_id' => $record->id,
                    'cale' => $record->fisier_pdf,
                    'nume_original' => basename($record->fisier_pdf),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('ff_plati_calupuri', function (Blueprint $table) {
                $table->dropColumn('fisier_pdf');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('ff_plati_calupuri', 'fisier_pdf')) {
            Schema::table('ff_plati_calupuri', function (Blueprint $table) {
                $table->string('fisier_pdf')->nullable();
            });
        }

        $attachments = DB::table('ff_plati_calupuri_fisiere')
            ->orderBy('id')
            ->select(['plata_calup_id', 'cale'])
            ->get();

        foreach ($attachments as $attachment) {
            $exists = DB::table('ff_plati_calupuri')
                ->where('id', $attachment->plata_calup_id)
                ->whereNull('fisier_pdf')
                ->exists();

            if ($exists) {
                DB::table('ff_plati_calupuri')
                    ->where('id', $attachment->plata_calup_id)
                    ->update(['fisier_pdf' => $attachment->cale]);
            }
        }

        Schema::dropIfExists('ff_plati_calupuri_fisiere');
    }
};
