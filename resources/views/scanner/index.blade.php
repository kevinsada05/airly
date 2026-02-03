@extends('layouts.app')

@section('content')
<section class="section">
    <div class="split">
        <div>
            <div class="eyebrow">♻️ Skaneri Personal</div>
            <h2>Udhëzime për riciklim</h2>
            <p class="lead">Ngarko një foto të një mbetjeje dhe merr udhëzime të qarta për riciklimin.</p>
        </div>
    </div>

    <div class="card" style="margin-top: 18px;">
        @if ($errors->any())
            <div class="alert">
                {{ $errors->first() }}
            </div>
        @endif
        <form class="form" method="POST" action="{{ route('scanner.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="field">
                <label for="image">Foto e mbetjes (JPG/PNG)</label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png" required>
            </div>
            <button class="btn btn-primary" type="submit">Analizo</button>
        </form>
    </div>

    @if ($scan)
        <div class="grid-2" style="margin-top: 18px; align-items: start;">
            <div class="card">
                <img src="{{ $imageUrl }}" alt="Mbetja" style="width: 100%; border-radius: 18px; display: block;">
            </div>
            <div class="card">
                <div class="eyebrow">Rezultati</div>
                <h3>{{ $scan->item_type ?? '—' }}</h3>
                <p>Riciklueshme: {{ $scan->recyclable === null ? '—' : ($scan->recyclable ? 'Po' : 'Jo') }}</p>
                @if ($scan->instructions)
                    <div style="margin-top: 12px;">
                        <div class="eyebrow">Udhëzime</div>
                        <p>{{ $scan->instructions }}</p>
                    </div>
                @endif
                @if ($scan->warnings)
                    <div style="margin-top: 12px;">
                        <div class="eyebrow">Paralajmërime</div>
                        <p>{{ $scan->warnings }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</section>
@endsection
