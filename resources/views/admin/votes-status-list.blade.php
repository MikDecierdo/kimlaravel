@extends('layouts.admin')

@section('title', 'Vote Status - Admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/votes-status-list.css') }}">
<style>
.vs-stat-box { border: 2px solid #800020; }
.vs-stats-2 { grid-template-columns: 1fr 1fr; }
/* ── Campus-elections style filter bar ────────────────── */
.elec-filter-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    padding: 1.2rem 1.4rem;
    margin-bottom: 1rem;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 0.9rem;
}
.elec-filter-group { display: flex; flex-direction: column; gap: 0.3rem; min-width: 0; }
.elec-filter-group label { font-size: 0.76rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.04em; }
.elec-filter-input, .elec-filter-select {
    padding: 0.52rem 0.85rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #1f2937;
    background: #f9fafb;
    transition: border-color 0.2s;
    outline: none;
}
.elec-filter-input:focus, .elec-filter-select:focus { border-color: #800020; background: #fff; }
.elec-search-group { flex: 1; min-width: 180px; }
.elec-search-group label { font-size: 0.76rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 0.3rem; }
.elec-search-group input { width: 100%; padding: 0.52rem 0.85rem; border: 1.5px solid #e5e7eb; border-radius: 8px; font-size: 0.875rem; background: #f9fafb; outline: none; transition: border-color 0.2s; }
.elec-search-group input:focus { border-color: #800020; background: #fff; }
.elec-filter-actions { display: flex; gap: 0.5rem; align-items: flex-end; }
.elec-btn-apply { padding: 0.52rem 1.1rem; background: linear-gradient(135deg,#800020 0%,#A0153E 100%); color: white; border: none; border-radius: 8px; font-size: 0.82rem; font-weight: 700; cursor: pointer; transition: opacity 0.2s,transform 0.15s; white-space: nowrap; }
.elec-btn-apply:hover { opacity: 0.88; transform: translateY(-1px); }
.elec-btn-reset { padding: 0.52rem 1rem; background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: background 0.2s; white-space: nowrap; }
.elec-btn-reset:hover { background: #e2e8f0; }
.elec-results-bar { font-size: 0.84rem; color: #64748b; font-weight: 500; margin-bottom: 1rem; }
</style>
@endpush

@section('admin-content')

<header>
    <div class="header-title">
        <h1>
            <i class="fa-solid fa-chart-line" style="color:#800020;margin-right:0.5rem;"></i>
            Election Vote Status
        </h1>
        <p>Voting progress across all department elections</p>
    </div>
</header>

@if($activeElections->count() > 0)

    @php
        $allDepts = $activeElections->pluck('department')->unique()->sort()->values();
    @endphp

    <!-- Filter Bar -->
    <div class="elec-filter-card">
        <div class="elec-filter-group">
            <label>Year From</label>
            <input type="number" id="vsFilterYearFrom" class="elec-filter-input"
                   placeholder="e.g. 2024" min="2000" max="2099" style="min-width:120px;">
        </div>
        <div class="elec-filter-group">
            <label>Year To</label>
            <input type="number" id="vsFilterYearTo" class="elec-filter-input"
                   placeholder="e.g. 2026" min="2000" max="2099" style="min-width:120px;">
        </div>
        <div class="elec-filter-group">
            <label>Status</label>
            <select id="vsFilterStatus" class="elec-filter-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="finished">Finished</option>
            </select>
        </div>
        <div class="elec-filter-group">
            <label>Department</label>
            <select id="vsFilterDept" class="elec-filter-select">
                <option value="">All Departments</option>
                @foreach($allDepts as $d)
                    <option value="{{ strtolower($d) }}">{{ $d }}</option>
                @endforeach
            </select>
        </div>
        <div class="elec-search-group">
            <label>Search</label>
            <input type="text" id="vsSearchInput" placeholder="Search elections..." oninput="vsApplyFilters()">
        </div>
        <div class="elec-filter-actions">
            <button class="elec-btn-apply" onclick="vsApplyFilters()"><i class="fa-solid fa-filter"></i> Apply</button>
            <button class="elec-btn-reset" onclick="vsResetFilters()"><i class="fa-solid fa-rotate-left"></i> Reset</button>
        </div>
    </div>
    <div class="elec-results-bar" id="vsResultsBar"></div>

    <div id="vsGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.5rem;margin-top:1rem;">
        @foreach($activeElections as $election)
            @php
                $endDate      = \Carbon\Carbon::parse($election->end_date);
                $startDate    = \Carbon\Carbon::parse($election->start_date);
                $now          = now();
                $daysLeft     = (int) $now->diffInDays($endDate, false);
                $hoursLeft    = (int) $now->diffInHours($endDate, false);
                $isEnded      = $now->gt($endDate);
                $isFinished   = $isEnded;
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
                 data-dept="{{ strtolower($election->department) }}"
                 onclick="window.location.href='{{ route('admin.votes-status.show', $election->id) }}'">

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
                        &nbsp;--&nbsp;
                        {{ $endDate->format('M. d, Y') }}
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

                    <div class="vs-stats vs-stats-2">
                        <div class="vs-stat-box">
                            <div class="vs-stat-num">{{ $election->votes_count ?? 0 }}</div>
                            <div class="vs-stat-lbl">Total Votes</div>
                        </div>
                        <div class="vs-stat-box">
                            <div class="vs-stat-num">{{ $election->candidates_count }}</div>
                            <div class="vs-stat-lbl">Candidates</div>
                        </div>
                    </div>

                    <button class="vs-btn">
                        <i class="fa-solid fa-chart-pie"></i>&nbsp; {{ $isFinished ? 'View Only' : 'View Detailed Results' }}
                    </button>
                </div>
            </div>
        @endforeach

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
        <p>There are currently no elections across any department.</p>
    </div>
@endif

@push('scripts')
<script src="{{ asset('assets/dept-head/js/votes-status-list.js') }}"></script>
<script>
(function () {
    vsApplyFilters = function () {
        var yearFrom = parseInt(document.getElementById('vsFilterYearFrom').value) || null;
        var yearTo   = parseInt(document.getElementById('vsFilterYearTo').value)   || null;
        var status   = document.getElementById('vsFilterStatus') ? document.getElementById('vsFilterStatus').value.toLowerCase() : '';
        var dept     = document.getElementById('vsFilterDept')   ? document.getElementById('vsFilterDept').value.toLowerCase()   : '';
        var search   = document.getElementById('vsSearchInput')  ? document.getElementById('vsSearchInput').value.toLowerCase().trim() : '';

        var cards = document.querySelectorAll('#vsGrid .vs-card');
        var visibleCount = 0;

        cards.forEach(function (card) {
            var cardYear   = parseInt(card.dataset.year);
            var cardStatus = card.dataset.status || '';
            var cardDept   = (card.dataset.dept  || '').toLowerCase();
            var cardName   = (card.dataset.name  || '').toLowerCase();

            var ok = (!yearFrom || cardYear >= yearFrom)
                  && (!yearTo   || cardYear <= yearTo)
                  && (!status   || cardStatus === status)
                  && (!dept     || cardDept === dept)
                  && (!search   || cardName.indexOf(search) !== -1);

            card.style.display = ok ? '' : 'none';
            if (ok) visibleCount++;
        });

        var noResults = document.getElementById('vsNoResults');
        if (noResults) noResults.style.display = visibleCount === 0 ? 'block' : 'none';

        var total = document.querySelectorAll('#vsGrid .vs-card').length;
        var bar = document.getElementById('vsResultsBar');
        if (bar) bar.textContent = 'Showing ' + visibleCount + ' of ' + total + ' election' + (total !== 1 ? 's' : '');
    };

    vsResetFilters = function () {
        ['vsFilterYearFrom','vsFilterYearTo','vsFilterStatus','vsFilterDept','vsSearchInput'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.value = '';
        });
        vsApplyFilters();
    };
})();
</script>
@endpush
@endsection
