@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h1 class="h5 mb-0">Legendă acces implicit pe roluri</h1>
                    </div>
                    <div class="card-body">
                        <p class="mb-4">
                            Formularul de <strong>adăugare/modificare utilizator</strong> afișează o legendă cu modulele
                            aplicației și rolurile care le acordă în mod implicit. Matricea apare imediat înaintea
                            casetelor pentru selectarea rolurilor și este disponibilă atât la crearea unui cont nou, cât și
                            la actualizarea unui cont existent.
                        </p>

                        <h2 class="h6 text-uppercase text-muted">Cum citești matricea</h2>
                        <ul class="mb-4">
                            <li><strong>Coloana „Modul”</strong> indică funcționalitatea din aplicație (ex.: Comenzi, Documente, Service).</li>
                            <li><strong>Descrierea</strong> sintetizează ce acoperă modulul respectiv pentru a-ți reaminti rapid scopul lui.</li>
                            <li><strong>Rolurile cu acces implicit</strong> afișează insigne pentru fiecare rol care acordă automat modulul atunci când este atribuit unui utilizator.</li>
                        </ul>

                        <h2 class="h6 text-uppercase text-muted">Recomandări pentru onboarding</h2>
                        <ul class="mb-4">
                            <li>Înainte de a bifa rolurile, consultă legenda pentru a verifica dacă permisiunile implicite acoperă responsabilitățile noului coleg.</li>
                            <li>Dacă un rol nu oferă toate modulele necesare, combină-l cu alte roluri relevante – permisiunile directe nu mai sunt configurabile individual.</li>
                            <li>Păstrează supravegherea asupra rolurilor speciale (ex. Super Admin) – acestea apar în legendă pentru transparență, dar rămân rezervate echipei de administratori principali.</li>
                        </ul>

                        <h2 class="h6 text-uppercase text-muted">Actualizarea permisiunilor existente</h2>
                        <p class="mb-3">
                            Atunci când revizuiești accesul unui utilizator existent, compară responsabilitățile din fișa postului cu informațiile din legendă. Ajustează rolurile pentru a obține combinația corectă, fără a încerca să adaugi permisiuni manuale.
                        </p>
                        <p class="mb-0 text-muted small">
                            Dacă întâlnești conturi cu permisiuni directe moștenite, notează-le și migrează accesul către roluri dedicate pentru a păstra consistența.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
