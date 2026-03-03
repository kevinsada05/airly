@extends('layouts.app')

@section('content')
<section class="section">
    @if ($scan)
        <div class="grid-2" style="margin-bottom: 18px; align-items: start;">
            <div class="card">
                <img src="{{ $imageUrl }}" alt="Mbetja e zgjedhur" style="width: 100%; border-radius: 14px; display: block;">
            </div>
            <div class="card">
                <div class="eyebrow">Rezultati i zgjedhur</div>
                <h3>{{ $scan->item_type ?? '—' }}</h3>
                <p>Rreziku:
                    @php
                        $sev = $scan->severity;
                        $labels = ['green' => 'Gjelbër', 'orange' => 'Portokalli', 'red' => 'Kuqe', 'purple' => 'Lejla'];
                    @endphp
                    {{ $labels[$sev] ?? '—' }}
                </p>
                <p>Riciklueshme: {{ $scan->recyclable === null ? '—' : ($scan->recyclable ? 'Po' : 'Jo') }}</p>
                @if ($scan->instructions)
                    <p>Udhëzime: {{ $scan->instructions }}</p>
                @endif
                @if ($scan->warnings)
                    <p>Paralajmërime: {{ $scan->warnings }}</p>
                @endif
            </div>
        </div>
    @endif

    <div class="section" style="padding: 0; margin-top: 22px;">
        <div class="eyebrow">🧪 Skanimet e fundit</div>
        <h2>Rezultate të publikuara</h2>
        <h3> Të gjitha rezultatet mund t'i shihni në faqen tonë në instagram <a class="instagram-link" href="https://www.instagram.com/airly2026?igsh=MXA0a3lxZG14bDZjbg==" target="_blank" rel="noopener noreferrer">@airly2026</a></h3>
        <div class="features" style="margin-top: 18px;">
        @forelse ($recentScans as $recentScan)
            <a class="feature" href="{{ route('scanner.index', ['scan' => $recentScan->id]) }}" style="display: block;">
                <img src="{{ url('/storage/' . $recentScan->file_path) }}" alt="Mbetja" style="width: 100%; border-radius: 14px; display: block; margin-bottom: 10px;">
                <strong>{{ $recentScan->item_type ?? '—' }}</strong>
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
