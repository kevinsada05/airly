@extends('layouts.app')

@section('content')
<section class="hero">
    <div class="hero-grid">
        <div>
            <div class="pill">Paneli juaj</div>
            <h1>Mirë se erdhe, Admin.</h1>
            <p>Shihni gjendjen e analizave dhe aktivitetin e fundit të ngarkimeve.</p>
        </div>
        <div class="card">
            <div class="eyebrow">Statusi aktual</div>
            <h2>Analizat janë aktive</h2>
            <p>Të dhënat më poshtë përditësohen me çdo ngarkim.</p>
            <div class="features" style="margin-top: 16px;">
                <div class="feature">
                    <strong>Ngarkime totale</strong>
                    <p>{{ $totalUploads }}</p>
                </div>
                <div class="feature">
                    <strong>Të përfunduara</strong>
                    <p>{{ $processedCount }}</p>
                </div>
                <div class="feature">
                    <strong>Në pritje</strong>
                    <p>{{ $pendingCount + $processingCount }}</p>
                </div>
                <div class="feature">
                    <strong>Dështime</strong>
                    <p>{{ $failedCount }}</p>
                </div>
                <div class="feature">
                    <strong>Riciklimi gati</strong>
                    <p>{{ $wasteScanCount }}</p>
                </div>
                <div class="feature">
                    <strong>Zonat aktive</strong>
                    <p>{{ $zonesCount }}</p>
                </div>
                <div class="feature">
                    <strong>Zonat e kuqe</strong>
                    <p>{{ $zonesRedCount }}</p>
                </div>
                <div class="feature">
                    <strong>Zonat e gjelbra</strong>
                    <p>{{ $zonesGreenCount }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
