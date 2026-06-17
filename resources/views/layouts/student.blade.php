@extends('layouts.app')

@section('title', 'Student Dashboard')

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

.student-notification-wrap {
    position: fixed;
    top: 1rem;
    right: 1.5rem;
    z-index: 220;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.student-notification-bell {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #334155;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.12);
    position: relative;
}

.student-notification-bell i {
    font-size: 1.12rem;
}

.student-notification-bell:hover {
    color: #800020;
    border-color: #800020;
}

.student-notification-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: #dc2626;
    color: #fff;
    font-size: 0.68rem;
    font-weight: 800;
    display: none;
    align-items: center;
    justify-content: center;
}

.student-notification-panel {
    width: 350px;
    max-width: calc(100vw - 2rem);
    max-height: 70vh;
    overflow: hidden;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 18px 42px rgba(15, 23, 42, 0.16);
    margin-top: 0.6rem;
    display: none;
}

.student-notification-panel.is-open {
    display: block;
}

.student-notification-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 0.85rem;
    border-bottom: 1px solid #f1f5f9;
}

.student-notification-header h4 {
    margin: 0;
    font-size: 0.92rem;
    color: #0f172a;
}

.student-notification-read-btn {
    border: none;
    background: none;
    color: #800020;
    font-size: 0.76rem;
    font-weight: 700;
    cursor: pointer;
}

.student-notification-list {
    max-height: calc(70vh - 52px);
    overflow-y: auto;
}

.student-notification-item {
    display: block;
    text-decoration: none;
    padding: 0.78rem 0.85rem;
    border-bottom: 1px solid #f8fafc;
    color: #1f2937;
}

.student-notification-item:hover {
    background: #fff7f9;
}

.student-notification-item.unread {
    background: #fff3f6;
}

.student-notification-message {
    font-size: 0.84rem;
    line-height: 1.35;
    color: #1f2937;
}

.student-notification-meta {
    margin-top: 0.35rem;
    font-size: 0.74rem;
    color: #64748b;
}

.student-notification-empty {
    padding: 1rem;
    text-align: center;
    color: #64748b;
    font-size: 0.84rem;
}

@media (max-width: 768px) {
    .student-notification-wrap {
        top: 0.7rem;
        right: 0.8rem;
    }

    .student-notification-panel {
        width: min(350px, calc(100vw - 1rem));
    }
}
</style>
@endpush

@section('content')
<nav class="sidebar">
    @php
        $studentPortalUser = auth()->guard('student')->user();
        $isFacultyPortalUser = false;
        if ($studentPortalUser && !empty($studentPortalUser->email)) {
            $isFacultyPortalUser = \App\Models\Staff::where('email', $studentPortalUser->email)
                ->where('is_department_head', false)
                ->where(function ($query) {
                    $query->whereNull('position')
                        ->orWhereRaw('LOWER(position) in (?, ?)', ['faculty', 'none']);
                })
                ->exists();
        }

        $studentDashboardRoute = $isFacultyPortalUser ? route('faculty.dashboard') : route('dashboard');
    @endphp
    <div class="logo">
        <img src="{{ asset('images/spc-logo.png') }}" alt="SPC Logo" style="width: 50px; height: 50px; margin-bottom: 0.5rem;">
        <span>SPC System</span>
    </div>
    <ul class="nav-links">
        <li class="nav-item">
            <a href="{{ $studentDashboardRoute }}" class="nav-link {{ request()->routeIs('dashboard') || request()->routeIs('faculty.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('voting') }}" class="nav-link {{ request()->routeIs('voting') ? 'active' : '' }}">
                <i class="fa-solid fa-square-poll-vertical"></i> Voting
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('student.votes-status') }}" class="nav-link {{ request()->routeIs('student.votes-status') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i> Votes Status
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('events') }}" class="nav-link {{ request()->routeIs('events') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days"></i> Events
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('voting.history') }}" class="nav-link {{ request()->routeIs('voting.history') ? 'active' : '' }}">
                <i class="fa-solid fa-clock-rotate-left"></i> Vote History
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('student.candidate-applications') }}" class="nav-link {{ request()->routeIs('student.candidate-applications') ? 'active' : '' }}">
                <i class="fa-solid fa-file-signature"></i> Candidate Application
            </a>
        </li>
    </ul>
    <div class="user-profile">
        @php $studentUser = $studentPortalUser; @endphp

        {{-- Drop-up popup --}}
        <div class="profile-dropup">
            <a href="{{ route('student.profile') }}" class="profile-dropup-btn">
                <i class="fa-solid fa-user-pen"></i> Edit Profile
            </a>
        </div>

        <div class="avatar" style="overflow: hidden; padding: 0;">
            @if($studentUser->profile_picture)
                <img src="{{ $studentUser->profile_picture }}" alt="{{ $studentUser->name }}"
                     style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
            @else
                {{ substr($studentUser->name, 0, 1) }}
            @endif
        </div>
        <div style="flex: 1; min-width: 0;">
            <h4 style="font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $studentUser->name }}
            </h4>
            <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $studentUser->department }} Dept</span>
        </div>
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: var(--text-muted); font-size: 1.2rem;" title="Logout">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</nav>

<main class="main-content">
    <div class="student-notification-wrap" id="studentNotificationWrap">
        <button type="button" id="studentNotificationBell" class="student-notification-bell" title="Notifications">
            <i class="fa-regular fa-bell"></i>
            <span id="studentNotificationBadge" class="student-notification-badge">0</span>
        </button>
        <div id="studentNotificationPanel" class="student-notification-panel">
            <div class="student-notification-header">
                <h4>Notifications</h4>
                <button type="button" id="studentNotificationReadAll" class="student-notification-read-btn">Mark all as read</button>
            </div>
            <div id="studentNotificationList" class="student-notification-list">
                <div class="student-notification-empty">Loading notifications...</div>
            </div>
        </div>
    </div>

    @yield('student-content')
</main>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

@push('scripts')
<script>
(function () {
    var bell = document.getElementById('studentNotificationBell');
    var panel = document.getElementById('studentNotificationPanel');
    var list = document.getElementById('studentNotificationList');
    var badge = document.getElementById('studentNotificationBadge');
    var readAllBtn = document.getElementById('studentNotificationReadAll');
    var wrap = document.getElementById('studentNotificationWrap');

    if (!bell || !panel || !list || !badge || !readAllBtn || !wrap) {
        return;
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderNotifications(payload) {
        var unreadCount = Number(payload.unread_count || 0);
        var notifications = Array.isArray(payload.notifications) ? payload.notifications : [];

        if (unreadCount > 0) {
            badge.style.display = 'inline-flex';
            badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
        } else {
            badge.style.display = 'none';
        }

        if (notifications.length === 0) {
            list.innerHTML = '<div class="student-notification-empty">No notifications yet.</div>';
            return;
        }

        list.innerHTML = notifications.map(function (item) {
            var href = item.url ? item.url : 'javascript:void(0)';
            var unreadClass = item.is_read ? '' : ' unread';
            return '<a class="student-notification-item' + unreadClass + '" href="' + escapeHtml(href) + '">'
                + '<div class="student-notification-message">' + escapeHtml(item.message || 'New notification') + '</div>'
                + '<div class="student-notification-meta">' + escapeHtml(item.created_at_human || '') + '</div>'
                + '</a>';
        }).join('');
    }

    function fetchNotifications() {
        fetch('{{ route('student.notifications.index') }}', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data && data.success) {
                renderNotifications(data);
            }
        })
        .catch(function (err) {
            console.error('Student notifications fetch error:', err);
        });
    }

    bell.addEventListener('click', function () {
        panel.classList.toggle('is-open');
    });

    document.addEventListener('click', function (event) {
        if (!wrap.contains(event.target)) {
            panel.classList.remove('is-open');
        }
    });

    readAllBtn.addEventListener('click', function () {
        fetch('{{ route('student.notifications.mark-all-read') }}', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': (typeof csrfToken !== 'undefined' ? csrfToken : '')
            }
        })
        .then(function (res) { return res.json(); })
        .then(function () {
            fetchNotifications();
        })
        .catch(function (err) {
            console.error('Mark notifications read error:', err);
        });
    });

    function refreshNotifications() {
        fetchNotifications();
    }

    fetchNotifications();
    window.setInterval(refreshNotifications, 10000);
    window.addEventListener('focus', refreshNotifications);
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            refreshNotifications();
        }
    });
})();
</script>
@endpush
@endsection
