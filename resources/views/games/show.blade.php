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
                {{ $mySpecs->RAM }} RAM,
                {{ $mySpecs->STORAGE }} storage
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

            $cpuMinOk     = $cmp['cpu_min_ok']     ?? false;
            $gpuMinOk     = $cmp['gpu_min_ok']     ?? false;
            $ramMinOk     = $cmp['ram_min_ok']     ?? false;
            $storageMinOk = $cmp['storage_min_ok'] ?? false;

            $cpuRecOk     = $cmp['cpu_rec_ok']     ?? true;
            $gpuRecOk     = $cmp['gpu_rec_ok']     ?? true;
            $ramRecOk     = $cmp['ram_rec_ok']     ?? true;
            $storageRecOk = $cmp['storage_rec_ok'] ?? true;
        @endphp

        @php
            $allRecOk = $cpuRecOk && $gpuRecOk && $ramRecOk && $storageRecOk;
            $allMinOk = $cpuMinOk && $gpuMinOk && $ramMinOk && $storageMinOk;
        @endphp

        @if ($comparison)
            @if ($allRecOk)
                <div class="alert alert-success mb-4">
                    Your PC meets the <strong>recommended</strong> requirements for this game.
                </div>
            @elseif ($allMinOk)
                <div class="alert alert-success mb-4">
                    Your PC meets the <strong>minimum</strong> requirements for this game.
                </div>
            @else
                <div class="alert alert-danger mb-4">
                    Your PC is <strong>below</strong> the minimum requirements for this game.
                </div>
            @endif
        @endif

        <!-- {{-- Debug block (optional) --}}
        {{-- 
        @if ($comparison)
            <pre class="bg-dark text-light p-3 mt-3">
            {{ json_encode([
                'pc' => [
                    'cpu_score'     => $comparison['pc_cpu_score']    ?? null,
                    'gpu_score'     => $comparison['pc_gpu_score']    ?? null,
                    'ram_gb'        => $comparison['pc_ram_gb']       ?? null,
                    'storage_gb'    => $comparison['pc_storage_gb']   ?? null,
                ],
                'min' => [
                    'cpu_score'     => $game->min_cpu_score      ?? null,
                    'gpu_score'     => $game->min_gpu_score      ?? null,
                    'ram_gb'        => $game->min_ram_gb         ?? null,
                    'storage_gb'    => $game->min_storage_gb     ?? null,
                ],
                'rec' => [
                    'cpu_score'     => $game->rec_cpu_score      ?? null,
                    'gpu_score'     => $game->rec_gpu_score      ?? null,
                    'ram_gb'        => $game->rec_ram_gb         ?? null,
                    'storage_gb'    => $game->rec_storage_gb     ?? null,
                ],
                'flags' => $comparison,
            ], JSON_PRETTY_PRINT) }}
            </pre>
        @endif
        --}} -->
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
                            <td @class(['', 'text-danger' => ! $cpuMinOk])>
                                {{ data_get($game->minimum_parsed, 'processor', 'N/A') }}
                            </td>
                            <td @class(['', 'text-danger' => ! $cpuRecOk])>
                                {{ data_get($game->recommended_parsed, 'processor', 'N/A') }}
                            </td>
                        </tr>

                        <tr>
                            <th>GPU</th>
                            <td>{{ optional($mySpecs->gpu)->name ?? '—' }}</td>
                            <td @class(['', 'text-danger' => ! $gpuMinOk])>
                                {{ data_get($game->minimum_parsed, 'graphics', 'N/A') }}
                            </td>
                            <td @class(['', 'text-danger' => ! $gpuRecOk])>
                                {{ data_get($game->recommended_parsed, 'graphics', 'N/A') }}
                            </td>
                        </tr>

                        <tr>
                            <th>RAM</th>
                            <td>{{ $mySpecs->RAM ?? '—' }}</td>
                            <td @class(['', 'text-danger' => ! $ramMinOk])>
                                {{ data_get($game->minimum_parsed, 'memory', 'N/A') }}
                            </td>
                            <td @class(['', 'text-danger' => ! $ramRecOk])>
                                {{ data_get($game->recommended_parsed, 'memory', 'N/A') }}
                            </td>
                        </tr>

                        <tr>
                            <th>Storage</th>
                            <td>{{ $mySpecs->STORAGE ?? '—' }}</td>
                            <td @class(['', 'text-danger' => ! $storageMinOk])>
                                {{ data_get($game->minimum_parsed, 'storage', 'N/A') }}
                            </td>
                            <td @class(['', 'text-danger' => ! $storageRecOk])>
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