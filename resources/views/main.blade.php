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
                        <label for="cpu_id" class="form-label">CPU</label>
                        <select class="form-select" id="cpu_id" name="cpu_id">
                            <option value="">-- Select CPU --</option>
                            @foreach ($cpus as $cpu)
                                <option
                                    value="{{ $cpu->id }}"
                                    @selected(old('cpu_id', optional($mySpecs)->cpu_id) == $cpu->id)>
                                    {{ $cpu->name }} (score: {{ $cpu->score }})
                                </option>
                            @endforeach
                        </select>
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
                                <label for="gpu_id" class="form-label">GPU</label>
                                <select class="form-select" id="gpu_id" name="gpu_id">
                                    <option value="">-- Select GPU --</option>
                                    @foreach ($gpus as $gpu)
                                        <option
                                            value="{{ $gpu->id }}"
                                            @selected(old('gpu_id', optional($mySpecs)->gpu_id) == $gpu->id)>
                                            {{ $gpu->name }} (score: {{ $gpu->score }})
                                        </option>
                                    @endforeach
                                </select>
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
                        The App ID is the number in the Steam store URL (e.g. https://store.steampowered.com/app/<strong>730</strong>/...).
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Save & Show Requirements
                </button>
            </form>
        </div>
    </div>

    @if ($mySpecs && $steamRequirements)
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        My PC Specs
                    </div>
                    <div class="card-body">
                        <p><strong>CPU:</strong> {{ optional($mySpecs->cpu)->name ?? $mySpecs->CPU }}</p>
                        <p><strong>RAM:</strong> {{ $mySpecs->RAM }}</p>
                        <p><strong>Storage:</strong> {{ $mySpecs->STORAGE }}</p>
                        <p><strong>GPU:</strong> {{ optional($mySpecs->gpu)->name ?? $mySpecs->GPU }}</p>
                    </div>
                </div>
            </div>
            @if (!empty($comparison))
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            Comparison vs minimum requirements
                        </div>
                        <div class="card-body">
                            <p>
                                Overall:
                                @if ($comparison['ok'])
                                    <span class="text-success">Your PC meets the minimum requirements.</span>
                                @else
                                    <span class="text-danger">Your PC does not meet the minimum requirements.</span>
                                @endif
                            </p>
                            <ul class="list-unstyled">
                                <li>
                                    CPU: {{ $comparison['cpu']['pc'] }} vs required {{ $comparison['cpu']['req'] }}
                                    @if ($comparison['cpu']['ok'])
                                        <span class="text-success">(OK)</span>
                                    @else
                                        <span class="text-danger">(Too weak)</span>
                                    @endif
                                </li>
                                <li>
                                    GPU: {{ $comparison['gpu']['pc'] }} vs required {{ $comparison['gpu']['req'] }}
                                    @if ($comparison['gpu']['ok'])
                                        <span class="text-success">(OK)</span>
                                    @else
                                        <span class="text-danger">(Too weak)</span>
                                    @endif
                                </li>
                                <li>
                                    RAM: {{ $comparison['ram']['pc'] }} GB vs required {{ $comparison['ram']['req'] }} GB
                                    @if ($comparison['ram']['ok'])
                                        <span class="text-success">(OK)</span>
                                    @else
                                        <span class="text-danger">(Too low)</span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            @if ($pcDebug)
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-header">Debug: My PC data used for comparison</div>
                        <div class="card-body">
            <pre class="small bg-light p-2 border rounded">
            {{ json_encode($pcDebug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
            </pre>
                        </div>
                    </div>
                </div>
            @endif

            @if ($gameDebug)
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-header">Debug: Game data used for comparison</div>
                        <div class="card-body">
            <pre class="small bg-light p-2 border rounded">
            {{ json_encode($gameDebug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
            </pre>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @elseif($mySpecs)
        <div class="alert alert-info">
            Your specs are saved, but Steam requirements are not loaded yet. Enter a Steam App ID and submit the form.
        </div>
    @endif
</div>
@endsection
