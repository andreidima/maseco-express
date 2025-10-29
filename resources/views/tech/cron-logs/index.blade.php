@extends('layouts.app')

@php
    use App\Models\CronJobLog;
    use Illuminate\Support\Str;

    $statusClasses = [
        CronJobLog::STATUS_STARTED => 'bg-secondary',
        CronJobLog::STATUS_COMPLETED => 'bg-success',
        CronJobLog::STATUS_FAILED => 'bg-danger',
    ];
@endphp

@section('content')
    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h1 class="h3 mb-0">Cron job logs</h1>

            @if ($filtersActive)
                <a class="btn btn-outline-secondary" href="{{ route('tech.cron-logs.index') }}">
                    <i class="fa-solid fa-rotate-left me-1"></i>Resetează filtrele
                </a>
            @endif
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <strong>Filtrează rezultatele</strong>
            </div>
            <div class="card-body">
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="start_date">De la</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $filters['start_date'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="end_date">Până la</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $filters['end_date'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="job_key">Job</label>
                        <select class="form-select" id="job_key" name="job_key">
                            <option value="">Toate joburile</option>
                            @foreach ($jobKeys as $jobKey)
                                <option value="{{ $jobKey }}" @selected($filters['job_key'] === $jobKey)>{{ $jobKey }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Toate statusurile</option>
                            @foreach ($availableStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-filter me-1"></i>Filtrează
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if ($stats->isNotEmpty())
            <div class="row g-3 mb-4">
                @foreach ($stats as $stat)
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title h6 mb-0">{{ $stat['job_key'] }}</h5>
                                    @if ($stat['last_status'])
                                        <span class="badge {{ $statusClasses[$stat['last_status']] ?? 'bg-light text-dark' }}">
                                            {{ ucfirst($stat['last_status']) }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mb-2 small text-muted">
                                    Ultima rulare:
                                    @if ($stat['last_run_at'])
                                        <strong>{{ $stat['last_run_at']->format('Y-m-d H:i:s') }}</strong>
                                        <span class="text-muted">({{ $stat['last_run_at']->diffForHumans() }})</span>
                                    @else
                                        <span>—</span>
                                    @endif
                                </p>
                                <p class="mb-2 small">
                                    <span class="badge bg-success me-1">Succes: {{ $stat['total_successes'] }}</span>
                                    <span class="badge bg-danger">Eșecuri: {{ $stat['total_failures'] }}</span>
                                </p>
                                @if ($stat['last_message'])
                                    <p class="mb-0 small text-muted">„{{ Str::limit($stat['last_message'], 120) }}”</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Jurnal execuții</strong>
                <span class="text-muted small">{{ $logs->total() }} înregistrări</span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Creat la</th>
                            <th scope="col">Job</th>
                            <th scope="col">Route</th>
                            <th scope="col">Status</th>
                            <th scope="col">Durată</th>
                            <th scope="col">Mesaj</th>
                            <th scope="col" class="text-end">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            @php
                                $logData = [
                                    'id' => $log->id,
                                    'job_key' => $log->job_key,
                                    'route' => $log->route,
                                    'status' => $log->status,
                                    'runtime' => $log->runtime,
                                    'message' => $log->message,
                                    'payload' => $log->payload,
                                    'created_at' => optional($log->created_at)->toIso8601String(),
                                ];
                            @endphp
                            <tr>
                                <td>{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                                <td><code>{{ $log->job_key }}</code></td>
                                <td>{{ $log->route ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $statusClasses[$log->status] ?? 'bg-light text-dark' }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if (! is_null($log->runtime))
                                        {{ number_format($log->runtime, 4) }} s
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $log->message ? Str::limit($log->message, 80) : '—' }}</td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#logDetailsModal"
                                            data-log='@json($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)'>
                                            <i class="fa-solid fa-circle-info me-1"></i>Detalii rapide
                                        </button>
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('tech.cron-logs.show', $log) }}">
                                            <i class="fa-solid fa-up-right-from-square me-1"></i>Deschide pagină
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Nu există înregistrări pentru criteriile selectate.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="card-footer">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logDetailsModalLabel">Detalii cron job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
                </div>
                <div class="modal-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Job</dt>
                        <dd class="col-sm-9" data-field="job-key"></dd>

                        <dt class="col-sm-3">Route</dt>
                        <dd class="col-sm-9" data-field="route"></dd>

                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9" data-field="status"></dd>

                        <dt class="col-sm-3">Durată</dt>
                        <dd class="col-sm-9" data-field="runtime"></dd>

                        <dt class="col-sm-3">Creat la</dt>
                        <dd class="col-sm-9" data-field="created-at"></dd>

                        <dt class="col-sm-3">Mesaj</dt>
                        <dd class="col-sm-9" data-field="message"></dd>

                        <dt class="col-sm-3">Payload</dt>
                        <dd class="col-sm-9">
                            <pre data-field="payload" class="bg-light border rounded p-3 small mb-0" style="max-height: 320px; overflow:auto;">—</pre>
                        </dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('logDetailsModal');
            if (!modal) {
                return;
            }

            modal.addEventListener('show.bs.modal', function (event) {
                const trigger = event.relatedTarget;
                if (!trigger) {
                    return;
                }

                const data = trigger.getAttribute('data-log');
                if (!data) {
                    return;
                }

                let log;
                try {
                    log = JSON.parse(data);
                } catch (error) {
                    log = null;
                }

                const assignText = (selector, value) => {
                    const element = modal.querySelector(`[data-field="${selector}"]`);
                    if (!element) {
                        return;
                    }
                    element.textContent = value ?? '—';
                };

                assignText('job-key', log?.job_key ?? '—');
                assignText('route', log?.route ?? '—');
                assignText('status', log?.status ? log.status.charAt(0).toUpperCase() + log.status.slice(1) : '—');
                assignText('runtime', typeof log?.runtime === 'number' ? `${log.runtime.toFixed(4)} s` : '—');
                assignText('created-at', log?.created_at ? new Date(log.created_at).toLocaleString() : '—');
                assignText('message', log?.message ?? '—');

                const payloadField = modal.querySelector('[data-field="payload"]');
                if (payloadField) {
                    if (log?.payload) {
                        payloadField.textContent = JSON.stringify(log.payload, null, 2);
                    } else {
                        payloadField.textContent = '—';
                    }
                }
            });
        });
    </script>
@endpush
