@extends('layouts.app')

@section('content')
<section class="section">
    <div class="split">
        <div>
            <div class="eyebrow">📍 Zonë</div>
            <h2>{{ $zone->name }}</h2>
            <p class="lead">Ngjyra: @php
                $sev = $zone->current_severity->value ?? $zone->current_severity;
                $labels = ['green' => 'Gjelbër', 'orange' => 'Portokalli', 'red' => 'Kuqe'];
            @endphp
            {{ $labels[$sev] ?? '—' }}</p>
        </div>
        <div style="text-align: right;">
            <a class="btn btn-ghost" href="{{ route('zones.index') }}">Kthehu te zonat</a>
        </div>
    </div>

    <div class="features" style="margin-top: 18px;">
        @forelse ($uploads as $upload)
            <div class="feature">
                <img src="{{ $upload->image_url }}" alt="Imazhi" style="width: 100%; border-radius: 16px; display: block; margin-bottom: 10px;">
                <strong>{{ $upload->analysisResult?->pollution_detected ? 'Ndotje e zbuluar' : 'E pastër' }}</strong>
                <p>Ngjyra: @php
                    $sev = $upload->analysisResult?->severity->value ?? $upload->analysisResult?->severity;
                    $labels = ['green' => 'Gjelbër', 'orange' => 'Portokalli', 'red' => 'Kuqe'];
                @endphp
                {{ $labels[$sev] ?? '—' }}</p>
                <p>Koordinata: {{ $upload->lat }}, {{ $upload->lng }}</p>
                <a class="btn btn-ghost" href="{{ route('uploads.show', $upload) }}">Shiko detajet</a>
            </div>
        @empty
            <div class="feature">
                <strong>Nuk ka imazhe në këtë zonë</strong>
                <p>Ngarkimet do të shfaqen sapo të analizohen.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
