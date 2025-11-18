<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('valabilitati_divizii', function (Blueprint $table): void {
            $table->id();
            $table->string('nume')->unique();
            $table->timestamps();
        });

        Schema::table('valabilitati', function (Blueprint $table): void {
            $table->foreignId('divizie_id')
                ->nullable()
                ->after('sofer_id')
                ->constrained('valabilitati_divizii');
        });

        DB::transaction(function (): void {
            $now = Carbon::now();
            $fallbackName = 'Nespecificat';
            $divizieCache = [];

            $valabilitati = DB::table('valabilitati')
                ->select(['id', 'denumire'])
                ->get();

            foreach ($valabilitati as $valabilitate) {
                $name = trim((string) ($valabilitate->denumire ?? ''));
                if ($name === '') {
                    $name = $fallbackName;
                }

                if (! array_key_exists($name, $divizieCache)) {
                    $existingId = DB::table('valabilitati_divizii')
                        ->where('nume', $name)
                        ->value('id');

                    if ($existingId) {
                        $divizieCache[$name] = $existingId;
                    } else {
                        $divizieCache[$name] = DB::table('valabilitati_divizii')->insertGetId([
                            'nume' => $name,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }

                DB::table('valabilitati')
                    ->where('id', $valabilitate->id)
                    ->update(['divizie_id' => $divizieCache[$name]]);
            }

            if (! array_key_exists($fallbackName, $divizieCache)) {
                $divizieCache[$fallbackName] = DB::table('valabilitati_divizii')->insertGetId([
                    'nume' => $fallbackName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('valabilitati')
                ->whereNull('divizie_id')
                ->update(['divizie_id' => $divizieCache[$fallbackName]]);
        });

        Schema::table('valabilitati', function (Blueprint $table): void {
            $table->dropColumn('denumire');
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('valabilitati', function (Blueprint $table): void {
                $table->dropForeign('valabilitati_divizie_id_foreign');
            });

            DB::statement('ALTER TABLE valabilitati MODIFY divizie_id BIGINT UNSIGNED NOT NULL');

            Schema::table('valabilitati', function (Blueprint $table): void {
                $table->foreign('divizie_id')->references('id')->on('valabilitati_divizii');
            });
        }
    }

    public function down(): void
    {
        Schema::table('valabilitati', function (Blueprint $table): void {
            $table->string('denumire')->nullable()->after('sofer_id');
        });

        DB::transaction(function (): void {
            $valabilitati = DB::table('valabilitati')
                ->leftJoin('valabilitati_divizii', 'valabilitati_divizii.id', '=', 'valabilitati.divizie_id')
                ->select(['valabilitati.id', 'valabilitati_divizii.nume as divizie_nume'])
                ->get();

            foreach ($valabilitati as $valabilitate) {
                $name = trim((string) ($valabilitate->divizie_nume ?? ''));

                DB::table('valabilitati')
                    ->where('id', $valabilitate->id)
                    ->update(['denumire' => $name !== '' ? $name : 'Nespecificat']);
            }
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE valabilitati MODIFY denumire VARCHAR(255) NOT NULL');
        }

        Schema::table('valabilitati', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('divizie_id');
        });

        Schema::dropIfExists('valabilitati_divizii');
    }
};
