@extends('layouts.student')

@section('title', 'Voting')

@section('student-content')

<style>
/* ── Filter bar (mirrors shared.css) ─────────────────── */
.filter-card {
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
.filter-left {
    display: flex;
    align-items: flex-end;
    gap: 1rem;
    flex-wrap: wrap;
    flex: 1;
}
.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.filter-group label { font-size: 0.8rem; font-weight: 600; color: #1f2937; }
.filter-input {
    padding: 0.55rem 0.9rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #1f2937;
    background: white;
    transition: border-color 0.2s;
    min-width: 150px;
}
.filter-input:focus { outline: none; border-color: #800020; box-shadow: 0 0 0 3px rgba(128,0,32,0.08); }
.filter-select {
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
.filter-select:focus { outline: none; border-color: #800020; }
.filter-actions { display: flex; align-items: flex-end; gap: 0.5rem; }
.btn-apply {
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
.btn-apply:hover { background: #5c0015; transform: translateY(-1px); }
.btn-reset {
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
.btn-reset:hover { background: #111827; transform: translateY(-1px); }
.search-wrapper { display: flex; flex-direction: column; gap: 0.35rem; flex-shrink: 0; }
.search-input {
    padding: 0.55rem 1rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #1f2937;
    background: white;
    min-width: 220px;
    transition: border-color 0.2s;
}
.search-input:focus { outline: none; border-color: #800020; box-shadow: 0 0 0 3px rgba(128,0,32,0.08); }
.search-input::placeholder { color: #9ca3af; }

/* ── Election nav card (mirrors candidates.css) ───────── */
.nav-card {
    position: relative;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
    border: 1px solid #f1f5f9;
    cursor: pointer;
}
.nav-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(128,0,32,0.18);
}

.nav-card.nav-card-dimmed {
    cursor: default;
}

.nav-card.nav-card-dimmed:hover {
    transform: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.nav-card.nav-card-dimmed::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(107, 114, 128, 0.28);
    z-index: 4;
    pointer-events: none;
}

.nav-state-label {
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

.nav-state-label.nav-state-label-disabled {
    font-size: 1.25rem;
    letter-spacing: 0.06em;
}

.nav-card .overlay-trigger:disabled,
.nav-card .card-action-btn:disabled {
    cursor: not-allowed !important;
    opacity: 0.45 !important;
    filter: grayscale(0.35);
    transform: none !important;
    pointer-events: none;
}

.nav-card .card-action-btn {
    position: relative;
    z-index: 8;
}
/* ── Description overlay (mirrors dept-head campus-elections) ── */
.nav-card .desc-overlay {
    position: absolute;
    top: 0;
    right: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(160deg, #800020 0%, #A0153E 100%);
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 2rem;
    text-align: center;
    transition: right 0.4s cubic-bezier(0.4,0,0.2,1);
    z-index: 10;
}
.nav-card.overlay-open .desc-overlay {
    right: 0;
}
.nav-card .overlay-trigger {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 6;
    background: #800020;
    color: white;
    border: none;
    border-radius: 50%;
    width: 34px;
    height: 34px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 10px rgba(128,0,32,0.4);
    transition: background 0.2s, transform 0.2s;
}
.nav-card .overlay-trigger:hover {
    background: #A0153E;
    transform: translateY(-50%) scale(1.1);
}
</style>

<header>
    <div class="header-title">
        <h1>Campus Elections</h1>
        <p>{{ $department }} Department &bull; Active elections are votable, while finished and disabled elections are view-only</p>
    </div>
</header>

@if($activeElections->count() > 0)

<!-- Filter Bar -->
<div class="filter-card">
    <div class="filter-left">
        <div class="filter-group">
            <label>Date From</label>
            <input type="date" id="elecFilterDateFrom" class="filter-input">
        </div>
        <div class="filter-group">
            <label>Date To</label>
            <input type="date" id="elecFilterDateTo" class="filter-input">
        </div>
        <div class="filter-group">
            <label>Sort By</label>
            <select id="elecFilterSort" class="filter-select">
                <option value="">Default</option>
                <option value="newest">Newest</option>
                <option value="oldest">Oldest</option>
                <option value="az">A – Z</option>
            </select>
        </div>
        <div class="filter-actions">
            <button onclick="applyElecFilters()" class="btn-apply">Apply Filters</button>
            <button onclick="resetElecFilters()" class="btn-reset">Reset</button>
        </div>
    </div>
    <div class="search-wrapper">
        <label style="font-size:0.8rem;font-weight:600;color:#1f2937;">Search</label>
        <input type="text" id="elecFilterSearch" class="search-input" placeholder="Search elections..." oninput="applyElecFilters()">
    </div>
</div>

<!-- Election Grid -->
<div class="election-grid" id="electionsGrid">
    @foreach($activeElections as $election)
        @php
            $candidatesCount = $election->candidates_count ?? 0;
            $votedAt = $votedElections[$election->id] ?? null;
            $endDate = \Carbon\Carbon::parse($election->end_date);
            $isFinished = now()->gt($endDate);
            $isDisabled = !$election->is_active && !$isFinished;
        @endphp
        <div class="nav-card slide-top{{ ($isFinished || $isDisabled) ? ' nav-card-dimmed' : '' }}"
             id="nav-card-{{ $election->id }}"
             data-start="{{ \Carbon\Carbon::parse($election->start_date)->format('Y-m-d') }}"
             data-candidates="{{ $candidatesCount }}"
             data-name="{{ strtolower($election->election_name) }}"
             @unless($isFinished || $isDisabled) onclick="window.location.href='{{ route('voting.election', $election->id) }}'" @endunless>

            <!-- Description Overlay -->
            <div class="desc-overlay">
                <button onclick="closeDescOverlay({{ $election->id }}); event.stopPropagation();" style="position:absolute;top:12px;right:14px;background:rgba(255,255,255,0.2);border:none;color:white;border-radius:50%;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:0.9rem;transition:background 0.2s;" onmouseenter="this.style.background='rgba(255,255,255,0.35)'" onmouseleave="this.style.background='rgba(255,255,255,0.2)'">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <i class="fa-solid fa-vote-yea" style="font-size:2.5rem;margin-bottom:1rem;opacity:0.8;"></i>
                <h4 style="font-size:1rem;font-weight:700;margin:0 0 0.75rem;letter-spacing:0.03em;">{{ $election->election_name }}</h4>
                <p style="font-size:0.88rem;line-height:1.6;opacity:0.92;margin:0;">{{ $election->description && $election->description !== 'none' ? $election->description : 'No description provided.' }}</p>
            </div>
            @if($isFinished)
                <div class="nav-state-label">Finished</div>
            @elseif($isDisabled)
                <div class="nav-state-label nav-state-label-disabled">Disabled</div>
            @endif
            <!-- Arrow trigger -->
            <button class="overlay-trigger" @unless($isFinished || $isDisabled) onclick="openDescOverlay({{ $election->id }}); event.stopPropagation();" @else disabled aria-disabled="true" @endunless title="View description">
                <i class="fa-solid fa-chevron-left" style="font-size:0.85rem;"></i>
            </button>

            <!-- Banner -->
            <div style="width:100%; height:200px; background:linear-gradient(135deg, #800020 0%, #A0153E 100%); position:relative; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                @if($election->banner_image)
                    <img src="{{ $election->banner_image }}" alt="{{ $election->election_name }}" style="width:100%; height:100%; object-fit:cover;">
                @else
                    <i class="fa-solid fa-vote-yea" style="font-size:5rem; color:rgba(255,255,255,0.18);"></i>
                @endif

                <!-- Status badge — top right -->
                <div style="position:absolute; top:12px; right:12px; z-index:2;">
                    @if($isFinished)
                        <span style="display:inline-flex; align-items:center; gap:6px; padding:5px 14px; border-radius:20px; font-size:0.82rem; font-weight:600; background:white; color:#1e293b; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                            <span style="width:8px;height:8px;border-radius:50%;background:#6b7280;display:inline-block;"></span>
                            Finished
                        </span>
                    @elseif($isDisabled)
                        <span style="display:flex; align-items:center; justify-content:center; gap:6px; padding:6px 10px; border-radius:14px; font-size:0.74rem; font-weight:700; background:white; color:#334155; box-shadow:0 2px 8px rgba(0,0,0,0.15); width:150px; max-width:150px; text-align:center; line-height:1.2; white-space:normal;">
                            <span style="width:8px;height:8px;border-radius:50%;background:#9ca3af;display:inline-block;"></span>
                            Disabled by Department Head or Faculty Access
                        </span>
                    @elseif($votedAt)
                        <span style="display:inline-flex; align-items:center; gap:6px; padding:5px 14px; border-radius:20px; font-size:0.82rem; font-weight:600; background:#22c55e; color:white; box-shadow:0 2px 8px rgba(0,0,0,0.2);">
                            <i class="fa-solid fa-circle-check" style="font-size:0.8rem;"></i> Voted
                        </span>
                    @else
                        <span style="display:inline-flex; align-items:center; gap:6px; padding:5px 14px; border-radius:20px; font-size:0.82rem; font-weight:600; background:white; color:#1e293b; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                            <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
                            Active
                        </span>
                    @endif
                </div>

                <!-- Candidate count badge — top left -->
                <div style="position:absolute; top:12px; left:12px; z-index:2;">
                    <span style="display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:20px; font-size:0.8rem; font-weight:700; background:white; color:#1e293b; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                        <i class="fa-solid fa-users" style="color:#800020;"></i> {{ $candidatesCount }}
                    </span>
                </div>
            </div>

            <!-- Content -->
            <div style="padding:1.3rem 1.5rem 1.5rem;">
                <p style="font-size:0.72rem; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase; margin:0 0 0.25rem;">{{ $election->department }} DEPARTMENT</p>
                <h3 style="font-size:1.05rem; font-weight:800; color:#1e293b; margin:0 0 0.8rem; text-transform:uppercase; letter-spacing:0.02em; line-height:1.3;">{{ $election->election_name }}</h3>
                <p style="font-size:0.83rem; color:#475569; margin:0 0 0.25rem;">
                    <i class="fa-regular fa-calendar" style="width:14px;"></i>
                    Started: {{ \Carbon\Carbon::parse($election->start_date)->format('M. d, Y') }}
                </p>
                <p style="font-size:0.83rem; color:#475569; margin:0 0 1.25rem;">
                    <i class="fa-regular fa-calendar-xmark" style="width:14px;"></i>
                    Expires: {{ $endDate->format('M. d, Y') }}
                </p>
                @if($isFinished)
                    <button class="btn-hover card-action-btn" disabled aria-disabled="true"
                            style="width:100%; padding:0.6rem; border:2px solid #64748b; border-radius:25px; font-weight:700; font-size:0.82rem; letter-spacing:0.05em; cursor:not-allowed; transition:all 0.3s; background:#64748b; color:white; text-transform:uppercase;">
                        <i class="fa-solid fa-lock"></i>&nbsp; VOTING CLOSED
                    </button>
                @elseif($isDisabled)
                    <button class="btn-hover card-action-btn" disabled aria-disabled="true"
                            style="width:100%; padding:0.6rem; border:2px solid #64748b; border-radius:25px; font-weight:700; font-size:0.82rem; letter-spacing:0.05em; cursor:not-allowed; transition:all 0.3s; background:#64748b; color:white; text-transform:uppercase;">
                        <i class="fa-solid fa-ban"></i>&nbsp; VOTING DISABLED
                    </button>
                @elseif($votedAt)
                    <div style="width:100%; padding:0.75rem 1rem; background:#f0fdf4; border:2px solid #22c55e; border-radius:25px; text-align:center;">
                        <div style="font-weight:800; font-size:0.88rem; color:#15803d; letter-spacing:0.03em; display:flex; align-items:center; justify-content:center; gap:0.45rem; margin-bottom:0.25rem;">
                            <i class="fa-solid fa-circle-check"></i> You've finished voting!
                        </div>
                        <div style="font-size:0.76rem; color:#16a34a; opacity:0.85;">
                            {{ \Carbon\Carbon::parse($votedAt)->format('M. d, Y • h:i A') }}
                        </div>
                    </div>
                @else
                    <button class="btn-hover card-action-btn"
                            style="width:100%; padding:0.6rem; border:2px solid #800020; border-radius:25px; font-weight:700; font-size:0.82rem; letter-spacing:0.05em; cursor:pointer; transition:all 0.3s; background:#800020; color:white; text-transform:uppercase;">
                        <i class="fa-solid fa-check-to-slot"></i>&nbsp; VOTE NOW
                    </button>
                @endif
            </div>
        </div>
    @endforeach

    <!-- No-filter-results placeholder -->
    <div id="elecEmptyFilter" style="display:none; grid-column:1/-1; text-align:center; padding:4rem; background:white; border-radius:16px; color:#888;">
        <i class="fa-solid fa-filter" style="font-size:3rem; margin-bottom:1rem; opacity:0.3;"></i>
        <h3 style="color:#6B7280;">No elections match your filters</h3>
        <p style="color:#9ca3af;">Try adjusting or resetting your filters.</p>
    </div>
</div>

@else
    <div style="text-align:center; padding:4rem 2rem; background:white; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.08);">
        <i class="fa-solid fa-inbox" style="font-size:4rem; color:#d1d5db; margin-bottom:1rem; display:block;"></i>
        <h3 style="color:#6B7280; margin-bottom:0.5rem;">No Elections Found</h3>
        <p style="color:#9ca3af; font-size:0.95rem;">There are currently no active, finished, or disabled elections for {{ $department }} department.</p>
        <p style="color:#9ca3af; font-size:0.9rem; margin-top:0.5rem;">Check back later for new election schedules.</p>
    </div>
@endif

<script>
function openDescOverlay(id) {
    var card = document.getElementById('nav-card-' + id);
    if (card) card.classList.add('overlay-open');
}
function closeDescOverlay(id) {
    var card = document.getElementById('nav-card-' + id);
    if (card) card.classList.remove('overlay-open');
}

function applyElecFilters() {
    var dateFrom = document.getElementById('elecFilterDateFrom') ? document.getElementById('elecFilterDateFrom').value : '';
    var dateTo   = document.getElementById('elecFilterDateTo')   ? document.getElementById('elecFilterDateTo').value   : '';
    var sortBy   = document.getElementById('elecFilterSort')     ? document.getElementById('elecFilterSort').value     : '';
    var search   = document.getElementById('elecFilterSearch')   ? document.getElementById('elecFilterSearch').value.toLowerCase().trim() : '';

    var grid  = document.getElementById('electionsGrid');
    if (!grid) return;
    var cards = Array.from(grid.querySelectorAll('.nav-card'));

    cards.forEach(function (card) {
        var startDate = card.dataset.start || '';
        var cardName  = card.dataset.name  || '';
        var visible   = true;
        if (dateFrom && startDate < dateFrom) visible = false;
        if (dateTo   && startDate > dateTo)   visible = false;
        if (search   && cardName.indexOf(search) === -1) visible = false;
        card.style.display = visible ? '' : 'none';
    });

    var visibleCards = cards.filter(function (c) { return c.style.display !== 'none'; });

    visibleCards.sort(function (a, b) {
        if (sortBy === 'newest') return a.dataset.start < b.dataset.start ?  1 : -1;
        if (sortBy === 'oldest') return a.dataset.start > b.dataset.start ?  1 : -1;
        if (sortBy === 'az')     return (a.dataset.name || '').localeCompare(b.dataset.name || '');
        return 0;
    });
    visibleCards.forEach(function (card) { grid.appendChild(card); });

    var emptyEl = document.getElementById('elecEmptyFilter');
    if (emptyEl) emptyEl.style.display = visibleCards.length === 0 ? 'block' : 'none';
}

function resetElecFilters() {
    ['elecFilterDateFrom', 'elecFilterDateTo', 'elecFilterSort', 'elecFilterSearch'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    applyElecFilters();
}
</script>

@push('styles')
<style>
.election-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}
.slide-top {
    animation: slide-top .5s cubic-bezier(.25,.46,.45,.94) both;
}
@keyframes slide-top {
    0%   { transform: translateY(100px); opacity: 0; }
    100% { transform: translateY(0);     opacity: 1; }
}
.btn-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    opacity: 0.9;
}

@media (max-width: 768px) {
    .nav-state-label {
        font-size: 1.2rem;
    }

    .nav-state-label.nav-state-label-disabled {
        font-size: 0.9rem;
    }
}
</style>
@endpush

@endsection
