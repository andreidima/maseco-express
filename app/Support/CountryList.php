<?php

namespace App\Support;

class CountryList
{
    /**
     * @var array<string, string>
     */
    private const COUNTRIES = [
        'AL' => 'Albania',
        'AT' => 'Austria',
        'BA' => 'Bosnia și Herțegovina',
        'BE' => 'Belgia',
        'BG' => 'Bulgaria',
        'CH' => 'Elveția',
        'CY' => 'Cipru',
        'CZ' => 'Cehia',
        'DE' => 'Germania',
        'DK' => 'Danemarca',
        'EE' => 'Estonia',
        'ES' => 'Spania',
        'FI' => 'Finlanda',
        'FR' => 'Franța',
        'GB' => 'Regatul Unit',
        'GR' => 'Grecia',
        'HR' => 'Croația',
        'HU' => 'Ungaria',
        'IE' => 'Irlanda',
        'IT' => 'Italia',
        'LT' => 'Lituania',
        'LU' => 'Luxemburg',
        'LV' => 'Letonia',
        'MD' => 'Republica Moldova',
        'MK' => 'Macedonia de Nord',
        'MT' => 'Malta',
        'NL' => 'Țările de Jos',
        'NO' => 'Norvegia',
        'PL' => 'Polonia',
        'PT' => 'Portugalia',
        'RO' => 'România',
        'RS' => 'Serbia',
        'SE' => 'Suedia',
        'SI' => 'Slovenia',
        'SK' => 'Slovacia',
        'TR' => 'Turcia',
        'UA' => 'Ucraina',
    ];

    public static function options(): array
    {
        return self::COUNTRIES;
    }

    public static function label(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        $upperCode = strtoupper($code);

        return self::COUNTRIES[$upperCode] ?? $upperCode;
    }
}
