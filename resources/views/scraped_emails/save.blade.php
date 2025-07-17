@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="shadow-lg" style="border-radius: 40px;">
                <div class="border border-secondary p-2 culoare2" style="border-radius: 40px 40px 0 0;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-envelope-open-text me-1"></i>
                        {{ isset($scraped_email) ? 'Editează Email' : 'Adaugă Email' }}
                    </span>
                </div>

                @include ('errors')

                <div class="card-body p-4 border border-secondary" style="border-radius: 0 0 40px 40px;">
                    <form class="needs-validation" novalidate
                          method="POST"
                          action="{{ isset($scraped_email) ? route('scraped-emails.update', $scraped_email->id) : route('scraped_emails.store') }}">
                        @csrf
                        @if(isset($scraped_email))
                            @method('PUT')
                        @endif

                        @include('scraped_emails.form', [
                            'scraped_email' => $scraped_email ?? null,
                            'buttonText'    => isset($scraped_email) ? 'Salvează modificările' : 'Adaugă Email',
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
