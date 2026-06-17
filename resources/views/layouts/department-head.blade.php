@extends('layouts.app')

@section('title', 'Department Head Dashboard')

@push('styles')
<style>
/* ── Sidebar user-profile drop-up ──────────────────────── */
.user-profile {
    position: relative;
}
.profile-dropup {
    position: absolute;
    bottom: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.15), 0 2px 8px rgba(0,0,0,0.08);
    padding: 0.4rem 0.4rem 0.7rem;
    opacity: 0;
    pointer-events: none;
    transform: translateY(6px);
    transition: opacity 0.2s ease, transform 0.2s ease;
    z-index: 200;
}
.user-profile:hover .profile-dropup {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
}
.profile-dropup-btn {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.6rem 0.85rem;
    border-radius: 7px;
    color: #1f2937;
    font-size: 0.88rem;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.15s;
}
.profile-dropup-btn:hover {
    background: #fff0f3;
    color: #800020;
}
</style>
@endpush

@section('content')
<nav class="sidebar">
    <div class="logo">
        <img src="{{ asset('images/spc-logo.png') }}" alt="SPC Logo" style="width: 50px; height: 50px; margin-bottom: 0.5rem;">
        <span>Dept Head</span>
    </div>
    @php
        $staffUser = auth()->user();
        $isDepartmentHead = (bool) ($staffUser->is_department_head ?? false);
        $canApproveStudents = method_exists($staffUser, 'hasDepartmentPortalPermission')
            ? $staffUser->hasDepartmentPortalPermission('approve_students')
            : $isDepartmentHead;
    @endphp
    <ul class="nav-links">
        <li class="nav-item">
            <a href="{{ route('department-head.dashboard') }}" class="nav-link {{ request()->routeIs('department-head.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('department-head.campus-elections') }}" class="nav-link {{ request()->routeIs('department-head.campus-elections') ? 'active' : '' }}">
                <i class="fa-solid fa-square-poll-vertical"></i>Elections
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('department-head.candidates') }}" class="nav-link {{ request()->routeIs('department-head.candidates') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i>Candidates
            </a>
        </li>
        @if($canApproveStudents)
        <li class="nav-item">
            <a href="{{ route('department-head.students') }}" class="nav-link {{ request()->routeIs('department-head.students') ? 'active' : '' }}">
                <i class="fa-solid fa-user-graduate"></i>Students
            </a>
        </li>
        @endif
        @if($isDepartmentHead)
            <li class="nav-item">
                <a href="{{ route('department-head.faculty') }}" class="nav-link {{ request()->routeIs('department-head.faculty*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-shield"></i>Faculty Access
                </a>
            </li>
        @endif
        @if($canApproveStudents)
        <li class="nav-item">
            @php
                $staffDept = $staffUser->department ?? '';
                $deptAliasMap = [
                    'IT'=>'BSIT','BSBA'=>'CBAE','EDUC'=>'CTE',
                    'ENGINEERING'=>'BSIT','NURSING'=>'CHTM',
                    'PSYCHOLOGY'=>'CBAE','ACCOUNTANCY'=>'CBAE',
                    'BSIT'=>'BSIT','CBAE'=>'CBAE','CRIM'=>'CRIM',
                    'CHTM'=>'CHTM','CTE'=>'CTE','SHS'=>'SHS',
                ];
                $mappedDept   = $deptAliasMap[$staffDept] ?? $staffDept;
                $depts        = array_unique([$staffDept, $mappedDept]);
                $pendingCount = \App\Models\User::whereIn('department', $depts)
                    ->where('role', 'student')
                    ->where('approval_status', 'pending')
                    ->count();
            @endphp
            <a href="{{ route('department-head.student-requests') }}" class="nav-link {{ request()->routeIs('department-head.student-requests*') ? 'active' : '' }}" style="position:relative;">
                <i class="fa-solid fa-user-clock"></i>Requests
                @if($pendingCount > 0)
                    <span style="position:absolute;top:6px;right:10px;background:#ef4444;color:#fff;border-radius:50%;width:18px;height:18px;font-size:.65rem;font-weight:800;display:flex;align-items:center;justify-content:center;">{{ $pendingCount }}</span>
                @endif
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a href="{{ route('department-head.votes-status') }}" class="nav-link {{ request()->routeIs('department-head.votes-status*') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i> Vote Status
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('department-head.election-winners') }}" class="nav-link {{ request()->routeIs('department-head.election-winners*') ? 'active' : '' }}">
                <i class="fa-solid fa-trophy"></i>Election Winners
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('department-head.events') }}" class="nav-link {{ request()->routeIs('department-head.events') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days"></i> Events
            </a>
        </li>
    </ul>
    <div class="user-profile">
        {{-- Drop-up popup --}}
        <div class="profile-dropup">
            <a href="{{ route('department-head.profile') }}" class="profile-dropup-btn">
                <i class="fa-solid fa-user-pen"></i> Edit Profile
            </a>
        </div>

        <div class="avatar" style="overflow: hidden; padding: 0;">
            @if($staffUser->profile_picture)
                <img src="{{ $staffUser->profile_picture }}" alt="{{ $staffUser->name }}"
                     style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
            @else
                {{ substr($staffUser->name, 0, 1) }}
            @endif
        </div>
        <div style="flex: 1; min-width: 0;">
            <h4 style="font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $staffUser->name }}
            </h4>
            <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $staffUser->department }} {{ $isDepartmentHead ? 'Head' : 'Faculty' }}</span>
        </div>
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: var(--text-muted); font-size: 1.2rem;" title="Logout">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</nav>

<main class="main-content">
    @yield('dept-head-content')
</main>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

@stack('scripts')
@endsection
