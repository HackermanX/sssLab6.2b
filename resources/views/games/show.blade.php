@extends('layouts.app')

@section('content')
<div class="min-vh-100 py-5" style="background:#06111c;">
    <div class="container text-light">
        <h1 class="mb-2">{{ $game->name }}</h1>
        <p class="text-muted mb-4">Release year: {{ $game->year }}</p>

        @if ($mySpecs)
            <p class="mb-4">
                Comparing against your PC:
                <strong>{{ optional($mySpecs->cpu)->name }}</strong>,
                <strong>{{ optional($mySpecs->gpu)->name }}</strong>,
                {{ $mySpecs->RAM }} RAM
            </p>
        @else
            <div class="alert alert-info mb-4">
                No PC specs in session. Go to
                <a href="{{ route('main.form') }}">PC requirements</a>
                and save your specs first.
            </div>
        @endif

        @php
            $cmp = $comparison ?? [];

            // Minimum flags from ComparePerformance::compare()
            $cpuMinOk = $cmp['cpu_min_ok'] ?? false;
            $gpuMinOk = $cmp['gpu_min_ok'] ?? false;
            $ramMinOk = $cmp['ram_min_ok'] ?? false;

            // Recommended checks computed here

            // 1) CPU recommended
            $cpuRecOk = true;
            if (isset($cmp['pc_cpu_score'], $game->rec_cpu_score) && $game->rec_cpu_score) {
                $cpuRecOk = (int) $cmp['pc_cpu_score'] >= (int) $game->rec_cpu_score;
            }

            // 2) GPU recommended
            $gpuRecOk = true;
            if (isset($cmp['pc_gpu_score'], $game->rec_gpu_score) && $game->rec_gpu_score) {
                $gpuRecOk = (int) $cmp['pc_gpu_score'] >= (int) $game->rec_gpu_score;
            }

            // 3) RAM recommended (derived from text like "8 GB RAM")
            $ramRecOk = true;
            if ($mySpecs) {
                $recRamText = data_get($game->recommended_parsed, 'memory');
                $userRamGb  = (int) $mySpecs->RAM;

                if (preg_match('/(\d+)\s*GB/i', (string) $recRamText, $m)) {
                    $recRamGb  = (int) $m[1];
                    $ramRecOk  = $userRamGb >= $recRamGb;
                }
            }
        @endphp

        {{-- Overall result --}}
        @if ($comparison)
            @if ($comparison['ok'] ?? false)
                <div class="alert alert-success mb-4">
                    Your PC meets the <strong>minimum</strong> requirements for this game.
                </div>
            @else
                <div class="alert alert-danger mb-4">
                    Your PC is <strong>below</strong> the minimum requirements for this game.
                </div>
            @endif
        @endif

        {{-- Detailed comparison table --}}
        <div class="card card-dark mb-4">
            <div class="card-header">
                Requirement comparison
            </div>
            <div class="card-body p-0">
                <table class="table table-dark table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width:20%"></th>
                            <th style="width:27%">Your PC</th>
                            <th style="width:27%">Minimum</th>
                            <th style="width:26%">Recommended</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>CPU</th>
                            <td>{{ optional($mySpecs->cpu)->name ?? '—' }}</td>
                            <td class="{{ $cpuMinOk ? '' : 'text-danger' }}">
                                {{ data_get($game->minimum_parsed, 'processor', 'N/A') }}
                            </td>
                            <td class="{{ $cpuRecOk ? '' : 'text-danger' }}">
                                {{ data_get($game->recommended_parsed, 'processor', 'N/A') }}
                            </td>
                        </tr>
                        <tr>
                            <th>GPU</th>
                            <td>{{ optional($mySpecs->gpu)->name ?? '—' }}</td>
                            <td class="{{ $gpuMinOk ? '' : 'text-danger' }}">
                                {{ data_get($game->minimum_parsed, 'graphics', 'N/A') }}
                            </td>
                            <td class="{{ $gpuRecOk ? '' : 'text-danger' }}">
                                {{ data_get($game->recommended_parsed, 'graphics', 'N/A') }}
                            </td>
                        </tr>
                        <tr>
                            <th>RAM</th>
                            <td>{{ $mySpecs->RAM ?? '—' }}</td>
                            <td class="{{ $ramMinOk ? '' : 'text-danger' }}">
                                {{ data_get($game->minimum_parsed, 'memory', 'N/A') }}
                            </td>
                            <td class="{{ $ramRecOk ? '' : 'text-danger' }}">
                                {{ data_get($game->recommended_parsed, 'memory', 'N/A') }}
                            </td>
                        </tr>
                        <tr>
                            <th>Storage</th>
                            <td>—</td>
                            <td>
                                {{ data_get($game->minimum_parsed, 'storage', 'N/A') }}
                            </td>
                            <td>
                                {{ data_get($game->recommended_parsed, 'storage', 'N/A') }}
                            </td>
                        </tr>
                        <tr>
                            <th>OS</th>
                            <td>—</td>
                            <td>{{ data_get($game->minimum_parsed, 'os', 'N/A') }}</td>
                            <td>{{ data_get($game->recommended_parsed, 'os', 'N/A') }}</td>
                        </tr>
                        <tr>
                            <th>DirectX / Other</th>
                            <td>—</td>
                            <td>
                                {{ data_get($game->minimum_parsed, 'directx') }}
                                {{ data_get($game->minimum_parsed, 'other') }}
                            </td>
                            <td>
                                {{ data_get($game->recommended_parsed, 'directx') }}
                                {{ data_get($game->recommended_parsed, 'other') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <a href="{{ route('games.index') }}" class="btn btn-secondary">
            ← Back to games
        </a>
    </div>
</div>
@endsection