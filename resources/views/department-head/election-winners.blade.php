@extends('layouts.department-head')

@section('title', 'Election Winners')

@push('styles')
<style>
.winner-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.2rem;
}
.winner-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    border: 1px solid #e5e7eb;
    position: relative;
}
.winner-card-banner {
    width: 100%;
    height: 200px;
    background: linear-gradient(135deg, #800020 0%, #A0153E 100%);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.winner-card-label {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 700;
    background: #fff;
    color: #1e293b;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}
.winner-card-body {
    padding: 1.4rem 1.5rem 1.5rem;
}
.winner-preview {
    display: grid;
    gap: 0.75rem;
    margin: 1rem 0 1.2rem;
}
.winner-preview-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
}
.winner-preview-row:last-child {
    padding-bottom: 0;
    border-bottom: 0;
}
.winner-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    background: #fff0f3;
    color: #800020;
    border: 1px solid #fecdd3;
    white-space: nowrap;
}
.winner-empty {
    padding: 1rem;
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
        <h1>Election Winners</h1>
        <p>Finished elections only, showing the newly elected officers</p>
    </div>
</header>

<div class="winner-card-grid">
    @forelse($elections as $election)
        <div class="winner-card">
            <div class="winner-card-banner">
                @if($election->banner_image)
                    <img src="{{ $election->banner_image }}" alt="{{ $election->election_name }}" style="width:100%;height:100%;object-fit:cover;">
                @else
                    <i class="fa-solid fa-trophy" style="font-size:5rem;color:rgba(255,255,255,0.18);"></i>
                @endif
                <div class="winner-card-label">
                    <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
                    Finished
                </div>
            </div>

            <div class="winner-card-body">
                <p style="font-size:0.75rem;font-weight:700;letter-spacing:0.08em;color:#1e293b;text-transform:uppercase;margin:0 0 0.3rem;">{{ $election->department }} Department</p>
                <h3 style="font-size:1.15rem;font-weight:800;color:#1e293b;margin:0 0 0.85rem;text-transform:uppercase;letter-spacing:0.02em;">{{ $election->election_name }}</h3>
                <p style="font-size:0.85rem;color:#475569;margin:0 0 0.35rem;">
                    Election Started: {{ $election->start_date->format('M d, Y') }}
                </p>
                <p style="font-size:0.85rem;color:#475569;margin:0 0 0.35rem;">
                    Election Ended: {{ $election->end_date->format('M d, Y') }}
                </p>
                <p style="font-size:0.85rem;color:#475569;margin:0;">
                    Winner Officers: {{ $election->winnerPositions->count() }}
                </p>

                <div class="winner-preview">
                    @forelse($election->winnerPositions->take(4) as $item)
                        @php $winner = $item['winner']; @endphp
                        <div class="winner-preview-row">
                            <div style="min-width:0;">
                                <div style="font-size:0.88rem;font-weight:800;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $item['position'] }}</div>
                                <div style="font-size:0.8rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $winner->full_name }} • {{ $winner->student_id }}</div>
                            </div>
                            <span class="winner-chip">{{ $winner->partylist ?: 'Independent' }}</span>
                        </div>
                    @empty
                        <div class="winner-empty">No winner officers available for this election.</div>
                    @endforelse
                </div>

                <div style="display:flex;gap:0.75rem;">
                    <a href="{{ route('department-head.election-winners.show', $election) }}" class="btn-hover card-action-btn" style="flex:1;padding:0.6rem;border:2px solid #800020;border-radius:25px;font-weight:700;font-size:0.85rem;letter-spacing:0.05em;cursor:pointer;transition:all 0.3s;background:#800020;color:white;text-transform:uppercase;text-align:center;text-decoration:none;">
                        VIEW
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="winner-card" style="grid-column:1/-1;">
            <div class="winner-empty" style="margin:1.25rem;">No finished elections available yet.</div>
        </div>
    @endforelse
</div>
@endsection