<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Epistorav2' }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; color: #1f2937; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
        .error { color: #b91c1c; font-size: 0.9rem; }
        input { width: 100%; padding: 0.6rem; margin-top: 0.4rem; margin-bottom: 0.8rem; }
        button { padding: 0.6rem 1rem; cursor: pointer; }
        nav a { margin-right: 1rem; }
    </style>
</head>
<body>
<div class="container">
    <nav>
        <a href="{{ route('home') }}">Home</a>
        @auth
            <a href="{{ route('writer.dashboard') }}">Dashboard</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Register</a>
        @endauth
    </nav>
    <hr>
    @yield('content')
</div>
</body>
</html>
