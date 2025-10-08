<?php

namespace Database\Seeders;

use App\Models\FacturiFurnizori\FacturaFurnizor;
use App\Models\FacturiFurnizori\PlataCalup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FacturiFurnizoriDemoSeeder extends Seeder
{
    /**
     * Seed the facturi furnizori tables with demo data.
     *
     * Run with: php artisan db:seed --class=FacturiFurnizoriDemoSeeder
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('ff_facturi_plati')->truncate();
        DB::table('ff_facturi')->truncate();
        DB::table('ff_plati_calupuri')->truncate();
        Schema::enableForeignKeyConstraints();

        // Create 10 invoices without calups ("Neplătite").
        FacturaFurnizor::factory()->count(10)->create();

        // Create two calups to host the remaining invoices.
        $calupuri = PlataCalup::factory()
            ->count(2)
            ->sequence(
                ['denumire_calup' => 'Calup Demo 1', 'data_plata' => Carbon::now()->addDays(3)],
                ['denumire_calup' => 'Calup Demo 2', 'data_plata' => Carbon::now()->addDays(10)],
            )
            ->create();

        // Create 10 invoices and attach them evenly to the calups.
        $facturiCuCalup = FacturaFurnizor::factory()->count(10)->create();

        foreach ($facturiCuCalup->chunk(5) as $index => $chunk) {
            $calup = $calupuri[$index] ?? $calupuri->last();
            $calup->facturi()->attach($chunk->pluck('id')->all());
        }
    }
}
