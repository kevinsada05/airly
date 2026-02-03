@extends('layouts.app')

@section('content')
<section class="section">
    <div class="split">
        <div>
            <div class="eyebrow">📂 Ngarkimet</div>
            <h2>Ngarkimet tuaja</h2>
            <p class="lead">Shikoni statusin e çdo imazhi dhe hapni detajet kur është gati.</p>
        </div>
        <div style="text-align: right;">
            <a class="btn btn-primary" href="{{ route('uploads.create') }}">Ngarko imazh</a>
        </div>
    </div>

    <div class="features" style="margin-top: 18px;">
        @forelse ($uploads as $upload)
            <a class="feature" href="{{ route('uploads.show', $upload) }}" style="display: block;">
                <strong>Status: @php
                    $status = $upload->status->value ?? $upload->status;
                    $labels = ['pending' => 'Në pritje', 'processing' => 'Në përpunim', 'processed' => 'E përfunduar', 'failed' => 'Dështoi'];
                @endphp
                {{ $labels[$status] ?? $status }}</strong>
                <p>Koordinata: {{ $upload->lat }}, {{ $upload->lng }}</p>
                @if ($upload->wasteScan)
                    <p>Riciklimi: Gati</p>
                @else
                    <p>Riciklimi: Në pritje</p>
                @endif
                <p>Ngarkuar: {{ $upload->created_at->format('d.m.Y H:i') }}</p>
            </a>
        @empty
            <div class="feature">
                <strong>Nuk ka ngarkime ende</strong>
                <p>Filloni duke ngarkuar imazhin e parë.</p>
                <a class="btn btn-ghost" href="{{ route('uploads.create') }}">Ngarko imazh</a>
            </div>
        @endforelse
    </div>

    <div style="margin-top: 18px;">
        {{ $uploads->links() }}
    </div>
</section>
@endsection
