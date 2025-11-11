<?php

namespace App\Support\Navigation;

use App\Models\User;
use App\Support\FacturiFurnizori\FacturiIndexFilterState;
use Illuminate\Support\Facades\Log;

class MainNavigation
{
    public static function brandCandidates(): array
    {
        return [
            [
                'permission' => 'gestiune-piese',
                'href' => route('gestiune-piese.index'),
            ],
            [
                'permission' => 'service-masini',
                'href' => route('service-masini.index'),
            ],
            [
                'permission' => 'dashboard',
                'href' => route('dashboard'),
            ],
        ];
    }

    public static function primaryLinks(): array
    {
        return [
            [
                'permission' => 'documente',
                'href' => '/file-manager-personalizat',
                'icon' => 'fa-solid fa-folder',
                'label' => null,
                'title' => 'File Explorer',
            ],
            [
                'permission' => 'dashboard',
                'href' => '/acasa',
                'icon' => 'fa-solid fa-house',
                'label' => null,
                'title' => 'Pagina principală',
            ],
            [
                'permission' => 'comenzi',
                'href' => '/comenzi',
                'icon' => 'fa-solid fa-clipboard-list',
                'label' => 'Comenzi',
            ],
        ];
    }

    public static function resurseDropdownItems(): array
    {
        return [
            [
                'permission' => 'comenzi',
                'href' => '/flota-statusuri',
                'icon' => 'fa-solid fa-truck',
                'label' => 'Flotă statusuri',
                'target' => '_blank',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'comenzi',
                'href' => '/flota-statusuri-c',
                'icon' => 'fa-solid fa-truck',
                'label' => 'Flotă statusuri C',
                'target' => '_blank',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'masini-valabilitati',
                'href' => '/masini-valabilitati',
                'icon' => 'fa-solid fa-truck',
                'label' => 'Mașini valabilități',
                'target' => '_blank',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'valabilitati',
                'href' => route('valabilitati.index'),
                'icon' => 'fa-solid fa-calendar-check',
                'label' => 'Valabilități',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'comenzi',
                'href' => '/oferte-curse',
                'icon' => 'fa-solid fa-truck',
                'label' => 'Oferte curse',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'firme',
                'href' => '/firme/clienti',
                'icon' => 'fa-solid fa-users',
                'label' => 'Firme Clienți',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'firme',
                'href' => '/firme/transportatori',
                'icon' => 'fa-solid fa-people-carry-box',
                'label' => 'Firme Transportatori',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'camioane',
                'href' => '/camioane',
                'icon' => 'fa-solid fa-truck',
                'label' => 'Camioane',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'locuri-operare',
                'href' => '/locuri-operare',
                'icon' => 'fa-solid fa-location-dot',
                'label' => 'Locuri de operare',
            ],
        ];
    }

    public static function rapoarteDropdownItems(): array
    {
        return [
            [
                'permission' => 'rapoarte',
                'href' => route('rapoarte.valabilitati'),
                'icon' => 'fa-solid fa-route',
                'label' => 'Curse valabilități',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'rapoarte',
                'href' => '/rapoarte/documente-transportatori',
                'icon' => 'fa-solid fa-file',
                'label' => 'Documente transportatori',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'comenzi',
                'href' => '/intermedieri',
                'icon' => 'fa-solid fa-file',
                'label' => 'Intermedieri',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'rapoarte',
                'href' => '/key-performance-indicators',
                'icon' => 'fa-solid fa-chart-simple',
                'label' => 'KPI',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'facturi',
                'href' => '/facturi-scadente',
                'icon' => 'fa-solid fa-file-invoice',
                'label' => 'Facturi scadente',
            ],
        ];
    }

    public static function utileDropdownItems(): array
    {
        $facturiIndexUrl = FacturiIndexFilterState::route();

        return [
            // Hidden temporarily; restore when mementouri pages should reappear.
            // [
            //     'permission' => 'mementouri',
            //     'href' => '/mementouri/1/mementouri',
            //     'icon' => 'fa-solid fa-bell',
            //     'label' => 'Mementouri generale',
            // ],
            // [
            //     'permission' => 'mementouri',
            //     'href' => '/mementouri/2/mementouri',
            //     'icon' => 'fa-solid fa-bell',
            //     'label' => 'Mementouri rca + copii conforme',
            // ],
            // [
            //     'permission' => 'mementouri',
            //     'href' => '/mementouri/3/mementouri',
            //     'icon' => 'fa-solid fa-bell',
            //     'label' => 'Mementouri itp + rovinieta',
            // ],
            [
                'permission' => 'mementouri',
                'href' => route('masini-mementouri.index'),
                'icon' => 'fa-solid fa-car',
                'label' => 'Mementouri mașini',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'statii-peco',
                'href' => '/statii-peco',
                'icon' => 'fa-solid fa-gas-pump',
                'label' => 'Stații peco',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'documente-word',
                'href' => '/documente-word',
                'icon' => 'fa-solid fa-file-word',
                'label' => 'Documente word',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'facturi',
                'href' => '/facturi',
                'icon' => 'fa-solid fa-file-invoice',
                'label' => 'Facturi',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'facturi-furnizori',
                'href' => $facturiIndexUrl,
                'icon' => 'fa-solid fa-file-invoice-dollar',
                'label' => 'Facturi furnizori',
            ],
            [
                'permission' => 'gestiune-piese',
                'href' => route('gestiune-piese.index'),
                'icon' => 'fa-solid fa-boxes-stacked',
                'label' => 'Gestiune piese',
            ],
            [
                'permission' => 'service-masini',
                'href' => route('service-masini.index'),
                'icon' => 'fa-solid fa-screwdriver-wrench',
                'label' => 'Service mașini',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'mesagerie',
                'href' => route('mesaje-trimise-sms.index'),
                'icon' => 'fa-solid fa-comment-sms',
                'label' => 'SMS trimise',
            ],
        ];
    }

    public static function navigationForUser(?User $user): array
    {
        return [
            'brand_href' => self::resolveBrandHref($user),
            'primary_links' => self::filterPrimaryLinks($user),
            'resurse_dropdown' => self::filterDropdownItems(self::resurseDropdownItems(), $user),
            'rapoarte_dropdown' => self::filterDropdownItems(self::rapoarteDropdownItems(), $user),
            'utile_dropdown' => self::filterDropdownItems(self::utileDropdownItems(), $user),
        ];
    }

    public static function resolveBrandHref(?User $user): string
    {
        if ($user) {
            foreach (self::brandCandidates() as $candidate) {
                if (! isset($candidate['permission']) || $user->hasPermission($candidate['permission'])) {
                    return $candidate['href'];
                }
            }
        }

        return url('/');
    }

    public static function firstAccessibleUrlFor(?User $user): ?string
    {
        Log::debug('MainNavigation::firstAccessibleUrlFor invoked', [
            'user_id' => $user?->id,
        ]);

        if ($user) {
            foreach (self::brandCandidates() as $candidate) {
                if (! isset($candidate['permission']) || $user->hasPermission($candidate['permission'])) {
                    Log::debug('MainNavigation brand candidate matched', [
                        'user_id' => $user->id,
                        'permission' => $candidate['permission'] ?? null,
                        'href' => $candidate['href'],
                    ]);

                    return $candidate['href'];
                }

                Log::debug('MainNavigation brand candidate skipped', [
                    'user_id' => $user->id,
                    'permission' => $candidate['permission'] ?? null,
                    'href' => $candidate['href'],
                ]);
            }
        }

        $sections = [
            self::filterPrimaryLinks($user),
            self::filterDropdownItems(self::resurseDropdownItems(), $user),
            self::filterDropdownItems(self::rapoarteDropdownItems(), $user),
            self::filterDropdownItems(self::utileDropdownItems(), $user),
        ];

        foreach ($sections as $sectionIndex => $links) {
            foreach ($links as $linkIndex => $link) {
                if (($link['type'] ?? 'link') === 'divider') {
                    continue;
                }

                Log::debug('MainNavigation non-brand candidate matched', [
                    'user_id' => $user?->id,
                    'section' => $sectionIndex,
                    'link_index' => $linkIndex,
                    'href' => $link['href'],
                ]);

                return $link['href'];
            }
        }

        Log::debug('MainNavigation::firstAccessibleUrlFor found no accessible link', [
            'user_id' => $user?->id,
        ]);

        return null;
    }

    protected static function filterPrimaryLinks(?User $user): array
    {
        return array_values(array_filter(self::primaryLinks(), function (array $link) use ($user) {
            if (! isset($link['permission'])) {
                return true;
            }

            return $user && $user->hasPermission($link['permission']);
        }));
    }

    protected static function filterDropdownItems(array $items, ?User $user): array
    {
        $filtered = array_values(array_filter($items, function ($item) use ($user) {
            if (($item['type'] ?? 'link') === 'divider') {
                return true;
            }

            if (! isset($item['permission'])) {
                return true;
            }

            return $user && $user->hasPermission($item['permission']);
        }));

        while (! empty($filtered) && (($filtered[0]['type'] ?? 'link') === 'divider')) {
            array_shift($filtered);
        }

        while (! empty($filtered)) {
            $lastIndex = array_key_last($filtered);

            if (($filtered[$lastIndex]['type'] ?? 'link') !== 'divider') {
                break;
            }

            array_pop($filtered);
        }

        $cleaned = [];

        foreach ($filtered as $entry) {
            if (($entry['type'] ?? 'link') === 'divider') {
                if (empty($cleaned)) {
                    continue;
                }

                $previous = $cleaned[array_key_last($cleaned)];

                if (($previous['type'] ?? 'link') === 'divider') {
                    continue;
                }
            }

            $cleaned[] = $entry;
        }

        return $cleaned;
    }
}
