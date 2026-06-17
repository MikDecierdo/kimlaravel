@extends('layouts.admin')

@section('admin-content')
<style>
.btn-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    opacity: 0.9;
}
/* ── Shared nav-card (dept & election folders) ── */
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
    box-shadow: 0 12px 32px rgba(0,0,0,0.18);
}
.nav-card.nav-card-dimmed::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(107, 114, 128, 0.28);
    z-index: 4;
    pointer-events: none;
}
.election-state-label {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    z-index: 7;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 900;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #ffffff;
    background: transparent;
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
.election-view-btn {
    position: relative;
    z-index: 8;
}
/* ── candidate result card ── */
.folder-card {
    transition: transform 0.3s, box-shadow 0.3s;
}
.folder-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(128, 0, 32, 0.15);
}
/* ── Breadcrumb ── */
.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #64748b;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.breadcrumb-nav .crumb {
    cursor: pointer;
    color: #800020;
    font-weight: 600;
    text-decoration: underline;
}
.breadcrumb-nav .crumb:hover { opacity: 0.7; }
.breadcrumb-nav .sep { color: #94a3b8; }
.breadcrumb-nav .active-crumb { color: #1e293b; font-weight: 600; cursor: default; text-decoration: none; }
/* ── Shared filter bar (all 3 levels) ───────────────── */
.nav-filter-bar {
    background: white;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    padding: 1.1rem 1.3rem;
    margin-bottom: 1.2rem;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 0.85rem;
}
.nfg { display: flex; flex-direction: column; gap: 0.28rem; }
.nfg label { font-size: 0.73rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.04em; }
.nfg input, .nfg select {
    padding: 0.5rem 0.8rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.86rem;
    color: #1f2937;
    background: #f9fafb;
    outline: none;
    transition: border-color 0.2s;
}
.nfg input:focus, .nfg select:focus { border-color: #800020; background: #fff; }
.nfg-search { flex: 1; min-width: 180px; }
.nfg-search input { width: 100%; }
.nf-apply {
    padding: 0.5rem 1.05rem;
    background: linear-gradient(135deg, #800020 0%, #A0153E 100%);
    color: white; border: none; border-radius: 8px;
    font-size: 0.8rem; font-weight: 700; cursor: pointer;
    transition: opacity 0.2s, transform 0.15s; white-space: nowrap;
}
.nf-apply:hover { opacity: 0.88; transform: translateY(-1px); }
.nf-reset {
    padding: 0.5rem 0.95rem;
    background: #f1f5f9; color: #475569;
    border: 1.5px solid #e2e8f0; border-radius: 8px;
    font-size: 0.8rem; font-weight: 600; cursor: pointer;
    transition: background 0.2s; white-space: nowrap;
}
.nf-reset:hover { background: #e2e8f0; }
.nf-results { font-size: 0.83rem; color: #64748b; font-weight: 500; margin-bottom: 0.9rem; }
.nf-empty { text-align: center; padding: 3.5rem; color: #888; display: none; }
</style>

<header>
    <div class="header-title">
        <h1 id="pageTitle">Manage Candidates</h1>
        <p id="pageSubtitle">Select a department to view its elections</p>
    </div>
    <div style="display: flex; gap: 1rem; align-items: center;">
        <button id="backBtn" onclick="goBack()" style="display: none; padding: 0.75rem 1.5rem; background: #9E9E9E; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s;" class="btn-hover">
            <i class="fa-solid fa-arrow-left"></i> Back
        </button>
        <button class="btn-primary btn-hover" onclick="openModal()" style="background: linear-gradient(135deg, #800020 0%, #A0153E 100%); transition: all 0.3s;">
            <i class="fa-solid fa-plus"></i> Add Candidate
        </button>
    </div>
</header>

<!-- BREADCRUMB -->
<div class="breadcrumb-nav" id="breadcrumb">
    <span class="active-crumb"><i class="fa-solid fa-house-chimney" style="margin-right:4px;"></i>Departments</span>
</div>

<!-- ───────────────────────────────────────────────
     LEVEL 1 — DEPARTMENT FOLDERS
────────────────────────────────────────────────── -->
<!-- Level 1 filter bar (departments) -->
<div id="lvl1FilterBar" class="nav-filter-bar">
    <div class="nfg nfg-search">
        <label>Search Department</label>
        <input type="text" id="deptSearchInput" placeholder="Search by department name..." oninput="deptApplyFilter()">
    </div>
    <div style="display:flex;gap:0.5rem;align-items:flex-end;">
        <button class="nf-reset" onclick="deptResetFilter()"><i class="fa-solid fa-rotate-left"></i> Reset</button>
    </div>
</div>
<div class="nf-results" id="lvl1ResultsBar"></div>

@php
$deptGradients = [
    'BSIT'        => 'linear-gradient(160deg, #16a34a 0%, #052e16 100%)',
    'IT'          => 'linear-gradient(160deg, #16a34a 0%, #052e16 100%)',
    'BSBA'        => 'linear-gradient(160deg, #d97706 0%, #1c1400 100%)',
    'CRIM'        => 'linear-gradient(160deg, #7c3aed 0%, #1a0533 100%)',
    'CHTM'        => 'linear-gradient(160deg, #db2777 0%, #1f0011 100%)',
    'BSED'        => 'linear-gradient(160deg, #1d4ed8 0%, #030c1e 100%)',
    'EDUC'        => 'linear-gradient(160deg, #1d4ed8 0%, #030c1e 100%)',
    'ENGINEERING' => 'linear-gradient(160deg, #800020 0%, #1a0008 100%)',
];
$deptAccents = [
    'BSIT'        => '#86efac',
    'IT'          => '#86efac',
    'BSBA'        => '#FCD34D',
    'CRIM'        => '#c4b5fd',
    'CHTM'        => '#f9a8d4',
    'BSED'        => '#93c5fd',
    'EDUC'        => '#93c5fd',
    'ENGINEERING' => '#FFC107',
];
$deptSolids = [
    'BSIT'        => '#15803d',
    'IT'          => '#15803d',
    'BSBA'        => '#b45309',
    'CRIM'        => '#6d28d9',
    'CHTM'        => '#be185d',
    'BSED'        => '#1e40af',
    'EDUC'        => '#1e40af',
    'ENGINEERING' => '#800020',
];
$deptLogos = [
    'BSIT'        => '/images/dept-logos/bsit.png',
    'IT'          => '/images/dept-logos/bsit.png',
    'BSBA'        => '/images/dept-logos/bsba.png',
    'CRIM'        => '/images/dept-logos/crim.png',
    'CHTM'        => '/images/dept-logos/chtm.png',
    'BSED'        => '/images/dept-logos/bsed.png',
    'EDUC'        => '/images/dept-logos/bsed.png',
    'ENGINEERING' => '/images/dept-logos/engineering.png',
];
@endphp
<div id="departmentsView">
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
        @forelse($electionsByDept as $department => $deptElections)
            @php
                $totalCandidates = $deptElections->sum('candidates_count');
                $totalElections  = $deptElections->count();
                $activeCount     = $deptElections->filter(fn($e) => $e->is_active && !$e->end_date->isPast())->count();
                $gradient        = $deptGradients[$department] ?? 'linear-gradient(160deg, #800020 0%, #1a0008 100%)';
                $accent          = $deptAccents[$department]   ?? '#FFC107';
                $solid           = $deptSolids[$department]    ?? '#800020';
                $logo            = $deptLogos[$department]     ?? null;
            @endphp
            <div class="nav-card cand-dept-card" data-dept="{{ strtolower($department) }}" onclick="showElections('{{ addslashes($department) }}')">

                <!-- Banner -->
                <div style="width:100%; height:200px; background:{{ $gradient }}; position:relative; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                    <!-- subtle dept-colour glow behind logo -->
                    <div style="position:absolute; inset:0; background:radial-gradient(ellipse at center, rgba(255,255,255,0.08) 0%, transparent 70%);"></div>
                    @if($logo)
                        <img src="{{ $logo }}" alt="{{ $department }}"
                             style="height:300px; width: 300px; object-fit:contain; position:relative; z-index:1; filter:drop-shadow(0 6px 18px rgba(0,0,0,0.55));">
                    @else
                        <i class="fa-solid fa-building-columns" style="font-size:5rem; color:{{ $accent }}; position:relative; z-index:1; filter:drop-shadow(0 4px 12px rgba(0,0,0,0.4));"></i>
                    @endif
                    <!-- active elections badge top-left -->
                    <div style="position:absolute; top:12px; left:12px; z-index:2;">
                        <span style="display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:20px; font-size:0.75rem; font-weight:700; background:white; color:#1e293b; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                            <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
                            {{ $activeCount }} Active
                        </span>
                    </div>
                    <!-- candidate count badge top-right -->
                    <div style="position:absolute; top:12px; right:12px; z-index:2;">
                        <span style="display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:20px; font-size:0.75rem; font-weight:700; background:white; color:#1e293b; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                            <i class="fa-solid fa-users" style="color:{{ $solid }};"></i> {{ $totalCandidates }}
                        </span>
                    </div>
                    <!-- bottom accent bar -->
                    <div style="position:absolute; bottom:0; left:0; right:0; height:4px; background:{{ $solid }};"></div>
                </div>

                <!-- Content -->
                <div style="padding:1.3rem 1.5rem 1.5rem;">
                    <p style="font-size:0.72rem; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase; margin:0 0 0.25rem;">Department</p>
                    <h3 style="font-size:1.2rem; font-weight:800; color:{{ $solid }}; margin:0 0 0.75rem; text-transform:uppercase; letter-spacing:0.03em;">{{ $department }}</h3>
                    <p style="font-size:0.85rem; color:#475569; margin:0 0 1.25rem;">
                        <i class="fa-solid fa-folder" style="color:{{ $solid }}; margin-right:4px;"></i>
                        {{ $totalElections }} {{ Str::plural('election', $totalElections) }}
                    </p>
                    <button onclick="showElections('{{ addslashes($department) }}')" class="btn-hover"
                            style="width:100%; padding:0.6rem; border:2px solid {{ $solid }}; border-radius:25px; font-weight:700; font-size:0.82rem; letter-spacing:0.05em; cursor:pointer; transition:all 0.3s; background:{{ $solid }}; color:white; text-transform:uppercase;">
                        VIEW ELECTIONS
                    </button>
                </div>
            </div>
        @empty
            <div style="grid-column:1/-1; text-align:center; padding:4rem; color:#888;">
                <i class="fa-solid fa-folder-open" style="font-size:4rem; margin-bottom:1rem; opacity:0.3;"></i>
                <p style="font-size:1.1rem;">No departments with elections yet.</p>
                <p style="font-size:0.9rem;">Create elections first, then candidates can be added.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Level 2 filter bar (elections inside a dept) -->
<div id="lvl2FilterBar" class="nav-filter-bar" style="display:none;">
    <div class="nfg">
        <label>Status</label>
        <select id="elecNavStatus" onchange="elecNavApply()">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="finished">Finished</option>
        </select>
    </div>
    <div class="nfg">
        <label>Start Date From</label>
        <input type="date" id="elecNavFrom">
    </div>
    <div class="nfg">
        <label>Start Date To</label>
        <input type="date" id="elecNavTo">
    </div>
    <div class="nfg nfg-search">
        <label>Search Election</label>
        <input type="text" id="elecNavSearch" placeholder="Search election name..." oninput="elecNavApply()">
    </div>
    <div style="display:flex;gap:0.5rem;align-items:flex-end;">
        <button class="nf-apply" onclick="elecNavApply()"><i class="fa-solid fa-filter"></i> Apply</button>
        <button class="nf-reset" onclick="elecNavReset()"><i class="fa-solid fa-rotate-left"></i> Reset</button>
    </div>
</div>
<div class="nf-results" id="lvl2ResultsBar" style="display:none;"></div>

<!-- ───────────────────────────────────────────────
     LEVEL 2 — ELECTION FOLDERS (per department)
────────────────────────────────────────────────── -->
<div id="electionsView" style="display: none;">
    @foreach($electionsByDept as $department => $deptElections)
        @php
            $gradient = $deptGradients[$department] ?? 'linear-gradient(160deg, #800020 0%, #1a0008 100%)';
            $accent   = $deptAccents[$department]   ?? '#FFC107';
            $solid    = $deptSolids[$department]    ?? '#800020';
        @endphp
        <div class="dept-elections" data-department="{{ $department }}" style="display: none;">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                @forelse($deptElections as $election)
                    @php
                        $isFinished = $election->end_date->isPast();
                        $statusKey = $isFinished ? 'finished' : ($election->is_active ? 'active' : 'inactive');
                    @endphp
                    <div class="nav-card elec-nav-card {{ $isFinished ? 'nav-card-dimmed' : '' }}"
                         data-name="{{ strtolower($election->election_name) }}"
                         data-status="{{ $statusKey }}"
                         data-start="{{ $election->start_date->format('Y-m-d') }}"
                         onclick="showCandidates('{{ addslashes($department) }}', {{ $election->id }})">

                        @if($isFinished)
                            <div class="election-state-label">
                                Finished
                            </div>
                        @endif

                        <!-- Banner -->
                        <div style="width:100%; height:200px; background:{{ $gradient }}; position:relative; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                            @if($election->banner_image)
                                <img src="{{ $election->banner_image }}" alt="{{ $election->election_name }}" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <i class="fa-solid fa-vote-yea" style="font-size:5rem; color:rgba(255,255,255,0.18);"></i>
                            @endif
                            <!-- Status badge -->
                            <div style="position:absolute; top:12px; right:12px;">
                                <span style="display:inline-flex; align-items:center; gap:6px; padding:5px 14px; border-radius:20px; font-size:0.82rem; font-weight:600; background:white; color:#1e293b; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $isFinished ? '#6b7280' : ($election->is_active ? '#22c55e' : '#9ca3af') }};display:inline-block;"></span>
                                    {{ $isFinished ? 'Finished' : ($election->is_active ? 'Active' : 'Inactive') }}
                                </span>
                            </div>
                            <!-- Candidate count badge -->
                            <div style="position:absolute; top:12px; left:12px;">
                                <span style="display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:20px; font-size:0.8rem; font-weight:700; background:white; color:#1e293b; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                                    <i class="fa-solid fa-users" style="color:{{ $solid }};"></i> {{ $election->candidates_count }}
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div style="padding:1.3rem 1.5rem 1.5rem;">
                            <p style="font-size:0.72rem; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase; margin:0 0 0.25rem;">{{ $department }} DEPARTMENT</p>
                            <h3 style="font-size:1.05rem; font-weight:800; color:#1e293b; margin:0 0 0.8rem; text-transform:uppercase; letter-spacing:0.02em; line-height:1.3;">{{ $election->election_name }}</h3>
                            <p style="font-size:0.83rem; color:#475569; margin:0 0 0.25rem;">
                                <i class="fa-regular fa-calendar" style="width:14px;"></i>
                                Started: {{ $election->start_date->format('M. d, Y') }}
                            </p>
                            <p style="font-size:0.83rem; color:#475569; margin:0 0 1.25rem;">
                                <i class="fa-regular fa-calendar-xmark" style="width:14px;"></i>
                                Expires: {{ $election->end_date->format('M. d, Y') }}
                            </p>
                            <button onclick="showCandidates('{{ addslashes($department) }}', {{ $election->id }})" class="btn-hover election-view-btn"
                                    style="width:100%; padding:0.6rem; border:2px solid {{ $solid }}; border-radius:25px; font-weight:700; font-size:0.82rem; letter-spacing:0.05em; cursor:pointer; transition:all 0.3s; background:{{ $solid }}; color:white; text-transform:uppercase;">
                                {{ $isFinished ? 'VIEW ONLY' : 'VIEW CANDIDATES' }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div style="grid-column:1/-1; text-align:center; padding:3rem; color:#888;">
                        <i class="fa-solid fa-vote-yea" style="font-size:3rem; margin-bottom:1rem; opacity:0.3;"></i>
                        <p>No elections found for this department.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

<!-- Level 3 filter bar (candidates inside an election) -->
<div id="lvl3FilterBar" class="nav-filter-bar" style="display:none;">
    <div class="nfg">
        <label>Position</label>
        <select id="candNavPosition" onchange="candNavApply()">
            <option value="">All Positions</option>
        </select>
    </div>
    <div class="nfg nfg-search">
        <label>Search Candidate</label>
        <input type="text" id="candNavSearch" placeholder="Name or Student ID..." oninput="candNavApply()">
    </div>
    <div style="display:flex;gap:0.5rem;align-items:flex-end;">
        <button class="nf-apply" onclick="candNavApply()"><i class="fa-solid fa-filter"></i> Apply</button>
        <button class="nf-reset" onclick="candNavReset()"><i class="fa-solid fa-rotate-left"></i> Reset</button>
    </div>
</div>
<div class="nf-results" id="lvl3ResultsBar" style="display:none;"></div>

<!-- ───────────────────────────────────────────────
     LEVEL 3 — CANDIDATE CARDS (per election)
────────────────────────────────────────────────── -->
<div id="candidatesView" style="display: none;">
    @foreach($elections as $election)
        <div class="election-candidates" data-election-id="{{ $election->id }}" style="display: none;">
            @if($election->candidates->isEmpty())
                <div style="text-align: center; padding: 4rem; color: #888;">
                    <i class="fa-solid fa-user-slash" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p style="font-size: 1.1rem;">No candidates in this election yet.</p>
                    <p style="font-size: 0.9rem;">Click "Add Candidate" to add the first candidate.</p>
                </div>
            @else
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                    @foreach($election->candidates as $candidate)
                        @php
                            $candUser    = \App\Models\User::where('student_id', $candidate->student_id)->first();
                            $candYrLevel = $candUser ? ($candUser->year_level ?? '') : '';
                            $candBorder  = $deptSolids[$candidate->department] ?? '#800020';
                        @endphp
                        <div class="folder-card cand-nav-card"
                             data-name="{{ strtolower($candidate->first_name . ' ' . $candidate->last_name) }}"
                             data-student-id="{{ strtolower($candidate->student_id) }}"
                             data-position="{{ strtolower($candidate->position) }}"
                             style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: transform 0.25s, box-shadow 0.25s; border: 3px solid {{ $candBorder }};">
                            <!-- Photo -->
                            <div style="width: 100%; height: 260px; background: linear-gradient(135deg, #800020 0%, #A0153E 100%); position: relative; overflow: hidden;">
                                @if($candUser && $candUser->profile_picture)
                                    <img src="{{ $candUser->profile_picture }}" alt="{{ $candidate->full_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @elseif($candidate->image)
                                    <img src="{{ $candidate->image }}" alt="{{ $candidate->full_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: white;">
                                        <i class="fa-solid fa-user" style="font-size: 5rem; opacity: 0.3;"></i>
                                    </div>
                                @endif
                                <!-- Position label at bottom -->
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 0.55rem 1rem; background: linear-gradient(to top, rgba(0,0,0,0.72) 0%, transparent 100%);">
                                    <span style="font-size: 0.73rem; font-weight: 700; color: #FFC107; text-transform: uppercase; letter-spacing: 0.05em;">{{ $candidate->position }}</span>
                                </div>
                            </div>
                            <!-- Content -->
                            <div style="padding: 1.1rem 1.25rem 1.4rem; text-align: center;">
                                <h3 style="font-size: 1.08rem; font-weight: 800; margin: 0 0 0.3rem; color: #111827;">
                                    {{ $candidate->first_name }}@if($candidate->middle_name) {{ substr($candidate->middle_name, 0, 1) }}.@endif {{ $candidate->last_name }}
                                </h3>
                                <p style="font-size: 0.84rem; font-weight: 600; color: #475569; margin: 0 0 0.18rem;">Student ID: {{ $candidate->student_id }}</p>
                                <p style="font-size: 0.84rem; color: #64748b; margin: 0 0 1.2rem; min-height: 1.2em;">{{ $candYrLevel }}</p>
                                <div style="display: flex; gap: 0.6rem; justify-content: center;">
                                    <button onclick="openEditCandidateModal({{ $candidate->id }})" class="btn-hover"
                                            style="padding: 0.48rem 1.5rem; border: 2px solid #800020; border-radius: 50px; font-weight: 700; cursor: pointer; background: transparent; color: #800020; font-size: 0.82rem; transition: all 0.25s;">
                                        Update
                                    </button>
                                    <button onclick="deleteCandidate({{ $candidate->id }})" class="btn-hover"
                                            style="padding: 0.48rem 1.5rem; border: 2px solid #800020; border-radius: 50px; font-weight: 700; cursor: pointer; background: #800020; color: white; font-size: 0.82rem; transition: all 0.25s;">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>

<!-- MODAL -->
<div class="modal-overlay" id="candidateModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; overflow-y: auto; padding: 2rem 0;">
    <div class="modal" style="background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 900px; margin: auto; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Add New Candidate</h2>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <form id="addCandidateForm" onsubmit="handleAddCandidate(event)" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">First Name</label>
                    <input type="text" id="candFirstName" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Middle Name <span style="color: #888; font-weight: normal;">(Optional)</span></label>
                    <input type="text" id="candMiddleName" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Last Name</label>
                    <input type="text" id="candLastName" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Student ID</label>
                    <input type="text" id="candStudentId" required placeholder="e.g. 2024-12345" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Department</label>
                    <select id="candDept" required onchange="loadElections()" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                        <option value="">Select Department</option>
                        <option value="BSIT">BSIT</option>
                        <option value="CBAE">CBAE</option>
                        <option value="CRIM">CRIM</option>
                        <option value="CHTM">CHTM</option>
                        <option value="CTE">CTE</option>
                        <option value="SHS">SHS</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Active Election</label>
                <select id="candElection" required onchange="loadPositions()" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                    <option value="">Select Department First</option>
                </select>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Position</label>
                <select id="candPosition" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                    <option value="">Select Election First</option>
                </select>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Upload Photo <span style="color: #888; font-weight: normal;">(Optional)</span></label>
                <input type="file" id="candPhoto" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
            </div>
            <button type="submit" class="btn-primary" style="width:100%; justify-content: center;">Add Candidate</button>
        </form>
    </div>
</div>

<!-- EDIT CANDIDATE MODAL -->
<div class="modal-overlay" id="editCandidateModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; overflow-y: auto; padding: 2rem 0;">
    <div class="modal" style="background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 900px; margin: auto; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Update Candidate</h2>
            <button onclick="closeEditCandidateModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <form id="editCandidateForm" onsubmit="handleUpdateCandidate(event)" enctype="multipart/form-data">
            <input type="hidden" id="editCandId">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">First Name</label>
                    <input type="text" id="editCandFirstName" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Middle Name <span style="color: #888; font-weight: normal;">(Optional)</span></label>
                    <input type="text" id="editCandMiddleName" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Last Name</label>
                    <input type="text" id="editCandLastName" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Student ID</label>
                    <input type="text" id="editCandStudentId" required placeholder="e.g. 2024-12345" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Department</label>
                    <select id="editCandDept" required onchange="loadEditElections()" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                        <option value="">Select Department</option>
                        <option value="BSIT">BSIT</option>
                        <option value="CBAE">CBAE</option>
                        <option value="CRIM">CRIM</option>
                        <option value="CHTM">CHTM</option>
                        <option value="CTE">CTE</option>
                        <option value="SHS">SHS</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Active Election</label>
                <select id="editCandElection" required onchange="loadEditPositions()" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                    <option value="">Select Department First</option>
                </select>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Position</label>
                <select id="editCandPosition" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                    <option value="">Select Election First</option>
                </select>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Upload Photo <span style="color: #888; font-weight: normal;">(Optional)</span></label>
                <input type="file" id="editCandPhoto" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                <div id="currentPhoto" style="margin-top: 0.5rem;"></div>
            </div>
            <button type="submit" class="btn-primary" style="width:100%; justify-content: center;">Update Candidate</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
const activeElections = @json($activeElections);
const allElections    = @json($elections->map(fn($e) => ['id' => $e->id, 'election_name' => $e->election_name, 'department' => $e->department]));
const candidatesData  = @json($candidates);

// ── Navigation state ──────────────────────────────────────────────
let currentDepartment = null;
let currentElectionId = null;
// 'departments' | 'elections' | 'candidates'
let currentLevel = 'departments';

function renderBreadcrumb() {
    const bc = document.getElementById('breadcrumb');
    if (currentLevel === 'departments') {
        bc.innerHTML = `<span class="active-crumb"><i class="fa-solid fa-house-chimney" style="margin-right:4px;"></i>Departments</span>`;
    } else if (currentLevel === 'elections') {
        bc.innerHTML = `
            <span class="crumb" onclick="showDepartments()"><i class="fa-solid fa-house-chimney" style="margin-right:4px;"></i>Departments</span>
            <span class="sep"><i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i></span>
            <span class="active-crumb">${currentDepartment}</span>`;
    } else {
        const el = allElections.find(e => e.id === currentElectionId);
        const elName = el ? el.election_name : 'Election';
        bc.innerHTML = `
            <span class="crumb" onclick="showDepartments()"><i class="fa-solid fa-house-chimney" style="margin-right:4px;"></i>Departments</span>
            <span class="sep"><i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i></span>
            <span class="crumb" onclick="showElections('${currentDepartment}')">${currentDepartment}</span>
            <span class="sep"><i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i></span>
            <span class="active-crumb">${elName}</span>`;
    }
}

function showDepartments() {
    currentLevel = 'departments';
    currentDepartment = null;
    currentElectionId = null;

    document.getElementById('departmentsView').style.display = 'block';
    document.getElementById('electionsView').style.display  = 'none';
    document.getElementById('candidatesView').style.display = 'none';
    document.getElementById('lvl1FilterBar').style.display  = 'flex';
    document.getElementById('lvl2FilterBar').style.display  = 'none';
    document.getElementById('lvl3FilterBar').style.display  = 'none';
    document.getElementById('lvl1ResultsBar').style.display = 'block';
    document.getElementById('lvl2ResultsBar').style.display = 'none';
    document.getElementById('lvl3ResultsBar').style.display = 'none';
    deptApplyFilter();

    document.getElementById('pageTitle').textContent    = 'Manage Candidates';
    document.getElementById('pageSubtitle').textContent = 'Select a department to view its elections';
    document.getElementById('backBtn').style.display    = 'none';
    renderBreadcrumb();
}

function showElections(department) {
    currentLevel      = 'elections';
    currentDepartment = department;
    currentElectionId = null;

    document.getElementById('departmentsView').style.display = 'none';
    document.getElementById('candidatesView').style.display  = 'none';
    document.getElementById('electionsView').style.display   = 'block';
    document.getElementById('lvl1FilterBar').style.display   = 'none';
    document.getElementById('lvl2FilterBar').style.display   = 'flex';
    document.getElementById('lvl3FilterBar').style.display   = 'none';
    document.getElementById('lvl1ResultsBar').style.display  = 'none';
    document.getElementById('lvl2ResultsBar').style.display  = 'block';
    document.getElementById('lvl3ResultsBar').style.display  = 'none';

    // Hide all dept-election panels, show the right one
    document.querySelectorAll('.dept-elections').forEach(el => el.style.display = 'none');
    const panel = document.querySelector(`.dept-elections[data-department="${department}"]`);
    if (panel) panel.style.display = 'block';

    elecNavReset();

    document.getElementById('pageTitle').textContent    = department + ' — Elections';
    document.getElementById('pageSubtitle').textContent = 'Select an election to view its candidates';
    document.getElementById('backBtn').style.display    = 'inline-flex';
    renderBreadcrumb();
}

function showCandidates(department, electionId) {
    currentLevel      = 'candidates';
    currentDepartment = department;
    currentElectionId = electionId;

    document.getElementById('departmentsView').style.display = 'none';
    document.getElementById('electionsView').style.display   = 'none';
    document.getElementById('candidatesView').style.display  = 'block';
    document.getElementById('lvl1FilterBar').style.display   = 'none';
    document.getElementById('lvl2FilterBar').style.display   = 'none';
    document.getElementById('lvl3FilterBar').style.display   = 'flex';
    document.getElementById('lvl1ResultsBar').style.display  = 'none';
    document.getElementById('lvl2ResultsBar').style.display  = 'none';
    document.getElementById('lvl3ResultsBar').style.display  = 'block';

    // Hide all election-candidate panels, show the right one
    document.querySelectorAll('.election-candidates').forEach(el => el.style.display = 'none');
    const panel = document.querySelector(`.election-candidates[data-election-id="${electionId}"]`);
    if (panel) panel.style.display = 'block';

    // Populate position filter from current election's candidates
    const posSel = document.getElementById('candNavPosition');
    posSel.innerHTML = '<option value="">All Positions</option>';
    if (panel) {
        const positions = [...new Set([...panel.querySelectorAll('.cand-nav-card')].map(c => c.dataset.position).filter(Boolean))];
        positions.sort().forEach(pos => {
            const opt = document.createElement('option');
            opt.value = pos;
            opt.textContent = pos.charAt(0).toUpperCase() + pos.slice(1);
            posSel.appendChild(opt);
        });
    }
    candNavReset();

    const el = allElections.find(e => e.id === electionId);
    const elName = el ? el.election_name : 'Election';
    document.getElementById('pageTitle').textContent    = elName;
    document.getElementById('pageSubtitle').textContent = 'Candidates participating in this election';
    document.getElementById('backBtn').style.display    = 'inline-flex';
    renderBreadcrumb();
}

function goBack() {
    if (currentLevel === 'candidates') {
        showElections(currentDepartment);
    } else {
        showDepartments();
    }
}

function loadElections() {
    const dept = document.getElementById('candDept').value;
    const electionSelect = document.getElementById('candElection');
    const positionSelect = document.getElementById('candPosition');
    
    electionSelect.innerHTML = '<option value="">Select Election</option>';
    positionSelect.innerHTML = '<option value="">Select Election First</option>';
    
    if (!dept) {
        electionSelect.innerHTML = '<option value="">Select Department First</option>';
        return;
    }
    
    const deptElections = activeElections.filter(e => e.department === dept && e.is_active);
    
    if (deptElections.length === 0) {
        electionSelect.innerHTML = '<option value="">No active elections for this department</option>';
        return;
    }
    
    deptElections.forEach(election => {
        const option = document.createElement('option');
        option.value = election.id;
        option.textContent = election.election_name;
        electionSelect.appendChild(option);
    });
}

function loadPositions() {
    const electionId = parseInt(document.getElementById('candElection').value);
    const positionSelect = document.getElementById('candPosition');
    positionSelect.innerHTML = '<option value="">Select Position</option>';
    
    if (!electionId) {
        positionSelect.innerHTML = '<option value="">Select Election First</option>';
        return;
    }
    
    const election = activeElections.find(e => e.id === electionId);
    
    if (!election || !election.positions || election.positions.length === 0) {
        positionSelect.innerHTML = '<option value="">No positions available</option>';
        return;
    }
    
    election.positions.forEach(position => {
        const option = document.createElement('option');
        option.value = position;
        option.textContent = position;
        positionSelect.appendChild(option);
    });
}

function openModal() {
    document.getElementById('candidateModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('candidateModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function handleAddCandidate(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('campus_election_id', document.getElementById('candElection').value);
    formData.append('first_name', document.getElementById('candFirstName').value);
    formData.append('middle_name', document.getElementById('candMiddleName').value || '');
    formData.append('last_name', document.getElementById('candLastName').value);
    formData.append('student_id', document.getElementById('candStudentId').value);
    formData.append('position', document.getElementById('candPosition').value);
    formData.append('department', document.getElementById('candDept').value);
    
    const photoFile = document.getElementById('candPhoto').files[0];
    if (photoFile) {
        formData.append('photo', photoFile);
    }

    fetch('/admin/candidates', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            closeModal();
            document.getElementById('addCandidateForm').reset();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

function deleteCandidate(id) {
    if (!confirm('Are you sure you want to delete this candidate?')) return;

    fetch(`/admin/candidates/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

function openEditCandidateModal(id) {
    const candidate = candidatesData.find(c => c.id === id);
    if (!candidate) return;

    document.getElementById('editCandId').value = candidate.id;
    document.getElementById('editCandFirstName').value = candidate.first_name;
    document.getElementById('editCandMiddleName').value = candidate.middle_name || '';
    document.getElementById('editCandLastName').value = candidate.last_name;
    document.getElementById('editCandStudentId').value = candidate.student_id;
    document.getElementById('editCandDept').value = candidate.department;
    
    // Load elections for the department
    loadEditElections();
    
    // Wait a bit for elections to load, then set the election and position
    setTimeout(() => {
        if (candidate.campus_election_id) {
            document.getElementById('editCandElection').value = candidate.campus_election_id;
            loadEditPositions();
            setTimeout(() => {
                document.getElementById('editCandPosition').value = candidate.position;
            }, 100);
        } else {
            document.getElementById('editCandPosition').value = candidate.position;
        }
    }, 100);

    // Show current photo
    const photoDiv = document.getElementById('currentPhoto');
    if (candidate.image) {
        photoDiv.innerHTML = `<small style="color: #888;">Current photo: <img src="${candidate.image}" style="max-width: 100px; border-radius: 4px; vertical-align: middle;"></small>`;
    } else {
        photoDiv.innerHTML = '';
    }

    document.getElementById('editCandidateModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeEditCandidateModal() {
    document.getElementById('editCandidateModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function loadEditElections() {
    const dept = document.getElementById('editCandDept').value;
    const electionSelect = document.getElementById('editCandElection');
    const positionSelect = document.getElementById('editCandPosition');
    
    electionSelect.innerHTML = '<option value="">Select Election</option>';
    positionSelect.innerHTML = '<option value="">Select Election First</option>';
    
    if (!dept) {
        electionSelect.innerHTML = '<option value="">Select Department First</option>';
        return;
    }
    
    const deptElections = activeElections.filter(e => e.department === dept && e.is_active);
    
    if (deptElections.length === 0) {
        electionSelect.innerHTML = '<option value="">No active elections for this department</option>';
        return;
    }
    
    deptElections.forEach(election => {
        const option = document.createElement('option');
        option.value = election.id;
        option.textContent = election.election_name;
        electionSelect.appendChild(option);
    });
}

function loadEditPositions() {
    const electionId = parseInt(document.getElementById('editCandElection').value);
    const positionSelect = document.getElementById('editCandPosition');
    
    positionSelect.innerHTML = '<option value="">Select Position</option>';
    
    if (!electionId) {
        positionSelect.innerHTML = '<option value="">Select Election First</option>';
        return;
    }
    
    const election = activeElections.find(e => e.id === electionId);
    
    if (!election || !election.positions || election.positions.length === 0) {
        positionSelect.innerHTML = '<option value="">No positions available</option>';
        return;
    }
    
    election.positions.forEach(position => {
        const option = document.createElement('option');
        option.value = position;
        option.textContent = position;
        positionSelect.appendChild(option);
    });
}

function handleUpdateCandidate(e) {
    e.preventDefault();
    
    const id = document.getElementById('editCandId').value;
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('campus_election_id', document.getElementById('editCandElection').value);
    formData.append('first_name', document.getElementById('editCandFirstName').value);
    formData.append('middle_name', document.getElementById('editCandMiddleName').value || '');
    formData.append('last_name', document.getElementById('editCandLastName').value);
    formData.append('student_id', document.getElementById('editCandStudentId').value);
    formData.append('position', document.getElementById('editCandPosition').value);
    formData.append('department', document.getElementById('editCandDept').value);
    
    const photoFile = document.getElementById('editCandPhoto').files[0];
    if (photoFile) {
        formData.append('photo', photoFile);
    }

    fetch(`/admin/candidates/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            closeEditCandidateModal();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}


window.onclick = function(event) {
    if (event.target.id === 'candidateModal') {
        closeModal();
    }
}

// ── Level 1 filter: Departments ─────────────────────────────
function deptApplyFilter() {
    const q = (document.getElementById('deptSearchInput').value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.cand-dept-card');
    let shown = 0;
    cards.forEach(c => {
        const match = !q || c.dataset.dept.includes(q);
        c.style.display = match ? '' : 'none';
        if (match) shown++;
    });
    const bar = document.getElementById('lvl1ResultsBar');
    if (bar) bar.textContent = `Showing ${shown} of ${cards.length} department${cards.length !== 1 ? 's' : ''}`;
}
function deptResetFilter() {
    document.getElementById('deptSearchInput').value = '';
    deptApplyFilter();
}

// ── Level 2 filter: Elections inside a dept ───────────────
function elecNavApply() {
    const status = document.getElementById('elecNavStatus').value;
    const from   = document.getElementById('elecNavFrom').value;
    const to     = document.getElementById('elecNavTo').value;
    const q      = (document.getElementById('elecNavSearch').value || '').toLowerCase().trim();
    const panel  = document.querySelector('.dept-elections[style*="block"]') ||
                   document.querySelector(`.dept-elections[data-department="${currentDepartment}"]`);
    if (!panel) return;
    const cards = panel.querySelectorAll('.elec-nav-card');
    let shown = 0;
    cards.forEach(c => {
        const ok = (!status || c.dataset.status === status)
                && (!from   || c.dataset.start >= from)
                && (!to     || c.dataset.start <= to)
                && (!q      || c.dataset.name.includes(q));
        c.style.display = ok ? '' : 'none';
        if (ok) shown++;
    });
    const bar = document.getElementById('lvl2ResultsBar');
    if (bar) bar.textContent = `Showing ${shown} of ${cards.length} election${cards.length !== 1 ? 's' : ''}`;
}
function elecNavReset() {
    document.getElementById('elecNavStatus').value  = '';
    document.getElementById('elecNavFrom').value    = '';
    document.getElementById('elecNavTo').value      = '';
    document.getElementById('elecNavSearch').value  = '';
    elecNavApply();
}

// ── Level 3 filter: Candidates inside an election ─────────
function candNavApply() {
    const pos   = document.getElementById('candNavPosition').value;
    const q     = (document.getElementById('candNavSearch').value || '').toLowerCase().trim();
    const panel = document.querySelector(`.election-candidates[data-election-id="${currentElectionId}"]`);
    if (!panel) return;
    const cards = panel.querySelectorAll('.cand-nav-card');
    let shown = 0;
    cards.forEach(c => {
        const ok = (!pos || c.dataset.position === pos)
                && (!q  || c.dataset.name.includes(q) || c.dataset.studentId.includes(q));
        c.style.display = ok ? '' : 'none';
        if (ok) shown++;
    });
    const bar = document.getElementById('lvl3ResultsBar');
    if (bar) bar.textContent = `Showing ${shown} of ${cards.length} candidate${cards.length !== 1 ? 's' : ''}`;
}
function candNavReset() {
    document.getElementById('candNavPosition').value = '';
    document.getElementById('candNavSearch').value   = '';
    candNavApply();
}

// Init dept filter count on page load
document.addEventListener('DOMContentLoaded', deptApplyFilter);
</script>
@endpush
@endsection
