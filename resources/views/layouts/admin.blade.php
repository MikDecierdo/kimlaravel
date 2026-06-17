@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<nav class="sidebar">
    <div class="logo">
        <img src="{{ asset('images/spc-logo.png') }}" alt="SPC Logo" style="width: 50px; height: 50px; margin-bottom: 0.5rem;">
        <span>SPC Admin</span>
    </div>
    <ul class="nav-links">
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.campus-elections') }}" class="nav-link {{ request()->routeIs('admin.campus-elections') ? 'active' : '' }}">
                <i class="fa-solid fa-square-poll-vertical"></i> Campus Elections
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.candidates') }}" class="nav-link {{ request()->routeIs('admin.candidates') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i> Candidates
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.votes-status') }}" class="nav-link {{ request()->routeIs('admin.votes-status') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i> Votes Status
            </a>
        </li>
                <li class="nav-item">
            <a href="{{ route('admin.department-heads') }}" class="nav-link {{ request()->routeIs('admin.department-heads') ? 'active' : '' }}">
                <i class="fa-solid fa-user-tie"></i> Department Heads
            </a>
        </li>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.faculties') }}" class="nav-link {{ request()->routeIs('admin.faculties*') ? 'active' : '' }}">
                <i class="fa-solid fa-chalkboard-user"></i> Faculties
            </a>
        <li class="nav-item">
            <a href="{{ route('admin.students') }}" class="nav-link {{ request()->routeIs('admin.students') ? 'active' : '' }}">
                <i class="fa-solid fa-graduation-cap"></i> Students
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.events') }}" class="nav-link {{ request()->routeIs('admin.events') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days"></i> Events
            </a>
        </li>
    </ul>
    <div class="user-profile">
        <div class="avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
        <div style="flex: 1;">
            <h4 style="font-size: 0.9rem;">{{ auth()->user()->name }}</h4>
            <span style="font-size: 0.75rem; color: var(--text-muted);">Administrator</span>
        </div>
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: var(--text-muted); font-size: 1.2rem;" title="Logout">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</nav>

<main class="main-content">
    @yield('admin-content')
</main>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
@endsection
