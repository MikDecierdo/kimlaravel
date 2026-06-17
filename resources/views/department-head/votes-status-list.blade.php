@extends('layouts.department-head')

@section('title', 'Vote Status - Department Head')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/votes-status-list.css') }}">
@endpush

@section('dept-head-content')

<header>
    <div class="header-title">
        <h1>Vote Status</h1>
        <p>Voting progress for all elections</p>
    </div>
</header>

    @if($activeElections->count() > 0)
    <!-- Filter Bar -->
    <div class="vs-filter-card">
        <div class="vs-filter-left">
            <div class="vs-filter-group">
                <label>Year Created From</label>
                <input type="number" id="vsFilterYearFrom" class="vs-filter-input" placeholder="e.g. 2024" min="2000" max="2099" style="min-width:130px;">
            </div>
            <div class="vs-filter-group">
                <label>Year Created To</label>
                <input type="number" id="vsFilterYearTo" class="vs-filter-input" placeholder="e.g. 2026" min="2000" max="2099" style="min-width:130px;">
            </div>
            <div class="vs-filter-group">
                <label>Status</label>
                <select id="vsFilterStatus" class="vs-filter-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="finished">Finished</option>
                </select>
            </div>
            <div class="vs-filter-actions">
                <button onclick="vsApplyFilters()" class="vs-btn-apply">Apply Filters</button>
                <button onclick="vsResetFilters()" class="vs-btn-reset">Reset</button>
            </div>
        </div>
        <div class="vs-search-wrapper">
            <label style="font-size:0.8rem;font-weight:600;color:#1f2937;">Search</label>
            <input type="text" id="vsSearchInput" class="vs-search-input" placeholder="Search elections..." oninput="vsApplyFilters()">
        </div>
    </div>

        <div id="vsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
            @foreach($activeElections as $election)
                @php
                    $endDate   = \Carbon\Carbon::parse($election->end_date);
                    $startDate = \Carbon\Carbon::parse($election->start_date);
                    $now       = now();
                    $daysLeft  = (int) $now->diffInDays($endDate, false);
                    $hoursLeft = (int) $now->diffInHours($endDate, false);
                    $isEnded   = $now->gt($endDate);
                    $isFinished = $isEnded;
                    $isNotStarted = $now->lt($startDate);
                    if ($election->is_active && !$isEnded && !$isNotStarted) {
                        $statusDot   = '#22c55e';
                        $statusLabel = 'Active';
                        $statusKey   = 'active';
                    } elseif ($isEnded) {
                        $statusDot   = '#64748b';
                        $statusLabel = 'Finished';
                        $statusKey   = 'finished';
                    } else {
                        $statusDot   = '#9ca3af';
                        $statusLabel = 'Inactive';
                        $statusKey   = 'inactive';
                    }
                @endphp
                <div class="vs-card{{ $isFinished ? ' vs-card-finished' : '' }}"
                    data-name="{{ strtolower($election->election_name) }}"
                    data-year="{{ $election->created_at->format('Y') }}"
                    data-status="{{ $statusKey }}"
                    onclick="window.location.href='{{ route('department-head.votes-status.show', $election->id) }}'">

                    @if($isFinished)
                        <div class="vs-finished-label">Finished</div>
                    @endif

                    <!-- Banner -->
                    <div class="vs-banner">
                        @if($election->banner_image)
                            <img src="{{ $election->banner_image }}" alt="{{ $election->election_name }}">
                        @else
                            <i class="fa-solid fa-vote-yea vs-banner-placeholder"></i>
                        @endif
                        <!-- Status Badge -->
                        <div class="vs-status-badge">
                            <span style="width:8px;height:8px;border-radius:50%;background:{{ $statusDot }};display:inline-block;"></span>
                            {{ $statusLabel }}
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="vs-body">
                        <p class="vs-dept">{{ $election->department }} Department</p>
                        <h3 class="vs-name">{{ $election->election_name }}</h3>
                        <p class="vs-dates">
                            {{ \Carbon\Carbon::parse($election->start_date)->format('M. d, Y') }}
                            &nbsp;–&nbsp;
                            {{ $endDate->format('M, d, Y') }}
                        </p>

                        <p class="vs-meta" style="margin-bottom:0;">
                            @if($statusKey === 'finished')
                                <strong>Notice:</strong> This election has finished.
                            @elseif($statusKey === 'inactive')
                                <strong>Notice:</strong> This election is currently inactive.
                            @elseif($daysLeft > 0)
                                <strong>{{ $daysLeft }}</strong> Day{{ $daysLeft !== 1 ? 's' : '' }} left
                            @elseif($hoursLeft > 0)
                                <strong>{{ $hoursLeft }}</strong> Hour{{ $hoursLeft !== 1 ? 's' : '' }} left
                            @else
                                <strong>Notice:</strong> Ending soon
                            @endif
                        </p>

                        <!-- Stat boxes -->
                        <div class="vs-stats" style="grid-template-columns: 1fr;">
                            <div class="vs-stat-box">
                                <div class="vs-stat-num">{{ $election->votes_count ?? 0 }}</div>
                                <div class="vs-stat-lbl">Total Votes</div>
                            </div>
                        </div>

                        <button class="vs-btn">
                            <i class="fa-solid fa-chart-pie"></i>&nbsp; {{ $isFinished ? 'View Only' : 'View Detailed Results' }}
                        </button>
                    </div>
                </div>
            @endforeach
            <!-- No results row (shown by JS) -->
            <div id="vsNoResults" class="vs-no-results" style="display:none;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h3>No Matching Elections</h3>
                <p>Try adjusting your filters or search term.</p>
            </div>
        </div>
    @else
        <div class="no-elections">
            <i class="fa-solid fa-inbox"></i>
            <h3>No Elections Found</h3>
            <p>There are currently no elections in your department.</p>
        </div>
    @endif

@push('scripts')
<script src="{{ asset('assets/dept-head/js/votes-status-list.js') }}"></script>
@endpush
@endsection
