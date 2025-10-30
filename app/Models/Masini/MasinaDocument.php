<?php

namespace App\Models\Masini;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MasinaDocument extends Model
{
    use HasFactory;

    protected $table = 'masini_documente';

    public const TYPE_ITP = 'itp';
    public const TYPE_RCA = 'rca';
    public const TYPE_COPIE_CONFORMA = 'copie_conforma';
    public const TYPE_VIGNETA = 'vigneta';
    public const TYPE_TALON = 'talon';
    public const TYPE_CARTE_TEHNICA = 'carte_tehnica';
    public const TYPE_ASIGURARE_CMR = 'asigurare_cmr';

    protected $fillable = [
        'document_type',
        'tara',
        'data_expirare',
        'email_notificare',
    ];

    protected $casts = [
        'data_expirare' => 'date',
        'notificare_60_trimisa' => 'boolean',
        'notificare_30_trimisa' => 'boolean',
        'notificare_15_trimisa' => 'boolean',
        'notificare_1_trimisa' => 'boolean',
    ];

    public function masina()
    {
        return $this->belongsTo(Masina::class);
    }

    public function fisiere()
    {
        return $this->hasMany(MasinaDocumentFisier::class, 'document_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (MasinaDocument $document) {
            $document->fisiere->each->delete();
        });
    }

    public static function resolveForMasina(Masina $masina, string|int $key): self
    {
        if (is_numeric($key)) {
            return $masina->documente()->whereKey($key)->firstOrFail();
        }

        [$type, $country] = self::parseRouteKey((string) $key);

        $document = $masina->documente()
            ->where('document_type', $type)
            ->when($country === null, function ($query) {
                $query->whereNull('tara');
            }, function ($query) use ($country) {
                $query->where('tara', $country);
            })
            ->first();

        if ($document instanceof self) {
            return $document;
        }

        if (!self::isKnownDocumentKey($type, $country)) {
            throw (new ModelNotFoundException())->setModel(self::class, [$key]);
        }

        return $masina->documente()->create([
            'document_type' => $type,
            'tara' => $country,
        ]);
    }

    public static function parseRouteKey(string $key): array
    {
        $segments = explode(':', strtolower(trim($key)), 2);
        $type = $segments[0] ?? '';

        if ($type === '') {
            throw (new ModelNotFoundException())->setModel(self::class, [$key]);
        }

        $country = null;

        if (array_key_exists(1, $segments) && $segments[1] !== '') {
            $country = strtolower(trim($segments[1]));
        }

        return [$type, $country];
    }

    public static function buildRouteKey(string $type, ?string $country = null): string
    {
        $type = strtolower(trim($type));

        if ($country === null || $country === '') {
            return $type;
        }

        return $type . ':' . strtolower(trim($country));
    }

    public static function defaultDefinitions(): array
    {
        $definitions = [
            ['document_type' => self::TYPE_ITP],
            ['document_type' => self::TYPE_RCA],
            ['document_type' => self::TYPE_COPIE_CONFORMA],
        ];

        foreach (self::vignetteCountries() as $code => $name) {
            $definitions[] = [
                'document_type' => self::TYPE_VIGNETA,
                'tara' => $code,
            ];
        }

        $definitions = array_merge($definitions, [
            ['document_type' => self::TYPE_TALON],
            ['document_type' => self::TYPE_CARTE_TEHNICA],
            ['document_type' => self::TYPE_ASIGURARE_CMR],
        ]);

        return $definitions;
    }

    public static function gridDocumentTypes(): array
    {
        return [
            self::TYPE_ITP => 'ITP',
            self::TYPE_RCA => 'RCA',
            self::TYPE_COPIE_CONFORMA => 'Copie conformă',
            self::TYPE_ASIGURARE_CMR => 'Asigurare CMR',
        ];
    }

    public static function vignetteCountries(): array
    {
        return [
            'ro' => 'RO',
            'hu' => 'HU',
            'at' => 'AT',
            'brennero' => 'Brennero',
            'cz' => 'CZ',
            'sk' => 'SK',
        ];
    }

    public static function vignetteDisplayLabel(?string $countryCode): string
    {
        $countryCode = $countryCode !== null ? strtolower($countryCode) : null;
        $countries = self::vignetteCountries();

        $suffix = strtoupper((string) $countryCode);

        if ($countryCode !== null && array_key_exists($countryCode, $countries)) {
            $suffix = $countries[$countryCode];
        }

        if ($countryCode === 'brennero') {
            return $suffix !== '' ? $suffix : 'Brennero';
        }

        return 'Vignetă ' . $suffix;
    }

    public static function uploadDocumentLabels(): array
    {
        $labels = [
            self::TYPE_RCA => 'RCA',
            self::TYPE_TALON => 'Talon',
            self::TYPE_CARTE_TEHNICA => 'Carte tehnică',
            self::TYPE_COPIE_CONFORMA => 'Copie conformă',
            self::TYPE_ASIGURARE_CMR => 'Asigurare CMR',
        ];

        foreach (self::vignetteCountries() as $code => $_label) {
            $labels[self::TYPE_VIGNETA . ':' . $code] = self::vignetteDisplayLabel($code);
        }

        return $labels;
    }

    public function colorClass(): string
    {
        if (!$this->data_expirare) {
            return 'bg-secondary-subtle';
        }

        $diff = $this->daysUntilExpiry();

        if ($diff === null) {
            return 'bg-secondary-subtle';
        }

        if ($diff <= 1) {
            return 'bg-danger text-white';
        }

        if ($diff <= 15) {
            return 'bg-expiring-15 text-white';
        }

        if ($diff <= 30) {
            return 'bg-warning';
        }

        if ($diff <= 60) {
            return 'bg-success text-white';
        }

        return '';
    }

    public function daysUntilExpiry(): ?int
    {
        if (!$this->data_expirare) {
            return null;
        }

        return Carbon::now()->startOfDay()->diffInDays($this->data_expirare, false);
    }

    public static function notificationThresholds(): array
    {
        return [
            60 => 'notificare_60_trimisa',
            30 => 'notificare_30_trimisa',
            15 => 'notificare_15_trimisa',
            1 => 'notificare_1_trimisa',
        ];
    }

    public function resetNotificationFlags(): void
    {
        $this->forceFill([
            'notificare_60_trimisa' => false,
            'notificare_30_trimisa' => false,
            'notificare_15_trimisa' => false,
            'notificare_1_trimisa' => false,
        ])->saveQuietly();
    }

    public function markThresholdAsSent(int $threshold): void
    {
        $columns = self::notificationThresholds();

        if (!isset($columns[$threshold])) {
            return;
        }

        $column = $columns[$threshold];

        $this->forceFill([
            $column => true,
        ])->saveQuietly();
    }

    public function label(): string
    {
        if ($this->document_type === self::TYPE_VIGNETA) {
            return self::vignetteDisplayLabel($this->tara);
        }

        return match ($this->document_type) {
            self::TYPE_ITP => 'ITP',
            self::TYPE_RCA => 'RCA',
            self::TYPE_COPIE_CONFORMA => 'Copie conformă',
            self::TYPE_TALON => 'Talon',
            self::TYPE_CARTE_TEHNICA => 'Carte tehnică',
            self::TYPE_ASIGURARE_CMR => 'Asigurare CMR',
            default => ucfirst(str_replace('_', ' ', (string) $this->document_type)),
        };
    }

    protected static function isKnownDocumentKey(string $type, ?string $country): bool
    {
        $type = strtolower($type);
        $country = $country !== null ? strtolower($country) : null;

        foreach (self::defaultDefinitions() as $definition) {
            $definitionType = strtolower($definition['document_type']);
            $definitionCountry = $definition['tara'] ?? null;

            if ($definitionType !== $type) {
                continue;
            }

            if ($definitionCountry === null && $country === null) {
                return true;
            }

            if ($definitionCountry !== null && strtolower($definitionCountry) === $country) {
                return true;
            }
        }

        return false;
    }
}
