@extends('layouts.app')

@section('content')
<section class="section">
    <div class="split">
        <div>
            <div class="eyebrow">🗺️ Zona</div>
            <h2>Zonat e ndotjes</h2>
            <p class="lead">Lista e zonave dhe intensiteti i ndotjes sipas analizave.</p>
        </div>
        <div style="text-align: right;">
            <a class="btn btn-ghost" href="{{ route('map.index') }}">Shiko hartën</a>
        </div>
    </div>

    <div class="features" style="margin-top: 18px;">
        @forelse ($zones as $zone)
            <a class="feature" href="{{ route('zones.show', $zone) }}" style="display: block;">
                <strong>{{ $zone->name }}</strong>
                <p>Ngjyra: @php
                    $sev = $zone->current_severity->value ?? $zone->current_severity;
                    $labels = ['green' => 'Gjelbër', 'orange' => 'Portokalli', 'red' => 'Kuqe'];
                @endphp
                {{ $labels[$sev] ?? '—' }}</p>
                <p>Imazhe: {{ $zone->image_uploads_count }}</p>
            </a>
        @empty
            <div class="feature">
                <strong>Nuk ka zona ende</strong>
                <p>Zona do të shfaqen sapo të ketë ngarkime.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
