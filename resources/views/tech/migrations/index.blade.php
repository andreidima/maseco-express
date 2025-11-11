@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Migration Center</h1>
            <div class="d-flex gap-2">
                <form action="{{ route('tech.migrations.preview') }}" method="post">
                    @csrf
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="fa-solid fa-eye me-1"></i>Preview migrations
                    </button>
                </form>
                <form action="{{ route('tech.migrations.run') }}" method="post" onsubmit="return confirm('Run the pending migrations now?');">
                    @csrf
                    <button class="btn btn-danger" type="submit">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>Run migrations
                    </button>
                </form>
                <form action="{{ route('tech.migrations.seed') }}" method="post" onsubmit="return confirm('Run the roles seeder now?');">
                    @csrf
                    <button class="btn btn-outline-success" type="submit">
                        <i class="fa-solid fa-seedling me-1"></i>Run roles seeder
                    </button>
                </form>
            </div>
        </div>

        @if ($statusMessage)
            <div class="alert alert-{{ $statusLevel }}">
                {{ $statusMessage }}
            </div>
        @endif

        @if ($seederStatus)
            <div class="alert alert-{{ $seederStatusLevel }}">
                {{ $seederStatus }}
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <strong>Preview output</strong>
            </div>
            <div class="card-body">
                @if ($previewOutput)
                    <pre class="mb-0">{{ $previewOutput }}</pre>
                @else
                    <p class="mb-0 text-muted">Generate a preview to see which SQL statements will be executed.</p>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <strong>Execution log</strong>
            </div>
            <div class="card-body">
                @if ($executionOutput)
                    <pre class="mb-0">{{ $executionOutput }}</pre>
                @else
                    <p class="mb-0 text-muted">Run the migrations to capture the artisan output.</p>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <strong>Seeder log</strong>
            </div>
            <div class="card-body">
                @if ($seederOutput)
                    <pre class="mb-0">{{ $seederOutput }}</pre>
                @else
                    <p class="mb-0 text-muted">Execute the seeder to capture the artisan output.</p>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark">
                        <strong>Pending migrations</strong>
                    </div>
                    <div class="card-body">
                        @if (count($pendingMigrations))
                            <ul class="mb-0">
                                @foreach ($pendingMigrations as $migration)
                                    <li><code>{{ $migration }}</code></li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mb-0 text-muted">No pending migrations.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <strong>Executed migrations</strong>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">Batch</th>
                                        <th scope="col">Migration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($executedMigrations as $migration)
                                        <tr>
                                            <td class="align-middle">{{ $migration->batch }}</td>
                                            <td class="align-middle"><code>{{ $migration->migration }}</code></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-muted text-center py-3">No migrations have been executed yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
