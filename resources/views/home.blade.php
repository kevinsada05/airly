@extends('layouts.app')

@section('content')
<section class="hero">
    <div class="hero-grid">
        <div>
            <div class="pill">🌿 Monitorim mjedisor me imazhe statike</div>
            <h1>Zbuloni ndotjen me evidencë vizuale nga dronët</h1>
            <p class="lead">
                Platforma analizon imazhe ajrore dhe në terren për të identifikuar mbetje dhe ndotje.
                Rezultatet organizohen sipas zonave dhe pasqyrohen me nivele rreziku të qarta.
            </p>
        </div>
        <div class="card accent">
            <div class="editorial-split">
                <div>
                    <div class="eyebrow">🔍 Evidencë e strukturuar</div>
                    <h2>Çdo imazh kthehet në një sinjal të qartë</h2>
                    <p class="editorial-quote">
                        Fokus i pastër në prova vizuale pa pajisje live, pa integrime të ndërlikuara.
                    </p>
                </div>
                <ul class="editorial-list">
                    <li>
                        <div class="editorial-index">01</div>
                        <div>
                            <strong>Statike</strong>
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

<section class="section section-alt">
    <div class="container">
        <div class="eyebrow">🧭 Si funksionon</div>
        <h2>Proces i thjeshtë, me hapa të qartë</h2>
        <div class="steps-row">
            <div class="step-tile">
                <div class="step-circle">1</div>
                <h3>Ngarkoni imazhe statike</h3>
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
                <p>Green, orange, red për intensitet të qartë të ndotjes.</p>
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
            <div class="eyebrow">⚠️ Çfarë zgjidh</div>
            <h2>Problemet që adreson Airly</h2>
            <ul class="feature-list">
                <li><span></span>Identifikon hedhje të paligjshme në zona problematike.</li>
                <li><span></span>Vë në pah zonat me rrezik të lartë ndotjeje.</li>
                <li><span></span>Siguron prova vizuale për vendimmarrje.</li>
                <li><span></span>Gjurmim historik i ndryshimeve në kohë.</li>
            </ul>
        </div>
        <div>
            <div class="eyebrow">👥 Për kë është</div>
            <h2>Audienca që përfiton më shumë</h2>
            <p class="lead">Airly është ndërtuar për aktorët që kërkojnë prova të qarta dhe raportim të shpejtë.</p>
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
    <div class="container split split-tight centered">
        <div class="text-narrow center-text">
            <div class="eyebrow">✨ Pse Airly</div>
            <h2>Ndotja shfaqet qartë kur prova është vizuale</h2>
            <p class="lead">
                Airly punon me imazhe statike nga dronët dhe terreni për të dalluar
                ndotjen në zona të ndryshme. Pa live stream, pa kompleksitet
                vetëm evidencë e dokumentuar.
            </p>
            <ul class="feature-list" style="margin-top: 16px;">
                <li><span></span>Imazhe statike të dokumentuara, pa live stream.</li>
                <li><span></span>Klasifikim i qartë me ngjyra standarde.</li>
                <li><span></span>Platformë web, e rehatshme edhe në celular.</li>
                <li><span></span>Prova vizuale të ruajtura për çdo zonë.</li>
            </ul>
        </div>
        <div class="steps-row steps-compact">
            <div class="step-tile">
                <div class="step-circle">A</div>
                <h3>Pa transmetim live</h3>
                <p>Redukton kompleksitetin dhe kostot operative.</p>
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
@endsection
