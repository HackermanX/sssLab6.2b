@extends('layouts.app')

@section('content')
<div class="min-vh-100 py-5" style="background:#06111c;">
    <div class="container text-light">
        <a href="{{ route('games.index') }}" class="text-decoration-none mb-3 d-inline-block">&larr; Back to games</a>

        <h1 class="mb-2">{{ $game->name }}</h1>
        <p class="text-muted mb-4">Year: {{ $game->year ?? 'n/a' }}</p>

        @if ($mySpecs && $comparison)
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card card-dark">
                        <div class="card-header card-dark-header text-light">
                            My PC vs this game
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
                                </li>
                                <li>
                                    GPU: {{ $comparison['gpu']['pc'] }} vs required {{ $comparison['gpu']['req'] }}
                                </li>
                                <li>
                                    RAM: {{ $comparison['ram']['pc'] }} GB vs required {{ $comparison['ram']['req'] }} GB
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card card-dark">
                        <div class="card-header card-dark-header text-light">
                            My PC Specs
                        </div>
                        <div class="card-body">
                            <p><strong>CPU:</strong> {{ optional($mySpecs->cpu)->name ?? $mySpecs->CPU }}</p>
                            <p><strong>GPU:</strong> {{ optional($mySpecs->gpu)->name ?? $mySpecs->GPU }}</p>
                            <p><strong>RAM:</strong> {{ $mySpecs->RAM }}</p>
                            <p><strong>Storage:</strong> {{ $mySpecs->STORAGE }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info mb-4">
                To compare this game with your PC, first <a href="{{ route('main.form') }}">enter your specs</a>.
            </div>
        @endif

        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card card-dark">
                    <div class="card-header card-dark-header text-light">Minimum requirements</div>
                    <div class="card-body small">
                        @php $min = $game->minimum_parsed ?? []; @endphp
                        <p><strong>OS:</strong> {{ $min['os'] ?? 'n/a' }}</p>
                        <p><strong>Processor:</strong> {{ $min['processor'] ?? 'n/a' }}</p>
                        <p><strong>Memory:</strong> {{ $min['memory'] ?? 'n/a' }}</p>
                        <p><strong>Graphics:</strong> {{ $min['graphics'] ?? 'n/a' }}</p>
                        <p><strong>DirectX:</strong> {{ $min['directx'] ?? 'n/a' }}</p>
                        <p><strong>Storage:</strong> {{ $min['storage'] ?? 'n/a' }}</p>
                        @if (!empty($min['other']))
                            <p><strong>Other:</strong> {{ $min['other'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card card-dark">
                    <div class="card-header card-dark-header text-light">Recommended requirements</div>
                    <div class="card-body small">
                        @php $rec = $game->recommended_parsed ?? []; @endphp
                        @if ($rec)
                            <p><strong>OS:</strong> {{ $rec['os'] ?? 'n/a' }}</p>
                            <p><strong>Processor:</strong> {{ $rec['processor'] ?? 'n/a' }}</p>
                            <p><strong>Memory:</strong> {{ $rec['memory'] ?? 'n/a' }}</p>
                            <p><strong>Graphics:</strong> {{ $rec['graphics'] ?? 'n/a' }}</p>
                            <p><strong>DirectX:</strong> {{ $rec['directx'] ?? 'n/a' }}</p>
                            <p><strong>Storage:</strong> {{ $rec['storage'] ?? 'n/a' }}</p>
                            @if (!empty($rec['other']))
                                <p><strong>Other:</strong> {{ $rec['other'] }}</p>
                            @endif
                        @else
                            <p>Recommended requirements not available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection