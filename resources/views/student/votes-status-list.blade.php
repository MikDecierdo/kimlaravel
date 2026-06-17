@extends('layouts.student')

@section('title', 'Vote Status')

@section('student-content')

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
                <option value="inactive">Disabled by Department Head</option>
                <option value="upcoming">Upcoming</option>
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
            $endDate      = \Carbon\Carbon::parse($election->end_date);
            $startDate    = \Carbon\Carbon::parse($election->start_date);
            $now          = now();
            $daysLeft     = (int) $now->diffInDays($endDate, false);
            $hoursLeft    = (int) $now->diffInHours($endDate, false);
            $isEnded      = $now->gt($endDate);
            $isNotStarted = $now->lt($startDate);

            if ($isEnded) {
                $statusDot   = '#64748b';
                $statusLabel = 'Finished';
                $statusKey   = 'finished';
            } elseif (!$election->is_active) {
                $statusDot   = '#9ca3af';
                $statusLabel = 'Disabled by Department Head';
                $statusKey   = 'inactive';
            } elseif ($isNotStarted) {
                $statusDot   = '#0ea5e9';
                $statusLabel = 'Upcoming';
                $statusKey   = 'upcoming';
            } else {
                $statusDot   = '#22c55e';
                $statusLabel = 'Active';
                $statusKey   = 'active';
            }

            $isFinished = $statusKey === 'finished';
        @endphp
        <div class="vs-card{{ $isFinished ? ' vs-card-finished' : '' }}"
            data-name="{{ strtolower($election->election_name) }}"
            data-year="{{ $election->created_at->format('Y') }}"
            data-status="{{ $statusKey }}"
            onclick="window.location.href='{{ route('student.votes-status.show', $election->id) }}'">

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
                    {{ $startDate->format('M. d, Y') }}
                    &nbsp;–&nbsp;
                    {{ $endDate->format('M. d, Y') }}
                </p>

                <p class="vs-meta" style="margin-bottom:0;">
                    @if($statusKey === 'inactive')
                        <strong>Notice:</strong> This election is currently disabled by the Department Head.
                    @elseif($statusKey === 'finished')
                        <strong>Notice:</strong> This election has finished.
                    @elseif($statusKey === 'upcoming')
                        <strong>Starts in:</strong>
                        @if($daysLeft > 0)
                            {{ $daysLeft }} Day{{ $daysLeft !== 1 ? 's' : '' }}
                        @elseif($hoursLeft > 0)
                            {{ $hoursLeft }} Hour{{ $hoursLeft !== 1 ? 's' : '' }}
                        @else
                            Less than an hour
                        @endif
                    @else
                        <strong>
                            @if($daysLeft > 0)
                                {{ $daysLeft }}
                            @elseif($hoursLeft > 0)
                                {{ $hoursLeft }}
                            @else
                                0
                            @endif
                        </strong>
                        @if($daysLeft > 0)
                            Day{{ $daysLeft !== 1 ? 's' : '' }} left
                        @elseif($hoursLeft > 0)
                            Hour{{ $hoursLeft !== 1 ? 's' : '' }} left
                        @else
                            Ending soon
                        @endif
                    @endif
                </p>

                <div class="vs-stats" style="grid-template-columns: 1fr;">
                    <div class="vs-stat-box">
                        <div class="vs-stat-num">{{ $election->votes_count ?? 0 }}</div>
                        <div class="vs-stat-lbl">Total Votes</div>
                    </div>
                </div>

                <button class="vs-btn" @if($isFinished) aria-disabled="true" @endif>
                    <i class="fa-solid fa-chart-pie"></i>&nbsp; {{ $isFinished ? 'View Only' : 'View Detailed Results' }}
                </button>
            </div>
        </div>
    @endforeach

    <!-- No results placeholder (shown by JS) -->
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

<style>
/* ── Election card ─────────────────────────────────────── */
.vs-card {
    position: relative;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #f1f5f9;
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
}
.vs-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(128,0,32,0.18);
}

.vs-card.vs-card-finished::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(107, 114, 128, 0.28);
    z-index: 4;
    pointer-events: none;
}

.vs-finished-label {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    z-index: 7;
    font-size: 2rem;
    font-weight: 900;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #ffffff;
    -webkit-text-stroke: 2px #800020;
    text-shadow:
        0 2px 4px rgba(0, 0, 0, 0.55),
        0 6px 14px rgba(0, 0, 0, 0.45),
        1px 1px 0 #800020,
        -1px -1px 0 #800020,
        1px -1px 0 #800020,
        -1px 1px 0 #800020;
    white-space: nowrap;
    pointer-events: none;
}
.vs-banner {
    width: 100%;
    height: 180px;
    background: linear-gradient(135deg, #800020 0%, #A0153E 100%);
    position: relative;
    overflow: hidden;
    display: flex; align-items: center; justify-content: center;
}
.vs-banner img { width: 100%; height: 100%; object-fit: cover; }
.vs-banner-placeholder { font-size: 4.5rem; color: rgba(255,255,255,0.15); }
.vs-status-badge {
    position: absolute;
    top: 12px; right: 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    max-width: calc(100% - 24px);
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 600;
    background: white;
    color: #1e293b;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.vs-body {
    padding: 1.25rem 1.4rem 1.4rem;
    text-align: center;
}
.vs-dept {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    color: #1e293b;
    margin: 0 0 0.3rem;
}
.vs-name {
    font-size: 1.1rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    color: #1e293b;
    margin: 0 0 0.55rem;
    line-height: 1.3;
}
.vs-dates { font-size: 0.82rem; color: #64748b; margin: 0 0 0.6rem; }
.vs-meta  { font-size: 0.88rem; color: #64748b; margin: 0 0 0.3rem; line-height: 1.5; }
.vs-meta strong { color: #800020; font-weight: 800; }
.vs-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin: 1rem 0;
}
.vs-stat-box { border-radius: 14px; padding: 0.75rem 0.5rem; text-align: center; }
.vs-stat-num { font-size: 1.6rem; font-weight: 800; color: #1e293b; line-height: 1; margin-bottom: 0.35rem; }
.vs-stat-lbl { font-size: 0.75rem; color: #64748b; font-weight: 500; }
.vs-btn {
    display: block;
    position: relative;
    z-index: 8;
    width: 100%;
    padding: 0.75rem;
    background: #800020;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s, box-shadow 0.2s;
    text-align: center;
}
.vs-btn:hover { background: #5c0015; box-shadow: 0 4px 14px rgba(128,0,32,0.35); }

.vs-btn[aria-disabled="true"] {
    background: #64748b;
    cursor: not-allowed;
    box-shadow: none;
    pointer-events: none;
}

.vs-btn[aria-disabled="true"]:hover {
    background: #64748b;
    box-shadow: none;
}

/* ── Empty / no-results ────────────────────────────────── */
.no-elections {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 12px;
    margin-top: 2rem;
}
.no-elections i { font-size: 4rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
.no-elections h3 { color: #6B7280; margin-bottom: 0.5rem; }
.no-elections p  { color: #9ca3af; }
.vs-no-results {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 12px;
}
.vs-no-results i  { font-size: 3rem; color: #d1d5db; display: block; margin-bottom: 1rem; }
.vs-no-results h3 { color: #6B7280; margin-bottom: 0.5rem; }
.vs-no-results p  { color: #9ca3af; }

/* ── Filter bar ────────────────────────────────────────── */
.vs-filter-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}
.vs-filter-left { display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap; flex: 1; }
.vs-filter-group { display: flex; flex-direction: column; gap: 0.35rem; }
.vs-filter-group label { font-size: 0.8rem; font-weight: 600; color: #1f2937; }
.vs-filter-input {
    padding: 0.55rem 0.9rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #1f2937;
    background: white;
    transition: border-color 0.2s;
    min-width: 150px;
}
.vs-filter-input:focus { outline: none; border-color: #800020; box-shadow: 0 0 0 3px rgba(128,0,32,0.08); }
.vs-filter-select {
    padding: 0.55rem 0.9rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #1f2937;
    background: white;
    transition: border-color 0.2s;
    min-width: 160px;
    cursor: pointer;
}
.vs-filter-select:focus { outline: none; border-color: #800020; }
.vs-filter-actions { display: flex; align-items: flex-end; gap: 0.5rem; }
.vs-btn-apply {
    padding: 0.55rem 1.4rem;
    background: #800020;
    color: white;
    border: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.88rem;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
}
.vs-btn-apply:hover { background: #5c0015; transform: translateY(-1px); }
.vs-btn-reset {
    padding: 0.55rem 1.4rem;
    background: #1f2937;
    color: white;
    border: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.88rem;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
}
.vs-btn-reset:hover { background: #111827; transform: translateY(-1px); }
.vs-search-wrapper { display: flex; flex-direction: column; gap: 0.35rem; flex-shrink: 0; }
.vs-search-input {
    padding: 0.55rem 1rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #1f2937;
    background: white;
    min-width: 220px;
    transition: border-color 0.2s;
}
.vs-search-input:focus { outline: none; border-color: #800020; box-shadow: 0 0 0 3px rgba(128,0,32,0.08); }
.vs-search-input::placeholder { color: #9ca3af; }

@media (max-width: 768px) {
    .vs-finished-label {
        font-size: 1.2rem;
    }
}
</style>

<script>
function vsApplyFilters() {
    var yearFrom = parseInt(document.getElementById('vsFilterYearFrom').value) || null;
    var yearTo   = parseInt(document.getElementById('vsFilterYearTo').value)   || null;
    var status   = document.getElementById('vsFilterStatus')  ? document.getElementById('vsFilterStatus').value.toLowerCase()       : '';
    var search   = document.getElementById('vsSearchInput')   ? document.getElementById('vsSearchInput').value.toLowerCase().trim() : '';

    var cards = document.querySelectorAll('#vsGrid .vs-card');
    var visibleCount = 0;

    cards.forEach(function (card) {
        var cardYear   = parseInt(card.dataset.year);
        var cardStatus = card.dataset.status || '';
        var cardName   = (card.dataset.name  || '').toLowerCase();

        var matchYear   = (!yearFrom || cardYear >= yearFrom) && (!yearTo || cardYear <= yearTo);
        var matchStatus = !status || cardStatus === status;
        var matchSearch = !search || cardName.indexOf(search) !== -1;

        if (matchYear && matchStatus && matchSearch) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    var noResults = document.getElementById('vsNoResults');
    if (noResults) noResults.style.display = visibleCount === 0 ? 'block' : 'none';
}

function vsResetFilters() {
    ['vsFilterYearFrom', 'vsFilterYearTo', 'vsFilterStatus', 'vsSearchInput'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    vsApplyFilters();
}
</script>
@endsection
