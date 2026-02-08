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
                <label for="address">Kërko lokacion (adresë ose vend)</label>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <input id="address" name="address" type="text" placeholder="p.sh. Sheshi Skënderbej, Tiranë" style="flex: 1 1 260px;">
                    <button class="btn btn-ghost" type="button" id="address-search">Gjej në hartë</button>
                </div>
                <p class="lead" style="font-size: 0.95rem; margin-top: 6px;">Mund të kërkoni ose të klikoni direkt në hartë për të vendosur koordinatat.</p>
                <div id="address-status" style="margin-top: 6px; color: #6b6b63;"></div>
            </div>
            <div class="card" style="padding: 0; overflow: hidden;">
                <div id="upload-map" style="height: 360px; width: 100%;"></div>
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

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    const defaultLat = {{ old('lat', 41.3275) }};
    const defaultLng = {{ old('lng', 19.8187) }};
    const map = L.map('upload-map').setView([defaultLat, defaultLng], 10);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const latInput = document.getElementById('lat');
    const lngInput = document.getElementById('lng');
    const statusEl = document.getElementById('address-status');
    let marker = L.marker([defaultLat, defaultLng]).addTo(map);

    function setPosition(lat, lng, zoom = 16) {
        const latNum = Number(lat);
        const lngNum = Number(lng);
        if (Number.isNaN(latNum) || Number.isNaN(lngNum)) {
            return;
        }
        latInput.value = latNum.toFixed(6);
        lngInput.value = lngNum.toFixed(6);
        marker.setLatLng([latNum, lngNum]);
        map.setView([latNum, lngNum], zoom);
    }

    map.on('click', (e) => {
        setPosition(e.latlng.lat, e.latlng.lng);
        statusEl.textContent = 'Koordinatat u vendosën nga harta.';
    });

    document.getElementById('address-search').addEventListener('click', async () => {
        const query = document.getElementById('address').value.trim();
        if (!query) {
            statusEl.textContent = 'Ju lutemi shkruani një adresë ose vend.';
            return;
        }

        statusEl.textContent = 'Duke kërkuar...';
        try {
            const url = new URL('https://nominatim.openstreetmap.org/search');
            url.searchParams.set('format', 'json');
            url.searchParams.set('q', query);
            url.searchParams.set('limit', '1');
            url.searchParams.set('addressdetails', '1');

            const res = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) {
                throw new Error('Nuk u gjet asgjë.');
            }

            const data = await res.json();
            if (!data.length) {
                statusEl.textContent = 'Nuk u gjet asnjë rezultat.';
                return;
            }

            setPosition(data[0].lat, data[0].lon);
            statusEl.textContent = `U gjet: ${data[0].display_name}`;
        } catch (err) {
            statusEl.textContent = 'Kërkimi dështoi. Provoni përsëri.';
        }
    });

    latInput.addEventListener('change', () => {
        if (latInput.value && lngInput.value) {
            setPosition(latInput.value, lngInput.value, map.getZoom());
        }
    });
    lngInput.addEventListener('change', () => {
        if (latInput.value && lngInput.value) {
            setPosition(latInput.value, lngInput.value, map.getZoom());
        }
    });
</script>
@endsection
