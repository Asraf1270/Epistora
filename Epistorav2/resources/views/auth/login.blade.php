@extends('layouts.app')

@section('content')
    <div class="card">
        <h2>Login</h2>
        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
            @error('email') <div class="error">{{ $message }}</div> @enderror

            <label>Password</label>
            <input type="password" name="password" required>
            @error('password') <div class="error">{{ $message }}</div> @enderror

            <button type="submit">Sign in</button>
        </form>
    </div>
@endsection
