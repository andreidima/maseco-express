<?php

namespace App\Models\Masini;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        ];
    }

    public static function vignetteCountries(): array
    {
        return [
            'ro' => 'RO',
            'hu' => 'HU',
            'at' => 'AT',
            'cz' => 'CZ',
            'sk' => 'SK',
        ];
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

        foreach (self::vignetteCountries() as $code => $label) {
            $labels[self::TYPE_VIGNETA . ':' . $code] = 'Vignetă ' . $label;
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
            1 => 'notificare_1_trimisa',
        ];
    }

    public function resetNotificationFlags(): void
    {
        $this->forceFill([
            'notificare_60_trimisa' => false,
            'notificare_30_trimisa' => false,
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
            $countries = self::vignetteCountries();
            $suffix = $countries[$this->tara] ?? strtoupper((string) $this->tara);

            return 'Vignetă ' . $suffix;
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
}
