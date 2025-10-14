@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Seeder Center</h1>
        </div>

        @if ($statusMessage)
            <div class="alert alert-{{ $statusLevel }}">
                {{ $statusMessage }}
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <strong>Available seeders</strong>
            </div>
            <div class="card-body">
                @if ($availableSeeders->isNotEmpty())
                    <form action="{{ route('tech.seeders.run') }}" method="post" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-lg-6">
                            <label for="seeder" class="form-label">Seeder class</label>
                            <select name="seeder" id="seeder" class="form-select">
                                <option value="" data-description="{{ $defaultSeederDescription }}" @selected(empty($selectedSeeder))>
                                    DatabaseSeeder (default)
                                </option>
                                @foreach ($availableSeeders as $seeder)
                                    <option value="{{ $seeder['class'] }}" data-description="{{ $seeder['description'] }}" @selected($selectedSeeder === $seeder['class'])>
                                        {{ $seeder['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('seeder')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Seeder overview</label>
                            <div class="alert alert-info mb-0" id="seeder-description">
                                {{ $selectedSeederDescription ?? $defaultSeederDescription }}
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-seedling me-1"></i>Run seeder
                            </button>
                        </div>
                    </form>
                @else
                    <p class="mb-0 text-muted">No seeders were detected in <code>database/seeders</code>.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <strong>Execution output</strong>
            </div>
            <div class="card-body">
                @if ($executionOutput)
                    <pre class="mb-0">{{ $executionOutput }}</pre>
                @else
                    <p class="mb-0 text-muted">Run a seeder to capture the artisan output.</p>
                @endif
            </div>
        </div>
    </div>

    <script>
        (function () {
            const select = document.getElementById('seeder');
            const descriptionBox = document.getElementById('seeder-description');
            const fallbackDescription = @json($defaultSeederDescription);

            if (!select || !descriptionBox) {
                return;
            }

            const updateDescription = () => {
                const option = select.options[select.selectedIndex];
                const description = option ? option.getAttribute('data-description') : '';

                descriptionBox.textContent = description && description.trim() !== ''
                    ? description
                    : fallbackDescription;
            };

            select.addEventListener('change', updateDescription);
            updateDescription();
        })();
    </script>
@endsection
