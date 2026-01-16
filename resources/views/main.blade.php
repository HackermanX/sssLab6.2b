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

                    <!-- <div class="mb-3">
                        <label for="appId" class="form-label text-light">Steam App ID (any game)</label>
                        <input type="number"
                               class="form-control"
                               id="appId"
                               name="appId"
                               value="{{ old('appId', $appId) }}"
                               placeholder="e.g. 730 for CS2">
                        <div class="form-text text-muted">
                            Used to validate against the Steam API so your ID actually points to a game with PC requirements. [web:144]
                        </div>
                    </div> -->

                    <button type="submit" class="btn btn-primary px-4 py-2">
                        Save specs &amp; continue to games
                    </button>
                </form>
            </div>
        </div>

        @if($mySpecs && !session('status') && !$errors->any())
            <div class="alert alert-info">
                Your specs are saved. You can also browse games at
                <a href="{{ route('games.index') }}">the games page</a>.
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

@endsection