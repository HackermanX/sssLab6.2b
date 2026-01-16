<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PC vs Game Requirements</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap 5 CSS --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >

    <style>
        body {
            background: #020712;
            color: #f8f9fa;
        }
        .navbar-dark.bg-dark {
            background: #050915 !important;
        }
        .card-dark {
            background-color: #0b1622;
            border-color: #182635;
        }

        label,
        .form-label,
        .form-text,
        .form-control,
        .form-select {
            color: #f8f9fa;
        }

        .form-control,
        .form-select {
            background-color: #04101d;
            border-color: #27364a;
        }

        .form-control::placeholder {
            color: #adb5bd;
        }

        .card-header,
        .card-body,
        .table {
            color: #f8f9fa;
        }

        .text-muted {
            color: #ced4da !important;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('games.index') }}">
            PC Game Compatibility
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNavbar" aria-controls="mainNavbar"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('games.index') ? 'active' : '' }}"
                       href="{{ route('games.index') }}">
                        Games
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('main.form') ? 'active' : '' }}"
                       href="{{ route('main.form') }}">
                        My PC specs
                    </a>
                </li>
            </ul>
            <span class="navbar-text">
                Steam requirements checker
            </span>
        </div>
    </div>
</nav>

<div class="container my-4">
    @yield('content')
</div>

{{-- Bootstrap 5 JS bundle --}}
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"
></script>
</body>
</html>