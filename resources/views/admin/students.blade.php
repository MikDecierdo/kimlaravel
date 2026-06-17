@extends('layouts.admin')

@section('admin-content')
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
/* ── Table styling ─────────────────────────────────────── */
.stu-table-wrap {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
}
.stu-table-wrap table { width: 100%; border-collapse: collapse; }
.stu-table-wrap thead { background: linear-gradient(135deg, #800020 0%, #A0153E 100%); }
.stu-table-wrap thead th { padding: 0.9rem 1rem; text-align: left; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.06em; color: white; }
.stu-table-wrap tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.15s; }
.stu-table-wrap tbody tr:hover { background: #fdf2f4; }
.stu-table-wrap tbody td { padding: 0.85rem 1rem; font-size: 0.9rem; color: #1f2937; }
.stu-table-wrap tbody td:first-child { font-weight: 600; }
.stu-dept-chip {
    display: inline-block;
    padding: 0.2rem 0.65rem;
    background: rgba(128,0,32,0.08);
    color: #800020;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 700;
}
</style>

<header>
    <div class="header-title">
        <h1>Registered Students</h1>
        <p>View all registered student accounts &bull; <span id="stuTotalCount">{{ $students->count() }}</span> students</p>
    </div>
</header>

<!-- Filter Bar -->
<div class="elec-filter-card">
    <div class="elec-filter-group">
        <label>Department</label>
        <select id="stuFilterDept" class="elec-filter-select" onchange="stuApplyFilters()">
            <option value="">All Departments</option>
            @foreach($students->pluck('department')->filter()->unique()->sort()->values() as $dept)
                <option value="{{ strtolower($dept) }}">{{ $dept }}</option>
            @endforeach
        </select>
    </div>
    <div class="elec-filter-group">
        <label>Registered From</label>
        <input type="date" id="stuFilterFrom" class="elec-filter-input">
    </div>
    <div class="elec-filter-group">
        <label>Registered To</label>
        <input type="date" id="stuFilterTo" class="elec-filter-input">
    </div>
    <div class="elec-search-group">
        <label>Search</label>
        <input type="text" id="stuSearch" placeholder="Name, email or Student ID..." oninput="stuApplyFilters()">
    </div>
    <div class="elec-filter-actions">
        <button class="elec-btn-apply" onclick="stuApplyFilters()"><i class="fa-solid fa-filter"></i> Apply</button>
        <button class="elec-btn-reset" onclick="stuResetFilters()"><i class="fa-solid fa-rotate-left"></i> Reset</button>
    </div>
</div>
<div class="elec-results-bar" id="stuResultsBar"></div>

<div class="stu-table-wrap">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Student ID</th>
                <th>Department</th>
                <th>Registered</th>
            </tr>
        </thead>
        <tbody id="stuTbody">
            @forelse($students as $student)
                <tr data-name="{{ strtolower($student->name) }}"
                    data-email="{{ strtolower($student->email) }}"
                    data-student-id="{{ strtolower($student->student_id ?? '') }}"
                    data-dept="{{ strtolower($student->department ?? '') }}"
                    data-date="{{ $student->created_at->format('Y-m-d') }}">
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->student_id ?? 'N/A' }}</td>
                    <td><span class="stu-dept-chip">{{ $student->department ?? 'N/A' }}</span></td>
                    <td>{{ $student->created_at->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr id="stuEmptyRow">
                    <td colspan="5" style="padding: 2rem; text-align: center; color: #888;">No students registered yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
(function () {
    function stuApplyFilters() {
        var dept   = document.getElementById('stuFilterDept').value.toLowerCase();
        var from   = document.getElementById('stuFilterFrom').value;
        var to     = document.getElementById('stuFilterTo').value;
        var search = (document.getElementById('stuSearch').value || '').toLowerCase().trim();

        var rows  = document.querySelectorAll('#stuTbody tr:not(#stuEmptyRow)');
        var shown = 0;
        rows.forEach(function (row) {
            var ok = (!dept   || row.dataset.dept === dept)
                  && (!from   || row.dataset.date >= from)
                  && (!to     || row.dataset.date <= to)
                  && (!search || row.dataset.name.includes(search)
                              || row.dataset.email.includes(search)
                              || row.dataset.studentId.includes(search));
            row.style.display = ok ? '' : 'none';
            if (ok) shown++;
        });

        var total = rows.length;
        var bar = document.getElementById('stuResultsBar');
        if (bar) bar.textContent = 'Showing ' + shown + ' of ' + total + ' student' + (total !== 1 ? 's' : '');
    }

    function stuResetFilters() {
        document.getElementById('stuFilterDept').value = '';
        document.getElementById('stuFilterFrom').value = '';
        document.getElementById('stuFilterTo').value   = '';
        document.getElementById('stuSearch').value     = '';
        stuApplyFilters();
    }

    window.stuApplyFilters = stuApplyFilters;
    window.stuResetFilters = stuResetFilters;

    // Init count on load
    document.addEventListener('DOMContentLoaded', stuApplyFilters);
})();
</script>
@endsection
