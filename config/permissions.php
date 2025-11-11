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
        'statii-peco' => [
            'name' => 'Stații Peco',
            'description' => 'Permite consultarea catalogului de stații peco și încărcarea de fișiere Excel cu noi înregistrări.',
        ],
        'statii-peco-manage' => [
            'name' => 'Stații Peco (Ștergeri în masă)',
            'description' => 'Limitează operațiunile destructive (ex. ștergere în masă) la rolurile administrative.',
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
            'name' => 'Documente Word — Acces operativ',
            'description' => 'Permite dispecerilor și operatorilor să creeze, să actualizeze, să caute, să vizualizeze și să descarce documentele Word cu nivel de acces „Operator” (nivel 2).',
        ],
        'documente-word-manage' => [
            'name' => 'Documente Word — Administrare completă',
            'description' => 'Permite administratorilor să creeze, să actualizeze, să blocheze și să șteargă documentele Word, inclusiv pe cele rezervate nivelului „Administrator” (nivel 1).',
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
        'masini-valabilitati' => [
            'name' => 'Resurse — Mașini valabilități',
            'description' => 'Permite consultarea și actualizarea valabilităților pentru parcul auto.',
        ],
        'valabilitati' => [
            'name' => 'Gestionare Valabilități',
            'description' => 'Administrează cursele disponibile, istoricul localităților și intervalele de valabilitate.',
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
            'documente-word',
            'facturi',
            'rapoarte',
            'statii-peco',
            'masini-valabilitati',
        ],
        'mecanic' => [
            'service-masini',
            'gestiune-piese',
        ],
        'documente-word-operator' => [
            'documente-word',
            'masini-valabilitati',
        ],
        'documente-word-administrator' => [
            'documente-word',
            'documente-word-manage',
            'masini-valabilitati',
        ],
        'sofer' => [],
    ],
];
