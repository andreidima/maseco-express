@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h4 mb-0">
                        <i class="fa-solid fa-envelope-circle-check me-2"></i>
                        Configurare email
                    </h1>
                </div>

                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('test_status'))
                    <div class="alert alert-info" role="alert">
                        {{ session('test_status') }}
                    </div>
                @endif

                @if (session('test_error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('test_error') }}
                    </div>
                @endif

                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fa-solid fa-gears me-1"></i>
                        Setări SMTP
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tech.mail-settings.update') }}" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="mail_mailer" class="form-label">Mailer implicit</label>
                                <input type="text" class="form-control @error('mail_mailer') is-invalid @enderror"
                                       id="mail_mailer" name="mail_mailer" value="{{ old('mail_mailer', $mailSettings['mail_mailer']) }}"
                                       required>
                                @error('mail_mailer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="mail_host" class="form-label">Host</label>
                                    <input type="text" class="form-control @error('mail_host') is-invalid @enderror"
                                           id="mail_host" name="mail_host" value="{{ old('mail_host', $mailSettings['mail_host']) }}">
                                    @error('mail_host')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="mail_port" class="form-label">Port</label>
                                    <input type="number" class="form-control @error('mail_port') is-invalid @enderror"
                                           id="mail_port" name="mail_port" value="{{ old('mail_port', $mailSettings['mail_port']) }}"
                                           min="1" max="65535">
                                    @error('mail_port')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label for="mail_username" class="form-label">Utilizator</label>
                                    <input type="text" class="form-control @error('mail_username') is-invalid @enderror"
                                           id="mail_username" name="mail_username" value="{{ old('mail_username', $mailSettings['mail_username']) }}">
                                    @error('mail_username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="mail_password" class="form-label">Parolă</label>
                                    <input type="text" class="form-control @error('mail_password') is-invalid @enderror"
                                           id="mail_password" name="mail_password" value="{{ old('mail_password', $mailSettings['mail_password']) }}">
                                    @error('mail_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3 mt-3">
                                <label for="mail_encryption" class="form-label">Tip criptare</label>
                                <input type="text" class="form-control @error('mail_encryption') is-invalid @enderror"
                                       id="mail_encryption" name="mail_encryption" value="{{ old('mail_encryption', $mailSettings['mail_encryption']) }}">
                                @error('mail_encryption')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="mail_from_address" class="form-label">From address</label>
                                    <input type="email" class="form-control @error('mail_from_address') is-invalid @enderror"
                                           id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', $mailSettings['mail_from_address']) }}"
                                           required>
                                    @error('mail_from_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="mail_from_name" class="form-label">From name</label>
                                    <input type="text" class="form-control @error('mail_from_name') is-invalid @enderror"
                                           id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $mailSettings['mail_from_name']) }}">
                                    @error('mail_from_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label for="mail_reply_to_address" class="form-label">Reply-to address</label>
                                    <input type="email" class="form-control @error('mail_reply_to_address') is-invalid @enderror"
                                           id="mail_reply_to_address" name="mail_reply_to_address"
                                           value="{{ old('mail_reply_to_address', $mailSettings['mail_reply_to_address']) }}">
                                    @error('mail_reply_to_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="mail_reply_to_name" class="form-label">Reply-to name</label>
                                    <input type="text" class="form-control @error('mail_reply_to_name') is-invalid @enderror"
                                           id="mail_reply_to_name" name="mail_reply_to_name"
                                           value="{{ old('mail_reply_to_name', $mailSettings['mail_reply_to_name']) }}">
                                    @error('mail_reply_to_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-floppy-disk me-1"></i>
                                    Salvează setările
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i class="fa-solid fa-paper-plane me-1"></i>
                        Trimite email de test
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tech.mail-settings.test') }}" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="test_recipient" class="form-label">Destinatar</label>
                                <input type="email" class="form-control @error('test_recipient') is-invalid @enderror"
                                       id="test_recipient" name="test_recipient"
                                       value="{{ old('test_recipient') }}" required>
                                @error('test_recipient')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="test_subject" class="form-label">Subiect</label>
                                <input type="text" class="form-control @error('test_subject') is-invalid @enderror"
                                       id="test_subject" name="test_subject" value="{{ old('test_subject') }}"
                                       placeholder="Test configurare email Maseco">
                                @error('test_subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="test_message" class="form-label">Mesaj</label>
                                <textarea class="form-control @error('test_message') is-invalid @enderror" id="test_message"
                                          name="test_message" rows="4" placeholder="Acesta este un email de test...">{{ old('test_message') }}</textarea>
                                @error('test_message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-paper-plane me-1"></i>
                                    Trimite email de test
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
