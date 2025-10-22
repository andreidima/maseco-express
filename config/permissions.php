<?php

return [
    'modules' => [
        'dashboard' => [
            'name' => 'Access Dashboard',
            'description' => 'Vizualizează pagina principală și indicatorii generali.',
        ],
        'users' => [
            'name' => 'Manage Users',
            'description' => 'Acces la listarea și administrarea utilizatorilor.',
        ],
        'roles' => [
            'name' => 'Manage Roles',
            'description' => 'Configurează rolurile și accesul acestora.',
        ],
        'firme' => [
            'name' => 'Manage Companies',
            'description' => 'Gestionează clienții, transportatorii și partenerii.',
        ],
        'camioane' => [
            'name' => 'Manage Trucks',
            'description' => 'Administrează flota de camioane și documentele aferente.',
        ],
        'locuri-operare' => [
            'name' => 'Manage Operation Sites',
            'description' => 'Gestionează locațiile de operare și detaliile acestora.',
        ],
        'comenzi' => [
            'name' => 'Manage Orders',
            'description' => 'Creează, actualizează și monitorizează comenzile.',
        ],
        'mesagerie' => [
            'name' => 'Manage Messaging',
            'description' => 'Acces la notificările și mesajele trimise.',
        ],
        'mementouri' => [
            'name' => 'Manage Reminders',
            'description' => 'Administrează mementourile și alertele.',
        ],
        'documente' => [
            'name' => 'Manage Documents',
            'description' => 'Acces la managerul de fișiere și documente interne.',
        ],
        'documente-admin' => [
            'name' => 'Manage Admin Documents',
            'description' => 'Administrează documentele cu acces restricționat la administratori.',
        ],
        'facturi' => [
            'name' => 'Manage Invoices',
            'description' => 'Gestionează facturile și scadențarele.',
        ],
        'facturi-furnizori' => [
            'name' => 'Manage Supplier Invoices',
            'description' => 'Administrează facturile furnizorilor și plățile.',
        ],
        'service-masini' => [
            'name' => 'Manage Vehicle Service',
            'description' => 'Gestionează operațiunile din service-ul mașinilor.',
        ],
        'gestiune-piese' => [
            'name' => 'Manage Parts Inventory',
            'description' => 'Gestionează stocul de piese și ajustările acestuia.',
        ],
        'rapoarte' => [
            'name' => 'View Reports',
            'description' => 'Acces la rapoartele și analizele aplicației.',
        ],
        'tech-tools' => [
            'name' => 'Access Tech Tools',
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
            'documente-admin',
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
