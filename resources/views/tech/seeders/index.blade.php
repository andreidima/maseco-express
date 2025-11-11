@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start mb-4 gap-3">
            <div>
                <h1 class="h3 mb-1">Seeder Control Center</h1>
                <p class="text-muted mb-0">Review each seeder's responsibilities, impacted tables, and safety notes before executing it.</p>
            </div>
            <a href="{{ route('tech.migrations.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>Back to migrations dashboard
            </a>
        </div>

        @if ($seederStatus)
            <div class="alert alert-{{ $seederStatusLevel }}">
                {{ $seederStatus }}
            </div>
        @endif

        @if ($errors->has('seeder'))
            <div class="alert alert-danger">
                {{ $errors->first('seeder') }}
            </div>
        @endif

        @php
            $selectedSeeder = old('seeder', $lastRunSeeder);
        @endphp

        @if ($seeders->isNotEmpty())
            <div class="card mb-4 shadow-sm">
                <div class="card-header">
                    <strong>Run a seeder</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('tech.seeders.run') }}" method="post" class="row gy-3 align-items-end">
                        @csrf
                        <div class="col-md-8 col-lg-9">
                            <label for="seeder" class="form-label">Choose the seeder you want to execute</label>
                            <select name="seeder" id="seeder" class="form-select" required>
                                <option value="" disabled {{ $selectedSeeder ? '' : 'selected' }}>Select a seeder…</option>
                                @foreach ($seeders as $seeder)
                                    <option value="{{ $seeder['class'] }}" @selected($selectedSeeder === $seeder['class'])>
                                        {{ $seeder['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-3 d-grid d-md-flex">
                            <button class="btn btn-success ms-md-auto" type="submit"
                                onclick="return confirm('Run the selected seeder now?');">
                                <i class="fa-solid fa-seedling me-1"></i>Run selected seeder
                            </button>
                        </div>
                    </form>
                    <p class="small text-muted mb-0 mt-3">Detailed descriptions for each seeder are available below so you
                        can double-check what will happen before executing them.</p>
                </div>
            </div>
        @endif

        @if ($seeders->isEmpty())
            <div class="alert alert-warning">
                No seeders are currently configured. Add entries to <code>config/tech-seeders.php</code> to expose them here.
            </div>
        @else
            <div class="row g-4">
                @foreach ($seeders as $seeder)
                    @php
                        $isLastRun = $lastRunSeeder === $seeder['class'];
                        $isSelected = $selectedSeeder === $seeder['class'];
                        $cardBorderClass = $isLastRun
                            ? 'border-success border-2'
                            : ($isSelected
                                ? 'border-primary'
                                : '');
                    @endphp
                    <div class="col-12">
                        <div class="card shadow-sm {{ $cardBorderClass }}">
                            <div class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                                <div>
                                    <h2 class="h5 mb-1">{{ $seeder['name'] }}</h2>
                                    <p class="text-muted mb-0"><code>{{ $seeder['class'] }}</code></p>
                                </div>
                                <div class="text-lg-end">
                                    @if ($isLastRun)
                                        <span class="badge bg-success me-2">Last run</span>
                                    @elseif ($isSelected)
                                        <span class="badge bg-primary me-2">Selected</span>
                                    @endif
                                    @if (!empty($seeder['impact']))
                                        <span class="badge bg-info text-dark me-2">{{ $seeder['impact'] }}</span>
                                    @endif
                                    @if (!empty($seeder['estimated_runtime']))
                                        <span class="badge bg-light text-muted border">{{ $seeder['estimated_runtime'] }}</span>
                                    @endif
                                </div>
                                <form action="{{ route('tech.seeders.run') }}" method="post" onsubmit="return confirm('Run the {{ $seeder['name'] }} seeder now?');" class="ms-lg-auto">
                                    @csrf
                                    <input type="hidden" name="seeder" value="{{ $seeder['class'] }}">
                                    <button class="btn btn-success" type="submit">
                                        <i class="fa-solid fa-seedling me-1"></i>{{ $lastRunSeeder === $seeder['class'] ? 'Re-run seeder' : 'Run seeder' }}
                                    </button>
                                </form>
                            </div>
                            <div class="card-body">
                                @if (!empty($seeder['description']))
                                    <p>{{ $seeder['description'] }}</p>
                                @endif

                                <div class="row gy-4">
                                    @if (!empty($seeder['operations']))
                                        <div class="col-md-6 col-lg-4">
                                            <h3 class="h6 text-uppercase text-muted">Key operations</h3>
                                            <ul class="mb-0">
                                                @foreach ($seeder['operations'] as $operation)
                                                    <li>{{ $operation }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if (!empty($seeder['tables']))
                                        <div class="col-md-6 col-lg-4">
                                            <h3 class="h6 text-uppercase text-muted">Tables touched</h3>
                                            <ul class="mb-0">
                                                @foreach ($seeder['tables'] as $table => $explanation)
                                                    <li><strong>{{ is_string($table) ? $table : $explanation }}</strong>@if(is_string($table))<span class="d-block text-muted small">{{ $explanation }}</span>@endif</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if (!empty($seeder['recommended']) || !empty($seeder['safety']))
                                        <div class="col-md-6 col-lg-4">
                                            @if (!empty($seeder['recommended']))
                                                <h3 class="h6 text-uppercase text-muted">When to run</h3>
                                                <p class="mb-3">{{ $seeder['recommended'] }}</p>
                                            @endif
                                            @if (!empty($seeder['safety']))
                                                <h3 class="h6 text-uppercase text-muted">Safety notes</h3>
                                                <p class="mb-0">{{ $seeder['safety'] }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                @if (!empty($seeder['notes']))
                                    <hr>
                                    <h3 class="h6 text-uppercase text-muted">Additional considerations</h3>
                                    <ul class="mb-0">
                                        @foreach ($seeder['notes'] as $note)
                                            <li>{{ $note }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="card mt-4">
            <div class="card-header">
                <strong>Seeder run log</strong>
            </div>
            <div class="card-body">
                @if ($seederOutput)
                    <pre class="mb-0">{{ $seederOutput }}</pre>
                @else
                    <p class="mb-0 text-muted">Execute a seeder to capture the artisan output here.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
