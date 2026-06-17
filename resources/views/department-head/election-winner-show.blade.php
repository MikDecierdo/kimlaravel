@extends('layouts.department-head')

@section('title', 'Election Winners Chart')

@push('styles')
<style>
.chart-shell {
    display: grid;
    gap: 1.2rem;
}
.chart-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    overflow: hidden;
}
.chart-banner {
    padding: 1.3rem 1.5rem;
    background: linear-gradient(135deg, #800020 0%, #A0153E 100%);
    color: #fff;
}
.chart-section {
    padding: 1.3rem 1.5rem 1.5rem;
}
.chart-track {
    display: grid;
    gap: 1rem;
}
.chart-level {
    position: relative;
    padding-top: 0.75rem;
}
.chart-level:not(:first-child)::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    width: 2px;
    height: 0.75rem;
    background: #cbd5e1;
}
.chart-level-title {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    background: #fff0f3;
    border: 1px solid #fecdd3;
    color: #800020;
    font-size: 0.8rem;
    font-weight: 800;
    margin-bottom: 0.9rem;
}
.chart-node-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 0.9rem;
}
.chart-node {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 1rem 1rem 0.95rem;
    text-align: center;
    position: relative;
}
.chart-node::after {
    content: '';
    position: absolute;
    top: -0.75rem;
    left: 50%;
    width: 2px;
    height: 0.75rem;
    background: #cbd5e1;
}
.chart-node:first-child::after {
    display: none;
}
.chart-name {
    font-size: 0.98rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 0.25rem;
}
.chart-position {
    font-size: 0.8rem;
    font-weight: 700;
    color: #800020;
    margin-bottom: 0.35rem;
}
.chart-meta {
    font-size: 0.8rem;
    color: #64748b;
    margin-bottom: 0.65rem;
}
.chart-partylist {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.35rem 0.65rem;
    border-radius: 999px;
    background: #fff0f3;
    border: 1px solid #fecdd3;
    color: #800020;
    font-size: 0.72rem;
    font-weight: 800;
    margin-bottom: 0.45rem;
}
.chart-votes {
    font-size: 0.78rem;
    color: #475569;
}
.chart-empty {
    padding: 1rem 1.25rem;
    border-radius: 14px;
    background: #f8fafc;
    color: #64748b;
    border: 1px dashed #cbd5e1;
}
</style>
@endpush

@section('dept-head-content')
<header>
    <div class="header-title">
        <h1>{{ $election->election_name }}</h1>
        <p>Organizational chart of the newly elected officers</p>
    </div>
</header>

<div class="chart-shell">
    <div class="chart-card">
        <div class="chart-banner">
            <h2 style="margin:0 0 0.35rem;font-size:1.2rem;">{{ $election->department }} Department</h2>
            <div style="font-size:0.9rem;opacity:0.92;">
                {{ $election->start_date->format('M d, Y') }} - {{ $election->end_date->format('M d, Y') }}
            </div>
        </div>

        <div class="chart-section">
            @if(count($election->winnerSections) > 0)
                <div class="chart-track">
                    @foreach($election->winnerSections as $section)
                        <div class="chart-level">
                            <div class="chart-level-title">
                                <i class="fa-solid fa-sitemap"></i> {{ $section['label'] }}
                            </div>
                            <div class="chart-node-grid">
                                @foreach($section['items'] as $item)
                                    @php $winner = $item['winner']; @endphp
                                    <div class="chart-node">
                                        <div class="chart-position">{{ $item['position'] }}</div>
                                        <div class="chart-name">{{ $winner->full_name }}</div>
                                        <div class="chart-meta">ID {{ $winner->student_id }}</div>
                                        <div class="chart-partylist">{{ $winner->partylist ?: 'Independent' }}</div>
                                        <div class="chart-votes">{{ number_format($winner->votes_count) }} votes</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="chart-empty">No winner officers are available for this election yet.</div>
            @endif
        </div>
    </div>
</div>
@endsection