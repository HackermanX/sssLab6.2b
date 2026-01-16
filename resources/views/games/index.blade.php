@extends('layouts.app')

@section('content')
<div class="min-vh-100 py-5" style="background:#06111c;">
    <div class="container text-light">
        <h1 class="mb-4">Games browser</h1>

        @if ($mySpecs)
            <p class="mb-3">
                Comparing against your current PC:
                <strong>{{ optional($mySpecs->cpu)->name }}</strong>,
                <strong>{{ optional($mySpecs->gpu)->name }}</strong>,
                {{ $mySpecs->RAM }} RAM
            </p>
        @else
            <div class="alert alert-info">
                No PC specs in session. Go to
                <a href="{{ route('main.form') }}">PC requirements</a>
                and save your specs first.
            </div>
        @endif

        <form method="GET" action="{{ route('games.index') }}" class="card card-dark mb-4 p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search name</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Year from</label>
                    <input type="number" name="year_from" value="{{ request('year_from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Year to</label>
                    <input type="number" name="year_to" value="{{ request('year_to') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sort by</label>
                    <select name="sort" class="form-select">
                        <option value="name"          @selected(request('sort','name')==='name')>Name</option>
                        <option value="year"          @selected(request('sort')==='year')>Year</option>
                        <option value="min_cpu_score" @selected(request('sort')==='min_cpu_score')>CPU score</option>
                        <option value="min_gpu_score" @selected(request('sort')==='min_gpu_score')>GPU score</option>
                        <option value="min_ram_gb"    @selected(request('sort')==='min_ram_gb')>RAM (GB)</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Dir</label>
                    <select name="dir" class="form-select">
                        <option value="asc"  @selected(request('dir','asc')==='asc')>↑</option>
                        <option value="desc" @selected(request('dir')==='desc')>↓</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary w-100" type="submit">Apply</button>
                </div>
            </div>
        </form>

        @php
            $featured = [
                271590 => [
                    'title' => 'GTA V',
                    'year'  => 2015,
                    'image' => 'https://cdn.akamai.steamstatic.com/steam/apps/271590/header.jpg',
                ],
                730 => [
                    'title' => 'Counter-Strike 2',
                    'year'  => 2012,
                    'image' => 'https://cdn.akamai.steamstatic.com/steam/apps/730/header.jpg',
                ],
                1245620 => [
                    'title' => 'Elden Ring',
                    'year'  => 2022,
                    'image' => 'https://cdn.akamai.steamstatic.com/steam/apps/1245620/header.jpg',
                ],
                1091500 => [
                    'title' => 'Cyberpunk 2077',
                    'year'  => 2020,
                    'image' => 'https://cdn.akamai.steamstatic.com/steam/apps/1091500/header.jpg',
                ],
            ];
        @endphp

        <div class="row row-cols-1 row-cols-md-2 g-4">
            @php $hasFeatured = false; @endphp

            @foreach ($games as $game)
                @php $hasFeatured = true; @endphp

                @php
                    $cmp    = $comparisons[$game->id] ?? null;
                    $ok     = $cmp['ok'] ?? false;
                    $border = $mySpecs ? ($ok ? 'border-success' : 'border-danger') : '';
                        $data = $featured[$game->appid] ?? [
                            'title' => $game->name,
                            'year'  => $game->year,
                            'image' => null,
                        ];
                @endphp

                <div class="col">
                    <a href="{{ route('games.show', ['game' => $game->id]) }}" class="text-decoration-none">
                        <div class="card card-dark h-100 overflow-hidden {{ $border }}">
                            <div class="position-relative">
                                <img src="{{ $data['image'] }}"
                                     class="w-100"
                                     style="height: 180px; object-fit: cover;"
                                     alt="{{ $data['title'] }} banner">
                                <div class="position-absolute top-0 start-0 w-100 h-100"
                                     style="background: linear-gradient(to top, rgba(15,23,42,0.9), rgba(15,23,42,0.1));">
                                </div>
                                <div class="position-absolute bottom-0 start-0 p-3">
                                    <h3 class="h5 mb-1 text-light">{{ $data['title'] }}</h3>
                                    <span class="text-muted small">{{ $data['year'] }}</span>
                                </div>
                            </div>
                            @if ($mySpecs && $cmp)
                                <div class="card-body small">
                                    @if ($ok)
                                        <span class="text-success">You can probably run this.</span>
                                    @else
                                        <span class="text-danger">Your PC is below minimum.</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </a>
                </div>
            @endforeach

            @if (! $hasFeatured)
                <div class="col-12">
                    <div class="alert alert-info">
                        None of the featured games (GTA V, CS2, Elden Ring, Cyberpunk) are in the current filter.
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection