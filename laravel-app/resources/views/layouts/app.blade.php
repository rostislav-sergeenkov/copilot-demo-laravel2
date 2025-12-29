<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Expense Tracker')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body>
    <!-- Navigation Header -->
    <header class="app-header">
        <div class="header-content">
            <a href="{{ route('expenses.index') }}" class="app-logo">
                <span class="material-icons">account_balance_wallet</span>
                <span class="app-title">Expense Tracker</span>
            </a>
            <nav class="header-nav">
                <a href="{{ route('expenses.index') }}"
                    class="nav-link {{ request()->routeIs('expenses.index') ? 'active' : '' }}">
                    <span class="material-icons">list</span>
                    <span class="nav-text">All</span>
                </a>
                <a href="{{ route('expenses.daily') }}"
                    class="nav-link {{ request()->routeIs('expenses.daily') ? 'active' : '' }}">
                    <span class="material-icons">today</span>
                    <span class="nav-text">Daily</span>
                </a>
                <a href="{{ route('expenses.monthly') }}"
                    class="nav-link {{ request()->routeIs('expenses.monthly') ? 'active' : '' }}">
                    <span class="material-icons">calendar_month</span>
                    <span class="nav-text">Monthly</span>
                </a>
                @auth
                <form method="POST" action="{{ route('logout') }}" class="nav-logout">
                    @csrf
                    <button type="submit" class="nav-link logout-button">
                        <span class="material-icons">logout</span>
                        <span class="nav-text">Logout</span>
                    </button>
                </form>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Flash Messages / Snackbar -->
    @if(session('success'))
    <div class="snackbar snackbar-success" id="snackbar">
        <span class="material-icons">check_circle</span>
        <span class="snackbar-message">{{ session('success') }}</span>
        <button type="button" class="snackbar-close" onclick="dismissSnackbar()">
            <span class="material-icons">close</span>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="snackbar snackbar-error" id="snackbar">
        <span class="material-icons">error</span>
        <span class="snackbar-message">{{ session('error') }}</span>
        <button type="button" class="snackbar-close" onclick="dismissSnackbar()">
            <span class="material-icons">close</span>
        </button>
    </div>
    @endif

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="app-footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} Expense Tracker. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>

</html>