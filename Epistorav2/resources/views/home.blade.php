@extends('layouts.app')

@section('content')
    <div class="card">
        <h1>Epistorav2 Migration Bootstrap</h1>
        @auth
            <p>Signed in as {{ auth()->user()->name }} ({{ auth()->user()->role ?? 'user' }}).</p>
        @else
            <p>This is the first converted Laravel home route.</p>
        @endauth
    </div>
@endsection
