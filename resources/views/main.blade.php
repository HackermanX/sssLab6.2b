@extends('layouts.app')

@section('content')
<div class="container my-5">
    <h1 class="mb-4">My PC vs Steam Requirements</h1>

    {{-- PC specs + appId form --}}
    <div class="card mb-4">
        <div class="card-header">
            Enter your PC specs and Steam App ID
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('main.store') }}">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="CPU" class="form-label">CPU</label>
                        <input type="text"
                               class="form-control"
                               id="CPU"
                               name="CPU"
                               value="{{ old('CPU', optional($mySpecs)->CPU) }}"
                               placeholder="e.g. Intel Core i5-10400">
                    </div>
                    <div class="col-md-6">
                        <label for="RAM" class="form-label">RAM</label>
                        <input type="text"
                               class="form-control"
                               id="RAM"
                               name="RAM"
                               value="{{ old('RAM', optional($mySpecs)->RAM) }}"
                               placeholder="e.g. 16 GB">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="STORAGE" class="form-label">Storage</label>
                        <input type="text"
                               class="form-control"
                               id="STORAGE"
                               name="STORAGE"
                               value="{{ old('STORAGE', optional($mySpecs)->STORAGE) }}"
                               placeholder="e.g. 500 GB SSD">
                    </div>
                    <div class="col-md-6">
                        <label for="GPU" class="form-label">GPU</label>
                        <input type="text"
                               class="form-control"
                               id="GPU"
                               name="GPU"
                               value="{{ old('GPU', optional($mySpecs)->GPU) }}"
                               placeholder="e.g. NVIDIA GTX 1660">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="appId" class="form-label">Steam App ID</label>
                    <input type="number"
                           class="form-control"
                           id="appId"
                           name="appId"
                           value="{{ old('appId', $appId) }}"
                           placeholder="e.g. 730 for CS2">
                    <div class="form-text">
                        The App ID is the number in the Steam store URL (e.g. https://store.steampowered.com/app/<strong>730</strong>/...). [web:17]
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Save & Show Requirements
                </button>
            </form>
        </div>
    </div>

    {{-- Printed specs and Steam requirements --}}
    @if ($mySpecs && $steamRequirements)
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        My PC Specs
                    </div>
                    <div class="card-body">
                        <p><strong>CPU:</strong> {{ $mySpecs->CPU }}</p>
                        <p><strong>RAM:</strong> {{ $mySpecs->RAM }}</p>
                        <p><strong>Storage:</strong> {{ $mySpecs->STORAGE }}</p>
                        <p><strong>GPU:</strong> {{ $mySpecs->GPU }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        Steam Requirements (raw)
                    </div>
                    <div class="card-body">
                        {{-- Adjust to your getRequirements() structure --}}
                        <pre class="small bg-light p-2 border rounded">
{{ json_encode($steamRequirements, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                        </pre>
                    </div>
                </div>
            </div>
        </div>
    @elseif($mySpecs)
        <div class="alert alert-info">
            Your specs are saved, but Steam requirements are not loaded yet. Enter a Steam App ID and submit the form. [web:17]
        </div>
    @endif
</div>
@endsection
