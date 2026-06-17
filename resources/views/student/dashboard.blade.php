@extends('layouts.student')

@push('styles')
<style>
/* ── Student Dashboard — Chart section ─────────────────── */
.stu-chart-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: var(--card-shadow, 0 2px 12px rgba(0,0,0,0.08));
    margin-bottom: 2rem;
}
.stu-chart-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #1f2937;
    margin: 0 0 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.stu-chart-title-icon {
    width: 28px;
    height: 28px;
    background: #800020;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.stu-chart-empty {
    text-align: center;
    padding: 3rem;
    color: #9ca3af;
}
.stu-chart-empty i {
    font-size: 3rem;
    opacity: 0.25;
    display: block;
    margin-bottom: 1rem;
}
</style>
@endpush

@section('student-content')
@php $isFacultyPortal = ($portalType ?? 'student') === 'faculty'; @endphp
<header>
    <div class="header-title">
        <h1>{{ $isFacultyPortal ? 'Faculty Dashboard' : 'Dashboard' }}</h1>
        <p>{{ $isFacultyPortal ? 'Welcome, faculty voter. Here is your election and campus activity overview.' : "Welcome back! Here's what's happening on campus." }}</p>
    </div>
</header>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue"><i class="fa-solid fa-users"></i></div>
        <div>
            <h3>{{ $stats['total_candidates'] }}</h3>
            <p style="color:var(--text-muted); font-size:0.9rem;">Candidates</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-pink"><i class="fa-solid fa-calendar-check"></i></div>
        <div>
            <h3>{{ $stats['total_events'] }}</h3>
            <p style="color:var(--text-muted); font-size:0.9rem;">Upcoming Events</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green"><i class="fa-solid fa-check-double"></i></div>
        <div>
            <h3>{{ $stats['total_votes'] }}</h3>
            <p style="color:var(--text-muted); font-size:0.9rem;">Votes Cast</p>
        </div>
    </div>
</div>

<!-- Currently Leading — Bar Chart -->
<div class="stu-chart-section">
    <p class="stu-chart-title">
        <span class="stu-chart-title-icon">
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
        <div class="stu-chart-empty">
            <i class="fa-solid fa-chart-bar"></i>
            <p>No active election or no votes cast yet.</p>
        </div>
    @else
        <div style="position:relative;height:{{ max(200, $topCandidates->count() * 52) }}px;">
            <canvas id="stuLeadingChart"></canvas>
        </div>
    @endif
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
                    <div style="font-size: 0.8rem; color: var(--text-muted); display: flex; gap: 15px;">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@if($topCandidates->isNotEmpty())
<script>
(function () {
    var labels = @json($topCandidates->map(fn($c) => $c->first_name . ' ' . $c->last_name . ' (' . $c->position . ')'));
    var votes  = @json($topCandidates->pluck('votes_count'));
    var maxVote = Math.max.apply(null, votes.concat([1]));

    document.addEventListener('DOMContentLoaded', function () {
        var canvas = document.getElementById('stuLeadingChart');
        if (!canvas) return;
        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Votes',
                    data: votes,
                    backgroundColor: votes.map(function (v) {
                        return v === maxVote ? 'rgba(128,0,32,0.92)' : 'rgba(128,0,32,0.35)';
                    }),
                    borderColor: 'rgba(128,0,32,1)',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) { return ' ' + ctx.parsed.x + ' votes'; }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#6b7280', font: { size: 12 } },
                        grid: { color: '#f1f5f9' }
                    },
                    y: {
                        ticks: {
                            color: '#1f2937',
                            font: { size: 12, weight: '600' },
                            callback: function (val) {
                                var lbl = this.getLabelForValue(val);
                                return lbl.length > 32 ? lbl.substring(0, 32) + '\u2026' : lbl;
                            }
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    });
})();
</script>
@endif
@endpush
