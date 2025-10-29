@extends('layouts.app')

@php
    use App\Models\CronJobLog;

    $statusClasses = [
        CronJobLog::STATUS_STARTED => 'bg-secondary',
        CronJobLog::STATUS_COMPLETED => 'bg-success',
        CronJobLog::STATUS_FAILED => 'bg-danger',
    ];
@endphp

@section('content')
    <div class="container py-4">
        <div class="mb-3">
            <a href="{{ route('tech.cron-logs.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>Înapoi la listă
            </a>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Cron job log #{{ $log->id }}</strong>
                <span class="badge {{ $statusClasses[$log->status] ?? 'bg-light text-dark' }}">
                    {{ ucfirst($log->status) }}
                </span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Job</dt>
                    <dd class="col-sm-9"><code>{{ $log->job_key }}</code></dd>

                    <dt class="col-sm-3">Route</dt>
                    <dd class="col-sm-9">{{ $log->route ?? '—' }}</dd>

                    <dt class="col-sm-3">Mesaj</dt>
                    <dd class="col-sm-9">{{ $log->message ?? '—' }}</dd>

                    <dt class="col-sm-3">Durată</dt>
                    <dd class="col-sm-9">
                        @if (! is_null($log->runtime))
                            {{ number_format($log->runtime, 4) }} s
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="col-sm-3">Creat la</dt>
                    <dd class="col-sm-9">{{ optional($log->created_at)->format('Y-m-d H:i:s') ?? '—' }}</dd>

                    <dt class="col-sm-3">Actualizat la</dt>
                    <dd class="col-sm-9">{{ optional($log->updated_at)->format('Y-m-d H:i:s') ?? '—' }}</dd>

                    <dt class="col-sm-3">Payload</dt>
                    <dd class="col-sm-9">
                        <pre class="bg-light border rounded p-3 small" style="max-height: 400px; overflow:auto;">{{ $log->payload ? json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '—' }}</pre>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
@endsection
