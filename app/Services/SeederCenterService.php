<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class SeederCenterService
{
    public function getAvailableSeeders(): Collection
    {
        $seederPath = database_path('seeders');

        if (! is_dir($seederPath)) {
            return collect();
        }

        $files = Finder::create()
            ->files()
            ->in($seederPath)
            ->name('*.php');

        return collect(iterator_to_array($files))
            ->map(function (SplFileInfo $file) use ($seederPath) {
                $relativePath = Str::replaceFirst($seederPath . DIRECTORY_SEPARATOR, '', $file->getRealPath());
                $class = 'Database\\Seeders\\' . str_replace(['/', '\\', '.php'], ['\\', '\\', ''], $relativePath);
                $class = str_replace('\\\\', '\\', $class);

                return [
                    'class' => $class,
                    'label' => $this->presentableClassName($class),
                ];
            })
            ->filter(function (array $entry) {
                return class_exists($entry['class']);
            })
            ->reject(function (array $entry) {
                return $entry['class'] === 'Database\\Seeders\\DatabaseSeeder';
            })
            ->sortBy('label')
            ->values();
    }

    public function runSeeder(?string $class = null): string
    {
        $parameters = ['--force' => true];

        if ($class) {
            $parameters['--class'] = $class;
        }

        Artisan::call('db:seed', $parameters);

        return Artisan::output();
    }

    private function presentableClassName(string $class): string
    {
        $basename = class_basename($class);
        $trimmed = Str::replaceLast('Seeder', '', $basename);

        return $trimmed ? Str::headline($trimmed) : $basename;
    }
}
