@extends('layouts.app')

@php
    $exportQuery = collect($filters)
        ->reject(fn ($value) => $value === null || $value === '')
        ->all();
@endphp

@section('content')
<div class="container py-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <span class="badge bg-primary text-uppercase mb-2 mb-lg-1">
                    <i class="fa-solid fa-chart-simple me-1"></i>
                    Raport curse valabilități
                </span>
                <h1 class="h5 mb-0">Analiză pe șoferi, mașini și date calendaristice</h1>
            </div>
            <div class="btn-group" role="group" aria-label="Export">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('rapoarte.valabilitati.export', array_merge(['format' => 'csv'], $exportQuery)) }}">
                    <i class="fa-solid fa-file-csv me-1"></i>
                    Export CSV
                </a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('rapoarte.valabilitati.export', array_merge(['format' => 'xlsx'], $exportQuery)) }}">
                    <i class="fa-solid fa-file-excel me-1"></i>
                    Export Excel
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="driver" class="form-label">Șofer</label>
                    <select id="driver" name="driver" class="form-select">
                        <option value="">Toți șoferii</option>
                        <option value="__missing" @selected(($filters['driver'] ?? null) === '__missing')>Fără șofer asociat</option>
                        @foreach ($options['drivers'] as $driverOption)
                            <option value="{{ $driverOption }}" @selected(($filters['driver'] ?? null) === $driverOption)>
                                {{ $driverOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="vehicle" class="form-label">Mașină</label>
                    <select id="vehicle" name="vehicle" class="form-select">
                        <option value="">Toate mașinile</option>
                        <option value="__missing" @selected(($filters['vehicle'] ?? null) === '__missing')>Fără mașină asociată</option>
                        @foreach ($options['vehicles'] as $vehicleId => $vehicleLabel)
                            <option value="{{ $vehicleId }}" @selected(($filters['vehicle'] ?? null) === (string) $vehicleId)>
                                {{ $vehicleLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">De la</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Până la</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('rapoarte.valabilitati') }}" class="btn btn-outline-secondary">
                        Resetare
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-filter me-1"></i>
                        Aplică filtrele
                    </button>
                </div>
            </form>

            <div class="row g-3 mt-4">
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100 text-center">
                        <p class="text-muted mb-1">Total curse</p>
                        <p class="fs-3 fw-semibold mb-0">{{ number_format($report['summary']['total_trips']) }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100 text-center">
                        <p class="text-muted mb-1">Șoferi unici</p>
                        <p class="fs-3 fw-semibold mb-0">{{ number_format($report['summary']['unique_drivers']) }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100 text-center">
                        <p class="text-muted mb-1">Mașini unice</p>
                        <p class="fs-3 fw-semibold mb-0">{{ number_format($report['summary']['unique_vehicles']) }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100 text-center">
                        <p class="text-muted mb-1">Interval analizat</p>
                        <p class="fs-6 fw-semibold mb-0">
                            {{ $report['summary']['date_from'] ? \Carbon\CarbonImmutable::parse($report['summary']['date_from'])->format('d.m.Y') : '—' }}
                            –
                            {{ $report['summary']['date_to'] ? \Carbon\CarbonImmutable::parse($report['summary']['date_to'])->format('d.m.Y') : '—' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h2 class="h6 mb-0">Distribuție curse pe șoferi</h2>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <canvas id="driversChart" height="200"></canvas>
            </div>
            @if ($report['drivers']->isEmpty())
                <p class="text-muted mb-0">Nu există curse pentru criteriile selectate.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Șofer</th>
                                <th class="text-center">Total curse</th>
                                <th class="text-center">Mașini distincte</th>
                                <th>Prima cursă</th>
                                <th>Ultima cursă</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report['drivers'] as $driver)
                                <tr>
                                    <td>{{ $driver['driver_name'] }}</td>
                                    <td class="text-center">{{ number_format($driver['trip_count']) }}</td>
                                    <td class="text-center">{{ number_format($driver['vehicle_count']) }}</td>
                                    <td>{{ $driver['first_trip_at'] ?? '—' }}</td>
                                    <td>{{ $driver['last_trip_at'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h2 class="h6 mb-0">Distribuție curse pe mașini</h2>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <canvas id="vehiclesChart" height="200"></canvas>
            </div>
            @if ($report['vehicles']->isEmpty())
                <p class="text-muted mb-0">Nu există curse pentru criteriile selectate.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Mașină</th>
                                <th class="text-center">Total curse</th>
                                <th class="text-center">Șoferi distinct</th>
                                <th>Prima cursă</th>
                                <th>Ultima cursă</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report['vehicles'] as $vehicle)
                                <tr>
                                    <td>{{ $vehicle['vehicle_label'] }}</td>
                                    <td class="text-center">{{ number_format($vehicle['trip_count']) }}</td>
                                    <td class="text-center">{{ number_format($vehicle['driver_count']) }}</td>
                                    <td>{{ $vehicle['first_trip_at'] ?? '—' }}</td>
                                    <td>{{ $vehicle['last_trip_at'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h2 class="h6 mb-0">Distribuție curse pe zile</h2>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <canvas id="datesChart" height="200"></canvas>
            </div>
            @if ($report['dates']->isEmpty())
                <p class="text-muted mb-0">Nu există curse pentru criteriile selectate.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Dată</th>
                                <th class="text-center">Total curse</th>
                                <th class="text-center">Șoferi distinct</th>
                                <th class="text-center">Mașini distincte</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report['dates'] as $date)
                                <tr>
                                    <td>{{ $date['trip_date'] }}</td>
                                    <td class="text-center">{{ number_format($date['trip_count']) }}</td>
                                    <td class="text-center">{{ number_format($date['driver_count']) }}</td>
                                    <td class="text-center">{{ number_format($date['vehicle_count']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const charts = @json($report['charts']);

            const createBarChart = (canvasId, labels, data, label, color) => {
                const canvas = document.getElementById(canvasId);

                if (!canvas || !Array.isArray(labels) || labels.length === 0) {
                    return;
                }

                const ctx = canvas.getContext('2d');

                return new window.Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [
                            {
                                label,
                                data,
                                backgroundColor: color,
                                borderColor: color,
                                borderWidth: 1,
                            },
                        ],
                    },
                    options: {
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                },
                            },
                        },
                    },
                });
            };

            const createLineChart = (canvasId, labels, data, label, color) => {
                const canvas = document.getElementById(canvasId);

                if (!canvas || !Array.isArray(labels) || labels.length === 0) {
                    return;
                }

                const ctx = canvas.getContext('2d');

                return new window.Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label,
                                data,
                                borderColor: color,
                                backgroundColor: color,
                                fill: false,
                                tension: 0.3,
                                pointRadius: 3,
                            },
                        ],
                    },
                    options: {
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                },
                            },
                        },
                    },
                });
            };

            createBarChart('driversChart', charts.drivers.labels, charts.drivers.data, 'Curse', 'rgba(54, 162, 235, 0.6)');
            createBarChart('vehiclesChart', charts.vehicles.labels, charts.vehicles.data, 'Curse', 'rgba(75, 192, 192, 0.6)');
            createLineChart('datesChart', charts.dates.labels, charts.dates.data, 'Curse pe zi', 'rgba(255, 99, 132, 0.7)');
        });
    </script>
@endpush
