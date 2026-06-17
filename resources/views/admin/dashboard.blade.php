@extends('layouts.admin')

@section('admin-content')
<header>
    <div class="header-title">
        <h1>Admin Dashboard</h1>
        <p>Manage the SPC Voting System and Events</p>
    </div>
</header>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue"><i class="fa-solid fa-users"></i></div>
        <div>
            <h3>{{ $stats['total_candidates'] }}</h3>
            <p style="color:var(--text-muted); font-size:0.9rem;">Total Candidates</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-pink"><i class="fa-solid fa-calendar-check"></i></div>
        <div>
            <h3>{{ $stats['total_events'] }}</h3>
            <p style="color:var(--text-muted); font-size:0.9rem;">Total Events</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green"><i class="fa-solid fa-check-double"></i></div>
        <div>
            <h3>{{ $stats['total_votes'] }}</h3>
            <p style="color:var(--text-muted); font-size:0.9rem;">Total Votes Cast</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-orange"><i class="fa-solid fa-graduation-cap"></i></div>
        <div>
            <h3>{{ $stats['total_students'] }}</h3>
            <p style="color:var(--text-muted); font-size:0.9rem;">Registered Students</p>
        </div>
    </div>
</div>

<h2 style="margin-bottom: 1rem;">Recent Events</h2>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem;">
    @forelse($recentEvents as $event)
        <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: var(--card-shadow); border-left: 5px solid var(--secondary);">
            <div style="display: flex; gap: 1rem;">
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 70px; height: 70px; background: #fff0f6; border-radius: 8px; color: var(--secondary); font-weight: 700;">
                    <span style="font-size: 1.5rem; line-height: 1;">{{ $event->event_date->format('d') }}</span>
                    <span style="font-size: 0.8rem; text-transform: uppercase;">{{ $event->event_date->format('M') }}</span>
                </div>
                <div style="flex: 1;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;">{{ $event->title }}</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.8rem;">
                        {{ Str::limit($event->description, 100) }}
                    </p>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                        <span><i class="fa-solid fa-building"></i> {{ $event->department }}</span>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <p style="grid-column: 1/-1; text-align: center; color: #888;">No recent events.</p>
    @endforelse
</div>
@endsection
