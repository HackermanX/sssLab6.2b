@extends('layouts.app')

@section('content')
<div class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0 text-light fw-semibold">My PC vs Steam Requirements</h1>
    </div>

    <div class="card mb-4 bg-dark border-0 shadow-lg rounded-3">
        <div class="card-header bg-dark border-0 text-light fw-semibold">
            Enter your PC specs, then click a game card to compare
        </div>
        <div class="card-body text-light">
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
                        <label for="cpu_id" class="form-label">CPU</label>
                        <select class="form-select bg-dark text-light border-secondary" id="cpu_id" name="cpu_id">
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
                               class="form-control bg-dark text-light border-secondary"
                               id="RAM"
                               name="RAM"
                               value="{{ old('RAM', optional($mySpecs)->RAM) }}"
                               placeholder="e.g. 16 GB">
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-6">
                        <label for="STORAGE" class="form-label">Storage</label>
                        <input type="text"
                               class="form-control bg-dark text-light border-secondary"
                               id="STORAGE"
                               name="STORAGE"
                               value="{{ old('STORAGE', optional($mySpecs)->STORAGE) }}"
                               placeholder="e.g. 500 GB SSD">
                    </div>
                    <div class="col-md-6">
                        <label for="gpu_id" class="form-label">GPU</label>
                        <select class="form-select bg-dark text-light border-secondary" id="gpu_id" name="gpu_id">
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

                <input type="hidden" name="appId" id="appId">
                <small class="text-muted">Your specs are saved automatically when you click a game.</small>
            </form>
        </div>
    </div>

    @php
        $sampleGames = [
            [
                'title' => 'GTA V',
                'year'  => 2015,
                'rating'=> 3.9,
                'image' => asset('img/games/gta5-placeholder.jpg'),
                'appid' => 271590,
            ],
            [
                'title' => 'Cyberpunk 2077',
                'year'  => 2020,
                'rating'=> 3.7,
                'image' => asset('img/games/cyberpunk-placeholder.jpg'),
                'appid' => 1091500,
            ],
            [
                'title' => 'Red Dead Redemption 2',
                'year'  => 2019,
                'rating'=> 4.3,
                'image' => asset('img/games/rdr2-placeholder.jpg'),
                'appid' => 1174180,
            ],
            [
                'title' => 'Counter‑Strike 2',
                'year'  => 2012,
                'rating'=> 3.4,
                'image' => asset('img/games/cs2-placeholder.jpg'),
                'appid' => 730,
            ],
        ];
    @endphp

    <div class="mb-2 text-light fw-semibold">Popular games</div>
    <div class="row g-4 mb-5">
        @foreach ($sampleGames as $game)
            <div class="col-md-3 col-sm-6">
                <button type="button"
                        class="game-card selectable-game btn p-0 w-100 border-0 text-start"
                        data-appid="{{ $game['appid'] }}">
                    <div class="game-card-image"
                         style="background-image: url('{{ $game['image'] }}')"></div>
                    <div class="game-card-overlay"></div>
                    <div class="game-card-content">
                        <h5 class="game-card-title">{{ $game['title'] }}</h5>
                        <div class="game-card-meta">
                            <span class="game-card-year">{{ $game['year'] }}</span>
                            <span class="game-card-rating">
                                <i class="bi bi-star-fill me-1"></i>{{ number_format($game['rating'], 1) }}
                            </span>
                        </div>
                    </div>
                </button>
            </div>
        @endforeach
    </div>

    @if ($mySpecs && $steamRequirements)
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100 bg-dark border-0 shadow-lg rounded-3">
                    <div class="card-header bg-dark border-0 text-light">
                        My PC Specs
                    </div>
                    <div class="card-body text-light">
                        <p><strong>CPU:</strong> {{ optional($mySpecs->cpu)->name ?? $mySpecs->CPU }}</p>
                        <p><strong>RAM:</strong> {{ $mySpecs->RAM }}</p>
                        <p><strong>Storage:</strong> {{ $mySpecs->STORAGE }}</p>
                        <p><strong>GPU:</strong> {{ optional($mySpecs->gpu)->name ?? $mySpecs->GPU }}</p>
                    </div>
                </div>
            </div>

            @if (!empty($comparison))
                <div class="col-md-6 mb-4">
                    <div class="card h-100 bg-dark border-0 shadow-lg rounded-3">
                        <div class="card-header bg-dark border-0 text-light">
                            Comparison vs minimum requirements
                        </div>
                        <div class="card-body text-light">
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
        </div>
    @elseif($mySpecs)
        <div class="alert alert-info bg-secondary border-0 text-light">
            Your specs are saved, but Steam requirements are not loaded yet. Click a game card to run a comparison.
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form       = document.getElementById('pc-form');
    const appIdInput = document.getElementById('appId');

    if (!form || !appIdInput) return;

    document.querySelectorAll('.selectable-game').forEach(card => {
        card.addEventListener('click', function () {
            const appId = this.dataset.appid;
            if (!appId) return;

            appIdInput.value = appId;
            form.submit();
        });
    });
});
</script>
@endpush
