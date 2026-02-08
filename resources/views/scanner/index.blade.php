@extends('layouts.app')

@section('content')
<section class="section">
    @if ($scan)
        <div class="grid-2" style="margin-top: 18px; align-items: start;">
            <div class="card">
                <img src="{{ $imageUrl }}" alt="Mbetja" style="width: 100%; border-radius: 18px; display: block;">
            </div>
            <div class="card">
                <div class="eyebrow">Rezultati</div>
                <h3>{{ $scan->item_type ?? '—' }}</h3>
                <p>Rreziku: @php
                    $sev = $scan->severity;
                    $labels = ['green' => 'Gjelbër', 'orange' => 'Portokalli', 'red' => 'Kuqe'];
                @endphp
                {{ $labels[$sev] ?? '—' }}</p>
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

    <div class="section" style="padding: 0; margin-top: 22px;">
        <div class="eyebrow">🧪 Skanimet e fundit</div>
        <h2>Rezultate të publikuara</h2>
        <div class="features" style="margin-top: 18px;">
        @forelse ($recentScans as $recentScan)
            <a class="feature" href="{{ route('scanner.index', ['scan' => $recentScan->id]) }}" style="display: block;">
                <img src="{{ url('/storage/' . $recentScan->file_path) }}" alt="Mbetja" style="width: 100%; border-radius: 14px; display: block; margin-bottom: 10px;">
                <strong>{{ $recentScan->item_type ?? '—' }}</strong>
                <p>Rreziku: @php
                    $sev = $recentScan->severity;
                    $labels = ['green' => 'Gjelbër', 'orange' => 'Portokalli', 'red' => 'Kuqe'];
                @endphp
                {{ $labels[$sev] ?? '—' }}</p>
                <p>Riciklueshme: {{ $recentScan->recyclable === null ? '—' : ($recentScan->recyclable ? 'Po' : 'Jo') }}</p>
                <p>Shfaq detajet</p>
            </a>
            @empty
                <div class="feature">
                    <strong>Nuk ka skanime ende</strong>
                    <p>Rezultatet publike do të shfaqen sapo administratori të shtojë skanime.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
