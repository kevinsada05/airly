@extends('layouts.app')

@section('content')
<section class="section">
    <div class="split">
        <div>
            <div class="eyebrow">🧾 Detajet</div>
            <h2>Imazhi i ngarkuar</h2>
            <p class="lead">Status: @php
                $status = $upload->status->value ?? $upload->status;
                $labels = ['pending' => 'Në pritje', 'processing' => 'Në përpunim', 'processed' => 'E përfunduar', 'failed' => 'Dështoi'];
            @endphp
            {{ $labels[$status] ?? $status }}</p>
        </div>
        <div class="action-bar">
            <a class="btn btn-primary" href="{{ route('map.index') }}">Shiko në hartë</a>
            <a class="btn btn-ghost" href="{{ route('uploads.index') }}">Kthehu te lista</a>
            @auth
                @if (auth()->user()->is_admin)
                    <button class="btn btn-danger js-delete-upload" type="button" data-action="{{ route('uploads.destroy', $upload) }}">
                        Fshi ngarkimin
                    </button>
                @endif
            @endauth
        </div>
    </div>

    <div class="grid-2" style="margin-top: 18px; align-items: start;">
        <div class="card">
            <img src="{{ $imageUrl }}" alt="Imazhi i ngarkuar" style="width: 100%; border-radius: 18px; display: block;">
        </div>
        <div class="card">
            <div class="eyebrow">Vendodhja</div>
            <h3 id="upload-address">Duke kërkuar adresën...</h3>
            <p id="upload-coordinates">Koordinata: {{ $upload->lat }}, {{ $upload->lng }}</p>
            <p>Ngarkuar: {{ $upload->created_at->locale('sq')->translatedFormat('d F Y') }}</p>
            <div class="card" style="padding: 0; overflow: hidden; margin-top: 12px;">
                <div id="upload-map" style="height: 220px; width: 100%;"></div>
            </div>
            <div style="margin-top: 12px;">
                <div class="eyebrow">Rezultati</div>
                @if ($upload->analysisResult)
                    <p>
                        Ndotja: {{ $upload->analysisResult->pollution_detected ? 'Po' : 'Jo' }}<br>
                        Ngjyra: @php
                            $sev = $upload->analysisResult->severity->value ?? $upload->analysisResult->severity;
                            $labels = ['green' => 'Gjelbër', 'orange' => 'Portokalli', 'red' => 'Kuqe'];
                        @endphp
                        {{ $labels[$sev] ?? '—' }}<br>
                        Besueshmëria: {{ $upload->analysisResult->confidence !== null ? number_format($upload->analysisResult->confidence * 100, 1) . '%' : '—' }}
                    </p>
                @else
                    <p>Rezultati nuk është gati ende.</p>
                @endif
            </div>
            @if ($upload->wasteScan)
                <div style="margin-top: 16px;">
                    <div class="eyebrow">Riciklimi personal</div>
                    <h3>{{ $upload->wasteScan->item_type ?? '—' }}</h3>
                    <p>Rreziku: @php
                        $sev = $upload->wasteScan->severity;
                        $labels = ['green' => 'Gjelbër', 'orange' => 'Portokalli', 'red' => 'Kuqe'];
                    @endphp
                    {{ $labels[$sev] ?? '—' }}</p>
                    <p>Riciklueshme: {{ $upload->wasteScan->recyclable === null ? '—' : ($upload->wasteScan->recyclable ? 'Po' : 'Jo') }}</p>
                    @if ($upload->wasteScan->instructions)
                        <div style="margin-top: 10px;">
                            <div class="eyebrow">Udhëzime</div>
                            <p>{{ $upload->wasteScan->instructions }}</p>
                        </div>
                    @endif
                    @if ($upload->wasteScan->warnings)
                        <div style="margin-top: 10px;">
                            <div class="eyebrow">Paralajmërime</div>
                            <p>{{ $upload->wasteScan->warnings }}</p>
                        </div>
                    @endif
                </div>
            @else
                <div style="margin-top: 16px;">
                    <div class="eyebrow">Riciklimi personal</div>
                    <p>Analiza e riciklimit është në pritje. Shfaqet sapo të jetë gati.</p>
                </div>
            @endif
            @if ($upload->note)
                <div style="margin-top: 12px;">
                    <div class="eyebrow">Shënim</div>
                    <p>{{ $upload->note }}</p>
                </div>
            @endif
        </div>
    </div>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    const uploadLat = {{ $upload->lat }};
    const uploadLng = {{ $upload->lng }};
    const map = L.map('upload-map').setView([uploadLat, uploadLng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    L.marker([uploadLat, uploadLng]).addTo(map);

    const addressEl = document.getElementById('upload-address');
    const coordsEl = document.getElementById('upload-coordinates');
    coordsEl.textContent = `Koordinata: ${uploadLat.toFixed(6)}, ${uploadLng.toFixed(6)}`;

    (async () => {
        try {
            const url = new URL('https://nominatim.openstreetmap.org/reverse');
            url.searchParams.set('format', 'jsonv2');
            url.searchParams.set('lat', uploadLat);
            url.searchParams.set('lon', uploadLng);
            url.searchParams.set('zoom', '18');

            const res = await fetch(url.toString(), {
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) {
                throw new Error('Failed');
            }

            const data = await res.json();
            addressEl.textContent = data.display_name || 'Adresë e papërcaktuar';
        } catch (e) {
            addressEl.textContent = 'Adresë e papërcaktuar';
        }
    })();
</script>
@endsection
