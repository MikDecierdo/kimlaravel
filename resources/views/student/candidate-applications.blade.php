@extends('layouts.student')

@section('student-content')
@php
    $openCount = $elections->where('registration_status', 'OPEN')->count();
@endphp

<header>
    <div class="header-title">
        <h1>Candidate Applications</h1>
        <p>Apply as a candidate for open elections and track your application status.</p>
    </div>
</header>

<div style="display:grid; gap:1rem; margin-bottom:1.2rem;">
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:0.9rem 1rem; display:flex; justify-content:space-between; align-items:center; gap:0.8rem; flex-wrap:wrap;">
        <div style="font-size:0.9rem; color:#334155;">
            Open registrations: <strong style="color:#800020;">{{ $openCount }}</strong>
        </div>
        <div style="font-size:0.82rem; color:#64748b;">
            Status legend: <span style="color:#166534; font-weight:700;">Open</span> / <span style="color:#92400e; font-weight:700;">Upcoming</span> / <span style="color:#991b1b; font-weight:700;">Closed</span>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:1rem; margin-bottom:1.5rem;">
    @forelse($elections as $election)
        @php
            $status = strtoupper((string) ($election->registration_status ?? 'CLOSED'));
            $isOpen = $status === 'OPEN';
            $badgeBg = $isOpen ? '#dcfce7' : ($status === 'UPCOMING' ? '#ffedd5' : '#fee2e2');
            $badgeColor = $isOpen ? '#166534' : ($status === 'UPCOMING' ? '#92400e' : '#991b1b');
        @endphp
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,0.05);">
            <div style="padding:1rem; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:flex-start; gap:0.8rem;">
                <div>
                    <p style="margin:0; font-size:0.72rem; font-weight:700; letter-spacing:0.05em; color:#64748b; text-transform:uppercase;">{{ $election->department }}</p>
                    <h3 style="margin:0.15rem 0 0; font-size:1rem; color:#0f172a;">{{ $election->election_name }}</h3>
                </div>
                <span style="padding:0.3rem 0.6rem; border-radius:999px; font-size:0.75rem; font-weight:700; background:{{ $badgeBg }}; color:{{ $badgeColor }};">{{ $status }}</span>
            </div>
            <div style="padding:0.9rem 1rem;">
                <p style="margin:0 0 0.2rem; font-size:0.82rem; color:#475569;">Registration start: <strong>{{ optional($election->registration_start_date ?? $election->start_date)->format('M d, Y') }}</strong></p>
                <p style="margin:0 0 0.7rem; font-size:0.82rem; color:#475569;">Registration end: <strong>{{ optional($election->registration_end_date ?? $election->end_date)->format('M d, Y') }}</strong></p>
                <p style="margin:0 0 0.9rem; font-size:0.82rem; color:#64748b; min-height:2.4em;">{{ $election->description ?: 'No election description provided.' }}</p>

                <button
                    type="button"
                    onclick="openApplicationModal({{ $election->id }})"
                    style="width:100%; padding:0.58rem 0.8rem; border-radius:10px; border:2px solid #800020; background:{{ $isOpen ? '#800020' : '#f8fafc' }}; color:{{ $isOpen ? '#fff' : '#94a3b8' }}; font-weight:700; cursor:{{ $isOpen ? 'pointer' : 'not-allowed' }};"
                    {{ $isOpen ? '' : 'disabled' }}
                >
                    {{ $isOpen ? 'Apply as Candidate' : 'Registration ' . ucfirst(strtolower($status)) }}
                </button>
            </div>
        </div>
    @empty
        <div style="grid-column:1/-1; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:2rem; text-align:center; color:#64748b;">
            No elections available for candidate application.
        </div>
    @endforelse
</div>

<div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,0.04);">
    <div style="padding:0.9rem 1rem; border-bottom:1px solid #f1f5f9;">
        <h2 style="margin:0; font-size:1rem; color:#0f172a;">My Application Status</h2>
        <div class="application-status-tabs">
            <button type="button" class="application-status-tab is-active" data-status="ALL">All</button>
            <button type="button" class="application-status-tab" data-status="PENDING">Pending</button>
            <button type="button" class="application-status-tab" data-status="APPROVED">Approved</button>
            <button type="button" class="application-status-tab" data-status="REJECTED">Rejected</button>
        </div>
    </div>
    <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse; min-width:780px;">
            <thead>
                <tr style="background:#f8fafc; color:#475569; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.04em;">
                    <th style="text-align:left; padding:0.75rem 1rem;">Election</th>
                    <th style="text-align:left; padding:0.75rem 1rem;">Department</th>
                    <th style="text-align:left; padding:0.75rem 1rem;">Submitted At</th>
                    <th style="text-align:left; padding:0.75rem 1rem;">Status</th>
                    <th style="text-align:left; padding:0.75rem 1rem;">Applied Position</th>
                    <th style="text-align:left; padding:0.75rem 1rem;">Description</th>
                </tr>
            </thead>
            <tbody id="applicationStatusTableBody">
                @forelse($applications as $application)
                    @php
                        $status = strtoupper((string) $application->status);
                        $statusBg = $status === 'APPROVED' ? '#dcfce7' : ($status === 'REJECTED' ? '#fee2e2' : '#ffedd5');
                        $statusColor = $status === 'APPROVED' ? '#166534' : ($status === 'REJECTED' ? '#991b1b' : '#92400e');
                        $responses = (array) ($application->form_responses ?? []);
                        $description = trim((string) ($application->decision_description ?? ''));
                        if ($description === '') {
                            $description = trim((string) ($responses['platform_statement'] ?? $responses['description'] ?? ''));
                        }
                    @endphp
                    <tr class="application-status-row" data-status="{{ $status }}" style="border-top:1px solid #f1f5f9;">
                        <td style="padding:0.75rem 1rem; color:#0f172a; font-weight:600;">{{ optional($application->election)->election_name ?? 'Election removed' }}</td>
                        <td style="padding:0.75rem 1rem; color:#475569;">{{ optional($application->election)->department ?? 'N/A' }}</td>
                        <td style="padding:0.75rem 1rem; color:#475569;">{{ optional($application->submitted_at)->format('M d, Y h:i A') ?? optional($application->created_at)->format('M d, Y h:i A') }}</td>
                        <td style="padding:0.75rem 1rem;"><span style="padding:0.25rem 0.55rem; border-radius:999px; background:{{ $statusBg }}; color:{{ $statusColor }}; font-size:0.75rem; font-weight:700;">{{ $status }}</span></td>
                        <td style="padding:0.75rem 1rem; color:#475569;">{{ $responses['position'] ?? 'N/A' }}</td>
                        <td style="padding:0.75rem 1rem; color:#475569; max-width:280px; white-space:normal; word-break:break-word;">{{ $description !== '' ? $description : 'N/A' }}</td>
                    </tr>
                @empty
                    <tr id="applicationStatusEmptyRow">
                        <td colspan="6" style="padding:1rem; text-align:center; color:#64748b;">You have not submitted any candidate applications yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="application-status-pagination">
        <button type="button" id="applicationStatusPrevBtn" class="application-status-page-btn">Previous</button>
        <span id="applicationStatusPageInfo" class="application-status-page-info">Page 1 of 1</span>
        <button type="button" id="applicationStatusNextBtn" class="application-status-page-btn">Next</button>
    </div>
</div>

<style>
    .application-status-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        margin-top: 0.85rem;
    }

    .application-status-tab {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #475569;
        border-radius: 999px;
        padding: 0.35rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .application-status-tab:hover {
        border-color: #800020;
        color: #800020;
    }

    .application-status-tab.is-active {
        background: #800020;
        border-color: #800020;
        color: #fff;
    }

    .application-status-pagination {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.65rem;
        border-top: 1px solid #f1f5f9;
        padding: 0.75rem 1rem;
        flex-wrap: wrap;
    }

    .application-status-page-btn {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        border-radius: 8px;
        padding: 0.35rem 0.7rem;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
    }

    .application-status-page-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .application-status-page-info {
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .candidate-app-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(5px);
        z-index: 1000;
        display: none;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        padding: 1rem;
    }

    .candidate-app-modal-overlay.active {
        display: flex;
        opacity: 1;
    }

    .candidate-app-modal {
        background: #fff;
        width: 95vw;
        max-width: 1200px;
        height: 90vh;
        border-radius: 12px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(128, 0, 0, 0.1);
        animation: candidateSlideUp 0.4s ease-out;
    }

    @keyframes candidateSlideUp {
        from { transform: translateY(50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .candidate-app-close {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #f1f1f1;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.1rem;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
        z-index: 10;
    }

    .candidate-app-close:hover {
        background: #800020;
        color: white;
    }

    .candidate-app-header {
        text-align: left;
        border-bottom: 2px solid #e0e0e0;
        padding: 10px 30px;
        background: #fafafa;
        flex-shrink: 0;
    }

    .candidate-app-header-flex {
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        gap: 15px;
    }

    .candidate-app-header-brand {
        width: 56px;
        height: 56px;
        flex-shrink: 0;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        margin-top: 2px;
    }

    .candidate-app-header-brand img {
        height: 50px;
        width: auto;
    }

    .candidate-app-csg-mark {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        border: 3px solid #800020;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        color: #800020;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.92rem;
        letter-spacing: 0.04em;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-top: 2px;
    }

    .candidate-app-header-text h1 {
        font-size: 1.2rem;
        color: #800020;
        font-weight: 700;
        text-transform: uppercase;
        margin: 0;
        text-align: left;
    }

    .candidate-app-header-text h2 {
        font-size: 0.75rem;
        color: #666;
        text-transform: uppercase;
        font-weight: 500;
        margin: 0.15rem 0 0;
        text-align: left;
    }

    .candidate-app-body {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        padding: 5px 20px 20px;
        overflow: hidden;
        gap: 18px;
    }

    .candidate-app-section-title {
        font-size: 0.9rem;
        color: #800020;
        font-weight: 600;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 5px;
        flex-shrink: 0;
    }

    .candidate-app-form label {
        display: block;
        font-size: 0.75rem;
        font-weight: 500;
        color: #2d3436;
        margin-bottom: 4px;
    }

    .candidate-app-form input,
    .candidate-app-form select,
    .candidate-app-form textarea {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        font-family: 'Poppins', sans-serif;
        font-size: 0.85rem;
        transition: 0.2s;
        background: #fafafa;
    }

    .candidate-app-form input:focus,
    .candidate-app-form select:focus,
    .candidate-app-form textarea:focus {
        border-color: #800020;
        background: #fff;
        outline: none;
    }

    .candidate-top-section {
        flex-shrink: 0;
        margin-top: -2px;
    }

    .candidate-purpose-area {
        margin-bottom: 15px;
    }

    .candidate-name-grid {
        display: grid;
        grid-template-columns: 1.1fr 1fr 1.2fr 1fr;
        gap: 18px;
        margin-bottom: 15px;
    }

    .candidate-purpose-area textarea {
        resize: vertical;
        min-height: 60px;
    }

    .candidate-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .candidate-info-row-3 {
        grid-column: span 2;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 18px;
    }

    .candidate-split-panels-row {
        display: flex;
        gap: 24px;
        flex-grow: 1;
        min-height: 0;
    }

    .candidate-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #fff;
    }

    .candidate-panel-scroll {
        flex-grow: 1;
        overflow-y: auto;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 12px;
        background: #fdfdfd;
        min-height: 0;
    }

    .candidate-panel-scroll::-webkit-scrollbar { width: 6px; }
    .candidate-panel-scroll::-webkit-scrollbar-thumb { background-color: #ccc; border-radius: 4px; }

    .candidate-leadership-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8rem;
    }

    .candidate-leadership-table th {
        background: #800020;
        color: #fff;
        padding: 8px;
        text-align: left;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .candidate-leadership-table td {
        padding: 8px 6px;
        border-bottom: 1px solid #eee;
    }

    .candidate-leadership-table td input {
        border: none;
        background: transparent;
        padding: 4px;
        font-size: 0.8rem;
    }

    .candidate-platform-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .candidate-platform-row {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff;
        border: 1px solid #eee;
        padding: 7px 10px;
        border-radius: 4px;
        transition: 0.2s;
    }

    .candidate-platform-row:hover { border-color: #ddd; }

    .candidate-platform-number {
        background: #800020;
        color: white;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: bold;
        flex-shrink: 0;
    }

    .candidate-platform-input { flex-grow: 1; }

    .candidate-remove-platform {
        color: #ff4d4d;
        cursor: pointer;
        font-size: 1.2rem;
        line-height: 1;
        padding: 0 5px;
        opacity: 0.6;
    }

    .candidate-remove-platform:hover { opacity: 1; transform: scale(1.1); }

    .candidate-app-footer {
        padding: 12px 30px;
        border-top: 1px solid #e0e0e0;
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        background: #fafafa;
        flex-shrink: 0;
    }

    .candidate-app-btn {
        padding: 0.58rem 0.8rem;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        border: 2px solid transparent;
        font-family: 'Poppins', sans-serif;
        transition: 0.2s;
        min-width: 120px;
    }

    .candidate-app-btn-cancel {
        background: #e5e7eb;
        color: #6b7280;
        border-color: #e5e7eb;
    }

    .candidate-app-btn-cancel:hover {
        background: #d1d5db;
        border-color: #d1d5db;
    }

    .candidate-app-btn-submit {
        background: #800020;
        color: #fff;
        border-color: #800020;
        box-shadow: 0 8px 24px rgba(128, 0, 32, 0.18);
    }

    .candidate-app-btn-submit:hover {
        background: #5c0000;
        border-color: #5c0000;
    }

    .candidate-app-add-btn {
        font-size: 0.8rem;
        background: #eee;
        width: 100%;
        margin-top: 5px;
        padding: 8px 10px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
    }

    .candidate-extra-fields {
        display: grid;
        gap: 0.9rem;
    }

    .candidate-extra-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
    }

    @media (max-width: 900px) {
        .candidate-name-grid,
        .candidate-info-grid,
        .candidate-info-row-3,
        .candidate-extra-grid {
            grid-template-columns: 1fr;
        }

        .candidate-split-panels-row {
            flex-direction: column;
        }

        .candidate-app-body {
            overflow-y: auto;
        }

        .candidate-panel-scroll {
            max-height: 300px;
        }
    }
</style>

<div id="applicationModal" class="modal-overlay candidate-app-modal-overlay">
    <div class="candidate-app-modal">
        <button type="button" class="candidate-app-close" onclick="closeApplicationModal()">&times;</button>

        <header class="candidate-app-header">
            <div class="candidate-app-header-flex">
                <div id="applicationHeaderBrand" class="candidate-app-header-brand">
                    <img id="applicationHeaderLogo" src="{{ asset('images/spc-logo.png') }}" alt="SPC Logo">
                </div>
                <div class="candidate-app-header-text">
                    <h1 id="applicationModalTitle">ELECTION APPLICATION</h1>
                    <h2 id="applicationModalSubtitle">Candidate Application</h2>
                </div>
            </div>
        </header>

        <div class="candidate-app-body">
            <form id="candidateApplicationForm" class="candidate-app-form" onsubmit="submitCandidateApplication(event)" style="display:flex; flex-direction:column; height:100%;">
                @csrf
                <input type="hidden" id="applicationElectionId">
                <input type="hidden" id="applicationElectionDepartment">
                <input type="hidden" id="applicationElectionName">
                <input type="hidden" id="field_full_name" data-field-key="full_name" data-required="1">

                <div class="candidate-top-section">
                    <div class="candidate-app-section-title">Statement of Purpose</div>
                    <div class="candidate-purpose-area">
                        <label for="field_platform_statement">Why are you running for public office?</label>
                        <textarea id="field_platform_statement" data-field-key="platform_statement" data-required="1" placeholder="Write a short statement of purpose..." required></textarea>
                    </div>

                    <div class="candidate-app-section-title">Personal Information</div>
                    <div class="candidate-name-grid">
                        <div class="form-group">
                            <label for="field_first_name">First Name</label>
                            <input type="text" id="field_first_name" placeholder="First name" required>
                        </div>
                        <div class="form-group">
                            <label for="field_middle_name">Middle Name</label>
                            <input type="text" id="field_middle_name" placeholder="Middle name">
                        </div>
                        <div class="form-group">
                            <label for="field_last_name">Last Name</label>
                            <input type="text" id="field_last_name" placeholder="Last name" required>
                        </div>
                        <div class="form-group">
                            <label>Course & Year</label>
                            <input type="text" id="field_course_year" data-field-key="course_year" data-required="1" readonly>
                        </div>

                    </div>
                    <div class="candidate-info-grid">

                        <div class="candidate-info-row-3">
                            <div class="form-group">
                                <label>Mobile Number</label>
                                <input type="tel" id="field_mobile_number" data-field-key="mobile_number" data-required="1" placeholder="0912-345-6789" required>
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" id="field_email_address" data-field-key="email_address" data-required="1" readonly>
                            </div>
                            <div class="form-group">
                                <label>Position</label>
                                <select id="field_position" data-field-key="position" data-required="1" required>
                                    <option value="" disabled selected>Select...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="candidate-split-panels-row">
                    <div class="candidate-panel">
                        <div class="candidate-app-section-title">Leadership Experience</div>
                        <div class="candidate-panel-scroll">
                            <table class="candidate-leadership-table" id="expTable">
                                <thead>
                                    <tr>
                                        <th style="width: 35%;">Organization</th>
                                        <th style="width: 30%;">Position</th>
                                        <th style="width: 35%;">Term</th>
                                    </tr>
                                </thead>
                                <tbody id="leadershipTableBody">
                                    <tr>
                                        <td><input type="text" class="leadership-org" placeholder="Org Name"></td>
                                        <td><input type="text" class="leadership-position" placeholder="Role"></td>
                                        <td><input type="text" class="leadership-term" placeholder="S.Y."></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" class="leadership-org"></td>
                                        <td><input type="text" class="leadership-position"></td>
                                        <td><input type="text" class="leadership-term"></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" class="leadership-org"></td>
                                        <td><input type="text" class="leadership-position"></td>
                                        <td><input type="text" class="leadership-term"></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" class="leadership-org"></td>
                                        <td><input type="text" class="leadership-position"></td>
                                        <td><input type="text" class="leadership-term"></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" class="leadership-org"></td>
                                        <td><input type="text" class="leadership-position"></td>
                                        <td><input type="text" class="leadership-term"></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" class="leadership-org"></td>
                                        <td><input type="text" class="leadership-position"></td>
                                        <td><input type="text" class="leadership-term"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="candidate-app-add-btn" onclick="addRow()">+ Add Row</button>
                    </div>

                    <div class="candidate-panel">
                        <div class="candidate-app-section-title">Platform of Candidacy</div>
                        <div class="candidate-panel-scroll">
                            <div class="candidate-platform-list" id="platformList">
                                <div class="candidate-platform-row">
                                    <div class="candidate-platform-number">1</div>
                                    <input type="text" class="candidate-platform-input" placeholder="First key platform...">
                                    <span class="candidate-remove-platform" onclick="removePlatform(this)">&times;</span>
                                </div>
                                <div class="candidate-platform-row">
                                    <div class="candidate-platform-number">2</div>
                                    <input type="text" class="candidate-platform-input" placeholder="Second key platform...">
                                    <span class="candidate-remove-platform" onclick="removePlatform(this)">&times;</span>
                                </div>
                                <div class="candidate-platform-row">
                                    <div class="candidate-platform-number">3</div>
                                    <input type="text" class="candidate-platform-input" placeholder="Third key platform...">
                                    <span class="candidate-remove-platform" onclick="removePlatform(this)">&times;</span>
                                </div>
                                <div class="candidate-platform-row">
                                    <div class="candidate-platform-number">4</div>
                                    <input type="text" class="candidate-platform-input" placeholder="Fourth key platform...">
                                    <span class="candidate-remove-platform" onclick="removePlatform(this)">&times;</span>
                                </div>
                                <div class="candidate-platform-row">
                                    <div class="candidate-platform-number">5</div>
                                    <input type="text" class="candidate-platform-input" placeholder="Fifth key platform...">
                                    <span class="candidate-remove-platform" onclick="removePlatform(this)">&times;</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="candidate-app-add-btn" onclick="addPlatform()">+ Add Platform</button>
                    </div>
                </div>

                <div id="additionalRequirementsSection" style="display:none; margin-top:1rem;">
                    <div class="candidate-app-section-title">Additional Requirements</div>
                    <div id="additionalRequirementsFields" class="candidate-extra-grid"></div>
                </div>

                <div style="margin-top:1rem; flex-shrink:0;">
                    <button type="submit" style="display:none;"></button>
                </div>
            </form>
        </div>

        <footer class="candidate-app-footer">
            <button type="button" class="candidate-app-btn candidate-app-btn-cancel" onclick="closeApplicationModal()">Cancel</button>
            <button type="submit" class="candidate-app-btn candidate-app-btn-submit" form="candidateApplicationForm" style="padding:0.58rem 0.8rem; border-radius:10px; border:2px solid #800020; background:#800020; color:#fff; font-weight:700; cursor:pointer;">Submit Application</button>
        </footer>
    </div>
</div>

@push('scripts')
<script>
@php
    $student = auth()->guard('student')->user();
    $studentProfile = [
        'first_name' => $student ? $student->name : '',
        'middle_name' => $student ? $student->middle_name : '',
        'last_name' => $student ? $student->last_name : '',
        'full_name' => trim(implode(' ', array_filter([
            $student ? $student->name : '',
            $student ? $student->middle_name : '',
            $student ? $student->last_name : '',
        ]))),
        'email' => $student ? $student->email : '',
        'student_id' => $student ? $student->student_id : '',
        'year_level' => $student ? $student->year_level : '',
        'department' => $student ? $student->department : '',
        'course_year' => trim(implode(' - ', array_filter([
            $student ? $student->department : '',
            $student ? $student->year_level : '',
        ]))),
    ];
@endphp

const candidateApplicationElections = @json($elections->values());
const studentProfile = @json($studentProfile);
const spcLogoPath = @json(asset('images/spc-logo.png'));
const csgLogoPath = @json(asset('images/CSG%20LOGO.png'));
const applicationStatusRefreshUrl = @json(route('student.candidate-applications.statuses'));

let activeApplicationElection = null;

function syncFullNameField() {
    const firstName = (document.getElementById('field_first_name')?.value || '').trim();
    const middleName = (document.getElementById('field_middle_name')?.value || '').trim();
    const lastName = (document.getElementById('field_last_name')?.value || '').trim();
    const combinedName = [firstName, middleName, lastName].filter(Boolean).join(' ').replace(/\s+/g, ' ').trim();
    const fullNameField = document.getElementById('field_full_name');

    if (fullNameField) {
        fullNameField.value = combinedName;
    }

    return combinedName;
}

function getElectionPositions(election) {
    const positions = Array.isArray(election?.positions) ? election.positions : [];
    return positions
        .map(function (position) { return String(position || '').trim(); })
        .filter(function (position, index, all) {
            return position !== '' && all.indexOf(position) === index;
        });
}

function updateApplicationHeaderBrand(election) {
    const brand = document.getElementById('applicationHeaderBrand');
    const logo = document.getElementById('applicationHeaderLogo');
    const title = document.getElementById('applicationModalTitle');
    const subtitle = document.getElementById('applicationModalSubtitle');

    if (!brand || !logo || !title || !subtitle) {
        return;
    }

    const department = String((election && election.department) || '').trim().toUpperCase();
    const electionName = String((election && election.election_name) || '').trim().toUpperCase();
    const isCsgElection = department === 'CSG' || electionName.includes('CSG');

    if (isCsgElection) {
        brand.innerHTML = '<img id="applicationHeaderLogo" src="' + csgLogoPath + '" alt="CSG Logo" onerror="this.style.display=\'none\'; this.parentElement.innerHTML=\'<div class=\\\"candidate-app-csg-mark\\\" aria-label=\\\"CSG Logo\\\"></div>';
        title.textContent = 'CSG ELECTION APPLICATION';
    } else {
        brand.innerHTML = '<img id="applicationHeaderLogo" src="' + spcLogoPath + '" alt="SPC Logo">';
        title.textContent = (department ? department + ' ' : '') + 'ELECTION APPLICATION';
    }

    subtitle.textContent = (election && election.election_name) ? ('Apply: ' + election.election_name) : 'Candidate Application';
}

function openApplicationModal(electionId) {
    const election = candidateApplicationElections.find(e => Number(e.id) === Number(electionId));
    if (!election) {
        _swalToast('error', 'Election not found.');
        return;
    }

    if ((election.registration_status || '').toUpperCase() !== 'OPEN') {
        _swalWarn('Registration Not Open', 'Candidate registration is not open for this election.');
        return;
    }

    activeApplicationElection = election;
    document.getElementById('applicationElectionId').value = election.id;
    document.getElementById('applicationElectionDepartment').value = election.department || '';
    document.getElementById('applicationElectionName').value = election.election_name || '';
    updateApplicationHeaderBrand(election);
    renderDynamicApplicationFields(election);
    document.getElementById('applicationModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeApplicationModal() {
    document.getElementById('applicationModal').classList.remove('active');
    document.getElementById('candidateApplicationForm').reset();
    document.getElementById('leadershipTableBody').innerHTML = '';
    document.getElementById('platformList').innerHTML = '';
    document.getElementById('additionalRequirementsFields').innerHTML = '';
    document.getElementById('additionalRequirementsSection').style.display = 'none';
    document.getElementById('applicationModalTitle').textContent = 'ELECTION APPLICATION';
    document.getElementById('applicationModalSubtitle').textContent = 'Candidate Application';
    document.getElementById('applicationHeaderBrand').innerHTML = '<img id="applicationHeaderLogo" src="' + spcLogoPath + '" alt="SPC Logo">';
    document.body.style.overflow = 'auto';
    activeApplicationElection = null;
}

function renderDynamicApplicationFields(election) {
    document.getElementById('field_first_name').value = studentProfile.first_name || '';
    document.getElementById('field_middle_name').value = studentProfile.middle_name || '';
    document.getElementById('field_last_name').value = studentProfile.last_name || '';
    syncFullNameField();
    document.getElementById('field_course_year').value = studentProfile.course_year || '';
    document.getElementById('field_mobile_number').value = '';
    document.getElementById('field_email_address').value = studentProfile.email || '';
    document.getElementById('field_platform_statement').value = '';

    const positionSelect = document.getElementById('field_position');
    positionSelect.innerHTML = '<option value="" disabled selected>Select...</option>';

    const positions = getElectionPositions(election);
    positions.forEach(function (position) {
        const option = document.createElement('option');
        option.value = position;
        option.textContent = position;
        positionSelect.appendChild(option);
    });

    populateLeadershipRows();
    populatePlatformRows();

    const container = document.getElementById('additionalRequirementsFields');
    container.innerHTML = '';

    const schema = Array.isArray(election.candidate_registration_schema) ? election.candidate_registration_schema : [];
    const handledKeys = new Set([
        'full_name',
        'student_id',
        'year_level',
        'department',
        'position',
        'platform_statement',
        'mobile_number',
        'email_address',
        'course_year',
    ]);

    schema.forEach(field => {
        const key = String(field.key || '').trim();
        if (!key || handledKeys.has(key)) return;

        const fieldWrapper = document.createElement('div');
        const label = document.createElement('label');
        label.style.display = 'block';
        label.style.marginBottom = '0.35rem';
        label.style.fontSize = '0.86rem';
        label.style.fontWeight = '600';
        label.textContent = field.label || key;

        let input;
        const type = String(field.type || 'text').toLowerCase();
        if (type === 'textarea') {
            input = document.createElement('textarea');
            input.rows = 4;
            if (field.max) {
                input.maxLength = Number(field.max);
            }
        } else if (type === 'select' && field.source === 'election_positions') {
            input = document.createElement('select');
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select position';
            input.appendChild(placeholder);

            const positions = getElectionPositions(election);
            positions.forEach(function (pos) {
                const option = document.createElement('option');
                option.value = pos;
                option.textContent = pos;
                input.appendChild(option);
            });
        } else {
            input = document.createElement('input');
            input.type = type === 'number' ? 'number' : 'text';
        }

        input.id = 'field_' + key;
        input.dataset.fieldKey = key;
        input.dataset.required = field.required ? '1' : '0';
        input.style.width = '100%';
        input.style.padding = '10px';
        input.style.border = '1px solid #cbd5e1';
        input.style.borderRadius = '8px';
        input.style.fontSize = '0.9rem';

        if (field.required) {
            input.required = true;
            label.textContent += ' *';
        }

        if (field.readonly) {
            input.readOnly = true;
            input.style.background = '#f8fafc';
            input.style.color = '#475569';
            input.value = studentProfile[key] || '';
        }

        fieldWrapper.appendChild(label);
        fieldWrapper.appendChild(input);
        container.appendChild(fieldWrapper);
    });

    document.getElementById('additionalRequirementsSection').style.display = container.children.length > 0 ? 'block' : 'none';
}

function populateLeadershipRows() {
    const tbody = document.getElementById('leadershipTableBody');
    if (!tbody) return;

    const existingRows = Array.from(tbody.querySelectorAll('tr'));
    if (existingRows.length === 0) {
        addRow();
        return;
    }
}

function populatePlatformRows() {
    const list = document.getElementById('platformList');
    if (!list) return;

    if (list.children.length === 0) {
        addPlatform();
    }
}

function addPlatform() {
    const list = document.getElementById('platformList');
    const div = document.createElement('div');
    div.className = 'candidate-platform-row';
    div.innerHTML = `
        <div class="candidate-platform-number">${list.children.length + 1}</div>
        <input type="text" class="candidate-platform-input" placeholder="Add new platform...">
        <span class="candidate-remove-platform" onclick="removePlatform(this)">&times;</span>
    `;
    list.appendChild(div);

    const scrollContainer = list.closest('.candidate-panel-scroll');
    if (scrollContainer) {
        scrollContainer.scrollTop = scrollContainer.scrollHeight;
    }
}

function removePlatform(btn) {
    const row = btn.closest('.candidate-platform-row');
    if (row) {
        row.remove();
        renumberPlatforms();
    }
}

function renumberPlatforms() {
    const items = document.querySelectorAll('.candidate-platform-row');
    items.forEach((item, index) => {
        const number = item.querySelector('.candidate-platform-number');
        if (number) {
            number.innerText = index + 1;
        }
    });
}

function addRow() {
    const tbody = document.querySelector('#leadershipTableBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" class="leadership-org" placeholder="Org"></td>
        <td><input type="text" class="leadership-position" placeholder="Pos"></td>
        <td><input type="text" class="leadership-term" placeholder="Term"></td>
    `;
    tbody.appendChild(tr);

    const scrollContainer = tbody.closest('.candidate-panel-scroll');
    if (scrollContainer) {
        scrollContainer.scrollTop = scrollContainer.scrollHeight;
    }
}

function submitCandidateApplication(event) {
    event.preventDefault();

    if (!activeApplicationElection) {
        _swalToast('error', 'No election selected.');
        return;
    }

    const combinedFullName = syncFullNameField();
    const responses = {};
    const fields = document.querySelectorAll('#candidateApplicationForm [data-field-key]');

    for (const field of fields) {
        const key = field.dataset.fieldKey;
        const value = (field.value || '').trim();
        const required = field.dataset.required === '1';

        if (required && !value) {
            _swalWarn('Incomplete Form', 'Please complete all required fields before submitting.');
            field.focus();
            return;
        }

        responses[key] = value;
    }

    const leadershipRows = Array.from(document.querySelectorAll('#leadershipTableBody tr')).map(function (row) {
        return {
            organization: (row.querySelector('.leadership-org')?.value || '').trim(),
            position: (row.querySelector('.leadership-position')?.value || '').trim(),
            term: (row.querySelector('.leadership-term')?.value || '').trim(),
        };
    }).filter(function (entry) {
        return entry.organization || entry.position || entry.term;
    });

    const platformRows = Array.from(document.querySelectorAll('#platformList .candidate-platform-input'))
        .map(function (input) {
            return (input.value || '').trim();
        })
        .filter(function (value) {
            return value !== '';
        });

    responses.leadership_experience = leadershipRows;
    responses.platform_of_candidacy = platformRows;
    responses.platform_statement = (document.getElementById('field_platform_statement').value || '').trim();
    responses.statement_of_purpose = responses.platform_statement;
    responses.position = (document.getElementById('field_position').value || '').trim();
    responses.full_name = combinedFullName;
    responses.student_id = (studentProfile.student_id || '').toString().trim();
    responses.year_level = (studentProfile.year_level || '').toString().trim();
    responses.department = (studentProfile.department || activeApplicationElection.department || '').toString().trim();
    responses.course_year = (document.getElementById('field_course_year').value || '').trim();
    responses.mobile_number = (document.getElementById('field_mobile_number').value || '').trim();
    responses.email_address = (document.getElementById('field_email_address').value || '').trim();

    const metaCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const formCsrfToken = document.querySelector('#candidateApplicationForm input[name="_token"]')?.value || '';
    const requestCsrfToken = metaCsrfToken || formCsrfToken || (typeof csrfToken !== 'undefined' ? csrfToken : '');

    if (!requestCsrfToken) {
        _swalWarn('Session Expired', 'Security token is missing. Please refresh the page and try again.');
        return;
    }

    fetch('{{ route('student.candidate-applications.store') }}', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': requestCsrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            _token: requestCsrfToken,
            election_id: activeApplicationElection.id,
            form_responses: responses,
        })
    })
    .then(async response => {
        if (response.status === 419) {
            throw { success: false, message: 'Session expired (CSRF). Please refresh the page and submit again.' };
        }

        let payload = {};
        try {
            payload = await response.json();
        } catch (e) {
            payload = { success: false, message: 'Unexpected server response.' };
        }

        if (!response.ok || !payload.success) {
            throw payload;
        }

        _swalToast('success', payload.message || 'Application submitted successfully.');
        closeApplicationModal();
        setTimeout(() => window.location.reload(), 800);
    })
    .catch(error => {
        if (error.errors) {
            const first = Object.values(error.errors)[0];
            _swalToast('error', Array.isArray(first) ? first[0] : first);
            return;
        }

        _swalToast('error', error.message || 'Unable to submit application.');
    });
}

window.addEventListener('click', function (event) {
    if (event.target && event.target.id === 'applicationModal') {
        closeApplicationModal();
    }
});

const APPLICATION_STATUS_PAGE_SIZE = 6;
let applicationStatusActiveFilter = 'ALL';
const applicationStatusPagesByFilter = {
    ALL: 1,
    PENDING: 1,
    APPROVED: 1,
    REJECTED: 1,
};

function getApplicationStatusRows() {
    return Array.from(document.querySelectorAll('#applicationStatusTableBody .application-status-row'));
}

function ensureApplicationStatusEmptyRow() {
    const tbody = document.getElementById('applicationStatusTableBody');
    if (!tbody) {
        return null;
    }

    let emptyRow = document.getElementById('applicationStatusEmptyRow');
    if (!emptyRow) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'applicationStatusEmptyRow';
        emptyRow.innerHTML = '<td colspan="6" style="padding:1rem; text-align:center; color:#64748b;"></td>';
        tbody.appendChild(emptyRow);
    }

    return emptyRow;
}

function getRowsForFilter(statusFilter) {
    const allRows = getApplicationStatusRows();
    if (statusFilter === 'ALL') {
        return allRows;
    }

    return allRows.filter(function (row) {
        return String(row.dataset.status || '').toUpperCase() === statusFilter;
    });
}

function renderApplicationStatusTable() {
    const allRows = getApplicationStatusRows();
    const rowsForFilter = getRowsForFilter(applicationStatusActiveFilter);
    const emptyRow = ensureApplicationStatusEmptyRow();
    const prevBtn = document.getElementById('applicationStatusPrevBtn');
    const nextBtn = document.getElementById('applicationStatusNextBtn');
    const pageInfo = document.getElementById('applicationStatusPageInfo');

    allRows.forEach(function (row) {
        row.style.display = 'none';
    });

    const totalRows = rowsForFilter.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / APPLICATION_STATUS_PAGE_SIZE));
    const currentPage = Math.min(applicationStatusPagesByFilter[applicationStatusActiveFilter] || 1, totalPages);
    applicationStatusPagesByFilter[applicationStatusActiveFilter] = currentPage;

    const startIndex = (currentPage - 1) * APPLICATION_STATUS_PAGE_SIZE;
    const endIndex = startIndex + APPLICATION_STATUS_PAGE_SIZE;

    rowsForFilter.slice(startIndex, endIndex).forEach(function (row) {
        row.style.display = 'table-row';
    });

    if (emptyRow) {
        if (totalRows === 0) {
            const statusLabel = applicationStatusActiveFilter === 'ALL'
                ? 'any candidate applications yet.'
                : 'any ' + applicationStatusActiveFilter.toLowerCase() + ' applications yet.';
            emptyRow.querySelector('td').textContent = 'You have not submitted ' + statusLabel;
            emptyRow.style.display = 'table-row';
        } else {
            emptyRow.style.display = 'none';
        }
    }

    if (pageInfo) {
        pageInfo.textContent = 'Page ' + currentPage + ' of ' + totalPages;
    }

    if (prevBtn) {
        prevBtn.disabled = currentPage <= 1;
    }

    if (nextBtn) {
        nextBtn.disabled = currentPage >= totalPages;
    }

    document.querySelectorAll('.application-status-tab').forEach(function (tab) {
        tab.classList.toggle('is-active', (tab.dataset.status || '').toUpperCase() === applicationStatusActiveFilter);
    });
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function buildApplicationStatusRow(item) {
    var status = String(item.status || 'PENDING').toUpperCase();
    var statusBg = status === 'APPROVED' ? '#dcfce7' : (status === 'REJECTED' ? '#fee2e2' : '#ffedd5');
    var statusColor = status === 'APPROVED' ? '#166534' : (status === 'REJECTED' ? '#991b1b' : '#92400e');
    var description = escapeHtml(item.description || 'N/A');

    return '<tr class="application-status-row" data-status="' + status + '" style="border-top:1px solid #f1f5f9;">'
        + '<td style="padding:0.75rem 1rem; color:#0f172a; font-weight:600;">' + escapeHtml(item.election_name || 'Election removed') + '</td>'
        + '<td style="padding:0.75rem 1rem; color:#475569;">' + escapeHtml(item.department || 'N/A') + '</td>'
        + '<td style="padding:0.75rem 1rem; color:#475569;">' + escapeHtml(item.submitted_at || 'N/A') + '</td>'
        + '<td style="padding:0.75rem 1rem;"><span style="padding:0.25rem 0.55rem; border-radius:999px; background:' + statusBg + '; color:' + statusColor + '; font-size:0.75rem; font-weight:700;">' + status + '</span></td>'
        + '<td style="padding:0.75rem 1rem; color:#475569;">' + escapeHtml(item.position || 'N/A') + '</td>'
        + '<td style="padding:0.75rem 1rem; color:#475569; max-width:280px; white-space:normal; word-break:break-word;">' + description + '</td>'
        + '</tr>';
}

function fetchApplicationStatuses() {
    if (!applicationStatusRefreshUrl) {
        return;
    }

    fetch(applicationStatusRefreshUrl, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
        if (!data || !data.success) {
            return;
        }

        var tbody = document.getElementById('applicationStatusTableBody');
        if (!tbody) {
            return;
        }

        var emptyRow = document.getElementById('applicationStatusEmptyRow');
        if (emptyRow) {
            emptyRow.remove();
        }

        tbody.querySelectorAll('.application-status-row').forEach(function (row) {
            row.remove();
        });

        if (Array.isArray(data.applications) && data.applications.length > 0) {
            tbody.insertAdjacentHTML('beforeend', data.applications.map(buildApplicationStatusRow).join(''));
        } else {
            tbody.insertAdjacentHTML('beforeend', '<tr id="applicationStatusEmptyRow"><td colspan="6" style="padding:1rem; text-align:center; color:#64748b;">You have not submitted any candidate applications yet.</td></tr>');
        }

        renderApplicationStatusTable();
    })
    .catch(function (error) {
        console.error('Application status refresh error:', error);
    });
}

document.querySelectorAll('.application-status-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
        const selectedFilter = String(tab.dataset.status || 'ALL').toUpperCase();
        applicationStatusActiveFilter = selectedFilter;
        if (!applicationStatusPagesByFilter[selectedFilter]) {
            applicationStatusPagesByFilter[selectedFilter] = 1;
        }
        renderApplicationStatusTable();
    });
});

document.getElementById('applicationStatusPrevBtn')?.addEventListener('click', function () {
    const current = applicationStatusPagesByFilter[applicationStatusActiveFilter] || 1;
    applicationStatusPagesByFilter[applicationStatusActiveFilter] = Math.max(1, current - 1);
    renderApplicationStatusTable();
});

document.getElementById('applicationStatusNextBtn')?.addEventListener('click', function () {
    const rowsForFilter = getRowsForFilter(applicationStatusActiveFilter);
    const totalPages = Math.max(1, Math.ceil(rowsForFilter.length / APPLICATION_STATUS_PAGE_SIZE));
    const current = applicationStatusPagesByFilter[applicationStatusActiveFilter] || 1;
    applicationStatusPagesByFilter[applicationStatusActiveFilter] = Math.min(totalPages, current + 1);
    renderApplicationStatusTable();
});

renderApplicationStatusTable();
fetchApplicationStatuses();
window.setInterval(fetchApplicationStatuses, 10000);
window.addEventListener('focus', fetchApplicationStatuses);
document.addEventListener('visibilitychange', function () {
    if (!document.hidden) {
        fetchApplicationStatuses();
    }
});
</script>
@endpush
@endsection
