@extends('layouts.app')

@section('content')
<div class="min-vh-100 py-5" style="background:#06111c;">
    <div class="container">
        <h1 class="mb-4 text-light fw-semibold">My PC vs Steam Requirements</h1>

        {{-- Flash status message (e.g. after delete) --}}
        @if (session('status'))
            <div class="alert alert-success mb-3">
                {{ session('status') }}
            </div>
        @endif

        {{-- Delete my specs button: deletes ALL FetchReq rows --}}
        @if ($mySpecs)
            <div class="d-flex justify-content-end mb-3">
                <form action="{{ route('main.destroy') }}" method="POST"
                      onsubmit="return confirm('Delete your saved specs?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        Delete my specs
                    </button>
                </form>
            </div>
        @endif

        {{-- PC specs + appId form --}}
        <div class="card card-dark mb-4">
            <div class="card-header card-dark-header text-light">
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

                <form method="POST" action="{{ route('main.store') }}" id="pc-form">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cpu_id" class="form-label text-light">CPU</label>
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
                            <label for="RAM" class="form-label text-light">RAM</label>
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
                            <label for="STORAGE" class="form-label text-light">Storage</label>
                            <input type="text"
                                   class="form-control"
                                   id="STORAGE"
                                   name="STORAGE"
                                   value="{{ old('STORAGE', optional($mySpecs)->STORAGE) }}"
                                   placeholder="e.g. 500 GB SSD">
                        </div>
                        <div class="col-md-6">
                            <label for="gpu_id" class="form-label text-light">GPU</label>
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
                        <label for="appId" class="form-label text-light">Steam App ID</label>
                        <input type="number"
                               class="form-control"
                               id="appId"
                               name="appId"
                               value="{{ old('appId', $appId) }}"
                               placeholder="e.g. 730 for CS2">
                        <div class="form-text text-muted">
                            The App ID is the number in the Steam store URL (e.g. https://store.steampowered.com/app/<strong>730</strong>/...).
                        </div>
                    </div>

                    {{-- Game cards BELOW inputs --}}
                    <div class="mb-4">
                        <h2 class="h5 text-light mb-3">Quick pick a game</h2>

                        <div class="row row-cols-1 row-cols-md-2 g-4">
                            @php
                                $games = [
                                    ['title' => 'GTA V', 'year' => 2015, 'appid' => 271590,
                                     'image' => 'https://cdn.akamai.steamstatic.com/steam/apps/271590/header.jpg'],
                                    ['title' => 'Counter-Strike 2', 'year' => 2012, 'appid' => 730,
                                     'image' => 'https://cdn.akamai.steamstatic.com/steam/apps/730/header.jpg'],
                                    ['title' => 'Elden Ring', 'year' => 2022, 'appid' => 1245620,
                                     'image' => 'https://cdn.akamai.steamstatic.com/steam/apps/1245620/header.jpg'],
                                    ['title' => 'Cyberpunk 2077', 'year' => 2020, 'appid' => 1091500,
                                     'image' => 'https://cdn.akamai.steamstatic.com/steam/apps/1091500/header.jpg'],
                                ];
                            @endphp

                            @foreach ($games as $game)
                                <div class="col">
                                    <button type="button"
                                            class="game-card btn p-0 w-100 text-start border-0"
                                            data-appid="{{ $game['appid'] }}">
                                        <div class="card card-dark h-100 overflow-hidden">
                                            <div class="position-relative">
                                                <img src="{{ $game['image'] }}"
                                                     class="w-100"
                                                     style="height: 170px; object-fit: cover;"
                                                     alt="{{ $game['title'] }} banner">
                                                <div class="position-absolute top-0 start-0 w-100 h-100"
                                                     style="background: linear-gradient(to top, rgba(15,23,42,0.9), rgba(15,23,42,0.1));">
                                                </div>
                                                <div class="position-absolute bottom-0 start-0 p-3">
                                                    <h3 class="h5 mb-1 text-light">{{ $game['title'] }}</h3>
                                                    <span class="text-muted small">{{ $game['year'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary px-4 py-2">
                        Save &amp; Show Requirements
                    </button>
                </form>
            </div>
        </div>

        @if ($mySpecs && $steamRequirements)
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card card-dark h-100">
                        <div class="card-header card-dark-header text-light">
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
                        <div class="card card-dark h-100">
                            <div class="card-header card-dark-header text-light">
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
                        <div class="card card-dark">
                            <div class="card-header card-dark-header text-light">Debug: My PC data used for comparison</div>
                            <div class="card-body">
<pre class="small bg-dark text-light p-2 border rounded">
{{ json_encode($pcDebug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
</pre>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($gameDebug)
                    <div class="col-12 mb-3">
                        <div class="card card-dark">
                            <div class="card-header card-dark-header text-light">Debug: Game data used for comparison</div>
                            <div class="card-body">
<pre class="small bg-dark text-light p-2 border rounded">
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
</div>

@push('styles')
<style>
    body {
        background: #050b12;
        color: #e5ecf5;
    }
    .card-dark {
        background: #111827;
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.25);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.7);
    }
    .card-dark-header {
        border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        background: linear-gradient(135deg, rgba(56,189,248,0.08), rgba(129,140,248,0.02));
    }
    .form-control,
    .form-select {
        background-color: #020617;
        border-color: #1f2937;
        color: #e5e7eb;
    }
    .form-control:focus,
    .form-select:focus {
        background-color: #020617;
        border-color: #38bdf8;
        box-shadow: 0 0 0 0.2rem rgba(56,189,248,0.25);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.game-card').forEach(function (card) {
            card.addEventListener('click', function () {
                const appIdInput = document.getElementById('appId');
                const appId = this.dataset.appid;

                if (appIdInput) {
                    appIdInput.value = appId;
                    appIdInput.focus();
                }
                 document.getElementById('pc-form').submit();
            });
        });
    });
</script>
@endpush
@endsection
