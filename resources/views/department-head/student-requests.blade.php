@extends('layouts.department-head')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/shared.css') }}">
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/students.css') }}">
<style>
/* ── Summary stats row ─────────────────────────────────────── */
.req-stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}
.req-stat-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.req-stat-icon {
    width: 48px; height: 48px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; color: #fff;
}
.si-pending  { background: linear-gradient(135deg, #f97316, #ea580c); }
.si-approved { background: linear-gradient(135deg, #800020, #a0153e); }
.si-denied   { background: linear-gradient(135deg, #6b7280, #374151); }
.req-stat-num   { font-size: 1.75rem; font-weight: 900; color: #111827; line-height: 1; }
.req-stat-label { font-size: 0.78rem; color: #6b7280; font-weight: 600; margin-top: 2px; }

/* ── Tab bar ───────────────────────────────────────────────── */
.req-tab-bar {
    display: flex;
    gap: 0;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #e5e7eb;
}
.req-tab-btn {
    padding: 0.7rem 1.5rem;
    border: none; background: none;
    font-size: 0.88rem; font-weight: 600;
    color: #6b7280; cursor: pointer;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: color 0.2s, border-color 0.2s;
    display: flex; align-items: center; gap: 0.45rem;
    border-radius: 6px 6px 0 0;
}
.req-tab-btn:hover  { color: #800020; background: rgba(128,0,32,.04); }
.req-tab-btn.active { color: #800020; border-bottom-color: #800020; }
.tab-badge          { background: #ef4444; color: #fff; border-radius: 50px; padding: 1px 7px; font-size: 0.7rem; font-weight: 800; }
.tab-badge.maroon   { background: #800020; }
.tab-badge.gray     { background: #9ca3af; }

/* ── Tab panels ────────────────────────────────────────────── */
.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* ── Status text ───────────────────────────────────────────── */
.badge-pending  { color: #c2410c; font-weight: 700; font-size: 0.85rem; }
.badge-approved { color: #800020; font-weight: 700; font-size: 0.85rem; }
.badge-denied   { color: #6b7280; font-weight: 700; font-size: 0.85rem; }

/* ── Search bar ────────────────────────────────────────────── */
.req-search-bar {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 1rem 1.5rem;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.req-search-bar label { font-size: 0.8rem; font-weight: 600; color: #1f2937; white-space: nowrap; }
.req-search-input {
    padding: 0.55rem 0.9rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.9rem; color: #1f2937;
    background: white;
    transition: border-color 0.2s;
    min-width: 220px; flex: 1;
}
.req-search-input:focus {
    outline: none;
    border-color: #800020;
    box-shadow: 0 0 0 3px rgba(128,0,32,0.08);
}

/* ── Action buttons ────────────────────────────────────────── */
.btn-req-approve {
    padding: 0.3rem 1rem;
    background: white; color: #800020;
    border: 2px solid #800020;
    border-radius: 20px; font-weight: 700; font-size: 0.8rem;
    cursor: pointer;
    transition: background 0.2s, color 0.2s, transform 0.2s;
    margin-right: 4px;
    display: inline-flex; align-items: center; gap: 4px;
}
.btn-req-approve:hover { background: #800020; color: white; transform: translateY(-1px); }
.btn-req-deny {
    padding: 0.3rem 1rem;
    background: #800020; color: white;
    border: 2px solid #800020;
    border-radius: 20px; font-weight: 700; font-size: 0.8rem;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
    display: inline-flex; align-items: center; gap: 4px;
}
.btn-req-deny:hover { background: #5c0015; border-color: #5c0015; transform: translateY(-1px); }

/* ── Avatar cell ───────────────────────────────────────────── */
.req-avatar-cell { display: flex; align-items: center; gap: 0.75rem; }
.req-avatar-sm {
    width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, #800020, #a0153e);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 0.85rem; font-weight: 800; overflow: hidden;
}
.req-avatar-sm img { width: 100%; height: 100%; object-fit: cover; }
.req-avatar-sm.gray { background: linear-gradient(135deg, #6b7280, #374151); }

/* ── Empty state ───────────────────────────────────────────── */
.empty-state {
    text-align: center; padding: 4rem 2rem;
    background: white; border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.empty-state i  { font-size: 2.5rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
.empty-state h3 { font-size: 1rem; font-weight: 700; color: #374151; margin-bottom: 0.4rem; }
.empty-state p  { font-size: 0.88rem; color: #9ca3af; }

/* ── Flash messages ────────────────────────────────────────── */
.flash {
    padding: 0.85rem 1.2rem; border-radius: 10px;
    margin-bottom: 1.25rem; font-size: 0.88rem; font-weight: 600;
    display: flex; align-items: center; gap: 0.6rem;
}
.flash-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.flash-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

@media (max-width: 768px) {
    .req-stats-row { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('dept-head-content')

<header>
    <div class="header-title">
        <h1>Student Account Requests</h1>
        <p>{{ $department }} Department &bull;
            <span id="reqCount">{{ $pending->count() + $approved->count() + $denied->count() }}</span> Total Requests
        </p>
    </div>
</header>

<div class="students-container">

    @if(session('success'))
        <div class="flash flash-success">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flash flash-error">
            <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Summary stat cards --}}
    <div class="req-stats-row">
        <div class="req-stat-card">
            <div class="req-stat-icon si-pending"><i class="fa-solid fa-hourglass-half"></i></div>
            <div>
                <div class="req-stat-num">{{ $pending->count() }}</div>
                <div class="req-stat-label">Pending Requests</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div class="req-stat-icon si-approved"><i class="fa-solid fa-user-check"></i></div>
            <div>
                <div class="req-stat-num">{{ $approved->count() }}</div>
                <div class="req-stat-label">Approved</div>
            </div>
        </div>
        <div class="req-stat-card">
            <div class="req-stat-icon si-denied"><i class="fa-solid fa-user-xmark"></i></div>
            <div>
                <div class="req-stat-num">{{ $denied->count() }}</div>
                <div class="req-stat-label">Denied</div>
            </div>
        </div>
    </div>

    {{-- Tab bar --}}
    <div class="req-tab-bar">
        <button class="req-tab-btn active" onclick="switchReqTab('pending', this)">
            <i class="fa-solid fa-hourglass-half"></i> Pending
            @if($pending->count() > 0)
                <span class="tab-badge">{{ $pending->count() }}</span>
            @endif
        </button>
        <button class="req-tab-btn" onclick="switchReqTab('approved', this)">
            <i class="fa-solid fa-check-circle"></i> Approved
            <span class="tab-badge maroon">{{ $approved->count() }}</span>
        </button>
        <button class="req-tab-btn" onclick="switchReqTab('denied', this)">
            <i class="fa-solid fa-ban"></i> Denied
            <span class="tab-badge gray">{{ $denied->count() }}</span>
        </button>
    </div>

    {{-- PENDING --}}
    <div id="tab-pending" class="tab-panel active">
        @if($pending->count() > 0)
            <div class="req-search-bar">
                <label>Search</label>
                <input type="text" class="req-search-input" placeholder="Search by name, email or student ID..."
                       oninput="filterReqTable('tbl-pending', this.value)">
            </div>
            <div class="table-wrapper">
                <table class="students-tbl" id="tbl-pending">
                    <thead>
                        <tr>
                            <th>NO.</th>
                            <th>STUDENT</th>
                            <th>EMAIL</th>
                            <th>STUDENT ID</th>
                            <th>SUBMITTED</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pending as $student)
                        <tr data-search="{{ strtolower($student->name . ' ' . $student->last_name . ' ' . $student->email . ' ' . $student->student_id) }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="req-avatar-cell">
                                    <div class="req-avatar-sm">
                                        @if($student->profile_picture)
                                            <img src="{{ $student->profile_picture }}" alt="">
                                        @else
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <strong>{{ trim($student->name . ' ' . $student->middle_name . ' ' . $student->last_name) }}</strong>
                                </div>
                            </td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->student_id ?? '&mdash;' }}</td>
                            <td>{{ $student->created_at->format('M d, Y') }}</td>
                            <td><span class="badge-pending"><i class="fa-solid fa-hourglass-half"></i> Pending</span></td>
                            <td style="white-space:nowrap;">
                                <form class="req-action-form" method="POST" action="{{ route('department-head.student-requests.approve', $student) }}" style="display:inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="button" class="btn-req-approve req-swal-btn"
                                        data-action="approve"
                                        data-name="{{ addslashes(trim($student->name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name)) }}">
                                        <i class="fa-solid fa-check"></i> Approve
                                    </button>
                                </form>
                                <form class="req-action-form" method="POST" action="{{ route('department-head.student-requests.deny', $student) }}" style="display:inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="button" class="btn-req-deny req-swal-btn"
                                        data-action="deny"
                                        data-name="{{ addslashes(trim($student->name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name)) }}">
                                        <i class="fa-solid fa-xmark"></i> Deny
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <h3>No Pending Requests</h3>
                <p>All student registration requests have been reviewed.</p>
            </div>
        @endif
    </div>

    {{-- APPROVED --}}
    <div id="tab-approved" class="tab-panel">
        @if($approved->count() > 0)
            <div class="req-search-bar">
                <label>Search</label>
                <input type="text" class="req-search-input" placeholder="Search by name, email or student ID..."
                       oninput="filterReqTable('tbl-approved', this.value)">
            </div>
            <div class="table-wrapper">
                <table class="students-tbl" id="tbl-approved">
                    <thead>
                        <tr>
                            <th>NO.</th>
                            <th>STUDENT</th>
                            <th>EMAIL</th>
                            <th>STUDENT ID</th>
                            <th>SUBMITTED</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approved as $student)
                        <tr data-search="{{ strtolower($student->name . ' ' . $student->last_name . ' ' . $student->email . ' ' . $student->student_id) }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="req-avatar-cell">
                                    <div class="req-avatar-sm">
                                        @if($student->profile_picture)
                                            <img src="{{ $student->profile_picture }}" alt="">
                                        @else
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <strong>{{ trim($student->name . ' ' . $student->middle_name . ' ' . $student->last_name) }}</strong>
                                </div>
                            </td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->student_id ?? '&mdash;' }}</td>
                            <td>{{ $student->created_at->format('M d, Y') }}</td>
                            <td><span class="badge-approved"><i class="fa-solid fa-check-circle"></i> Approved</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fa-solid fa-user-check"></i>
                <h3>No Approved Students Yet</h3>
                <p>Approved accounts will appear here.</p>
            </div>
        @endif
    </div>

    {{-- DENIED --}}
    <div id="tab-denied" class="tab-panel">
        @if($denied->count() > 0)
            <div class="req-search-bar">
                <label>Search</label>
                <input type="text" class="req-search-input" placeholder="Search by name, email or student ID..."
                       oninput="filterReqTable('tbl-denied', this.value)">
            </div>
            <div class="table-wrapper">
                <table class="students-tbl" id="tbl-denied">
                    <thead>
                        <tr>
                            <th>NO.</th>
                            <th>STUDENT</th>
                            <th>EMAIL</th>
                            <th>STUDENT ID</th>
                            <th>SUBMITTED</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($denied as $student)
                        <tr data-search="{{ strtolower($student->name . ' ' . $student->last_name . ' ' . $student->email . ' ' . $student->student_id) }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="req-avatar-cell">
                                    <div class="req-avatar-sm gray">
                                        @if($student->profile_picture)
                                            <img src="{{ $student->profile_picture }}" alt="">
                                        @else
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <strong>{{ trim($student->name . ' ' . $student->middle_name . ' ' . $student->last_name) }}</strong>
                                </div>
                            </td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->student_id ?? '&mdash;' }}</td>
                            <td>{{ $student->created_at->format('M d, Y') }}</td>
                            <td><span class="badge-denied"><i class="fa-solid fa-ban"></i> Denied</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fa-solid fa-ban"></i>
                <h3>No Denied Accounts</h3>
                <p>Denied accounts will appear here.</p>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
// ── Approve / Deny SweetAlert ─────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.req-swal-btn');
        if (!btn) return;
        var action = btn.dataset.action;   // 'approve' or 'deny'
        var name   = btn.dataset.name || 'this student';
        var isApprove = action === 'approve';

        var iconBg    = isApprove ? '#15803d' : '#800020';
        var iconClass = isApprove ? 'fa-check' : 'fa-xmark';
        var title     = isApprove ? 'Approve Account?' : 'Deny Account?';
        var nameColor = isApprove ? '#15803d'  : '#800020';
        var confirmTxt = isApprove
            ? '<i class="fa-solid fa-check" style="margin-right:5px;"></i>Yes, Approve'
            : '<i class="fa-solid fa-xmark" style="margin-right:5px;"></i>Yes, Deny';
        var subText = isApprove
            ? 'The student will be able to log in once approved.'
            : 'The student will not be able to access the system.';

        Swal.fire({
            html: '<div style="text-align:center;padding:.25rem 0">'
                + '<div style="width:66px;height:66px;background:' + iconBg + ';border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">'
                + '<i class="fa-solid ' + iconClass + '" style="font-size:1.4rem;color:#fff;"></i></div>'
                + '<h2 style="font-size:1.2rem;font-weight:800;color:#1f2937;margin-bottom:.4rem;">' + title + '</h2>'
                + '<p style="color:' + nameColor + ';font-weight:700;font-size:.95rem;word-break:break-word;">&ldquo;' + name + '&rdquo;</p>'
                + '<p style="color:#9ca3af;font-size:.82rem;margin-top:.4rem;">' + subText + '</p></div>',
            showCancelButton: true,
            confirmButtonText: confirmTxt,
            cancelButtonText: 'Cancel',
            focusCancel: true,
            buttonsStyling: false,
            customClass: { confirmButton: 'swal-btn-outline', cancelButton: 'swal-btn-solid', popup: 'swal-app-popup', actions: 'swal-app-actions' }
        }).then(function (result) {
            if (result.isConfirmed) {
                btn.closest('.req-action-form').submit();
            }
        });
    });
});

function switchReqTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.req-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}

function filterReqTable(tableId, query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
        const text = row.getAttribute('data-search') || '';
        row.style.display = text.includes(q) ? '' : 'none';
    });
}
</script>
@endpush

@endsection
