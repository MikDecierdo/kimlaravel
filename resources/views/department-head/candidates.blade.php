@extends('layouts.department-head')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/shared.css') }}">
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/candidates.css') }}">
@endpush

@section('dept-head-content')
@php
    $canManageCandidates = auth()->user()->hasDepartmentPortalPermission('add_candidates');
    $headDeptCompact = preg_replace('/[^A-Z]/', '', strtoupper(trim((string) (auth()->user()->department ?? ''))));
    $isCsgHead = in_array($headDeptCompact, ['CSG', 'CENTRALSTUDENTGOVERNMENT'], true);
@endphp

<header>
    <div class="header-title">
        <h1 id="pageTitle">Manage Candidates</h1>
        <p id="pageSubtitle">Select an election to view candidates</p>
    </div>
    <div id="candidateContextActions" data-can-manage="{{ $canManageCandidates ? '1' : '0' }}" style="display:none; align-items:center; gap:0.6rem;">
        <button type="button" class="btn-hover" onclick="showElections()" style="padding:0.62rem 1.1rem; border:2px solid #800020; border-radius:10px; font-weight:700; font-size:0.86rem; cursor:pointer; transition:all 0.3s; background:white; color:#800020;">
            <i class="fa-solid fa-arrow-left"></i> Back to Elections
        </button>
        <button id="contextAddCandidateBtn" type="button" class="btn-primary btn-hover" onclick="openModal()" style="{{ $canManageCandidates ? '' : 'display:none;' }} background: linear-gradient(135deg, #800020 0%, #A0153E 100%); transition: all 0.3s;">
            <i class="fa-solid fa-file-signature"></i> Submit Registration
        </button>
        <button id="contextRegistrationBtn" type="button" class="btn-hover" onclick="openRegistrationModal()" style="{{ $canManageCandidates ? '' : 'display:none;' }} padding:0.62rem 1.1rem; border:2px solid #800020; border-radius:10px; font-weight:700; font-size:0.86rem; cursor:pointer; transition:all 0.3s; background:white; color:#800020;">
            <i class="fa-solid fa-clipboard-check"></i> Registration List
        </button>
        <button id="contextViewOnlyBtn" type="button" class="btn-hover" style="display:none; padding:0.62rem 1.1rem; border:2px solid #64748b; border-radius:10px; font-weight:700; font-size:0.86rem; background:#64748b; color:white; cursor:not-allowed;" disabled aria-disabled="true">
            <i class="fa-solid fa-eye"></i> {{ $canManageCandidates ? 'View Only' : 'No Edit Permission' }}
        </button>
    </div>
</header>

<!-- ELECTION FOLDERS VIEW -->

<div id="electionsView">
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
                    <option value="most-candidates">Most Candidates</option>
                    <option value="fewest-candidates">Fewest Candidates</option>
                    <option value="newest">Newest</option>
                    <option value="oldest">Oldest</option>
                    <option value="az">A – Z</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select id="elecFilterStatus" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="finished">Finished</option>
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

    <div class="election-grid" id="electionsGrid">
        @forelse($elections as $election)
                @php
                    $isFinished = \Carbon\Carbon::parse($election->end_date)->isPast();
                    $isCsgOnlyManaged = !$isCsgHead && strtoupper(trim((string) $election->department)) === 'CSG';
                    $isViewOnly = $isFinished || !$election->is_active || $isCsgOnlyManaged;
                    $statusKey = $isFinished ? 'finished' : ($election->is_active ? 'active' : 'inactive');
                @endphp
                <div class="nav-card slide-top {{ $isViewOnly ? 'nav-card-dimmed' : '' }}" onclick="showElectionCandidates({{ $election->id }})"
                     data-election-id="{{ $election->id }}"
                 data-start="{{ \Carbon\Carbon::parse($election->start_date)->format('Y-m-d') }}"
                 data-status="{{ $statusKey }}"
                     data-view-only="{{ $isViewOnly ? '1' : '0' }}"
                     data-finished="{{ $isFinished ? '1' : '0' }}"
                 data-candidates="{{ $election->candidates_count }}"
                 data-name="{{ strtolower($election->election_name) }}">

                    @if($isFinished)
                        <div class="election-state-label">
                            Finished
                        </div>
                    @elseif($isCsgOnlyManaged)
                        <div class="election-state-label election-state-label-disabled">
                            CSG Only
                        </div>
                    @endif

                <!-- Banner -->
                <div style="width:100%; height:200px; background:linear-gradient(135deg, #800020 0%, #A0153E 100%); position:relative; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                    @if($election->banner_image)
                        <img src="{{ $election->banner_image }}" alt="{{ $election->election_name }}" style="width:100%; height:100%; object-fit:cover;">
                    @else
                        <i class="fa-solid fa-vote-yea" style="font-size:5rem; color:rgba(255,255,255,0.18);"></i>
                    @endif
                    <!-- Status badge top-right -->
                    <div style="position:absolute; top:12px; right:12px; z-index:2;">
                        <span style="display:inline-flex; align-items:center; gap:6px; padding:5px 14px; border-radius:20px; font-size:0.82rem; font-weight:600; background:white; color:#1e293b; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                            <span style="width:8px;height:8px;border-radius:50%;background:{{ $isFinished ? '#6b7280' : ($election->is_active ? '#22c55e' : '#9ca3af') }};display:inline-block;"></span>
                            {{ $isFinished ? 'Finished' : ($election->is_active ? 'Active' : 'Inactive') }}
                        </span>
                    </div>
                    <!-- Candidate count badge top-left -->
                    <div style="position:absolute; top:12px; left:12px; z-index:2;">
                        <span style="display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:20px; font-size:0.8rem; font-weight:700; background:white; color:#1e293b; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                            <i class="fa-solid fa-users" style="color:#800020;"></i> {{ $election->candidates_count }}
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
                        Expires: {{ \Carbon\Carbon::parse($election->end_date)->format('M. d, Y') }}
                    </p>
                    <button onclick="showElectionCandidates({{ $election->id }})" class="btn-hover election-view-btn"
                            style="width:100%; padding:0.6rem; border:2px solid #800020; border-radius:25px; font-weight:700; font-size:0.82rem; letter-spacing:0.05em; cursor:pointer; transition:all 0.3s; background:#800020; color:white; text-transform:uppercase;">
                        {{ $isViewOnly ? 'VIEW ONLY' : 'VIEW CANDIDATES' }}
                    </button>
                </div>
            </div>
        @empty
            <div style="grid-column:1/-1; text-align:center; padding:4rem; background:white; border-radius:16px; color:#888;">
                <i class="fa-solid fa-inbox" style="font-size:4rem; margin-bottom:1rem; opacity:0.3;"></i>
                <h3 style="color:#6B7280;">No Elections</h3>
                <p style="color:#9ca3af;">Create an election first to add candidates.</p>
                <a href="{{ route('department-head.campus-elections') }}" class="btn-primary" style="display:inline-block; margin-top:1rem; text-decoration:none;">
                    <i class="fa-solid fa-plus"></i> Create Election
                </a>
            </div>
        @endforelse
    </div>
</div>

<!-- CANDIDATES VIEW (Hidden by default) -->
<div id="candidatesView" style="display: none;">
    @foreach($elections as $election)
        @php
            $isFinished = \Carbon\Carbon::parse($election->end_date)->isPast();
            $isCsgOnlyManaged = !$isCsgHead && strtoupper(trim((string) $election->department)) === 'CSG';
            $isViewOnly = $isFinished || !$election->is_active || $isCsgOnlyManaged;
        @endphp
        <div class="election-candidates" data-election-id="{{ $election->id }}" data-view-only="{{ $isViewOnly ? '1' : '0' }}" style="display: none;">
            @if($election->candidatesByPosition->isEmpty())
                <div style="text-align: center; padding: 4rem; color: #888; background: white; border-radius: 12px;">
                    <i class="fa-solid fa-users" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p style="font-size: 1.1rem;">No candidates yet for this election.</p>
                    <p style="font-size: 0.9rem;">
                        @if($isCsgOnlyManaged)
                            Candidate additions are processed through Registration List, and CSG applicants are scoped by department.
                        @elseif($isViewOnly || !$canManageCandidates)
                            This election is in view-only mode.
                        @else
                            Click "Add Candidate" to create candidates for {{ $election->election_name }}.
                        @endif
                    </p>
                </div>
            @else
                @foreach($election->candidatesByPosition as $position => $candidates)
                    <div style="margin-bottom: 3rem;">
                        <!-- Position Header -->
                        <div style="background: linear-gradient(135deg, #800020 0%, #A0153E 100%); color: white; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 600;">
                                <i class="fa-solid fa-award"></i> {{ $position }}
                                <span style="float: right; font-size: 0.9rem; opacity: 0.9;">({{ $candidates->count() }} {{ Str::plural('candidate', $candidates->count()) }})</span>
                            </h3>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                            @foreach($candidates as $candidate)
                                @php
                                    $student     = \App\Models\User::where('student_id', $candidate->student_id)->first();
                                    $candYrLevel = $student ? ($student->year_level ?? '') : '';
                                    $candBorder = '#800020';
                                @endphp
                                <div class="folder-card slide-top" style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: transform 0.25s, box-shadow 0.25s; border: 3px solid #800020;">
                                    <!-- Photo -->
                                    <div style="width: 100%; height: 260px; background: linear-gradient(135deg, #800020 0%, #A0153E 100%); position: relative; overflow: hidden;">
                                        @if($student && $student->profile_picture)
                                            <img src="{{ $student->profile_picture }}" alt="{{ $candidate->first_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        @elseif($candidate->image)
                                            <img src="{{ $candidate->image }}" alt="{{ $candidate->first_name }}" style="width: 100%; height: 100%; object-fit: cover;">
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
                                        @if(!empty($candidate->partylist))
                                            <div style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.35rem 0.7rem;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;color:#800020;font-size:0.76rem;font-weight:700;margin-bottom:0.95rem;">
                                                <i class="fa-solid fa-flag"></i> {{ $candidate->partylist }}
                                            </div>
                                        @endif
                                        <div style="display: flex; gap: 0.6rem; justify-content: center;">
                                            @if($isViewOnly || !$canManageCandidates)
                                                <button class="btn-hover" disabled aria-disabled="true"
                                                        style="padding: 0.48rem 1.5rem; border: 2px solid #64748b; border-radius: 50px; font-weight: 700; cursor: not-allowed; background: #64748b; color: white; font-size: 0.82rem; transition: all 0.25s; opacity: 0.8;">
                                                    View Only
                                                </button>
                                            @else
                                                <button onclick="event.stopPropagation(); openEditModal({{ $candidate->id }}); return false;" class="btn-hover"
                                                        style="padding: 0.48rem 1.5rem; border: 2px solid #800020; border-radius: 50px; font-weight: 700; cursor: pointer; background: transparent; color: #800020; font-size: 0.82rem; transition: all 0.25s;">
                                                    Update
                                                </button>
                                                <button onclick="event.stopPropagation(); deleteCandidate({{ $candidate->id }}); return false;" class="btn-hover"
                                                        style="padding: 0.48rem 1.5rem; border: 2px solid #800020; border-radius: 50px; font-weight: 700; cursor: pointer; background: #800020; color: white; font-size: 0.82rem; transition: all 0.25s;">
                                                    Remove
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach
</div>

<!-- MODAL -->
<div class="modal-overlay" id="candidateModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; overflow-y: auto; padding: 2rem 0;">
    <div class="modal" style="background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 900px; margin: auto; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Candidate Registration Form</h2>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <form id="addCandidateForm" onsubmit="handleAddCandidate(event)" enctype="multipart/form-data">
            <input type="hidden" id="candElectionId">

            <div style="margin-bottom: 1rem; padding: 0.75rem 0.9rem; border-radius: 8px; background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; font-size: 0.86rem;">
                Submitted registration will not be added directly. You must confirm it first from Registration List.
            </div>

            <!-- Student Photo Preview -->
            <div style="margin-bottom: 1.5rem; text-align: center;">
                <div id="candidatePhotoPreview" style="width: 150px; height: 150px; border-radius: 50%; background: linear-gradient(135deg, #e9ecef 0%, #d1d5db 100%); display: flex; align-items: center; justify-content: center; border: 3px solid #800020; overflow: hidden; margin: 0 auto;">
                    <i class="fa-solid fa-user" style="font-size: 4rem; color: #6B7280;"></i>
                </div>
                <p style="margin-top: 0.5rem; font-size: 0.85rem; color: #6B7280;">Student Photo</p>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Search Student</label>
                <p style="margin: 0 0 0.45rem; font-size: 0.8rem; color: #64748b;">Only accepted students are searchable here.</p>
                <input type="text" id="studentSearch" placeholder="Type to search for a student..." autocomplete="off" oninput="searchStudents()" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                <input type="hidden" id="selectedUserId">
                <div id="studentDropdown" style="position: relative; display: none; border: 1px solid var(--border); border-top: none; border-radius: 0 0 8px 8px; max-height: 200px; overflow-y: auto; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: -8px; z-index: 100;">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Student Name</label>
                    <input type="text" id="candFullName" readonly style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: #f8fafc;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Student ID</label>
                    <input type="text" id="candStudentId" readonly style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: #f8fafc;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Current Election</label>
                    <input type="text" id="candCurrentElection" readonly style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: #f8fafc;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Year Level</label>
                    <input type="text" id="candYearLevel" readonly style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: #f8fafc;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Department</label>
                    <input type="text" id="candDept" readonly style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: #f8fafc;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Position</label>
                    <select id="candPosition" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                        <option value="">Select Election First</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Partylist <span style="color:#888; font-weight:normal;">(Optional)</span></label>
                <select id="candPartylist" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                    <option value="">Independent / No Partylist</option>
                </select>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Advocacy <span style="color: #888; font-weight: normal;">(Optional)</span></label>
                <textarea id="candAdvocacy" placeholder="Enter candidate's advocacy..." maxlength="250" oninput="updateCharCount()" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; min-height: 100px; resize: vertical; font-family: inherit;"></textarea>
                <div style="text-align: right; font-size: 0.85rem; color: #6b7280; margin-top: 0.25rem;">
                    <span id="charCount">0</span>/250 characters
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:100%; justify-content: center;">Submit Registration</button>
        </form>
    </div>
</div>

<!-- REGISTRATION REVIEW MODAL -->
<div class="modal-overlay" id="registrationModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; overflow-y: auto; padding: 2rem 0;">
    <div class="modal" style="background: white; padding: 1.5rem; border-radius: 12px; width: 92%; max-width: 1100px; margin: auto; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2 style="margin:0;">Candidate Registrations</h2>
            <button onclick="closeRegistrationModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <div style="margin-bottom: 1rem; display: grid; grid-template-columns: 1fr auto; gap: 0.75rem; align-items: center;">
            <input type="text" id="registrationSearchInput" oninput="renderRegistrationList()" placeholder="Search by name, student ID, year level, position, department" style="width:100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
            <button type="button" onclick="loadCandidateRegistrations()" class="btn-hover" style="padding: 0.55rem 0.95rem; border: 2px solid #800020; border-radius: 10px; font-weight: 700; background: #fff; color: #800020; cursor: pointer;">
                <i class="fa-solid fa-rotate"></i> Refresh
            </button>
        </div>

        <div id="registrationListContainer" style="display:grid; gap:0.8rem;"></div>
    </div>
</div>

<!-- UPDATE CANDIDATE MODAL -->
<div class="modal-overlay" id="editCandidateModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; overflow-y: auto; padding: 2rem 0;">
    <div class="modal" style="background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 600px; margin: auto; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Update Candidate Advocacy</h2>
            <button onclick="closeEditModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <form id="editCandidateForm" onsubmit="handleUpdateCandidate(event)">
            <input type="hidden" id="editCandId">
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Candidate Name</label>
                <input type="text" id="editCandName" readonly style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: #f8fafc;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Position</label>
                <input type="text" id="editCandPositionDisplay" readonly style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: #f8fafc;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Advocacy</label>
                <textarea id="editCandAdvocacy" placeholder="Enter candidate's advocacy..." maxlength="250" oninput="updateEditCharCount()" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; min-height: 120px; resize: vertical; font-family: inherit;"></textarea>
                <div style="text-align: right; font-size: 0.85rem; color: #6b7280; margin-top: 0.25rem;">
                    <span id="editCharCount">0</span>/250 characters
                </div>
            </div>
            
            <button type="submit" class="btn-primary" style="width:100%; justify-content: center;">Update Advocacy</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
const electionsData = @json($elections);
window.canManageCandidates = @json($canManageCandidates);
window.isCsgDepartmentHead = @json($isCsgHead);
</script>
<script src="{{ asset('assets/dept-head/js/candidates.js') }}?v={{ filemtime(public_path('assets/dept-head/js/candidates.js')) }}"></script>
@endpush
@endsection
