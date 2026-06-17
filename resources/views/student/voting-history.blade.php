@extends('layouts.student')

@section('title', 'Vote History')

@section('student-content')
@php
    $totalVotes     = $votes->count();
    $totalElections = $votes->map(fn($v) => optional($v->candidate)->campus_election_id)->filter()->unique()->count();
    $positions      = $votes->map(fn($v) => optional($v->candidate)->position)->filter()->unique()->sort()->values();
    $elections      = $votes->map(fn($v) => optional($v->candidate?->campusElection)->election_name)->filter()->unique()->sort()->values();
@endphp
<style>
/* ── Page Header ─────────────────────────── */
.page-header {
    margin-bottom: 1.5rem;
}
.page-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2b2d42;
    margin-bottom: 0.2rem;
}

    .page-header p {
    color: #6B7280;
    font-size: 0.9rem;
}

/* ── Summary Boxes ───────────────────────── */
.summary-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.summary-box {
    background: linear-gradient(135deg, #800020 0%, #5c0015 100%);
    color: #fff;
    border-radius: 10px;
    padding: 1rem 1.5rem;
    min-width: 160px;
    flex: 1;
    box-shadow: 0 2px 8px rgba(128,0,32,0.25);
}
.summary-box .val {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
}
.summary-box .lbl {
    font-size: 0.78rem;
    opacity: 0.85;
    margin-top: 0.3rem;
    font-weight: 500;
}
.summary-box.light {
    background: #fff;
    color: #800020;
    border: 2px solid #ead0d6;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.summary-box.light .val { color: #800020; }
.summary-box.light .lbl { color: #6B7280; opacity: 1; }

/* ── Filter Card (mirrors shared.css) ────── */
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
.filter-group label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #1f2937;
}
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
.filter-input:focus {
    outline: none;
    border-color: #800020;
    box-shadow: 0 0 0 3px rgba(128,0,32,0.08);
}
.filter-select {
    padding: 0.55rem 0.9rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #1f2937;
    background: white;
    transition: border-color 0.2s;
    min-width: 175px;
    cursor: pointer;
}
.filter-select:focus {
    outline: none;
    border-color: #800020;
    box-shadow: 0 0 0 3px rgba(128,0,32,0.08);
}
.filter-actions {
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
}
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
.search-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    flex-shrink: 0;
}
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
.search-input:focus {
    outline: none;
    border-color: #800020;
    box-shadow: 0 0 0 3px rgba(128,0,32,0.08);
}
.search-input::placeholder { color: #9ca3af; }

/* ── Table Wrapper (mirrors students.css) ── */
.table-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
}
.history-tbl {
    width: 100%;
    border-collapse: collapse;
}
.history-tbl thead tr { background: #800020; }
.history-tbl thead th {
    padding: 1rem 1.2rem;
    color: white;
    font-weight: 700;
    font-size: 0.8rem;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    text-align: left;
    white-space: nowrap;
    cursor: pointer;
    user-select: none;
}
.history-tbl thead th.no-sort { cursor: default; }
.history-tbl thead th:not(.no-sort):hover { background: #6d0018; }
.history-tbl thead th .sort-icon { margin-left: 6px; opacity: 0.75; }
.history-tbl tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
}
.history-tbl tbody tr:nth-child(even) { background: #f9fafb; }
.history-tbl tbody tr:hover { background: #f1f5f9 !important; }
.history-tbl tbody td {
    padding: 1rem 1.2rem;
    font-size: 0.9rem;
    color: #374151;
    vertical-align: middle;
}
.election-name-cell { color: #800020; font-weight: 600; }
.candidate-name     { font-weight: 600; color: #1f2937; }
.position-tag {
    display: inline-block;
    padding: 0.22rem 0.65rem;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    background: #ede9fe;
    color: #5b21b6;
}
.badge-active {
    display: inline-block;
    padding: 0.28rem 0.75rem;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 700;
    background: #d1fae5;
    color: #065f46;
}
.badge-ended {
    display: inline-block;
    padding: 0.28rem 0.75rem;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 700;
    background: #fee2e2;
    color: #991b1b;
}
.badge-removed {
    display: inline-block;
    padding: 0.28rem 0.75rem;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 700;
    background: #f3f4f6;
    color: #6B7280;
}
.voted-date-main  { display: block; }
.voted-date-time  { display: block; font-size: 0.77rem; color: #9ca3af; margin-top: 1px; }

/* ── Table Footer / Pagination ───────────── */
.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-top: 1px solid #f1f5f9;
    font-size: 0.85rem;
    color: #6b7280;
}
.pagination-btns {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}
.page-btn {
    padding: 0.35rem 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: white;
    color: #374151;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.2s;
    min-width: 36px;
    text-align: center;
}
.page-btn:hover:not(:disabled) { border-color: #800020; color: #800020; }
.page-btn.active { background: #800020; color: white; border-color: #800020; font-weight: 700; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* ── Empty / No-results State ────────────── */
.empty-state {
    text-align: center;
    padding: 4rem 1rem;
    color: #6B7280;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.empty-state i { font-size: 3rem; color: #d1d5db; margin-bottom: 1rem; }
.empty-state h3 { font-size: 1.1rem; font-weight: 600; color: #4B5563; margin-bottom: 0.5rem; }
.empty-state a  { color: #800020; text-decoration: none; font-weight: 600; }
.empty-state a:hover { text-decoration: underline; }
.no-results-row td {
    text-align: center;
    padding: 2.5rem;
    color: #9ca3af;
    font-style: italic;
}
</style>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fa-solid fa-clock-rotate-left" style="color:#800020; margin-right:0.5rem;"></i>Vote History</h1>
    <p>A complete record of all the votes you have cast across every election.</p>
</div>

<!-- Summary Boxes -->
<div class="summary-row">
    <div class="summary-box">
        <div class="val" id="summaryTotal">{{ $totalVotes }}</div>
        <div class="lbl">Total Votes Cast</div>
    </div>
    <div class="summary-box light">
        <div class="val">{{ $totalElections }}</div>
        <div class="lbl">Elections Participated</div>
    </div>
    <div class="summary-box light">
        <div class="val" id="summaryFiltered">{{ $totalVotes }}</div>
        <div class="lbl">Matching Current Filter</div>
    </div>
</div>

@if($votes->isEmpty())
<div class="empty-state">
    <i class="fa-solid fa-box-open"></i>
    <h3>No votes yet</h3>
    <p>You haven't cast any votes. Head to <a href="{{ route('voting') }}">Voting</a> to participate in an election.</p>
</div>
@else

<!-- Filter Bar -->
<div class="filter-card">
    <div class="filter-left">
        <div class="filter-group">
            <label>Date From</label>
            <input type="date" id="filterDateFrom" class="filter-input">
        </div>
        <div class="filter-group">
            <label>Date To</label>
            <input type="date" id="filterDateTo" class="filter-input">
        </div>
        <div class="filter-group">
            <label>Position</label>
            <select id="filterPosition" class="filter-select">
                <option value="">All Positions</option>
                @foreach($positions as $pos)
                    <option value="{{ strtolower($pos) }}">{{ $pos }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label>Election</label>
            <select id="filterElection" class="filter-select">
                <option value="">All Elections</option>
                @foreach($elections as $elec)
                    <option value="{{ strtolower($elec) }}">{{ $elec }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-actions">
            <button onclick="applyFilters()" class="btn-apply">Apply Filters</button>
            <button onclick="resetFilters()" class="btn-reset">Reset</button>
        </div>
    </div>
    <div class="search-wrapper">
        <label style="font-size:0.8rem;font-weight:600;color:#1f2937;">Search</label>
        <input type="text" id="searchInput" class="search-input" placeholder="Search candidate or election..." oninput="applyFilters()">
    </div>
</div>

<!-- Table -->
<div class="table-wrapper">
    <table class="history-tbl">
        <thead>
            <tr>
                <th onclick="sortTable(0)">NO. <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="sortTable(1)">ELECTION <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="sortTable(2)">DEPARTMENT <span class="sort-icon">&#x21C5;</span></th>
                <th class="no-sort">POSITION</th>
                <th onclick="sortTable(4)">CANDIDATE VOTED <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="sortTable(5)">DATE VOTED <span class="sort-icon">&#x21C5;</span></th>
                <th class="no-sort">STATUS</th>
            </tr>
        </thead>
        <tbody id="historyTbody">
            @foreach($votes as $vote)
            @php
                $candidate = $vote->candidate;
                $election  = optional($candidate)->campusElection;
            @endphp
            <tr
                data-candidate="{{ strtolower(optional($candidate)->full_name ?? '') }}"
                data-election="{{ strtolower(optional($election)->election_name ?? '') }}"
                data-department="{{ strtolower(optional($election)->department ?? '') }}"
                data-position="{{ strtolower(optional($candidate)->position ?? '') }}"
                data-date="{{ $vote->created_at->format('Y-m-d') }}"
                data-datetime="{{ $vote->created_at->timestamp }}"
            >
                <td>{{ $loop->iteration }}</td>
                <td class="election-name-cell">{{ optional($election)->election_name ?? '—' }}</td>
                <td>{{ optional($election)->department ?? '—' }}</td>
                <td><span class="position-tag">{{ optional($candidate)->position ?? '—' }}</span></td>
                <td class="candidate-name">{{ optional($candidate)->full_name ?? '—' }}</td>
                <td>
                    <span class="voted-date-main">{{ $vote->created_at->format('M d, Y') }}</span>
                    <span class="voted-date-time">{{ $vote->created_at->format('h:i A') }}</span>
                </td>
                <td>
                    @if(!$election)
                        <span class="badge-removed">Removed</span>
                    @elseif($election->is_active && $election->end_date >= now())
                        <span class="badge-active">Active</span>
                    @else
                        <span class="badge-ended">Ended</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="table-footer">
        <span id="tableInfo"></span>
        <div class="pagination-btns" id="tablePagination"></div>
    </div>
</div>

@endif

<script>
// ── State ─────────────────────────────────────────────────
var ROWS_PER_PAGE = 10;
var currentPage   = 1;
var sortCol       = -1;
var sortDir       = 1;
var filteredRows  = [];

function getAllRows() {
    return Array.from(document.querySelectorAll('#historyTbody tr[data-date]'));
}

// ── Filter ────────────────────────────────────────────────
function applyFilters() {
    var search   = (document.getElementById('searchInput')    ? document.getElementById('searchInput').value    : '').toLowerCase().trim();
    var position = (document.getElementById('filterPosition') ? document.getElementById('filterPosition').value : '').toLowerCase();
    var election = (document.getElementById('filterElection') ? document.getElementById('filterElection').value : '').toLowerCase();
    var dateFrom =  document.getElementById('filterDateFrom') ? document.getElementById('filterDateFrom').value  : '';
    var dateTo   =  document.getElementById('filterDateTo')   ? document.getElementById('filterDateTo').value    : '';

    filteredRows = getAllRows().filter(function (row) {
        var candName = row.dataset.candidate  || '';
        var elecName = row.dataset.election   || '';
        var deptName = row.dataset.department || '';
        var pos      = row.dataset.position   || '';
        var date     = row.dataset.date       || '';

        if (search   && candName.indexOf(search) === -1 && elecName.indexOf(search) === -1 && deptName.indexOf(search) === -1) return false;
        if (position && pos.indexOf(position)    === -1) return false;
        if (election && elecName.indexOf(election) === -1) return false;
        if (dateFrom && date < dateFrom) return false;
        if (dateTo   && date > dateTo)   return false;
        return true;
    });

    currentPage = 1;
    renderTable();
}

function resetFilters() {
    ['searchInput','filterPosition','filterElection','filterDateFrom','filterDateTo'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    applyFilters();
}

// ── Sort ──────────────────────────────────────────────────
function sortTable(col) {
    if (sortCol === col) { sortDir *= -1; } else { sortCol = col; sortDir = 1; }
    filteredRows.sort(function (a, b) {
        if (col === 5) {
            return (parseInt(a.dataset.datetime || 0) - parseInt(b.dataset.datetime || 0)) * sortDir;
        }
        var aT = a.cells[col] ? a.cells[col].innerText.trim() : '';
        var bT = b.cells[col] ? b.cells[col].innerText.trim() : '';
        return aT.localeCompare(bT, undefined, { numeric: true }) * sortDir;
    });
    renderTable();
}

// ── Render ────────────────────────────────────────────────
function renderTable() {
    var total      = filteredRows.length;
    var totalPages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));
    if (currentPage > totalPages) currentPage = totalPages;

    getAllRows().forEach(function (r) { r.style.display = 'none'; });
    var start = (currentPage - 1) * ROWS_PER_PAGE;
    var end   = Math.min(start + ROWS_PER_PAGE, total);
    filteredRows.slice(start, end).forEach(function (r) { r.style.display = ''; });

    // Re-number
    filteredRows.forEach(function (r, i) { r.cells[0].textContent = i + 1; });

    // Remove old no-results row
    var noResultRow = document.getElementById('noResultRow');
    if (noResultRow) noResultRow.remove();

    if (total === 0) {
        var tbody = document.getElementById('historyTbody');
        var row   = document.createElement('tr');
        row.id    = 'noResultRow';
        row.className = 'no-results-row';
        row.innerHTML = '<td colspan="7"><i class="fa-solid fa-magnifying-glass" style="margin-right:0.4rem;"></i>No matching records found</td>';
        tbody.appendChild(row);
    }

    var infoEl = document.getElementById('tableInfo');
    if (infoEl) {
        infoEl.textContent = total === 0
            ? 'No votes found'
            : 'Showing ' + (start + 1) + ' to ' + end + ' of ' + total + ' vote' + (total !== 1 ? 's' : '');
    }

    var summaryFiltered = document.getElementById('summaryFiltered');
    if (summaryFiltered) summaryFiltered.textContent = total;

    // Pagination
    var pagEl = document.getElementById('tablePagination');
    if (!pagEl) return;
    pagEl.innerHTML = '';

    var prev = document.createElement('button');
    prev.className = 'page-btn';
    prev.textContent = 'Previous';
    if (currentPage === 1) prev.disabled = true;
    prev.onclick = function () { currentPage--; renderTable(); };
    pagEl.appendChild(prev);

    for (var p = 1; p <= totalPages; p++) {
        (function (pg) {
            var btn = document.createElement('button');
            btn.className = 'page-btn' + (pg === currentPage ? ' active' : '');
            btn.textContent = pg;
            btn.onclick = function () { currentPage = pg; renderTable(); };
            pagEl.appendChild(btn);
        })(p);
    }

    var next = document.createElement('button');
    next.className = 'page-btn';
    next.textContent = 'Next';
    if (currentPage === totalPages) next.disabled = true;
    next.onclick = function () { currentPage++; renderTable(); };
    pagEl.appendChild(next);
}

// ── Init on load ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    applyFilters();
});
</script>
@endsection
