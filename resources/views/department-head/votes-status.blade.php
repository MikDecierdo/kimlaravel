@extends('layouts.department-head')

@section('title', 'Vote Status - ' . $activeElection->election_name)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/votes-status.css') }}">
@endpush

@section('dept-head-content')

<header>
    <div class="header-title">
        <h1>
            Vote Status
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

        @php
            $partylistCandidates = $activeElection->candidates->groupBy(function ($candidate) {
                return trim((string) $candidate->partylist) !== '' ? trim((string) $candidate->partylist) : 'Independent';
            });

            $partylistTeams = collect($activeElection->partylist_teams ?? [])
                ->map(function ($team) {
                    return trim((string) ($team['name'] ?? ''));
                })
                ->filter()
                ->unique()
                ->values();

            $partylistTaglines = collect($activeElection->partylist_teams ?? [])
                ->filter(function ($team) {
                    return trim((string) ($team['name'] ?? '')) !== '';
                })
                ->mapWithKeys(function ($team) {
                    return [trim((string) ($team['name'] ?? '')) => trim((string) ($team['tagline'] ?? ''))];
                });

            $partylistEntries = [];
            $partylistNames = $partylistCandidates->keys()->toArray();
            foreach ($partylistTeams as $partylistName) {
                if (!in_array($partylistName, $partylistNames, true)) {
                    $partylistNames[] = $partylistName;
                }
            }

            foreach ($partylistNames as $partylistName) {
                $candidates = $partylistCandidates[$partylistName] ?? collect();
                $votes = $candidates->sum('votes_count');
                $partylistEntries[] = [
                    'name' => $partylistName,
                    'tagline' => $partylistTaglines[$partylistName] ?? ($partylistName === 'Independent' ? 'No partylist affiliation' : ''),
                    'candidates' => $candidates->count(),
                    'votes' => $votes,
                    'percentage' => $stats['total_votes'] > 0 ? round(($votes / $stats['total_votes']) * 100, 1) : 0,
                ];
            }
        @endphp

        @if(count($partylistEntries) > 0)
            <div class="partylist-section">
                <div class="section-title">
                    <i class="fa-solid fa-flag"></i>
                    Partylist Status
                </div>
                <div class="partylist-grid">
                    @foreach($partylistEntries as $entry)
                        <div class="partylist-card">
                            <div class="partylist-name">{{ $entry['name'] }}</div>
                            @if($entry['tagline'])
                                <div class="partylist-tagline">{{ $entry['tagline'] }}</div>
                            @endif
                            <div class="partylist-stats">
                                <div class="partylist-stat">
                                    <div class="partylist-label">Candidates</div>
                                    <div class="partylist-value">{{ $entry['candidates'] }}</div>
                                </div>
                                <div class="partylist-stat">
                                    <div class="partylist-label">Total Votes</div>
                                    <div class="partylist-value">{{ number_format($entry['votes']) }}</div>
                                </div>
                                <div class="partylist-stat">
                                    <div class="partylist-label">Share</div>
                                    <div class="partylist-value">{{ $entry['percentage'] }}%</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

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
                                            @if(trim((string) $candidate->partylist) !== '')
                                                <span class="candidate-partylist-chip">{{ $candidate->partylist }}</span>
                                            @else
                                                <span class="candidate-partylist-chip candidate-partylist-independent">Independent</span>
                                            @endif
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

@push('scripts')
<script>
let totalSeconds = {{ $timeLeft['total_seconds'] }};
</script>
<script src="{{ asset('assets/dept-head/js/votes-status.js') }}"></script>
@endpush
@endsection
