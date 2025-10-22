<?php

return [
    'modules' => [
        'dashboard' => [
            'name' => 'Acces Panou Principal',
            'description' => 'Vizualizează pagina principală și indicatorii generali.',
        ],
        'users' => [
            'name' => 'Gestionare Utilizatori',
            'description' => 'Acces la listarea și administrarea utilizatorilor.',
        ],
        'roles' => [
            'name' => 'Gestionare Roluri',
            'description' => 'Configurează rolurile și accesul acestora.',
        ],
        'firme' => [
            'name' => 'Gestionare Companii',
            'description' => 'Gestionează clienții, transportatorii și partenerii.',
        ],
        'camioane' => [
            'name' => 'Gestionare Camioane',
            'description' => 'Administrează flota de camioane și documentele aferente.',
        ],
        'locuri-operare' => [
            'name' => 'Gestionare Locații de Operare',
            'description' => 'Gestionează locațiile de operare și detaliile acestora.',
        ],
        'comenzi' => [
            'name' => 'Gestionare Comenzi',
            'description' => 'Creează, actualizează și monitorizează comenzile.',
        ],
        'mesagerie' => [
            'name' => 'Gestionare Mesagerie',
            'description' => 'Acces la notificările și mesajele trimise.',
        ],
        'mementouri' => [
            'name' => 'Gestionare Mementouri',
            'description' => 'Administrează mementourile și alertele.',
        ],
        'documente' => [
            'name' => 'Gestionare Documente',
            'description' => 'Acces la managerul de fișiere și documente interne.',
        ],
        'facturi' => [
            'name' => 'Gestionare Facturi',
            'description' => 'Gestionează facturile și scadențarele.',
        ],
        'facturi-furnizori' => [
            'name' => 'Gestionare Facturi Furnizori',
            'description' => 'Administrează facturile furnizorilor și plățile.',
        ],
        'service-masini' => [
            'name' => 'Gestionare Service Auto',
            'description' => 'Gestionează operațiunile din service-ul mașinilor.',
        ],
        'gestiune-piese' => [
            'name' => 'Gestionare Stoc Piese',
            'description' => 'Gestionează stocul de piese și ajustările acestuia.',
        ],
        'rapoarte' => [
            'name' => 'Vizualizare Rapoarte',
            'description' => 'Acces la rapoartele și analizele aplicației.',
        ],
        'tech-tools' => [
            'name' => 'Acces Instrumente Tehnice',
            'description' => 'Acces la instrumentele tehnice dedicate echipei interne.',
        ],
    ],

    'role_defaults' => [
        'super-admin' => ['*'],
        'admin' => [
            'dashboard',
            'users',
            'roles',
            'firme',
            'camioane',
            'locuri-operare',
            'comenzi',
            'mesagerie',
            'mementouri',
            'documente',
            'facturi',
            'facturi-furnizori',
            'service-masini',
            'gestiune-piese',
            'rapoarte',
        ],
        'dispecer' => [
            'dashboard',
            'firme',
            'camioane',
            'locuri-operare',
            'comenzi',
            'mesagerie',
            'mementouri',
            'documente',
            'facturi',
            'facturi-furnizori',
            'rapoarte',
        ],
        'mecanic' => [
            'service-masini',
            'gestiune-piese',
        ],
    ],
];
