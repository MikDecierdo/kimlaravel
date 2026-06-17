@extends('layouts.department-head')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/dashboard.css') }}">
@endpush

@section('dept-head-content')

@php
    $staffUser = auth()->user();
    $canApproveStudents = method_exists($staffUser, 'hasDepartmentPortalPermission')
        ? $staffUser->hasDepartmentPortalPermission('approve_students')
        : (bool) ($staffUser->is_department_head ?? false);
@endphp

<!-- Animated Background -->
<div class="dashboard-background kenburns-left"></div>

<!-- Dashboard Content -->
<div class="dashboard-content">
<header>
    <div class="header-title">
        <h1>{{ $department }} Department Dashboard</h1>
        <p>Overview of your department's election activities</p>
    </div>
</header>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="icon" style="background:linear-gradient(135deg,#800020 0%,#A0153E 100%);color:white;">
            <i class="fa-solid fa-building-columns"></i>
        </div>
        <h3 style="color:#800020;">{{ $stats['total_elections'] }}</h3>
        <p>Total Elections</p>
    </div>
    <div class="stat-card">
        <div class="icon" style="background:linear-gradient(135deg,#FFC107 0%,#FFD54F 100%);color:#1e293b;">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <h3 style="color:#d97706;">{{ $stats['active_elections'] }}</h3>
        <p>Active Elections</p>
    </div>
    <div class="stat-card">
        <div class="icon" style="background:linear-gradient(135deg,#800020 0%,#A0153E 100%);color:white;">
            <i class="fa-solid fa-users"></i>
        </div>
        <h3 style="color:#800020;">{{ $stats['total_candidates'] }}</h3>
        <p>Total Candidates</p>
    </div>
    <div class="stat-card">
        <div class="icon" style="background:linear-gradient(135deg,#FFC107 0%,#FFD54F 100%);color:#1e293b;">
            <i class="fa-solid fa-check-to-slot"></i>
        </div>
        <h3 style="color:#d97706;">{{ $stats['total_votes'] }}</h3>
        <p>Total Votes Cast</p>
    </div>
</div>

<!-- Chart + Quick Actions (side-by-side) -->
<div class="chart-qa-row">
    <!-- Candidate Votes Chart -->
    <div class="chart-section">
        <p class="section-title">
            <span style="width:28px;height:28px;background:#800020;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-chart-bar" style="color:white;font-size:0.8rem;"></i>
            </span>
            Currently Leading by Position
            @if($activeElection)
                <span style="margin-left:auto;font-size:0.78rem;font-weight:600;color:#800020;background:#fff0f3;padding:0.25rem 0.75rem;border-radius:20px;border:1px solid #fecdd3;">
                    {{ $activeElection->election_name }}
                </span>
            @endif
        </p>
        @if($topCandidates->isEmpty())
            <div style="text-align:center;padding:3rem;color:#9ca3af;">
                <i class="fa-solid fa-chart-bar" style="font-size:3rem;opacity:0.25;margin-bottom:1rem;"></i>
                <p>No vote data yet. Votes will appear here once candidates are added and voting begins.</p>
            </div>
        @else
            <div style="position:relative;height:{{ max(200, $topCandidates->count() * 52) }}px;">
                <canvas id="candidateVotesChart"></canvas>
            </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-section">
        <p class="quick-actions-title">
            <span style="width:22px;height:22px;background:#800020;border-radius:5px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fa-solid fa-bolt" style="color:white;font-size:0.7rem;"></i>
            </span>
            Quick Actions
        </p>
        <div class="quick-actions-grid">
            <a href="{{ route('department-head.campus-elections') }}?action=add" class="qa-btn qa-btn-solid">
                <div class="qa-icon"><i class="fa-solid fa-plus"></i></div>
                Add Election
            </a>
            <a href="{{ route('department-head.candidates') }}?action=add" class="qa-btn qa-btn-outline">
                <div class="qa-icon"><i class="fa-solid fa-user-plus"></i></div>
                Add Candidate
            </a>
            @if($canApproveStudents)
            <a href="{{ route('department-head.students') }}?action=add" class="qa-btn qa-btn-solid">
                <div class="qa-icon"><i class="fa-solid fa-user-graduate"></i></div>
                Add Student
            </a>
            @endif
            <a href="{{ route('department-head.events') }}?action=add" class="qa-btn qa-btn-outline">
                <div class="qa-icon"><i class="fa-solid fa-newspaper"></i></div>
                Add Event Post
            </a>
        </div>
    </div>
</div>

<!-- Recent Elections + Recent Candidates -->
<div class="dash-bottom">
    <!-- Recent Elections -->
    <div class="dash-box">
        <p class="section-title">
            <span style="width:28px;height:28px;background:#800020;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-building-columns" style="color:white;font-size:0.8rem;"></i>
            </span>
            Recent Elections
        </p>
        @forelse($elections->take(5) as $election)
            <div style="padding:0.85rem 0;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;gap:0.75rem;">
                <div style="min-width:0;">
                    <h4 style="margin:0 0 0.2rem;font-size:0.92rem;font-weight:700;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $election->election_name }}</h4>
                    <p style="font-size:0.78rem;color:#64748b;margin:0;">{{ $election->start_date->format('M d, Y') }} &ndash; {{ $election->end_date->format('M d, Y') }}</p>
                </div>
                <span style="flex-shrink:0;padding:0.3rem 0.85rem;border-radius:20px;font-size:0.78rem;font-weight:700;background:{{ $election->is_active ? '#fef9c3' : '#f3f4f6' }};color:{{ $election->is_active ? '#92400e' : '#6b7280' }};border:1px solid {{ $election->is_active ? '#fde68a' : '#e5e7eb' }};">
                    {{ $election->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        @empty
            <p style="text-align:center;color:#9ca3af;padding:2rem;">No elections yet.</p>
        @endforelse
        <a href="{{ route('department-head.campus-elections') }}" style="display:inline-block;margin-top:1rem;font-size:0.82rem;font-weight:700;color:#800020;text-decoration:none;">View all elections &rarr;</a>
    </div>

    <!-- Recent Candidates -->
    <div class="dash-box">
        <p class="section-title">
            <span style="width:28px;height:28px;background:#800020;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-users" style="color:white;font-size:0.8rem;"></i>
            </span>
            Recent Candidates
        </p>
        <div style="display:flex;flex-direction:column;gap:0.75rem;">
            @forelse($candidates->take(5) as $candidate)
                <div style="display:flex;align-items:center;gap:0.9rem;padding:0.6rem 0;border-bottom:1px solid #f1f5f9;">
                    <div style="width:44px;height:44px;flex-shrink:0;border-radius:50%;background:linear-gradient(135deg,#800020 0%,#A0153E 100%);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;overflow:hidden;">
                        @if($candidate->image)
                            <img src="{{ $candidate->image }}" alt="{{ $candidate->first_name }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ strtoupper(substr($candidate->first_name,0,1)) }}
                        @endif
                    </div>
                    <div style="min-width:0;flex:1;">
                        <p style="margin:0;font-size:0.88rem;font-weight:700;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $candidate->first_name }} {{ $candidate->last_name }}</p>
                        <p style="margin:0;font-size:0.78rem;color:#800020;font-weight:600;">{{ $candidate->position }}</p>
                    </div>
                    <span style="flex-shrink:0;font-size:0.82rem;font-weight:700;color:#1f2937;">{{ $candidate->votes }} <span style="font-weight:400;color:#9ca3af;">votes</span></span>
                </div>
            @empty
                <p style="text-align:center;color:#9ca3af;padding:2rem;">No candidates yet.</p>
            @endforelse
        </div>
        <a href="{{ route('department-head.candidates') }}" style="display:inline-block;margin-top:1rem;font-size:0.82rem;font-weight:700;color:#800020;text-decoration:none;">View all candidates &rarr;</a>
    </div>
</div>

</div> <!-- End Dashboard Content -->

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@if($topCandidates->isNotEmpty())
<script>
const _chartLabels = @json($topCandidates->map(fn($c) => $c->first_name . ' ' . $c->last_name . ' (' . $c->position . ')'));
const _chartVotes  = @json($topCandidates->pluck('votes_count'));
</script>
@endif
<script src="{{ asset('assets/dept-head/js/dashboard.js') }}"></script>
</script>
@endpush
@endsection
