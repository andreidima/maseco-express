<?php

return [
    'modules' => [
        'dashboard' => [
            'name' => 'Acces Panou Principal',
            'description' => 'Cuprinde indicatorii principali, panourile de stare și legăturile rapide către procese zilnice.',
        ],
        'users' => [
            'name' => 'Gestionare Utilizatori',
            'description' => 'Include listarea, invitarea și administrarea conturilor, rolurilor și a permisiunilor individuale.',
        ],
        'firme' => [
            'name' => 'Gestionare Companii',
            'description' => 'Grupul dedicat gestiunii clienților, transportatorilor, furnizorilor și documentelor companiei.',
        ],
        'camioane' => [
            'name' => 'Gestionare Camioane',
            'description' => 'Conține inventarul vehiculelor, fișele tehnice și monitorizarea documentelor obligatorii.',
        ],
        'locuri-operare' => [
            'name' => 'Gestionare Locații de Operare',
            'description' => 'Include adresele operaționale, depozitele și configurările logistice aferente.',
        ],
        'comenzi' => [
            'name' => 'Gestionare Comenzi',
            'description' => 'Reunește fluxul de creare, planificare și urmărire a comenzilor și curselor.',
        ],
        'mesagerie' => [
            'name' => 'Gestionare Mesagerie',
            'description' => 'Acoperă notificările interne, comunicările către șoferi și istoricul mesajelor.',
        ],
        'mementouri' => [
            'name' => 'Gestionare Mementouri',
            'description' => 'Cuprinde configurarea alertelor, revizuirilor periodice și urmărirea termenelor.',
        ],
        'documente' => [
            'name' => 'Documente (vizualizare)',
            'description' => 'Oferă acces doar la navigarea și descărcarea documentelor din managerul de fișiere.',
        ],
        'documente-manage' => [
            'name' => 'Documente (Acțiuni)',
            'description' => 'Controlează acțiunile din coloana „Acțiuni” (încărcare, redenumire, ștergere) din managerul de fișiere.',
        ],
        'documente-word' => [
            'name' => 'Documente Word (vizualizare)',
            'description' => 'Permite accesul la biblioteca de documente Word interne.',
        ],
        'documente-word-manage' => [
            'name' => 'Documente Word (Acțiuni)',
            'description' => 'Autorizează modificarea, adăugarea și ștergerea documentelor Word.',
        ],
        'facturi' => [
            'name' => 'Gestionare Facturi',
            'description' => 'Include emiterea, urmărirea încasărilor și raportarea scadențarelor clienților.',
        ],
        'facturi-furnizori' => [
            'name' => 'Gestionare Facturi Furnizori',
            'description' => 'Cuprinde înregistrarea și aprobarea facturilor furnizorilor și a plăților aferente.',
        ],
        'service-masini' => [
            'name' => 'Gestionare Service Auto',
            'description' => 'Include recepția în service, programările, istoricul intervențiilor și validarea lucrărilor.',
        ],
        'gestiune-piese' => [
            'name' => 'Gestionare Stoc Piese',
            'description' => 'Acoperă stocurile de piese, transferurile interne și inventarele periodice.',
        ],
        'rapoarte' => [
            'name' => 'Vizualizare Rapoarte',
            'description' => 'Reunește tablourile de raportare, exporturile și indicatorii operaționali.',
        ],
        'tech-tools' => [
            'name' => 'Acces Instrumente Tehnice',
            'description' => 'Include instrumentele tehnice și de suport dedicate echipei interne.',
        ],
    ],

    'role_defaults' => [
        'super-admin' => ['*'],
        'admin' => ['*'],
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
            'rapoarte',
        ],
        'mecanic' => [
            'service-masini',
            'gestiune-piese',
        ],
        'documente-word-operator' => [
            'documente-word',
        ],
        'documente-word-administrator' => [
            'documente-word',
            'documente-word-manage',
        ],
    ],
];
