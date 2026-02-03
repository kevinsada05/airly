@extends('layouts.app')

@section('content')
<section class="hero">
    <div class="hero-grid">
        <div>
            <div class="eyebrow">AIRLY</div>
            <h1>Kyçu në platformë</h1>
            <p>Menaxho ngarkimet, shiko analizat dhe historikun e zonave.</p>
        </div>
        <div class="card">
            @if ($errors->any())
                <div class="alert">
                    {{ $errors->first() }}
                </div>
            @endif
            <form class="form" method="POST" action="{{ route('login.submit') }}">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>
                <div class="field">
                    <label for="password">Fjalëkalimi</label>
                    <input id="password" name="password" type="password" required>
                </div>
                <div class="field">
                    <label>
                        <input type="checkbox" name="remember"> Më mbaj mend
                    </label>
                </div>
                <button class="btn btn-primary" type="submit">Kyçu</button>
                <a class="btn btn-ghost" href="{{ route('register') }}">Regjistrohu</a>
            </form>
        </div>
    </div>
</section>
@endsection
