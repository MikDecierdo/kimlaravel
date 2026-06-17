@extends('layouts.admin')

@section('admin-content')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.btn-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    opacity: 0.9;
}
.election-card {
    position: relative;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
    border: 1px solid #f1f5f9;
}
.election-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(128, 0, 32, 0.18);
}
.election-card.election-card-dimmed::after {
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
/* Description overlay — slides in from the right when .overlay-open is added */
.election-card .desc-overlay {
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
    transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 10;
}
.election-card.overlay-open .desc-overlay {
    right: 0;
}
/* Arrow trigger button */
.election-card .overlay-trigger {
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
    box-shadow: 0 3px 10px rgba(128, 0, 32, 0.4);
    transition: background 0.2s, transform 0.2s;
}
.election-card .overlay-trigger:hover {
    background: #A0153E;
    transform: translateY(-50%) scale(1.1);
}
.election-card .overlay-trigger:disabled,
.election-card .card-action-btn:disabled {
    cursor: not-allowed !important;
    opacity: 0.45 !important;
    filter: grayscale(0.35);
    transform: none !important;
    pointer-events: none;
}
/* ── Filter Bar ─────────────────────────────── */
.elec-filter-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    padding: 1.2rem 1.4rem;
    margin-bottom: 1.5rem;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 0.9rem;
}
.elec-filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    min-width: 0;
}
.elec-filter-group label {
    font-size: 0.76rem;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.elec-filter-input,
.elec-filter-select {
    padding: 0.52rem 0.85rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #1f2937;
    background: #f9fafb;
    transition: border-color 0.2s;
    outline: none;
}
.elec-filter-input:focus,
.elec-filter-select:focus {
    border-color: #800020;
    background: #fff;
}
.elec-search-group {
    flex: 1;
    min-width: 180px;
}
.elec-search-group input {
    width: 100%;
    padding: 0.52rem 0.85rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    background: #f9fafb;
    outline: none;
    transition: border-color 0.2s;
}
.elec-search-group input:focus {
    border-color: #800020;
    background: #fff;
}
.elec-filter-actions {
    display: flex;
    gap: 0.5rem;
    align-items: flex-end;
}
.elec-btn-apply {
    padding: 0.52rem 1.1rem;
    background: linear-gradient(135deg, #800020 0%, #A0153E 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.15s;
    white-space: nowrap;
}
.elec-btn-apply:hover { opacity: 0.88; transform: translateY(-1px); }
.elec-btn-reset {
    padding: 0.52rem 1rem;
    background: #f1f5f9;
    color: #475569;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    white-space: nowrap;
}
.elec-btn-reset:hover { background: #e2e8f0; }
.elec-results-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    font-size: 0.85rem;
    color: #64748b;
    font-weight: 500;
}
#elecNoResults {
    grid-column: 1/-1;
    text-align: center;
    padding: 4rem;
    color: #888;
    display: none;
}
</style>

<header>
    <div class="header-title">
        <h1>Campus Elections</h1>
        <p>Manage campus-wide elections by department</p>
    </div>
    <button class="btn-primary btn-hover" onclick="openModal()" style="background: linear-gradient(135deg, #800020 0%, #A0153E 100%); transition: all 0.3s;">
        <i class="fa-solid fa-plus"></i> Add Campus Election
    </button>
</header>

<!-- ── Filter Bar ── -->
<div class="elec-filter-card">
    <div class="elec-filter-group">
        <label>Department</label>
        <select id="elecFilterDept" class="elec-filter-select" onchange="elecApplyFilters()">
            <option value="">All Departments</option>
            <option value="BSIT">BSIT</option>
            <option value="CBAE">CBAE</option>
            <option value="CRIM">CRIM</option>
            <option value="CHTM">CHTM</option>
            <option value="CTE">CTE</option>
            <option value="SHS">SHS</option>
        </select>
    </div>
    <div class="elec-filter-group">
        <label>Status</label>
        <select id="elecFilterStatus" class="elec-filter-select" onchange="elecApplyFilters()">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="finished">Finished</option>
        </select>
    </div>
    <div class="elec-filter-group">
        <label>Start Date From</label>
        <input type="date" id="elecFilterFrom" class="elec-filter-input">
    </div>
    <div class="elec-filter-group">
        <label>Start Date To</label>
        <input type="date" id="elecFilterTo" class="elec-filter-input">
    </div>
    <div class="elec-search-group">
        <label style="font-size:0.76rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.04em;">Search</label>
        <input type="text" id="elecSearchInput" placeholder="Search election name..." oninput="elecApplyFilters()">
    </div>
    <div class="elec-filter-actions">
        <button class="elec-btn-apply" onclick="elecApplyFilters()"><i class="fa-solid fa-filter"></i> Apply</button>
        <button class="elec-btn-reset" onclick="elecResetFilters()"><i class="fa-solid fa-rotate-left"></i> Reset</button>
    </div>
</div>

<!-- Results count -->
<div class="elec-results-bar">
    <span id="elecResultsCount"></span>
</div>

<div id="electionsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
    @forelse($elections as $election)
        @php
            $isFinished = $election->end_date->isPast();
            $statusKey = $isFinished ? 'finished' : ($election->is_active ? 'active' : 'inactive');
        @endphp
        <div class="election-card {{ $isFinished ? 'election-card-dimmed' : '' }}" id="election-card-{{ $election->id }}"
             data-name="{{ strtolower($election->election_name) }}"
             data-dept="{{ $election->department }}"
             data-status="{{ $statusKey }}"
             data-start="{{ $election->start_date->format('Y-m-d') }}">

            @if($isFinished)
                <div class="election-state-label">
                    Finished
                </div>
            @endif

            <!-- Left-arrow trigger button -->
            <button class="overlay-trigger" @if(!$isFinished) onclick="openDescOverlay({{ $election->id }})" @endif title="View description" @if($isFinished) disabled aria-disabled="true" @endif>
                <i class="fa-solid fa-chevron-left" style="font-size: 0.85rem;"></i>
            </button>

            <!-- Description overlay (opens on arrow click) -->
            <div class="desc-overlay">
                <!-- Close button inside overlay -->
                <button onclick="closeDescOverlay({{ $election->id }})" style="position: absolute; top: 12px; right: 14px; background: rgba(255,255,255,0.2); border: none; color: white; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; transition: background 0.2s;" onmouseenter="this.style.background='rgba(255,255,255,0.35)'" onmouseleave="this.style.background='rgba(255,255,255,0.2)'">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <i class="fa-solid fa-vote-yea" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.8;"></i>
                <h4 style="font-size: 1rem; font-weight: 700; margin: 0 0 0.75rem; letter-spacing: 0.03em;">{{ $election->election_name }}</h4>
                <p style="font-size: 0.88rem; line-height: 1.6; opacity: 0.92; margin: 0;">
                    {{ $election->description && $election->description !== 'none' ? $election->description : 'No description provided.' }}
                </p>
            </div>

            <!-- Banner Image (top maroon section) -->
            <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #800020 0%, #A0153E 100%); position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                @if($election->banner_image)
                    <img src="{{ $election->banner_image }}" alt="{{ $election->election_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    <i class="fa-solid fa-vote-yea" style="font-size: 5rem; color: rgba(255,255,255,0.2);"></i>
                @endif

                <!-- Status Badge (top-right) -->
                <div style="position: absolute; top: 12px; right: 12px;">
                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px; border-radius: 20px; font-size: 0.82rem; font-weight: 600; background: white; color: #1e293b; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $isFinished ? '#6b7280' : ($election->is_active ? '#22c55e' : '#9ca3af') }}; display: inline-block;"></span>
                        {{ $isFinished ? 'Finished' : ($election->is_active ? 'Active' : 'Inactive') }}
                    </span>
                </div>
            </div>

            <!-- Card Content -->
            <div style="padding: 1.4rem 1.5rem 1.5rem;">

                <!-- Department label -->
                <p style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.08em; color: #1e293b; text-transform: uppercase; margin: 0 0 0.3rem;">{{ $election->department }} DEPARTMENT</p>

                <!-- Election Name -->
                <h3 style="font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0 0 1rem; text-transform: uppercase; letter-spacing: 0.02em;">{{ $election->election_name }}</h3>

                <!-- Dates -->
                <p style="font-size: 0.85rem; color: #475569; margin: 0 0 0.3rem;">
                    Registration Start: {{ optional($election->registration_start_date ?? $election->start_date)->format('M. d, Y') }}
                </p>
                <p style="font-size: 0.85rem; color: #475569; margin: 0 0 0.3rem;">
                    Registration End: {{ optional($election->registration_end_date ?? $election->end_date)->format('M, d, Y') }}
                </p>
                <p style="font-size: 0.85rem; color: #475569; margin: 0 0 0.3rem;">
                    Election Started : {{ $election->start_date->format('M. d, Y') }}
                </p>
                <p style="font-size: 0.85rem; color: #475569; margin: 0 0 1.25rem;">
                    Election Expired: {{ $election->end_date->format('M, d, Y') }}
                </p>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 0.75rem;">
                    <button class="btn-hover card-action-btn" @if(!$isFinished) onclick="openEditModal({{ $election->id }})" @endif @if($isFinished) disabled aria-disabled="true" @endif
                            style="flex: 1; padding: 0.6rem; border: 2px solid #800020; border-radius: 25px; font-weight: 700; font-size: 0.85rem; letter-spacing: 0.05em; cursor: pointer; transition: all 0.3s; background: white; color: #800020; text-transform: uppercase;">
                        UPDATE
                    </button>
                    <button class="btn-hover card-action-btn" @if(!$isFinished) onclick="toggleStatus({{ $election->id }}, {{ $election->is_active ? 'false' : 'true' }})" @endif @if($isFinished) disabled aria-disabled="true" @endif
                            style="flex: 1; padding: 0.6rem; border: 2px solid #800020; border-radius: 25px; font-weight: 700; font-size: 0.85rem; letter-spacing: 0.05em; cursor: pointer; transition: all 0.3s; background: #800020; color: white; text-transform: uppercase;">
                        {{ $election->is_active ? 'DISABLE' : 'ENABLE' }}
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 4rem; color: #888;">
            <i class="fa-solid fa-vote-yea" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
            <p style="font-size: 1.1rem;">No campus elections yet.</p>
            <p style="font-size: 0.9rem;">Click "Add Campus Election" to create your first election.</p>
        </div>
    @endforelse
    <!-- JS no-results placeholder -->
    <div id="elecNoResults">
        <i class="fa-solid fa-magnifying-glass" style="font-size: 3rem; opacity: 0.25; margin-bottom: 1rem;"></i>
        <p style="font-size: 1rem;">No elections match your filters.</p>
    </div>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="electionModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; overflow-y: auto; padding: 2rem 0;">
    <div class="modal" style="background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 900px; margin: auto; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Add Campus Election</h2>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <form id="addElectionForm" onsubmit="handleAddElection(event)" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Department</label>
                    <select id="electionDept" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                        <option value="">Select Department</option>
                        <option value="BSIT">BSIT (Information Technology)</option>
                        <option value="CBAE">CBAE (Business &amp; Accountancy)</option>
                        <option value="CRIM">CRIM (Criminology)</option>
                        <option value="CHTM">CHTM (Hospitality &amp; Tourism)</option>
                        <option value="CTE">CTE (Teacher Education)</option>
                        <option value="SHS">SHS (Senior High School)</option>
                    </select>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Election Name</label>
                    <input type="text" id="electionName" required placeholder="e.g. IT Department Student Council Election 2024" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Registration Start Date</label>
                    <input type="date" id="electionRegStartDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Registration End Date</label>
                    <input type="date" id="electionRegEndDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Start Date</label>
                    <input type="date" id="electionStartDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">End Date</label>
                    <input type="date" id="electionEndDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Description (Optional)</label>
                <textarea id="electionDesc" rows="3" placeholder="Brief description of the election..." style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; resize: vertical;"></textarea>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Candidate Positions</label>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <select id="positionInput" style="flex: 1; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: white;">
                        <option value="">Choose a position</option>
                        <option value="President">President</option>
                        <option value="Vice President">Vice President</option>
                        <option value="Vice President Internal">Vice President Internal</option>
                        <option value="Vice President External">Vice President External</option>
                        <option value="Secretary">Secretary</option>
                        <option value="Treasurer">Treasurer</option>
                        <option value="Auditor">Auditor</option>
                        <option value="PIO">PIO</option>
                        <option value="PIO Internal">PIO Internal</option>
                        <option value="PIO External">PIO External</option>
                        <option value="1st Year Representative">1st Year Representative</option>
                        <option value="2nd Year Representative">2nd Year Representative</option>
                        <option value="3rd Year Representative">3rd Year Representative</option>
                        <option value="4th Year Representative">4th Year Representative</option>
                    </select>
                    <button type="button" onclick="addPositionChip()" style="padding: 10px 20px; background: linear-gradient(135deg, #800020 0%, #A0153E 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        <i class="fa-solid fa-plus"></i> Add Position
                    </button>
                </div>
                <div id="positionsChips" style="display: flex; flex-wrap: wrap; gap: 0.5rem; min-height: 40px; padding: 0.5rem; border: 1px solid var(--border); border-radius: 8px; background: #f8fafc;">
                    <span style="color: #888; font-size: 0.85rem;">No positions added yet</span>
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Upload Banner Image <span style="color: #888; font-weight: normal;">(Optional)</span></label>
                <input type="file" id="electionPhoto" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                <small style="color: #888; font-size: 0.8rem;">Recommended size: 1200x400px for election banner</small>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" id="electionActive" checked style="margin-right: 0.5rem; width: 18px; height: 18px;">
                    <span style="font-size: 0.9rem; font-weight: 500;">Active Election</span>
                </label>
            </div>
            <button type="submit" class="btn-primary" style="width:100%; justify-content: center;">Add Election</button>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editElectionModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; overflow-y: auto; padding: 2rem 0;">
    <div class="modal" style="background: white; padding: 2rem; border-radius: 12px; width: 90%; max-width: 900px; margin: auto; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Update Campus Election</h2>
            <button onclick="closeEditModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <form id="editElectionForm" onsubmit="handleUpdateElection(event)" enctype="multipart/form-data">
            <input type="hidden" id="editElectionId">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Department</label>
                    <select id="editElectionDept" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                        <option value="">Select Department</option>
                        <option value="BSIT">BSIT (Information Technology)</option>
                        <option value="CBAE">CBAE (Business &amp; Accountancy)</option>
                        <option value="CRIM">CRIM (Criminology)</option>
                        <option value="CHTM">CHTM (Hospitality &amp; Tourism)</option>
                        <option value="CTE">CTE (Teacher Education)</option>
                        <option value="SHS">SHS (Senior High School)</option>
                    </select>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Election Name</label>
                    <input type="text" id="editElectionName" required placeholder="e.g. IT Department Student Council Election 2024" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Registration Start Date</label>
                    <input type="date" id="editElectionRegStartDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Registration End Date</label>
                    <input type="date" id="editElectionRegEndDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Start Date</label>
                    <input type="date" id="editElectionStartDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">End Date</label>
                    <input type="date" id="editElectionEndDate" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Description (Optional)</label>
                <textarea id="editElectionDesc" rows="3" placeholder="Brief description of the election..." style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; resize: vertical;"></textarea>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Candidate Positions</label>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <select id="editPositionInput" style="flex: 1; padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: white;">
                        <option value="">Choose a position</option>
                        <option value="President">President</option>
                         <option value="Vice President">Vice President</option>
                        <option value="Vice President Internal">Vice President Internal</option>
                        <option value="Vice President External">Vice President External</option>
                        <option value="Secretary">Secretary</option>
                        <option value="Treasurer">Treasurer</option>
                        <option value="Auditor">Auditor</option>
                        <option value="PIO">PIO</option>
                        <option value="PIO Internal">PIO Internal</option>
                        <option value="PIO External">PIO External</option>
                        <option value="1st Year Representative">1st Year Representative</option>
                        <option value="2nd Year Representative">2nd Year Representative</option>
                        <option value="3rd Year Representative">3rd Year Representative</option>
                        <option value="4th Year Representative">4th Year Representative</option>
                    </select>
                    <button type="button" onclick="addEditPositionChip()" style="padding: 10px 20px; background: linear-gradient(135deg, #800020 0%, #A0153E 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        <i class="fa-solid fa-plus"></i> Add Position
                    </button>
                </div>
                <div id="editPositionsChips" style="display: flex; flex-wrap: wrap; gap: 0.5rem; min-height: 40px; padding: 0.5rem; border: 1px solid var(--border); border-radius: 8px; background: #f8fafc;">
                    <span style="color: #888; font-size: 0.85rem;">No positions added yet</span>
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 500;">Upload Banner Image <span style="color: #888; font-weight: normal;">(Optional)</span></label>
                <input type="file" id="editElectionPhoto" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                <small style="color: #888; font-size: 0.8rem;">Recommended size: 1200x400px for election banner</small>
                <div id="currentBanner" style="margin-top: 0.5rem;"></div>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" id="editElectionActive" style="margin-right: 0.5rem; width: 18px; height: 18px;">
                    <span style="font-size: 0.9rem; font-weight: 500;">Active Election</span>
                </label>
            </div>
            <button type="submit" class="btn-primary" style="width:100%; justify-content: center;">Update Election</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
const electionsData = @json($elections);

function openDescOverlay(id) {
    document.getElementById('election-card-' + id).classList.add('overlay-open');
}

function closeDescOverlay(id) {
    document.getElementById('election-card-' + id).classList.remove('overlay-open');
}

function addPositionChip() {
    const input = document.getElementById('positionInput');
    const position = input.value.trim();
    
    if (!position) {
        showToast('Please enter a position name', 'error');
        return;
    }
    
    const container = document.getElementById('positionsChips');
    
    // Remove placeholder text if exists
    if (container.querySelector('span:not(.position-chip)')) {
        container.innerHTML = '';
    }
    
    // Check for duplicates
    const existing = Array.from(container.querySelectorAll('.position-chip')).map(c => c.dataset.position);
    if (existing.includes(position)) {
        showToast('Position already added', 'error');
        return;
    }
    
    const chip = document.createElement('span');
    chip.className = 'position-chip';
    chip.dataset.position = position;
    chip.style.cssText = 'display: inline-flex; align-items: center; gap: 0.5rem; padding: 8px 12px; background: linear-gradient(135deg, #800020 0%, #A0153E 100%); color: white; border-radius: 20px; font-size: 0.85rem; font-weight: 600;';
    chip.innerHTML = `
        ${position}
        <button type="button" onclick="removePositionChip(this)" style="background: #FFC107; border: none; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #1e293b; font-size: 0.75rem; font-weight: bold;">
            ×
        </button>
    `;
    
    container.appendChild(chip);
    input.value = '';
    input.focus();
}

function addEditPositionChip() {
    const input = document.getElementById('editPositionInput');
    const position = input.value.trim();
    
    if (!position) {
        showToast('Please enter a position name', 'error');
        return;
    }
    
    const container = document.getElementById('editPositionsChips');
    
    // Remove placeholder text if exists
    if (container.querySelector('span:not(.position-chip)')) {
        container.innerHTML = '';
    }
    
    // Check for duplicates
    const existing = Array.from(container.querySelectorAll('.position-chip')).map(c => c.dataset.position);
    if (existing.includes(position)) {
        showToast('Position already added', 'error');
        return;
    }
    
    const chip = document.createElement('span');
    chip.className = 'position-chip';
    chip.dataset.position = position;
    chip.style.cssText = 'display: inline-flex; align-items: center; gap: 0.5rem; padding: 8px 12px; background: linear-gradient(135deg, #800020 0%, #A0153E 100%); color: white; border-radius: 20px; font-size: 0.85rem; font-weight: 600;';
    chip.innerHTML = `
        ${position}
        <button type="button" onclick="removePositionChip(this)" style="background: #FFC107; border: none; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #1e293b; font-size: 0.75rem; font-weight: bold;">
            ×
        </button>
    `;
    
    container.appendChild(chip);
    input.value = '';
    input.focus();
}

function removePositionChip(button) {
    const chip = button.parentElement;
    const container = chip.parentElement;
    chip.remove();
    
    // Add placeholder if no chips left
    if (container.querySelectorAll('.position-chip').length === 0) {
        container.innerHTML = '<span style="color: #888; font-size: 0.85rem;">No positions added yet</span>';
    }
}

function openModal() {
    document.getElementById('electionModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('electionModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function handleAddElection(e) {
    e.preventDefault();
    
    const positionChips = document.querySelectorAll('#positionsChips .position-chip');
    const positions = Array.from(positionChips).map(chip => chip.dataset.position);
    
    if (positions.length === 0) {
        showToast('Please add at least one position', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('department', document.getElementById('electionDept').value);
    formData.append('election_name', document.getElementById('electionName').value);
    formData.append('description', document.getElementById('electionDesc').value || '');
    formData.append('start_date', document.getElementById('electionStartDate').value);
    formData.append('end_date', document.getElementById('electionEndDate').value);
    formData.append('registration_start_date', document.getElementById('electionRegStartDate').value);
    formData.append('registration_end_date', document.getElementById('electionRegEndDate').value);
    formData.append('is_active', document.getElementById('electionActive').checked);
    
    positions.forEach((position, index) => {
        formData.append(`positions[${index}]`, position);
    });
    
    const photoFile = document.getElementById('electionPhoto').files[0];
    if (photoFile) {
        formData.append('banner_image', photoFile);
    }

    fetch('/admin/campus-elections', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw err;
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message);
            closeModal();
            document.getElementById('addElectionForm').reset();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (error.errors) {
            const firstError = Object.values(error.errors)[0][0];
            showToast(firstError, 'error');
        } else if (error.message) {
            showToast(error.message, 'error');
        } else {
            showToast('An error occurred. Please try again.', 'error');
        }
    });
}

function openEditModal(id) {
    const election = electionsData.find(e => e.id === id);
    if (!election) return;

    document.getElementById('editElectionId').value = election.id;
    document.getElementById('editElectionDept').value = election.department;
    document.getElementById('editElectionName').value = election.election_name;
    document.getElementById('editElectionDesc').value = election.description || '';
    document.getElementById('editElectionStartDate').value = election.start_date.split('T')[0];
    document.getElementById('editElectionEndDate').value = election.end_date.split('T')[0];
    document.getElementById('editElectionRegStartDate').value = (election.registration_start_date || election.start_date).split('T')[0];
    document.getElementById('editElectionRegEndDate').value = (election.registration_end_date || election.end_date).split('T')[0];
    document.getElementById('editElectionActive').checked = election.is_active;

    // Load positions as chips
    const chipsContainer = document.getElementById('editPositionsChips');
    chipsContainer.innerHTML = '';
    if (election.positions && election.positions.length > 0) {
        election.positions.forEach(position => {
            const chip = document.createElement('span');
            chip.className = 'position-chip';
            chip.dataset.position = position;
            chip.style.cssText = 'display: inline-flex; align-items: center; gap: 0.5rem; padding: 8px 12px; background: linear-gradient(135deg, #800020 0%, #A0153E 100%); color: white; border-radius: 20px; font-size: 0.85rem; font-weight: 600;';
            chip.innerHTML = `
                ${position}
                <button type="button" onclick="removePositionChip(this)" style="background: #FFC107; border: none; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #1e293b; font-size: 0.75rem; font-weight: bold;">
                    ×
                </button>
            `;
            chipsContainer.appendChild(chip);
        });
    } else {
        chipsContainer.innerHTML = '<span style="color: #888; font-size: 0.85rem;">No positions added yet</span>';
    }

    // Show current banner
    const bannerDiv = document.getElementById('currentBanner');
    if (election.banner_image) {
        bannerDiv.innerHTML = `<small style="color: #888;">Current banner: <img src="${election.banner_image}" style="max-width: 100px; border-radius: 4px; vertical-align: middle;"></small>`;
    } else {
        bannerDiv.innerHTML = '';
    }

    document.getElementById('editElectionModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editElectionModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function handleUpdateElection(e) {
    e.preventDefault();
    
    const id = document.getElementById('editElectionId').value;
    const positionChips = document.querySelectorAll('#editPositionsChips .position-chip');
    const positions = Array.from(positionChips).map(chip => chip.dataset.position);
    
    if (positions.length === 0) {
        _swalErr('Please add at least one position.');
        return;
    }

    _swalConfirm('Update Election?', 'Are you sure you want to update this campus election?', 'Yes, Update', function() {
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('department', document.getElementById('editElectionDept').value);
            formData.append('election_name', document.getElementById('editElectionName').value);
            formData.append('description', document.getElementById('editElectionDesc').value || '');
            formData.append('start_date', document.getElementById('editElectionStartDate').value);
            formData.append('end_date', document.getElementById('editElectionEndDate').value);
            formData.append('registration_start_date', document.getElementById('editElectionRegStartDate').value);
            formData.append('registration_end_date', document.getElementById('editElectionRegEndDate').value);
            formData.append('is_active', document.getElementById('editElectionActive').checked);
            
            positions.forEach((position, index) => {
                formData.append(`positions[${index}]`, position);
            });
            
            const photoFile = document.getElementById('editElectionPhoto').files[0];
            if (photoFile) {
                formData.append('banner_image', photoFile);
            }

            fetch(`/admin/campus-elections/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw err;
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            _swalOK('Updated!', data.message, function() { closeEditModal(); location.reload(); });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        let errorMessage = 'An error occurred. Please try again.';
        if (error.errors) { errorMessage = Object.values(error.errors)[0][0]; }
        else if (error.message) { errorMessage = error.message; }
        _swalErr(errorMessage);
    });
    });
}

function toggleStatus(id, newStatus) {
    const action = newStatus ? 'enable' : 'disable';
    const actionLabel = action.charAt(0).toUpperCase() + action.slice(1);

    _swalConfirm(actionLabel + ' Election?', 'Are you sure you want to ' + action + ' this campus election?', 'Yes, ' + actionLabel, function() {
        fetch(`/admin/campus-elections/${id}/toggle`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ is_active: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                _swalOK('Done!', data.message, function() { location.reload(); });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            _swalErr('An error occurred. Please try again.');
        });
    });
}

function deleteElection(id) {
    if (!confirm('Are you sure you want to delete this campus election?')) return;

    fetch(`/admin/campus-elections/${id}`, {
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

window.onclick = function(event) {
    if (event.target.id === 'electionModal') {
        closeModal();
    }
    if (event.target.id === 'editElectionModal') {
        closeEditModal();
    }
}

// ── Election Filter ──────────────────────────────────────
const elecAllCards = () => document.querySelectorAll('#electionsGrid .election-card');

function elecApplyFilters() {
    const dept   = document.getElementById('elecFilterDept').value.toLowerCase();
    const status = document.getElementById('elecFilterStatus').value.toLowerCase();
    const from   = document.getElementById('elecFilterFrom').value;
    const to     = document.getElementById('elecFilterTo').value;
    const search = document.getElementById('elecSearchInput').value.toLowerCase().trim();

    let shown = 0;
    elecAllCards().forEach(card => {
        const cDept   = (card.dataset.dept   || '').toLowerCase();
        const cStatus = (card.dataset.status || '').toLowerCase();
        const cStart  = card.dataset.start  || '';
        const cName   = card.dataset.name   || '';

        const matchDept   = !dept   || cDept === dept;
        const matchStatus = !status || cStatus === status;
        const matchFrom   = !from   || cStart >= from;
        const matchTo     = !to     || cStart <= to;
        const matchSearch = !search || cName.includes(search) || cDept.includes(search);

        const visible = matchDept && matchStatus && matchFrom && matchTo && matchSearch;
        card.style.display = visible ? '' : 'none';
        if (visible) shown++;
    });

    const total = elecAllCards().length;
    const countEl = document.getElementById('elecResultsCount');
    if (countEl) countEl.textContent = `Showing ${shown} of ${total} election${total !== 1 ? 's' : ''}`;

    const noRes = document.getElementById('elecNoResults');
    if (noRes) noRes.style.display = shown === 0 && total > 0 ? 'block' : 'none';
}

function elecResetFilters() {
    document.getElementById('elecFilterDept').value    = '';
    document.getElementById('elecFilterStatus').value  = '';
    document.getElementById('elecFilterFrom').value    = '';
    document.getElementById('elecFilterTo').value      = '';
    document.getElementById('elecSearchInput').value   = '';
    elecApplyFilters();
}

// Init count on page load
document.addEventListener('DOMContentLoaded', function() {
    const total = elecAllCards().length;
    const countEl = document.getElementById('elecResultsCount');
    if (countEl && total > 0) countEl.textContent = `Showing ${total} of ${total} election${total !== 1 ? 's' : ''}`;
});
</script>
@endpush
@endsection
