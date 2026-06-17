@extends('layouts.student')

@section('title', 'Vote Status - ' . $activeElection->election_name)

@section('student-content')
<style>
    .status-container {
        padding: 0;
        max-width: 100%;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        color: #6B7280;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }

    .back-btn:hover {
        background: #f8f9fa;
        border-color: #800020;
        color: #800020;
    }

    .header-section {
        background: linear-gradient(135deg, #800020 0%, #5c0015 100%);
        padding: 2rem;
        border-radius: 12px;
        color: white;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(128, 0, 32, 0.2);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .header-left h1 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .header-left p {
        opacity: 0.9;
        font-size: 0.95rem;
    }

    .timer-box {
        background: rgba(255, 255, 255, 0.15);
        padding: 1rem 1.5rem;
        border-radius: 10px;
        text-align: center;
        backdrop-filter: blur(10px);
    }

    .timer-label {
        font-size: 0.8rem;
        opacity: 0.9;
        margin-bottom: 0.5rem;
    }

    .timer-value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .stats-overview {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-left: 4px solid #800020;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #800020 0%, #5c0015 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2b2d42;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        color: #6B7280;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ── Per-position leading section ─────────────────────── */
    .leading-section {
        margin-bottom: 2rem;
    }

    .leading-section-title {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        font-size: 1.15rem;
        font-weight: 700;
        color: #2b2d42;
        margin-bottom: 1rem;
    }

    .leading-section-title i {
        color: #FDB927;
    }

    .leading-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
    }

    .pos-leader-card {
        background: white;
        border-radius: 12px;
        border: 2px solid #FDB927;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .pos-leader-card.no-votes {
        border-color: #e5e7eb;
        opacity: 0.85;
    }

    .pos-leader-pos {
        background: linear-gradient(135deg, #800020 0%, #5c0015 100%);
        color: white;
        padding: 0.55rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .pos-leader-pos i {
        color: #FDB927;
        font-size: 0.85rem;
    }

    .pos-leader-body {
        padding: 1.1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.4rem;
        flex: 1;
    }

    .pos-leader-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #FDB927;
        margin-bottom: 0.2rem;
    }

    .pos-leader-avatar-initials {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        border: 3px solid #FDB927;
        background: linear-gradient(135deg, #800020 0%, #5c0015 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 0.2rem;
    }

    .pos-leader-name {
        font-weight: 700;
        font-size: 0.92rem;
        color: #2b2d42;
        line-height: 1.3;
    }

    .pos-leader-id {
        font-size: 0.77rem;
        color: #6B7280;
    }

    .pos-leader-votes {
        margin-top: 0.4rem;
        font-size: 1.9rem;
        font-weight: 800;
        color: #800020;
        line-height: 1;
    }

    .pos-leader-votes-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #9ca3af;
        font-weight: 600;
    }

    .pos-leader-no-votes {
        padding: 1.5rem 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        color: #9ca3af;
        font-size: 0.85rem;
        flex: 1;
    }

    .pos-leader-no-votes i {
        font-size: 1.6rem;
    }

    /* ── Legacy candidate info styles (used in detailed results) ── */
    .candidate-photo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #FDB927;
    }

    .candidate-position {
        color: #800020;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .candidate-id {
        color: #6B7280;
        font-size: 0.9rem;
    }

    .results-section {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2b2d42;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-title i {
        color: #800020;
    }

    .position-group {
        margin-bottom: 2.5rem;
    }

    .position-group:last-child {
        margin-bottom: 0;
    }

    .position-header {
        background: linear-gradient(135deg, #800020 0%, #5c0015 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .candidates-list {
        border: 1px solid #e9ecef;
        border-top: none;
        border-radius: 0 0 8px 8px;
    }

    .candidate-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
        transition: all 0.2s ease;
        gap: 1rem;
    }

    .candidate-row:last-child {
        border-bottom: none;
    }

    .candidate-row:hover {
        background: #f8f9fa;
    }

    .rank-badge {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #800020 0%, #5c0015 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .rank-badge.gold {
        background: linear-gradient(135deg, #FFD700 0%, #FDB927 100%);
        color: #2b2d42;
    }

    .rank-badge.silver {
        background: linear-gradient(135deg, #C0C0C0 0%, #A8A8A8 100%);
    }

    .rank-badge.bronze {
        background: linear-gradient(135deg, #CD7F32 0%, #B8722A 100%);
    }

    .candidate-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 1rem;
        border: 2px solid #e9ecef;
        flex-shrink: 0;
    }

    .candidate-details {
        flex: 1;
        min-width: 0;
        margin-right: auto;
    }

    .candidate-name {
        font-weight: 600;
        color: #2b2d42;
        font-size: 1.05rem;
        margin-bottom: 0.25rem;
    }

    .candidate-meta {
        font-size: 0.85rem;
        color: #6B7280;
    }

    .vote-stats {
        display: flex;
        align-items: center;
        gap: 3rem;
        flex-shrink: 0;
    }

    .vote-count {
        text-align: center;
    }

    .vote-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #800020;
        line-height: 1;
    }

    .vote-text {
        font-size: 0.75rem;
        color: #6B7280;
        text-transform: uppercase;
    }

    .vote-bar {
        width: 200px;
        min-width: 200px;
    }

    .bar-label {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
    }

    .bar-bg {
        height: 10px;
        background: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
    }

    .bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #800020 0%, #5c0015 100%);
        border-radius: 10px;
        transition: width 0.6s ease;
    }

    .no-candidates {
        text-align: center;
        padding: 3rem;
        color: #6B7280;
    }

    .no-candidates i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .stats-overview {
            grid-template-columns: repeat(2, 1fr);
        }

        .vote-stats {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .vote-bar {
            width: 100%;
            min-width: 100%;
        }

        .candidate-row {
            flex-wrap: wrap;
        }

        .rank-badge {
            order: -1;
        }
    }
</style>

<header>
    <div class="header-title">
        <h1>
            <i class="fa-solid fa-chart-line" style="color: #800020; margin-right: 0.5rem;"></i>
            Vote Status - {{ $activeElection->election_name }}
        </h1>
        <p>{{ $activeElection->department }} Department • {{ \Carbon\Carbon::parse($activeElection->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($activeElection->end_date)->format('M d, Y') }}</p>
    </div>
</header>

<div class="status-container">
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
            // Fallback: if positions list is empty, derive from actual candidates
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
                            $posLeader = ($candidatesByPos[$pos] ?? collect())
                                ->sortByDesc('votes_count')
                                ->first();
                            $hasVotes = $posLeader && $posLeader->votes_count > 0;
                        @endphp
                        <div class="pos-leader-card {{ $hasVotes ? '' : 'no-votes' }}">
                            <div class="pos-leader-pos">
                                <i class="fa-solid fa-award"></i>
                                {{ $pos }}
                            </div>
                            @if($hasVotes)
                                <div class="pos-leader-body">
                                    @if($posLeader->image)
                                        <img src="{{ $posLeader->image }}"
                                             alt="{{ $posLeader->full_name }}"
                                             class="pos-leader-avatar">
                                    @else
                                        <div class="pos-leader-avatar-initials">
                                            {{ substr($posLeader->first_name, 0, 1) }}{{ substr($posLeader->last_name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="pos-leader-name">{{ $posLeader->full_name }}</div>
                                    <div class="pos-leader-id">ID: {{ $posLeader->student_id }}</div>
                                    <div class="pos-leader-votes">{{ number_format($posLeader->votes_count) }}</div>
                                    <div class="pos-leader-votes-label">votes</div>
                                </div>
                            @else
                                <div class="pos-leader-no-votes">
                                    <i class="fa-regular fa-hourglass"></i>
                                    <span>No votes yet</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Detailed Results by Position -->
        <div class="results-section">
            <h2 class="section-title">
                <i class="fa-solid fa-poll"></i>
                Detailed Results by Position
            </h2>

            @php
                $candidatesByPosition = $activeElection->candidates->groupBy('position');
            @endphp

            @if($candidatesByPosition->count() > 0)
                @foreach($candidatesByPosition as $position => $candidates)
                    <div class="position-group">
                        <div class="position-header">
                            <i class="fa-solid fa-trophy"></i> {{ $position }}
                        </div>
                        <div class="candidates-list">
                            @php
                                $totalVotesForPosition = $candidates->sum('votes_count');
                                $rankedCandidates = $candidates->sortByDesc('votes_count');
                            @endphp

                            @foreach($rankedCandidates as $index => $candidate)
                                <div class="candidate-row">
                                    <div class="rank-badge {{ $index === 0 ? 'gold' : ($index === 1 ? 'silver' : ($index === 2 ? 'bronze' : '')) }}">
                                        {{ $index + 1 }}
                                    </div>

                                    @if($candidate->image)
                                        <img src="{{ $candidate->image }}" alt="{{ $candidate->full_name }}" class="candidate-avatar">
                                    @else
                                        <div class="candidate-avatar" style="background: linear-gradient(135deg, #e9ecef 0%, #d1d5db 100%); display: flex; align-items: center; justify-content: center; color: #6B7280; font-weight: 700; font-size: 1.2rem;">
                                            {{ substr($candidate->first_name, 0, 1) }}{{ substr($candidate->last_name, 0, 1) }}
                                        </div>
                                    @endif

                                    <div class="candidate-details">
                                        <div class="candidate-name">{{ $candidate->full_name }}</div>
                                        <div class="candidate-meta">
                                            ID: {{ $candidate->student_id }} • {{ $candidate->department }}
                                        </div>
                                    </div>

                                    <div class="vote-stats">
                                        <div class="vote-count">
                                            <div class="vote-number">{{ number_format($candidate->votes_count) }}</div>
                                            <div class="vote-text">Votes</div>
                                        </div>

                                        <div class="vote-bar">
                                            @php
                                                $percentage = $totalVotesForPosition > 0 
                                                    ? round(($candidate->votes_count / $totalVotesForPosition) * 100, 1) 
                                                    : 0;
                                            @endphp
                                            <div class="bar-label">
                                                <span style="font-weight: 600; color: #2b2d42;">{{ $percentage }}%</span>
                                            </div>
                                            <div class="bar-bg">
                                                <div class="bar-fill" style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="no-candidates">
                    <i class="fa-solid fa-user-slash"></i>
                    <p>No candidates registered for this election yet.</p>
                </div>
            @endif
        </div>
</div>

<script>
    // Countdown timer
    let totalSeconds = {{ $timeLeft['total_seconds'] }};
    
    function updateCountdown() {
        if (totalSeconds <= 0) {
            document.getElementById('countdown').textContent = 'Ended';
            return;
        }
        
        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        
        const formatted = `${days}d ${String(hours).padStart(2, '0')}h ${String(minutes).padStart(2, '0')}m ${String(seconds).padStart(2, '0')}s`;
        document.getElementById('countdown').textContent = formatted;
        
        totalSeconds--;
    }
    
    // Update every second
    setInterval(updateCountdown, 1000);
</script>
@endsection
