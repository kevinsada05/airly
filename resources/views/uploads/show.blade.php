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
        <div style="text-align: right;">
            <a class="btn btn-primary" href="{{ route('map.index') }}">Shiko në hartë</a>
            <a class="btn btn-ghost" href="{{ route('uploads.index') }}">Kthehu te lista</a>
        </div>
    </div>

    <div class="grid-2" style="margin-top: 18px; align-items: start;">
        <div class="card">
            <img src="{{ $imageUrl }}" alt="Imazhi i ngarkuar" style="width: 100%; border-radius: 18px; display: block;">
        </div>
        <div class="card">
            <div class="eyebrow">Lokacioni</div>
            <h3>{{ $upload->lat }}, {{ $upload->lng }}</h3>
            <p>Ngarkuar: {{ $upload->created_at->locale('sq')->translatedFormat('d F Y') }}</p>
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
@endsection
