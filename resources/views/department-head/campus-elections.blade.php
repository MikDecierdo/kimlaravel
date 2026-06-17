@extends('layouts.department-head')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/shared.css') }}">
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/campus-elections.css') }}">
@endpush

@section('dept-head-content')
@php
    $canManageElections = auth()->user()->hasDepartmentPortalPermission('create_election');
    $isCsgHead = strtoupper(trim((string) (auth()->user()->department ?? ''))) === 'CSG';
@endphp


<header>
    <div class="header-title">
        <h1>Manage Elections</h1>
        <p>Manage elections for {{ auth()->user()->department }} department</p>
    </div>
    @if($canManageElections)
        <button class="btn-primary btn-hover" onclick="openModal()" style="background: linear-gradient(135deg, #800020 0%, #A0153E 100%); transition: all 0.3s;">
            <i class="fa-solid fa-plus"></i> Add Election
        </button>
    @else
        <button class="btn-hover" disabled aria-disabled="true" style="padding:0.62rem 1.1rem; border:2px solid #64748b; border-radius:10px; font-weight:700; font-size:0.86rem; background:#64748b; color:white; cursor:not-allowed; opacity:0.9;">
            <i class="fa-solid fa-eye"></i> View Only
        </button>
    @endif
</header>


<!-- Filter Bar -->
<div class="filter-card">
    <div class="filter-left">
        <div class="filter-group">
            <label>Date From</label>
            <input type="date" id="campusFilterDateFrom" class="filter-input">
        </div>
        <div class="filter-group">
            <label>Date To</label>
            <input type="date" id="campusFilterDateTo" class="filter-input">
        </div>
        <div class="filter-group">
            <label>Sort By</label>
            <select id="campusFilterSort" class="filter-select">
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
            <select id="campusFilterStatus" class="filter-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="finished">Finished</option>
            </select>
        </div>
        <div class="filter-actions">
            <button onclick="applyCampusFilters()" class="btn-apply">Apply Filters</button>
            <button onclick="resetCampusFilters()" class="btn-reset">Reset</button>
        </div>
    </div>
    <div class="search-wrapper">
        <label style="font-size:0.8rem;font-weight:600;color:#1f2937;">Search</label>
        <input type="text" id="campusFilterSearch" class="search-input" placeholder="Search elections..." oninput="applyCampusFilters()">
    </div>
</div>

<div class="election-grid" id="campusElectionsGrid">
    @forelse($elections as $election)
        @php
            $isFinished = $election->end_date->isPast();
            $isCsgOnlyManaged = !$isCsgHead && strtoupper(trim((string) $election->department)) === 'CSG';
            $isDimmed = $isFinished || $isCsgOnlyManaged;
            $statusKey = $isFinished ? 'finished' : ($election->is_active ? 'active' : 'inactive');
        @endphp
        <div class="election-card slide-top {{ $isDimmed ? 'election-card-dimmed' : '' }}" id="election-card-{{ $election->id }}"
             data-start="{{ $election->start_date->format('Y-m-d') }}"
             data-status="{{ $statusKey }}"
             data-candidates="{{ $election->candidates->count() }}"
             data-name="{{ strtolower($election->election_name) }}">
            <!-- Description Overlay -->
            <div class="desc-overlay">
                <button onclick="closeDescOverlay({{ $election->id }})" style="position:absolute;top:12px;right:14px;background:rgba(255,255,255,0.2);border:none;color:white;border-radius:50%;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:0.9rem;transition:background 0.2s;" onmouseenter="this.style.background='rgba(255,255,255,0.35)'" onmouseleave="this.style.background='rgba(255,255,255,0.2)'">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <i class="fa-solid fa-vote-yea" style="font-size:2.5rem;margin-bottom:1rem;opacity:0.8;"></i>
                <h4 style="font-size:1rem;font-weight:700;margin:0 0 0.75rem;letter-spacing:0.03em;">{{ $election->election_name }}</h4>
                <p style="font-size:0.88rem;line-height:1.6;opacity:0.92;margin:0;">{{ $election->description && $election->description !== 'none' ? $election->description : 'No description provided.' }}</p>
            </div>
            @if($isFinished)
                <div class="election-state-label">
                    Finished
                </div>
            @elseif($isCsgOnlyManaged)
                <div class="election-state-label">
                    CSG Only
                </div>
            @endif
            <!-- Arrow trigger -->
            <button class="overlay-trigger" @if(!$isDimmed) onclick="openDescOverlay({{ $election->id }})" @endif title="View description" @if($isDimmed) disabled aria-disabled="true" @endif>
                <i class="fa-solid fa-chevron-left" style="font-size:0.85rem;"></i>
            </button>
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
            </div>

            <!-- Content -->
            <div style="padding:1.4rem 1.5rem 1.5rem;">
                <p style="font-size:0.75rem; font-weight:700; letter-spacing:0.08em; color:#1e293b; text-transform:uppercase; margin:0 0 0.3rem;">{{ $election->department }} DEPARTMENT</p>
                <h3 style="font-size:1.15rem; font-weight:800; color:#1e293b; margin:0 0 1rem; text-transform:uppercase; letter-spacing:0.02em;">{{ $election->election_name }}</h3>
                <p style="font-size:0.85rem; color:#475569; margin:0 0 0.3rem;">
                    Registration Start: {{ optional($election->registration_start_date ?? $election->start_date)->format('M. d, Y') }}
                </p>
                <p style="font-size:0.85rem; color:#475569; margin:0 0 0.3rem;">
                    Registration End: {{ optional($election->registration_end_date ?? $election->end_date)->format('M, d, Y') }}
                </p>
                <p style="font-size:0.85rem; color:#475569; margin:0 0 0.3rem;">
                    Election Started : {{ $election->start_date->format('M. d, Y') }}
                </p>
                <p style="font-size:0.85rem; color:#475569; margin:0 0 1.25rem;">
                    Election Expired: {{ $election->end_date->format('M, d, Y') }}
                </p>
                <!-- Action buttons -->
                <div style="display:flex; gap:0.75rem;">
                    <button class="btn-hover card-action-btn" @if(!$isDimmed && $canManageElections) onclick="openEditModal({{ $election->id }})" @endif @if($isDimmed || !$canManageElections) disabled aria-disabled="true" @endif
                            style="flex:1; padding:0.6rem; border:2px solid #800020; border-radius:25px; font-weight:700; font-size:0.85rem; letter-spacing:0.05em; cursor:pointer; transition:all 0.3s; background:white; color:#800020; text-transform:uppercase;">
                        UPDATE
                    </button>
                    <button class="btn-hover card-action-btn" @if(!$isDimmed && $canManageElections) onclick="toggleStatus({{ $election->id }}, {{ $election->is_active ? 'false' : 'true' }})" @endif @if($isDimmed || !$canManageElections) disabled aria-disabled="true" @endif
                            style="flex:1; padding:0.6rem; border:2px solid #800020; border-radius:25px; font-weight:700; font-size:0.85rem; letter-spacing:0.05em; cursor:pointer; transition:all 0.3s; background:#800020; color:white; text-transform:uppercase;">
                        {{ $election->is_active ? 'DISABLE' : 'ENABLE' }}
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column:1/-1; text-align:center; padding:4rem; background:white; border-radius:16px; color:#888;">
            <i class="fa-solid fa-inbox" style="font-size:4rem; margin-bottom:1rem; opacity:0.3;"></i>
            <h3 style="color:#6B7280;">No Elections</h3>
            <p style="color:#9ca3af;">Click "Add Election" to create your first election.</p>
        </div>
    @endforelse
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="electionModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; overflow-y: auto; padding: 2rem 0;">
    <div class="modal" style="background: white; padding: 2rem; border-radius: 12px; width: min(96vw, 1320px); margin: auto; max-height: 92vh; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 24px 70px rgba(15, 23, 42, 0.22);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-shrink: 0;">
            <h2>Add Election</h2>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <form id="addElectionForm" onsubmit="handleAddElection(event)" enctype="multipart/form-data" style="display:flex; flex-direction:column; height:100%; overflow:hidden;">
            <div style="overflow:auto; flex:1 1 auto; padding-right:0.5rem; padding-bottom:1.5rem;">
                <div style="display:grid; grid-template-columns:minmax(0,1.1fr) minmax(360px,0.9fr); gap:1.5rem; align-items:start;">
                    <div style="display:grid; gap:1rem;">
                        <div style="margin-bottom: 0;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Department</label>
                            <input type="text" id="electionDept" value="{{ auth()->user()->department }}" readonly style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: #f8fafc;">
                        </div>
                        <div style="margin-bottom: 0;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Election Name</label>
                            <input type="text" id="electionName" required placeholder="e.g. IT Department Student Council Election 2024" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                            <div style="margin-bottom: 0;">
                                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Registration Start Date</label>
                                <input type="date" id="electionRegStartDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                            </div>
                            <div style="margin-bottom: 0;">
                                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Registration End Date</label>
                                <input type="date" id="electionRegEndDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                            </div>
                            <div style="margin-bottom: 0;">
                                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Start Date</label>
                                <input type="date" id="electionStartDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                            </div>
                            <div style="margin-bottom: 0;">
                                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">End Date</label>
                                <input type="date" id="electionEndDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                            </div>
                            <div style="margin-bottom: 0;">
                                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Voting Start Time</label>
                                <input type="time" id="electionVotingStartTime" value="08:00" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                            </div>
                            <div style="margin-bottom: 0;">
                                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Voting End Time</label>
                                <input type="time" id="electionVotingEndTime" value="17:00" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                            </div>
                        </div>
                        <div style="margin-bottom: 0;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Description <span style="color:#888; font-weight:normal;">(Optional)</span></label>
                            <textarea id="electionDesc" rows="5" placeholder="Brief description of the election..." style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; resize: vertical;"></textarea>
                        </div>
                    </div>
                    <div style="display:grid; gap:1rem;">
                        <div style="margin-bottom: 0;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Candidate Positions</label>
                            <div style="display:grid; grid-template-columns:1fr auto; gap:0.75rem; margin-bottom:0.75rem;">
                                <input type="text" id="customPositionName" placeholder="Add another position" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
                                <button type="button" onclick="addCustomPosition()" style="padding: 10px 16px; background: linear-gradient(135deg, #800020 0%, #A0153E 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                            <div id="positionInput" style="display:grid; grid-template-columns:repeat(2,minmax(160px,1fr)); gap:0.55rem; padding:0.75rem; border:1px solid var(--border); border-radius:8px; background:#fff; margin-bottom:0.5rem; max-height:250px; overflow:auto;">
                                @if($isCsgHead)
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="President"> President</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="Vice President Internal"> Vice President Internal</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="Vice President External"> Vice President External</label>
                                    @for($i = 1; $i <= 9; $i++)
                                        <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="Senator {{$i}}"> Senator {{$i}}</label>
                                    @endfor
                                @else
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="President"> President</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="Vice President"> Vice President</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="Vice President Internal"> Vice President Internal</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="Vice President External"> Vice President External</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="Secretary"> Secretary</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="Treasurer"> Treasurer</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="Auditor"> Auditor</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="PIO"> PIO</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="PIO Internal"> PIO Internal</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="PIO External"> PIO External</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="1st Year Representative"> 1st Year Representative</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="2nd Year Representative"> 2nd Year Representative</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="3rd Year Representative"> 3rd Year Representative</label>
                                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="positionChoices[]" value="4th Year Representative"> 4th Year Representative</label>
                                @endif
                            </div>
                            <div style="display:flex; justify-content:flex-end;">
                                <button id="addPositionBtn" type="button" onclick="addPositionChip()" style="padding: 10px 20px; background: linear-gradient(135deg, #800020 0%, #A0153E 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                    <i class="fa-solid fa-plus"></i> Add Position
                                </button>
                            </div>
                        </div>
                        <div style="margin-bottom: 0;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Partylist Teams <span style="color:#888; font-weight:normal;">(Optional)</span></label>
                            <div id="partylistInputs" style="display:grid; gap:0.75rem; padding:0.75rem; border:1px solid var(--border); border-radius:8px; background:#fff; margin-bottom:0.5rem;">
                                <div style="display:grid; grid-template-columns: 1fr 1fr auto; gap:0.6rem; align-items:end;">
                                    <div>
                                        <label style="display:block; margin-bottom:0.35rem; font-size:0.82rem; font-weight:500; color:#475569;">Team Name</label>
                                        <input type="text" id="partylistName" placeholder="e.g. Unity Bloc" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
                                    </div>
                                    <div>
                                        <label style="display:block; margin-bottom:0.35rem; font-size:0.82rem; font-weight:500; color:#475569;">Tagline</label>
                                        <input type="text" id="partylistTagline" placeholder="e.g. One goal, one vote" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
                                    </div>
                                    <button type="button" onclick="addPartylistTeam()" style="padding: 10px 16px; background: linear-gradient(135deg, #800020 0%, #A0153E 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; height:42px;">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                                <div id="partylistChipList" style="display:flex; flex-wrap:wrap; gap:0.5rem;"></div>
                                <p style="margin:0; font-size:0.8rem; color:#64748b;">These partylist teams will be available later when assigning candidates.</p>
                            </div>
                        </div>
                        <div style="margin-bottom: 0;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Upload Banner Image <span style="color: #888; font-weight: normal;">(Optional)</span></label>
                            <input type="file" id="electionPhoto" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                            <small style="color: #888; font-size: 0.8rem;">Recommended size: 1200x400px for election banner</small>
                        </div>
                        <div style="margin-bottom: 0;">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" id="electionActive" checked style="margin-right: 0.5rem; width: 18px; height: 18px;">
                                <span style="font-size: 0.9rem; font-weight: 500;">Active Election</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div style="position:sticky; bottom:0; left:0; width:100%; display:flex; justify-content:flex-end; gap:0.75rem; padding:1rem 0 0.75rem; border-top:1px solid #e5e7eb; background:white; flex-shrink:0; z-index:1;">
                <button type="button" onclick="closeModal()" style="width:160px; padding:0.70rem 1rem; border:2px solid #800020; border-radius:10px; background:#fff; color:#800020; font-weight:700; cursor:pointer; transition:all 0.2s;">
                    Cancel
                </button>
                <button type="submit" class="btn-primary" style="width:160px; justify-content:center; padding:0.75rem 1rem; border-radius:10px;">
                    Add Election
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editElectionModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; overflow-y: auto; padding: 2rem 0;">
    <div class="modal" style="background: white; padding: 2rem; border-radius: 12px; width: min(96vw, 1320px); margin: auto; max-height: 92vh; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 24px 70px rgba(15, 23, 42, 0.22);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-shrink: 0;">
            <h2>Update Election</h2>
            <button onclick="closeEditModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <form id="editElectionForm" onsubmit="handleUpdateElection(event)" enctype="multipart/form-data" style="display:flex; flex-direction:column; height:100%; overflow:hidden;">
            <div style="overflow:auto; flex:1 1 auto; padding-right:0.5rem; padding-bottom:1.5rem;">
                <input type="hidden" id="editElectionId">
                <div style="display:grid; grid-template-columns:minmax(0,1.1fr) minmax(360px,0.9fr); gap:1.5rem; align-items:start;">
                <div style="display:grid; gap:1rem;">
                    <div style="margin-bottom: 0;">
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Department</label>
                        <input type="text" id="editElectionDept" value="{{ auth()->user()->department }}" readonly style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: #f8fafc;">
                    </div>
                    <div style="margin-bottom: 0;">
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Election Name</label>
                        <input type="text" id="editElectionName" required placeholder="e.g. IT Department Student Council Election 2024" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div style="margin-bottom: 0;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Registration Start Date</label>
                            <input type="date" id="editElectionRegStartDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                        </div>
                        <div style="margin-bottom: 0;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Registration End Date</label>
                            <input type="date" id="editElectionRegEndDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                        </div>
                        <div style="margin-bottom: 0;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Start Date</label>
                            <input type="date" id="editElectionStartDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                        </div>
                        <div style="margin-bottom: 0;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">End Date</label>
                            <input type="date" id="editElectionEndDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                        </div>
                        <div style="margin-bottom: 0;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Voting Start Time</label>
                            <input type="time" id="editElectionVotingStartTime" value="08:00" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                        </div>
                        <div style="margin-bottom: 0;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Voting End Time</label>
                            <input type="time" id="editElectionVotingEndTime" value="17:00" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                        </div>
                    </div>
                    <div style="margin-bottom: 0;">
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Description <span style="color:#888; font-weight:normal;">(Optional)</span></label>
                        <textarea id="editElectionDesc" rows="5" placeholder="Brief description of the election..." style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; resize: vertical;"></textarea>
                    </div>
                </div>
                <div style="display:grid; gap:1rem;">
                    <div style="margin-bottom: 0;">
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Candidate Positions</label>
                        <div id="editPositionInput" style="display:grid; grid-template-columns:repeat(2,minmax(160px,1fr)); gap:0.55rem; padding:0.75rem; border:1px solid var(--border); border-radius:8px; background:#fff; margin-bottom:0.5rem; max-height:250px; overflow:auto;">
                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="editPositionChoices[]" value="President"> President</label>
                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="editPositionChoices[]" value="Vice President"> Vice President</label>
                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="editPositionChoices[]" value="Vice President Internal"> Vice President Internal</label>
                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="editPositionChoices[]" value="Vice President External"> Vice President External</label>
                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="editPositionChoices[]" value="Secretary"> Secretary</label>
                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="editPositionChoices[]" value="Treasurer"> Treasurer</label>
                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="editPositionChoices[]" value="Auditor"> Auditor</label>
                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="editPositionChoices[]" value="PIO"> PIO</label>
                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="editPositionChoices[]" value="PIO Internal"> PIO Internal</label>
                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="editPositionChoices[]" value="PIO External"> PIO External</label>
                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="editPositionChoices[]" value="1st Year Representative"> 1st Year Representative</label>
                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="editPositionChoices[]" value="2nd Year Representative"> 2nd Year Representative</label>
                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="editPositionChoices[]" value="3rd Year Representative"> 3rd Year Representative</label>
                    <label style="display:flex; align-items:center; gap:0.45rem; font-size:0.88rem;"><input type="checkbox" name="editPositionChoices[]" value="4th Year Representative"> 4th Year Representative</label>
                        </div>
                        <div style="display:flex; justify-content:flex-end;">
                            <button type="button" onclick="addEditPositionChip()" style="padding: 10px 20px; background: linear-gradient(135deg, #800020 0%, #A0153E 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                <i class="fa-solid fa-plus"></i> Add Position
                            </button>
                        </div>
                    </div>
                    <div style="margin-bottom: 0;">
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Partylist Teams <span style="color:#888; font-weight:normal;">(Optional)</span></label>
                        <div id="editPartylistInputs" style="display:grid; gap:0.75rem; padding:0.75rem; border:1px solid var(--border); border-radius:8px; background:#fff; margin-bottom:0.5rem;">
                            <div style="display:grid; grid-template-columns: 1fr 1fr auto; gap:0.6rem; align-items:end;">
                                <div>
                                    <label style="display:block; margin-bottom:0.35rem; font-size:0.82rem; font-weight:500; color:#475569;">Team Name</label>
                                    <input type="text" id="editPartylistName" placeholder="e.g. Unity Bloc" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
                                </div>
                                <div>
                                    <label style="display:block; margin-bottom:0.35rem; font-size:0.82rem; font-weight:500; color:#475569;">Tagline</label>
                                    <input type="text" id="editPartylistTagline" placeholder="e.g. One goal, one vote" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
                                </div>
                                <button type="button" onclick="addEditPartylistTeam()" style="padding: 10px 16px; background: linear-gradient(135deg, #800020 0%, #A0153E 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; height:42px;">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                            <div id="editPartylistChipList" style="display:flex; flex-wrap:wrap; gap:0.5rem;"></div>
                            <p style="margin:0; font-size:0.8rem; color:#64748b;">These partylist teams will be available later when assigning candidates.</p>
                        </div>
                    </div>
                    <div style="margin-bottom: 0;">
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Upload Banner Image <span style="color: #888; font-weight: normal;">(Optional)</span></label>
                        <input type="file" id="editElectionPhoto" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                        <small style="color: #888; font-size: 0.8rem;">Recommended size: 1200x400px for election banner</small>
                        <div id="currentBanner" style="margin-top: 0.5rem;"></div>
                    </div>
                    <div style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" id="editElectionActive" style="margin-right: 0.5rem; width: 18px; height: 18px;">
                            <span style="font-size: 0.9rem; font-weight: 500;">Active Election</span>
                        </label>
                    </div>
                </div>
            </div>
            <div style="position:sticky; bottom:0; left:0; width:100%; display:flex; justify-content:flex-end; gap:0.75rem; padding:1rem 0 0.75rem; border-top:1px solid #e5e7eb; background:white; flex-shrink:0; z-index:1;">
                <button type="button" onclick="closeEditModal()" style="width:160px; padding:0.70rem 1rem; border:2px solid #800020; border-radius:10px; background:#fff; color:#800020; font-weight:700; cursor:pointer; transition:all 0.2s;">
                    Cancel
                </button>
                <button type="submit" class="btn-primary" style="width:160px; justify-content:center; padding:0.80rem 1rem; border-radius:10px;">
                    Update Election
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const electionsData = @json($elections);
</script>
<script src="{{ asset('assets/dept-head/js/campus-elections.js') }}"></script>
@endpush
@endsection
