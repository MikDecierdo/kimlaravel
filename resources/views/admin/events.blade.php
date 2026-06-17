@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/shared.css') }}">
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/events.css') }}">
<style>
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
        <h1>Manage Events</h1>
        <p>View all department events &bull; <span id="eventCount">{{ $events->count() }}</span> Events</p>
    </div>
</header>

<!-- Filter Bar -->
<div class="elec-filter-card">
    <div class="elec-filter-group">
        <label>Date From</label>
        <input type="date" id="evtDateFrom" class="elec-filter-input">
    </div>
    <div class="elec-filter-group">
        <label>Date To</label>
        <input type="date" id="evtDateTo" class="elec-filter-input">
    </div>
    <div class="elec-filter-group">
        <label>Department</label>
        <select id="evtDept" class="elec-filter-select">
            <option value="">All Departments</option>
            @foreach($events->pluck('department')->unique()->sort()->values() as $dept)
                <option value="{{ strtolower($dept) }}">{{ $dept }}</option>
            @endforeach
        </select>
    </div>
    <div class="elec-filter-group">
        <label>Most Reactions</label>
        <select id="evtSortReactions" class="elec-filter-select">
            <option value="">Default Order</option>
            <option value="like">Most Liked</option>
            <option value="haha">Most Haha</option>
            <option value="love">Most Love (Heart)</option>
            <option value="total">Most Total Reactions</option>
        </select>
    </div>
    <div class="elec-search-group">
        <label>Search</label>
        <input type="text" id="evtSearch" placeholder="Search events..." oninput="evtApplyFilters()">
    </div>
    <div class="elec-filter-actions">
        <button class="elec-btn-apply" onclick="evtApplyFilters()"><i class="fa-solid fa-filter"></i> Apply</button>
        <button class="elec-btn-reset" onclick="evtResetFilters()"><i class="fa-solid fa-rotate-left"></i> Reset</button>
    </div>
</div>
<div class="elec-results-bar" id="evtResultsBar"></div>

<div class="events-table-container">
    <table id="eventsTable">
        <thead>
            <tr>
                <th onclick="evtSortTable(0)">NO. <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="evtSortTable(1)">TITLE <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="evtSortTable(2)">DEPARTMENT <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="evtSortTable(3)">DATE <span class="sort-icon">&#x21C5;</span></th>
                <th class="no-sort">DESCRIPTION</th>
                <th onclick="evtSortTable(5)">POSTED BY <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="evtSortTable(6)" style="white-space:nowrap;">LAST EDITED <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="evtSortTable(7)" style="text-align:center;"><i class="fa-solid fa-thumbs-up" style="color:rgba(255,255,255,0.85);"></i> LIKE <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="evtSortTable(8)" style="text-align:center;"><i class="fa-solid fa-face-laugh" style="color:rgba(255,255,255,0.85);"></i> HAHA <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="evtSortTable(9)" style="text-align:center;"><i class="fa-solid fa-heart" style="color:rgba(255,255,255,0.85);"></i> HEART <span class="sort-icon">&#x21C5;</span></th>
                <th class="no-sort">VIEW</th>
            </tr>
        </thead>
        <tbody id="eventsTbody">
            @forelse($events as $event)
                @php
                    $reactionCounts = $event->likes->groupBy('reaction_type')->map->count();
                    $likeCount  = $reactionCounts->get('like', 0);
                    $hahaCount  = $reactionCounts->get('haha', 0);
                    $loveCount  = $reactionCounts->get('love', 0);
                    $totalReact = $likeCount + $hahaCount + $loveCount;
                @endphp
                <tr data-no="{{ $loop->iteration }}"
                    data-title="{{ strtolower($event->title) }}"
                    data-dept="{{ strtolower($event->department) }}"
                    data-date="{{ $event->event_date }}"
                    data-updated="{{ $event->updated_at ? $event->updated_at->toDateString() : '' }}"
                    data-like="{{ $likeCount }}"
                    data-haha="{{ $hahaCount }}"
                    data-love="{{ $loveCount }}"
                    data-total="{{ $totalReact }}"
                >
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $event->title }}</td>
                    <td>{{ $event->department }}</td>
                    <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</td>
                    <td>{{ Str::limit($event->description, 50) }}</td>
                    <td style="white-space:nowrap; color:#374151;">{{ $event->user->name ?? '—' }}</td>
                    <td style="white-space:nowrap; color:#374151;">
                        @if($event->updated_at && $event->updated_at->ne($event->created_at))
                            {{ $event->updated_at->format('M d, Y') }}<br>
                            <span style="font-size:0.75rem;color:#9ca3af;">{{ $event->updated_at->format('h:i A') }}</span>
                        @else
                            <span style="color:#9ca3af;font-size:0.82rem;">Not yet edited</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <span style="color:#1877f2;font-weight:700;">{{ $likeCount }}</span>
                    </td>
                    <td style="text-align:center;">
                        <span style="color:#f7b125;font-weight:700;">{{ $hahaCount }}</span>
                    </td>
                    <td style="text-align:center;">
                        <span style="color:#f33e58;font-weight:700;">{{ $loveCount }}</span>
                    </td>
                    <td>
                        <button class="btn-tbl-update evt-view-btn"
                            data-title="{{ e($event->title) }}"
                            data-dept="{{ e($event->department) }}"
                            data-date="{{ $event->event_date }}"
                            data-description="{{ e($event->description) }}"
                            data-image="{{ $event->image ? asset('storage/' . $event->image) : '' }}"
                            data-posted-by="{{ e($event->user->name ?? '—') }}"
                            data-like="{{ $likeCount }}"
                            data-haha="{{ $hahaCount }}"
                            data-love="{{ $loveCount }}"
                        ><i class="fa-solid fa-eye"></i> View</button>
                    </td>
                </tr>
            @empty
                <tr class="evt-empty-row">
                    <td colspan="11" style="padding: 2rem; text-align: center; color: #888;">
                        <i class="fa-solid fa-calendar-days" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3; display: block;"></i>
                        No events yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- View Event Modal -->
<div id="evViewModal" class="modal">
    <div class="modal-content" style="max-width:560px;">
        <div class="modal-header">
            <h2>Event Details</h2>
            <span class="close" onclick="closeEvViewModal()">&times;</span>
        </div>

        <!-- Image -->
        <div id="evView_imageWrap" style="display:none;">
            <img id="evView_image" src="" alt="Event Image"
                 style="width:100%;max-height:280px;object-fit:cover;border-bottom:1px solid #e4e6eb;">
        </div>

        <!-- Details -->
        <div style="padding:1.25rem 1.5rem;display:flex;flex-direction:column;gap:0.9rem;">

            <div>
                <div style="font-size:0.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.25rem;">Title</div>
                <div id="evView_title" style="font-size:1.1rem;font-weight:700;color:#111827;"></div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div>
                    <div style="font-size:0.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.25rem;">Department</div>
                    <div id="evView_dept" style="font-size:0.9rem;font-weight:600;color:#374151;"></div>
                </div>
                <div>
                    <div style="font-size:0.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.25rem;">Event Date</div>
                    <div id="evView_date" style="font-size:0.9rem;font-weight:600;color:#374151;"></div>
                </div>
            </div>

            <div>
                <div style="font-size:0.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.25rem;">Posted By</div>
                <div id="evView_postedBy" style="font-size:0.9rem;color:#374151;"></div>
            </div>

            <div>
                <div style="font-size:0.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.25rem;">Description</div>
                <div id="evView_description" style="font-size:0.9rem;color:#374151;line-height:1.6;white-space:pre-wrap;"></div>
            </div>

            <!-- Reactions -->
            <div style="display:flex;gap:1.25rem;padding:.75rem 1rem;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">
                <div style="display:flex;align-items:center;gap:.45rem;">
                    <i class="fa-solid fa-thumbs-up" style="color:#1877f2;font-size:1.1rem;"></i>
                    <span id="evView_like" style="font-weight:700;color:#1877f2;font-size:1rem;"></span>
                </div>
                <div style="display:flex;align-items:center;gap:.45rem;">
                    <i class="fa-solid fa-face-laugh" style="color:#f7b125;font-size:1.1rem;"></i>
                    <span id="evView_haha" style="font-weight:700;color:#f7b125;font-size:1rem;"></span>
                </div>
                <div style="display:flex;align-items:center;gap:.45rem;">
                    <i class="fa-solid fa-heart" style="color:#f33e58;font-size:1.1rem;"></i>
                    <span id="evView_love" style="font-weight:700;color:#f33e58;font-size:1rem;"></span>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// ── View modal ───────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('eventsTbody').addEventListener('click', function (e) {
        var btn = e.target.closest('.evt-view-btn');
        if (!btn) return;
        var d = btn.dataset;
        document.getElementById('evView_title').textContent       = d.title;
        document.getElementById('evView_dept').textContent        = d.dept;
        document.getElementById('evView_date').textContent        = new Date(d.date).toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});
        document.getElementById('evView_postedBy').textContent    = d.postedBy;
        document.getElementById('evView_description').textContent = d.description;
        document.getElementById('evView_like').textContent        = d.like;
        document.getElementById('evView_haha').textContent        = d.haha;
        document.getElementById('evView_love').textContent        = d.love;
        var imgWrap = document.getElementById('evView_imageWrap');
        var img     = document.getElementById('evView_image');
        if (d.image) {
            img.src = d.image;
            imgWrap.style.display = '';
        } else {
            imgWrap.style.display = 'none';
        }
        document.getElementById('evViewModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    });
});
function closeEvViewModal() {
    document.getElementById('evViewModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ── Filter ───────────────────────────────────────────────
function evtApplyFilters() {
    var search    = (document.getElementById('evtSearch').value || '').toLowerCase().trim();
    var dateFrom  = document.getElementById('evtDateFrom').value;
    var dateTo    = document.getElementById('evtDateTo').value;
    var dept      = document.getElementById('evtDept').value;
    var sortReact = document.getElementById('evtSortReactions').value;

    var rows = Array.from(document.querySelectorAll('#eventsTbody tr:not(.evt-empty-row)'));
    var shown = 0;
    rows.forEach(function (row) {
        var title   = row.dataset.title || '';
        var date    = row.dataset.date  || '';
        var deptVal = row.dataset.dept  || '';
        var ok = true;
        if (search   && title.indexOf(search) === -1) ok = false;
        if (dept     && deptVal !== dept)              ok = false;
        if (dateFrom && date < dateFrom)               ok = false;
        if (dateTo   && date > dateTo)                 ok = false;
        row.style.display = ok ? '' : 'none';
        if (ok) shown++;
    });

    var total = rows.length;
    var bar = document.getElementById('evtResultsBar');
    if (bar) bar.textContent = 'Showing ' + shown + ' of ' + total + ' event' + (total !== 1 ? 's' : '');

    if (sortReact) {
        var tbody   = document.getElementById('eventsTbody');
        var visible = rows.filter(function (r) { return r.style.display !== 'none'; });
        visible.sort(function (a, b) {
            return (parseInt(b.dataset[sortReact]) || 0) - (parseInt(a.dataset[sortReact]) || 0);
        });
        visible.forEach(function (r) { tbody.appendChild(r); });
    }
}

function evtResetFilters() {
    document.getElementById('evtSearch').value        = '';
    document.getElementById('evtDateFrom').value      = '';
    document.getElementById('evtDateTo').value        = '';
    document.getElementById('evtDept').value          = '';
    document.getElementById('evtSortReactions').value = '';
    document.querySelectorAll('#eventsTbody tr').forEach(function (r) { r.style.display = ''; });
    var total = document.querySelectorAll('#eventsTbody tr:not(.evt-empty-row)').length;
    var bar = document.getElementById('evtResultsBar');
    if (bar) bar.textContent = 'Showing ' + total + ' of ' + total + ' event' + (total !== 1 ? 's' : '');
}

// ── Sort ─────────────────────────────────────────────────
var _evtSortDir = {};
function evtSortTable(colIndex) {
    var tbody = document.getElementById('eventsTbody');
    var rows  = Array.from(tbody.querySelectorAll('tr:not(.evt-empty-row)'));
    var asc   = !_evtSortDir[colIndex];
    _evtSortDir = {};
    _evtSortDir[colIndex] = asc;
    rows.sort(function (a, b) {
        var aVal = (a.cells[colIndex] ? a.cells[colIndex].textContent.trim() : '');
        var bVal = (b.cells[colIndex] ? b.cells[colIndex].textContent.trim() : '');
        var aNum = parseFloat(aVal);
        var bNum = parseFloat(bVal);
        if (!isNaN(aNum) && !isNaN(bNum)) return asc ? aNum - bNum : bNum - aNum;
        return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    });
    rows.forEach(function (r) { tbody.appendChild(r); });
}
</script>
@endpush
@endsection
