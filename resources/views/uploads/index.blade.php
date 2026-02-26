@extends('layouts.app')

@section('content')
<section class="section">
    <div class="split">
        <div>
            <div class="eyebrow">📂 Ngarkimet</div>
            @auth
                @if (auth()->user()->is_admin)
                    <h2>Të gjitha ngarkimet</h2>
                    <p class="lead">Shikoni statusin e çdo imazhi të publikuar në platformë.</p>
                @else
                    <h2>Ngarkimet tuaja + evidenca publike</h2>
                    <p class="lead">Shikoni ngarkimet tuaja dhe ato të publikuara nga administratori.</p>
                @endif
            @else
                <h2>Evidenca e publikuar</h2>
                <p class="lead">Ngarkimet e publikuara nga administratori për publikun.</p>
            @endauth
        </div>
        @auth
            <div style="text-align: right;">
                <a class="btn btn-primary" href="{{ route('uploads.create') }}">Ngarko imazh</a>
            </div>
        @endauth
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
                <p>Ngarkuar: {{ $upload->created_at->locale('sq')->translatedFormat('d F Y') }}</p>
            </a>
        @empty
            <div class="feature">
                <strong>Nuk ka ngarkime ende</strong>
                @auth
                    <p>Filloni duke ngarkuar imazhin e parë.</p>
                    <a class="btn btn-ghost" href="{{ route('uploads.create') }}">Ngarko imazh</a>
                @else
                    <p>Publikimet do të shfaqen sapo administratori të shtojë imazhe.</p>
                @endauth
            </div>
        @endforelse
    </div>

    @if ($uploads->hasPages())
        <div style="margin-top: 18px; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div style="color: var(--muted); font-size: 14px;">
                Faqja {{ $uploads->currentPage() }} / {{ $uploads->lastPage() }}
            </div>
            <div style="display: flex; gap: 10px;">
                @if ($uploads->onFirstPage())
                    <span class="btn btn-ghost" style="opacity: .5; pointer-events: none;">Mbrapa</span>
                @else
                    <a class="btn btn-ghost" href="{{ $uploads->previousPageUrl() }}">Mbrapa</a>
                @endif

                @if ($uploads->hasMorePages())
                    <a class="btn btn-ghost" href="{{ $uploads->nextPageUrl() }}">Përpara</a>
                @else
                    <span class="btn btn-ghost" style="opacity: .5; pointer-events: none;">Përpara</span>
                @endif
            </div>
        </div>
    @endif
</section>
@endsection
