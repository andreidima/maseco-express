@php($showSessionAlerts = $showSessionAlerts ?? true)

@if ($errors->any())
    <div class="alert alert-danger mb-0" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{  $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($showSessionAlerts && (session()->has('status') || session()->has('success')))
    <div class="alert alert-success">
        {{ session('status') }}
        {{ session('success') }}
    </div>
@elseif ($showSessionAlerts && (session()->has('eroare') || session()->has('error')))
    <div class="alert alert-danger">
        {{ session('eroare') }}
        {{ session('error') }}
    </div>
@elseif ($showSessionAlerts && (session()->has('atentionare') || session()->has('warning')))
    <div class="alert alert-warning">
        {{ session('atentionare') }}
        {{ session('warning') }}
    </div>
@endif
