@extends('layouts.app')

@section('content')
<section class="section">
    <div class="split">
        <div>
            <div class="eyebrow">📤 Ngarko imazh</div>
            <h2>Shtoni imazh të ri</h2>
            <p class="lead">Ngarkoni JPG/PNG dhe vendosni koordinatat e sakta.</p>
        </div>
        <div style="text-align: right;">
            <a class="btn btn-ghost" href="{{ route('uploads.index') }}">Kthehu te lista</a>
        </div>
    </div>

    <div class="card" style="margin-top: 18px;">
        @if ($errors->any())
            <div class="alert">
                {{ $errors->first() }}
            </div>
        @endif
        <form class="form" method="POST" action="{{ route('uploads.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="field">
                <label for="image">Imazhi (JPG/PNG)</label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png" required>
            </div>
            <div class="field">
                <label for="lat">Gjerësia gjeografike (lat)</label>
                <input id="lat" name="lat" type="number" step="0.000001" value="{{ old('lat') }}" required>
            </div>
            <div class="field">
                <label for="lng">Gjatësia gjeografike (lng)</label>
                <input id="lng" name="lng" type="number" step="0.000001" value="{{ old('lng') }}" required>
            </div>
            <div class="field">
                <label for="note">Shënim (opsionale)</label>
                <input id="note" name="note" type="text" value="{{ old('note') }}" maxlength="1000">
            </div>
            <button class="btn btn-primary" type="submit">Ngarko</button>
        </form>
    </div>

</section>
@endsection
