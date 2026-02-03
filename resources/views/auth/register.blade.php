@extends('layouts.app')

@section('content')
<section class="hero">
    <div class="hero-grid">
        <div>
            <div class="eyebrow">Krijo llogari</div>
            <h1>Regjistrohu për monitorim më të mirë</h1>
            <p>Ndihmo në identifikimin e ndotjes me evidencë vizuale.</p>
        </div>
        <div class="card">
            @if ($errors->any())
                <div class="alert">
                    {{ $errors->first() }}
                </div>
            @endif
            <form class="form" method="POST" action="{{ route('register.submit') }}">
                @csrf
                <div class="field">
                    <label for="name">Emri</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>
                <div class="field">
                    <label for="password">Fjalëkalimi</label>
                    <input id="password" name="password" type="password" required>
                </div>
                <div class="field">
                    <label for="password_confirmation">Konfirmo fjalëkalimin</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required>
                </div>
                <button class="btn btn-primary" type="submit">Regjistrohu</button>
                <a class="btn btn-ghost" href="{{ route('login') }}">Kyçu</a>
            </form>
        </div>
    </div>
</section>
@endsection
