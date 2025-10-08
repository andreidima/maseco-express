<?php

namespace App\Console\Commands;

use App\Models\FacturiFurnizori\PlataCalupFisier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrganizeCalupFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facturi-furnizori:organize-calup-files {--dry-run : Doar raporteaza schimbarile fara a muta fisierele}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reorganizeaza fisierele calupurilor pe directoare individuale pentru fiecare calup';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $fisiere = PlataCalupFisier::query()->orderBy('id')->get();

        if ($fisiere->isEmpty()) {
            $this->info('Nu exista fisiere pentru procesare.');

            return self::SUCCESS;
        }

        $mutate = 0;
        $dejaPozitionate = 0;
        $lipsa = 0;

        $this->output->progressStart($fisiere->count());

        foreach ($fisiere as $fisier) {
            $this->output->progressAdvance();

            $caleCurenta = $fisier->cale;

            if (!$caleCurenta) {
                $dejaPozitionate++;
                continue;
            }

            $targetFolder = $this->targetFolder($fisier->plata_calup_id);

            if (Str::startsWith($caleCurenta, $targetFolder . '/')) {
                $dejaPozitionate++;
                continue;
            }

            if (!Storage::exists($caleCurenta)) {
                $lipsa++;
                $this->warn("Fisier lipsa: {$caleCurenta}");
                continue;
            }

            $basename = basename($caleCurenta);

            if ($basename === '' || $basename === '.' || $basename === '..') {
                $basename = 'fisier-' . $fisier->id;
            }

            $caleNoua = $this->determinaCaleNoua($targetFolder, $basename, $fisier->id);

            if ($dryRun) {
                $mutate++;
                $this->line("[Simulare] {$caleCurenta} -> {$caleNoua}");
                continue;
            }

            Storage::makeDirectory($targetFolder);

            if (!Storage::move($caleCurenta, $caleNoua)) {
                $this->error("Mutarea a esuat pentru {$caleCurenta}");
                continue;
            }

            $fisier->update(['cale' => $caleNoua]);
            $mutate++;
        }

        $this->output->progressFinish();

        $this->info("Fisiere mutate: {$mutate}");
        $this->info("Fisiere deja in ordine: {$dejaPozitionate}");

        if ($lipsa > 0) {
            $this->warn("Fisiere lipsa: {$lipsa}");
        }

        return self::SUCCESS;
    }

    private function targetFolder(int $calupId): string
    {
        return 'facturi-furnizori/calupuri/' . $calupId;
    }

    private function determinaCaleNoua(string $folder, string $basename, int $fisierId): string
    {
        $nume = pathinfo($basename, PATHINFO_FILENAME);
        $extensie = pathinfo($basename, PATHINFO_EXTENSION);

        if ($nume === '') {
            $nume = 'fisier_' . $fisierId;
        }

        $nume = Str::slug(Str::ascii($nume) ?: ('fisier_' . $fisierId), '_');

        if ($nume === '') {
            $nume = 'fisier_' . $fisierId;
        }

        $nume = substr($nume, 0, 120);

        $sufix = $extensie ? '.' . strtolower($extensie) : '';
        $cale = $folder . '/' . $nume . $sufix;
        $counter = 1;

        while (Storage::exists($cale)) {
            $cale = $folder . '/' . $nume . '_' . $counter . $sufix;
            $counter++;
        }

        return $cale;
    }
}
