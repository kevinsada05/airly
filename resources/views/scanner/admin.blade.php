@extends('layouts.app')

@section('content')
<section class="section">
    <div class="split">
        <div>
            <div class="eyebrow">🧪 Admin Skaner</div>
            <h2>Ngarko skanim të ri</h2>
            <p class="lead">Vetëm administratorët mund të nisin skanimet.</p>
        </div>
        <div style="text-align: right;">
            <a class="btn btn-ghost" href="{{ route('scanner.index') }}">Shiko publikimet</a>
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

    <div class="section" style="padding: 0; margin-top: 24px;">
        <div class="eyebrow">📌 Skanimet e fundit</div>
        <h2>Menaxho publikimet</h2>
        <div class="features" style="margin-top: 18px;">
            @forelse ($recentScans as $recentScan)
                <div class="feature">
                    <img src="{{ url('/storage/' . $recentScan->file_path) }}" alt="Mbetja" style="width: 100%; border-radius: 14px; display: block; margin-bottom: 10px;">
                    <strong>{{ $recentScan->item_type ?? '—' }}</strong>
                    <p>Riciklueshme: {{ $recentScan->recyclable === null ? '—' : ($recentScan->recyclable ? 'Po' : 'Jo') }}</p>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px;">
                        <a class="btn btn-ghost" href="{{ route('scanner.index', ['scan' => $recentScan->id]) }}">Shiko</a>
                        <form method="POST" action="{{ route('scanner.reanalyze', $recentScan) }}">
                            @csrf
                            <button class="btn btn-ghost" type="submit">Rianalizo</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="feature">
                    <strong>Nuk ka skanime ende</strong>
                    <p>Ngarko skanimin e parë për ta bërë publik.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
