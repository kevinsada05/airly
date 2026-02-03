@extends('layouts.app')

@section('content')
<section class="section">
    <div class="split">
        <div>
            <div class="eyebrow">🧭 Harta</div>
            <h2>Harta e zonave</h2>
            <p class="lead">Klikoni në një zonë për të parë imazhet.</p>
        </div>
        <div style="text-align: right;">
            <a class="btn btn-ghost" href="{{ route('zones.index') }}">Lista e zonave</a>
        </div>
    </div>

    <div class="card" style="margin-top: 18px; padding: 0; overflow: hidden;">
        <div id="zones-map" style="height: 520px; width: 100%;"></div>
    </div>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    const zones = {!! $zonesJson !!};
    const map = L.map('zones-map').setView([41.3275, 19.8187], 7);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const bounds = [];

    zones.forEach(zone => {
        const coords = zone.polygon?.coordinates?.[0] || [];
        const latLngs = coords.map(pair => [pair[1], pair[0]]);
        if (latLngs.length === 0) {
            return;
        }

        const color = zone.severity === 'red'
            ? '#d74b4b'
            : (zone.severity === 'orange' ? '#f0a23a' : '#4f8f5a');

        const polygon = L.polygon(latLngs, {
            color,
            fillColor: color,
            fillOpacity: 0.35,
            weight: 2,
        }).addTo(map);

        polygon.bindPopup(`<strong>${zone.name}</strong><br><a href="/zones/${zone.id}">Hap zonën</a>`);
        bounds.push(...latLngs);
    });

    // Keep default focus on Tirana.
</script>
@endsection
