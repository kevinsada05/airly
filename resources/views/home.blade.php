@extends('layouts.app')

@section('content')
@php
    $severityLabel = function ($severity) {
        $value = is_object($severity) && isset($severity->value) ? $severity->value : (string) $severity;
        return [
            'green' => 'Gjelbër',
            'orange' => 'Portokalli',
            'red' => 'Kuqe',
        ][$value] ?? '—';
    };
@endphp

<section class="hero">
    <div class="hero-grid">
        <div>
            <div class="pill">🌿 Monitorim mjedisor me imazhe</div>
            <h1>Zbuloni ndotjen me prova vizuale nga dronët</h1>
            <p class="lead">
                Platforma analizon imazhe ajrore dhe në terren për të identifikuar mbetje dhe ndotje.
                Rezultatet organizohen sipas zonave dhe pasqyrohen me nivele rreziku të qarta.
            </p>
            <h3>Të gjitha rezultatet mund t'i shihni në faqen tonë në instagram <a class="instagram-link" href="https://www.instagram.com/airly2026?igsh=MXA0a3lxZG14bDZjbg==" target="_blank" rel="noopener noreferrer">@airly2026</a></h3>
        </div>
        <div class="card accent">
            <div class="editorial-split">
                <div>
                    <div class="eyebrow">🔍 Prova vizuale të strukturuara</div>
                    <h2>Çdo imazh kthehet në një sinjal të qartë</h2>
                    <p class="editorial-quote">
                        Fokus i pastër në prova vizuale me pajisje live, pa integrime të ndërlikuara.
                    </p>
                </div>
                <ul class="editorial-list">
                    <li>
                        <div class="editorial-index">01</div>
                        <div>
                            <strong>Live</strong>
                            <p>Imazhe nga dronë ose terren.</p>
                        </div>
                    </li>
                    <li>
                        <div class="editorial-index">02</div>
                        <div>
                            <strong>Klasifikim</strong>
                            <p>Ngjyra të qarta për rrezikun.</p>
                        </div>
                    </li>
                    <li>
                        <div class="editorial-index">03</div>
                        <div>
                            <strong>Historik</strong>
                            <p>Ndjekje e progresit në kohë.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section section-alt" style="padding: 56px 0;">
    <div class="container" style="padding-left: 48px; padding-right: 16px;">
        <div class="eyebrow">🧭 Si funksionon</div>
        <h2>Proces i thjeshtë, me hapa të qartë</h2>
        <div class="steps-row">
            <div class="step-tile">
                <div class="step-circle">1</div>
                <h3>Ngarkoni imazhe statike ose imazhe live</h3>
                <p>Ngarkime nga dronë ose në terren me lokacion të saktë.</p>
            </div>
            <div class="step-tile">
                <div class="step-circle">2</div>
                <h3>AI analizon ndotjen</h3>
                <p>Modeli identifikon mbetje dhe vlerëson ashpërsinë.</p>
            </div>
            <div class="step-tile">
                <div class="step-circle">3</div>
                <h3>Zonat klasifikohen me ngjyra</h3>
                <p>Të gjelbër, portokalli dhe të kuqe për intensitet të qartë të ndotjes.</p>
            </div>
            <div class="step-tile">
                <div class="step-circle">4</div>
                <h3>Prova vizuale për çdo zonë</h3>
                <p>Çdo rezultat ka evidencë të lidhur me imazhet.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="split">
        <div>
            <div class="eyebrow">⚠️ Çfarë zgjidh?</div>
            <h2>Problemet që adreson Airly</h2>
            <ul class="feature-list">
                <li><span></span>Identifikon hedhje të paligjshme në zona problematike.</li>
                <li><span></span>Vë në pah zonat me rrezik të lartë ndotjeje.</li>
                <li><span></span>Siguron prova vizuale për vendimmarrje.</li>
                <li><span></span>Gjurmim historik i ndryshimeve në kohë.</li>
            </ul>
        </div>
        <div>
            <div class="eyebrow">👥 Për kë është?</div>
            <h2>Audienca që përfiton më shumë</h2>
            <p class="lead">Airly është ndërtuar për ata që kërkojnë prova të qarta dhe raportim të shpejtë.</p>
            <div class="pill-cloud">
                <span>Komunat</span>
                <span>Organizatat mjedisore</span>
                <span>Studiuesit</span>
                <span>Operatorët e dronëve</span>
            </div>
            <div class="callout">
                <strong>Rezultat i përbashkët</strong>
                <p>Një pamje e unifikuar e ndotjes për vendimmarrje dhe bashkëpunim.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section-contrast">
    <div class="container split split-tight centered" style="padding-left: 24px; padding-right: 24px;">
        <div class="text-narrow center-text">
            <div class="eyebrow">✨ Pse Airly?</div>
            <h2>Ndotja shfaqet qartë kur prova është vizuale</h2>
            <p class="lead">
                Airly punon me imazhe nga dronët dhe terreni për të dalluar
                ndotjen në zona të ndryshme. Pa kompleksitet dhe me dokumentim të
                provave.
            </p>
            <ul class="feature-list" style="margin-top: 16px;">
                <li><span></span>Imazhe statike të dokumentuara.</li>
                <li><span></span>Klasifikim i qartë me ngjyra standarde.</li>
                <li><span></span>Platformë web, e rehatshme edhe për përdorim në celular.</li>
                <li><span></span>Prova vizuale të ruajtura për çdo zonë.</li>
            </ul>
        </div>
        <div class="steps-row steps-compact">
            <div class="step-tile">
                <div class="step-circle">A</div>
                <h3>Transmetim live</h3>
                <p>Redukton kompleksitetin për përdoruesit.</p>
            </div>
            <div class="step-tile">
                <div class="step-circle">B</div>
                <h3>Prova vizuale</h3>
                <p>Imazhe të ruajtura për analizë dhe krahasim në kohë.</p>
            </div>
            <div class="step-tile">
                <div class="step-circle">C</div>
                <h3>Vetëm web</h3>
                <p>Akses nga çdo pajisje, pa instalime shtesë.</p>
            </div>
            <div class="step-tile">
                <div class="step-circle">D</div>
                <h3>Klasifikim i thjeshtë</h3>
                <p>Ngjyra standarde për nivelin e ndotjes.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="split">
        <div>
            <div class="eyebrow">🖼️ Të rejat e fundit</div>
            <h2>Imazhet e publikuara</h2>
            <p class="lead">Këto janë ngarkimet më të fundit nga administratori, të hapura për publikun.</p>
        </div>
        <div style="text-align: right;">
            <a class="btn btn-ghost" href="{{ route('uploads.index') }}">Shiko të gjitha</a>
        </div>
    </div>
    <div class="features" style="margin-top: 18px; padding-left: 32px; padding-right: 32px;">
        @forelse ($recentUploads as $upload)
            <a class="feature" href="{{ route('uploads.show', $upload) }}" style="display: block;">
                <img src="{{ url('/storage/' . $upload->file_path) }}" alt="Imazh i publikuar" style="width: 100%; border-radius: 14px; display: block; margin-bottom: 10px;">
                <strong>{{ optional($upload->created_at)->format('d.m.Y H:i') }}</strong>
                <p>Statusi: {{ $upload->status?->value ?? '—' }}</p>
                <p>Shfaq detajet</p>
            </a>
        @empty
            <div class="feature">
                <strong>Nuk ka imazhe ende</strong>
                <p>Imazhet do të shfaqen sapo administratori të publikojë të parat.</p>
            </div>
        @endforelse
    </div>
</section>

<section class="section section-alt">
    <div class="split" style="padding-left: 48px; padding-right: 16px;">
        <div>
            <div class="eyebrow">🗺️ Zonat e monitoruara</div>
            <h2>Zona me intensitet të ndotjes</h2>
            <p class="lead">Një përmbledhje e shpejtë e zonave kryesore të monitoruara.</p>
        </div>
        <div style="text-align: right;">
            <a class="btn btn-ghost" href="{{ route('zones.index') }}">Shiko zonat</a>
        </div>
    </div>
    <div class="features" style="margin-top: 18px;">
        @forelse ($zones as $zone)
            <a class="feature" href="{{ route('zones.show', $zone) }}" style="display: block; padding: 20px 22px; margin: 0 10px;">
                <strong>{{ $zone->name }}</strong>
                <p>Ngjyra: {{ $severityLabel($zone->current_severity) }}</p>
                <p>Hap zonën për detaje</p>
            </a>
        @empty
            <div class="feature" style="padding: 20px 22px; margin: 0 10px;">
                <strong>Nuk ka zona ende</strong>
                <p>Zonat do të shfaqen sapo të jenë të disponueshme.</p>
            </div>
        @endforelse
    </div>
</section>

<section class="section">
    <div class="split">
        <div>
            <div class="eyebrow">♻️ Skanimet publike</div>
            <h2>Rezultate riciklimi</h2>
            <p class="lead">Shembuj të skanimeve të publikuara me udhëzime të qarta.</p>
        </div>
        <div style="text-align: right;">
            <a class="btn btn-ghost" href="{{ route('scanner.index') }}">Shiko skanimet</a>
        </div>
    </div>
    <div class="features" style="margin-top: 18px;">
        @forelse ($recentScans as $scan)
            <a class="feature" href="{{ route('scanner.index', ['scan' => $scan->id]) }}" style="display: block;">
                <img src="{{ url('/storage/' . $scan->file_path) }}" alt="Skanim" style="width: 100%; border-radius: 14px; display: block; margin-bottom: 10px;">
                <strong>{{ $scan->item_type ?? '—' }}</strong>
                <p>Rreziku: {{ $severityLabel($scan->severity) }}</p>
                <p>Shfaq detajet</p>
            </a>
        @empty
            <div class="feature">
                <strong>Nuk ka skanime ende</strong>
                <p>Rezultatet do të shfaqen sapo të publikohen skanime.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection

@section('footer_content')
All copyrights reserved <br>
Enxhi Dana, Ester Sanxhaku, Greisa Meta, Kamila Parllaku, Mesart Qejvani
@endsection
