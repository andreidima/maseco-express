<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class SeederCenterService
{
    private const DEFAULT_DESCRIPTION = 'No description available for this seeder.';

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
                    'description' => $this->describeSeederClass($class) ?? self::DEFAULT_DESCRIPTION,
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

    public function describeSeeder(?string $class = null): string
    {
        if (! $class) {
            return 'Runs the DatabaseSeeder default entry point. This typically triggers the seeders registered in DatabaseSeeder::run().';
        }

        return $this->describeSeederClass($class) ?? self::DEFAULT_DESCRIPTION;
    }

    private function describeSeederClass(string $class): ?string
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $exception) {
            return null;
        }

        if ($reflection->hasConstant('DESCRIPTION')) {
            $value = $reflection->getConstant('DESCRIPTION');

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        $defaults = $reflection->getDefaultProperties();

        if (array_key_exists('description', $defaults)) {
            $propertyValue = $defaults['description'];

            if (is_string($propertyValue) && trim($propertyValue) !== '') {
                return trim($propertyValue);
            }
        }

        $docComment = $reflection->getDocComment();

        if ($docComment) {
            $lines = preg_split('/\r\n|\r|\n/', $docComment) ?: [];

            foreach ($lines as $line) {
                $line = trim($line);
                $line = preg_replace('/^\/\*\*?/', '', $line);
                $line = preg_replace('/\*\/$/', '', $line);
                $line = preg_replace('/^\*/', '', $line);
                $line = trim($line);

                if ($line !== '') {
                    return $line;
                }
            }
        }

        return null;
    }

    private function presentableClassName(string $class): string
    {
        $basename = class_basename($class);
        $trimmed = Str::replaceLast('Seeder', '', $basename);

        return $trimmed ? Str::headline($trimmed) : $basename;
    }
}
