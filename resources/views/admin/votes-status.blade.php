@extends('layouts.admin')

@section('title', 'Vote Status - ' . $activeElection->election_name)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/votes-status.css') }}">
@endpush

@section('admin-content')



<header>
    <div class="header-title">
        <h1>
            <i class="fa-solid fa-chart-line" style="color: #800020; margin-right: 0.5rem;"></i>
            Vote Status - {{ $activeElection->election_name }}
        </h1>
        <p>{{ $activeElection->department }} Department &bull; {{ \Carbon\Carbon::parse($activeElection->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($activeElection->end_date)->format('M d, Y') }}</p>
    </div>
</header>

<div class="status-container">

    <a href="{{ route('admin.votes-status') }}" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i> Back to Election Status
    </a>

    <!-- Header Section -->
    <div class="header-section">
        <div class="header-content">
            <div class="header-left">
                <h1>{{ $activeElection->election_name }}</h1>
                <p><i class="fa-solid fa-building"></i> {{ $activeElection->department }} Department</p>
                <p><i class="fa-solid fa-calendar"></i> {{ \Carbon\Carbon::parse($activeElection->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($activeElection->end_date)->format('M d, Y') }}</p>
            </div>
            <div class="timer-box">
                <div class="timer-label">Time Remaining</div>
                <div class="timer-value" id="countdown">{{ $timeLeft['formatted'] }}</div>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-check-to-slot"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_votes']) }}</div>
            <div class="stat-label">Total Votes Cast</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['eligible_voters']) }}</div>
            <div class="stat-label">Eligible Voters</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div class="stat-value">{{ $stats['turnout_percentage'] }}%</div>
            <div class="stat-label">Voter Turnout</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div class="stat-value">{{ $activeElection->candidates->count() }}</div>
            <div class="stat-label">Total Candidates</div>
        </div>
    </div>

    <!-- Per-Position Leading Cards -->
    @php
        $electionPositions = $activeElection->positions ?? [];
        $candidatesByPos   = $activeElection->candidates->groupBy('position');
        if (empty($electionPositions)) {
            $electionPositions = $candidatesByPos->keys()->toArray();
        }
    @endphp
    @if(count($electionPositions) > 0)
        <div class="leading-section">
            <div class="leading-section-title">
                <i class="fa-solid fa-crown"></i> Currently Leading by Position
            </div>
            <div class="leading-grid">
                @foreach($electionPositions as $pos)
                    @php
                        $posLeader = ($candidatesByPos[$pos] ?? collect())->sortByDesc('votes_count')->first();
                        $hasVotes  = $posLeader && $posLeader->votes_count > 0;
                    @endphp
                    <div class="pos-leader-card {{ $hasVotes ? '' : 'no-votes' }}">
                        <div class="pos-leader-pos"><i class="fa-solid fa-award"></i> {{ $pos }}</div>
                        @if($hasVotes)
                            <div class="pos-leader-body">
                                @if($posLeader->image)
                                    <img src="{{ $posLeader->image }}" alt="{{ $posLeader->full_name }}" class="pos-leader-avatar">
                                @else
                                    <div class="pos-leader-avatar-initials">{{ substr($posLeader->first_name,0,1) }}{{ substr($posLeader->last_name,0,1) }}</div>
                                @endif
                                <div class="pos-leader-name">{{ $posLeader->full_name }}</div>
                                <div class="pos-leader-id">ID: {{ $posLeader->student_id }}</div>
                                <div class="pos-leader-votes">{{ number_format($posLeader->votes_count) }}</div>
                                <div class="pos-leader-votes-label">votes</div>
                            </div>
                        @else
                            <div class="pos-leader-no-votes"><i class="fa-regular fa-hourglass"></i><span>No votes yet</span></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Detailed Results -->
    <div class="results-section">
        <h2 class="section-title">
            <i class="fa-solid fa-poll"></i>
            Detailed Results
        </h2>

        @php
            $candidatesByPosition = $activeElection->candidates->groupBy('position');
        @endphp

        <div>
            @if($candidatesByPosition->count() > 0)
                @foreach($candidatesByPosition as $position => $candidates)
                    <div class="position-group" data-position-group="{{ $position }}">
                        <div class="position-header"><i class="fa-solid fa-trophy"></i> {{ $position }}</div>
                        <div class="candidates-list">
                            @php
                                $totalVotes = $candidates->sum('votes_count');
                                $ranked     = $candidates->sortByDesc('votes_count');
                            @endphp
                            @foreach($ranked as $index => $candidate)
                                <div class="candidate-row">
                                    <div class="rank-badge {{ $index===0?'gold':($index===1?'silver':($index===2?'bronze':'')) }}">{{ $index+1 }}</div>
                                    @if($candidate->image)
                                        <img src="{{ $candidate->image }}" alt="{{ $candidate->full_name }}" class="candidate-avatar">
                                    @else
                                        <div class="candidate-avatar" style="background:linear-gradient(135deg,#e9ecef 0%,#d1d5db 100%);display:flex;align-items:center;justify-content:center;color:#6B7280;font-weight:700;font-size:1.2rem;">{{ substr($candidate->first_name,0,1) }}{{ substr($candidate->last_name,0,1) }}</div>
                                    @endif
                                    <div class="candidate-details">
                                        <div class="candidate-name">{{ $candidate->full_name }}</div>
                                        <div class="candidate-meta">ID: {{ $candidate->student_id }} &bull; {{ $candidate->department }}</div>
                                    </div>
                                    <div class="vote-stats">
                                        <div class="vote-count">
                                            <div class="vote-number">{{ number_format($candidate->votes_count) }}</div>
                                            <div class="vote-text">Votes</div>
                                        </div>
                                        <div class="vote-bar">
                                            @php $pct = $totalVotes > 0 ? round(($candidate->votes_count/$totalVotes)*100,1) : 0; @endphp
                                            <div class="bar-label"><span style="font-weight:600;color:#2b2d42;">{{ $pct }}%</span></div>
                                            <div class="bar-bg"><div class="bar-fill" style="width:{{ $pct }}%"></div></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="no-candidates"><i class="fa-solid fa-user-slash"></i><p>No candidates registered yet.</p></div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
let totalSeconds = {{ $timeLeft['total_seconds'] }};
function updateCountdown() {
    if (totalSeconds <= 0) { document.getElementById('countdown').textContent = 'Ended'; return; }
    const d = Math.floor(totalSeconds / 86400);
    const h = Math.floor((totalSeconds % 86400) / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    const s = totalSeconds % 60;
    document.getElementById('countdown').textContent =
        d + 'd ' + String(h).padStart(2,'0') + 'h ' + String(m).padStart(2,'0') + 'm ' + String(s).padStart(2,'0') + 's';
    totalSeconds--;
}
setInterval(updateCountdown, 1000);

</script>
@endpush
@endsection

