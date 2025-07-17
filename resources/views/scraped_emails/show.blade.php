@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="shadow-lg" style="border-radius: 40px;">
                <div class="border border-secondary p-2 culoare2" style="border-radius: 40px 40px 0 0;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-envelope-open-text me-1"></i> Detalii Email
                    </span>
                </div>

                <div class="card-body p-4 border border-secondary" style="border-radius: 0 0 40px 40px;">
                    <div class="row">
                        <div class="col-md-6 mb-3"><strong>Subject:</strong> {{ $scraped_email->email_subject }}</div>
                        <div class="col-md-6 mb-3"><strong>Sender:</strong> {{ $scraped_email->from_email }}</div>
                        <div class="col-md-6 mb-3"><strong>Date Received:</strong> {{ \Carbon\Carbon::parse($scraped_email->date_received)->format('d.m.Y H:i') }}</div>
                        <div class="col-md-12 mb-3"><strong>Gmail Link:</strong> <a href="{{ $scraped_email->gmail_link }}" target="_blank">Vezi în Gmail</a></div>

                        <div class="col-md-4 mb-3"><strong>Load Code:</strong> {{ $scraped_email->load_postal_code ?? '-' }}</div>
                        <div class="col-md-4 mb-3"><strong>Load City:</strong> {{ $scraped_email->load_city ?? '-' }}</div>
                        <div class="col-md-4 mb-3"><strong>Load Interval:</strong> {{ $scraped_email->load_interval ?? '-' }}</div>

                        <div class="col-md-4 mb-3"><strong>Unload Code:</strong> {{ $scraped_email->unload_postal_code ?? '-' }}</div>
                        <div class="col-md-4 mb-3"><strong>Unload City:</strong> {{ $scraped_email->unload_city ?? '-' }}</div>
                        <div class="col-md-4 mb-3"><strong>Unload Interval:</strong> {{ $scraped_email->unload_interval ?? '-' }}</div>

                        <div class="col-md-12 mb-3"><strong>Details:</strong><br>{{ $scraped_email->details ?? '-' }}</div>

                        <div class="col-md-6 mb-3"><strong>Created At:</strong> {{ $scraped_email->created_at?->format('d.m.Y H:i') }}</div>
                        <div class="col-md-6 mb-3"><strong>Updated At:</strong> {{ $scraped_email->updated_at?->format('d.m.Y H:i') }}</div>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        <a href="{{ route('scraped-emails.edit', $scraped_email->id) }}" class="btn btn-primary me-3 rounded-3">
                            <i class="fa-solid fa-edit me-1"></i> Modifică
                        </a>
                        <a href="{{ Session::get('returnUrl', route('scraped_emails.index')) }}" class="btn btn-secondary rounded-3">
                            <i class="fa-solid fa-arrow-left me-1"></i> Înapoi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
