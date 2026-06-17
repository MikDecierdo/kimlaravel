@extends('layouts.department-head')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/shared.css') }}">
<link rel="stylesheet" href="{{ asset('assets/dept-head/css/events.css') }}">
@endpush

@section('dept-head-content')
@php
    $actor = auth()->user();
    $canPostEvents = $actor->hasDepartmentPortalPermission('post_events');
    $actorDisplayName = trim(($actor->name ?? '') . (!empty($actor->last_name) ? (' ' . $actor->last_name) : ''));
    $actorDisplayName = $actorDisplayName !== '' ? $actorDisplayName : 'Unknown User';
    $actorRoleLabel = (bool) ($actor->is_department_head ?? false) ? 'Department Head' : 'Faculty Access';
    $actorInitial = strtoupper(substr($actorDisplayName, 0, 1));
@endphp

<header>
    <div class="header-title">
        <h1>Manage Events</h1>
        <p>Create and manage events for {{ $scopeLabel ?? ($department . ' department') }} &bull; <span id="eventCount">{{ $events->count() }}</span> Events</p>
    </div>
    @if($canPostEvents)
        <button class="btn-primary btn-hover" onclick="openAddEventModal()">
            <i class="fa-solid fa-plus"></i> Add Post Event
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
            <input type="date" id="evtDateFrom" class="filter-input">
        </div>
        <div class="filter-group">
            <label>Date To</label>
            <input type="date" id="evtDateTo" class="filter-input">
        </div>
        <div class="filter-group">
            <label>Most Reactions</label>
            <select id="evtSortReactions" class="filter-select">
                <option value="">Default Order</option>
                <option value="like">Most Liked</option>
                <option value="haha">Most Haha</option>
                <option value="love">Most Love (Heart)</option>
                <option value="total">Most Total Reactions</option>
            </select>
        </div>
        <div class="filter-actions">
            <button onclick="evtApplyFilters()" class="btn-apply">Apply Filters</button>
            <button onclick="evtResetFilters()" class="btn-reset">Reset</button>
        </div>
    </div>
    <div class="evt-search-wrapper">
        <label style="font-size:0.8rem;font-weight:600;color:#1f2937;">Search</label>
        <input type="text" id="evtSearch" class="evt-search-input" placeholder="Search events..." oninput="evtApplyFilters()">
    </div>
</div>

<div class="events-table-container">
    <table id="eventsTable">
        <thead>
            <tr>
                <th onclick="evtSortTable(0)">NO. <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="evtSortTable(1)">TITLE <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="evtSortTable(2)">DATE <span class="sort-icon">&#x21C5;</span></th>
                <th class="no-sort">DESCRIPTION</th>
                <th onclick="evtSortTable(4)" style="white-space:nowrap;">LAST EDITED <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="evtSortTable(5)" style="text-align:center;"><i class="fa-solid fa-thumbs-up" style="color:rgba(255,255,255,0.85);"></i> LIKE <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="evtSortTable(6)" style="text-align:center;"><i class="fa-solid fa-face-laugh" style="color:rgba(255,255,255,0.85);"></i> HAHA <span class="sort-icon">&#x21C5;</span></th>
                <th onclick="evtSortTable(7)" style="text-align:center;"><i class="fa-solid fa-heart" style="color:rgba(255,255,255,0.85);"></i> HEART <span class="sort-icon">&#x21C5;</span></th>
                <th class="no-sort">ACTIONS</th>
            </tr>
        </thead>
        <tbody id="eventsTbody">
            @forelse($events as $event)
                @php
                    $reactionCounts = $event->likes->groupBy('reaction_type')->map->count();
                    $likeCount  = $reactionCounts->get('like', 0);
                    $hahaCount  = $reactionCounts->get('haha', 0);
                    $loveCount  = $reactionCounts->get('love', 0);
                    $totalReact = $likeCount + $hahaCount + $loveCount;

                    $poster = $event->staff;
                    $posterName = $poster
                        ? trim(($poster->name ?? '') . (!empty($poster->last_name) ? (' ' . $poster->last_name) : ''))
                        : 'Unknown Poster';
                    $posterName = $posterName !== '' ? $posterName : 'Unknown Poster';

                    if ($poster) {
                        $posterRoleLabel = (bool) ($poster->is_department_head ?? false)
                            ? 'Department Head'
                            : 'Faculty Access';
                        $posterRoleBadgeClass = (bool) ($poster->is_department_head ?? false)
                            ? 'background:#800020;color:#fff;'
                            : 'background:#0f766e;color:#fff;';
                    } else {
                        $posterRoleLabel = 'Legacy Post';
                        $posterRoleBadgeClass = 'background:#64748b;color:#fff;';
                    }

                    $canManageEvent = $canPostEvents && (bool) ($event->can_manage_event ?? false);
                @endphp
                <tr data-no="{{ $loop->iteration }}"
                    data-title="{{ strtolower($event->title) }}"
                    data-date="{{ $event->event_date }}"
                    data-updated="{{ $event->updated_at ? $event->updated_at->toDateString() : '' }}"
                    data-like="{{ $likeCount }}"
                    data-haha="{{ $hahaCount }}"
                    data-love="{{ $loveCount }}"
                    data-total="{{ $totalReact }}"
                >
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <div style="font-weight:600; color:#1f2937;">{{ $event->title }}</div>
                        <div style="margin-top:4px; font-size:0.76rem; color:#64748b; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <span>Posted by {{ $posterName }}</span>
                            <span style="display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:999px; font-size:0.68rem; font-weight:700; {{ $posterRoleBadgeClass }}">
                                <i class="fa-solid fa-user-tag" style="font-size:0.62rem;"></i>
                                {{ $posterRoleLabel }}
                            </span>
                        </div>
                    </td>
                    <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</td>
                    <td>{{ Str::limit($event->description, 50) }}</td>
                    <td style="white-space:nowrap; color:#374151;">
                        @if($event->updated_at && $event->updated_at->ne($event->created_at))
                            {{ $event->updated_at->format('M d, Y') }}<br>
                            <span style="font-size:0.75rem;color:#9ca3af;">{{ $event->updated_at->format('h:i A') }}</span>
                        @else
                            <span style="color:#9ca3af;font-size:0.82rem;">Not yet edited</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <span style="color: #1877f2; font-weight: 700;">{{ $likeCount }}</span>
                    </td>
                    <td style="text-align: center;">
                        <span style="color: #f7b125; font-weight: 700;">{{ $hahaCount }}</span>
                    </td>
                    <td style="text-align: center;">
                        <span style="color: #f33e58; font-weight: 700;">{{ $loveCount }}</span>
                    </td>
                    <td>
                        @if($canManageEvent)
                        <button class="btn-tbl-update evt-edit-btn"
                            data-id="{{ $event->id }}"
                            data-title="{{ e($event->title) }}"
                            data-department="{{ e($event->department) }}"
                            data-date="{{ $event->event_date }}"
                            data-description="{{ e($event->description) }}"
                            data-image="{{ $event->image ? asset('storage/' . $event->image) : '' }}"
                        ><i class="fa-solid fa-edit"></i> Edit</button>
                        <button class="btn-tbl-delete evt-delete-btn" data-id="{{ $event->id }}" data-title="{{ e($event->title) }}"
                        ><i class="fa-solid fa-trash"></i> Delete</button>
                        @else
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:#f1f5f9;color:#334155;font-size:0.78rem;font-weight:700;">
                            <i class="fa-solid fa-globe"></i> View Only
                        </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr class="evt-empty-row">
                    <td colspan="9" style="padding: 2rem; text-align: center; color: #888;">
                        <i class="fa-solid fa-calendar-days" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3; display: block;"></i>
                        No events yet. Create your first event!
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add Event Modal - Facebook Style -->
<div id="addEventModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create Event</h2>
            <span class="close" onclick="closeAddEventModal()">&times;</span>
        </div>
        
        <form id="addEventForm" enctype="multipart/form-data">
            @csrf
            
            <!-- User Info Section -->
            <div class="user-info">
                <div class="user-avatar">{{ $actorInitial }}</div>
                <div class="user-details">
                    <h4>{{ $actorDisplayName }}</h4>
                    <div class="visibility">
                        <i class="fa-solid fa-earth-americas"></i>
                        <span>{{ $actorRoleLabel }} &bull; {{ $department }} Students</span>
                    </div>
                </div>
            </div>
            
            <!-- Hidden Inputs -->
            <div class="hidden-inputs">
                <input type="text" name="department" value="{{ $department }}" readonly>
                <input type="date" name="event_date" id="event_date_hidden" required>
                <input type="file" name="image" id="imageInput" accept="image/*">
            </div>
            
            <!-- Post Input Area -->
            <div class="post-input-area">
                <textarea 
                    name="title" 
                    id="eventTitle"
                    placeholder="Event title..."
                    maxlength="100"
                    required
                    style="font-size: 1rem; min-height: 50px; font-weight: 600;"
                ></textarea>
                <textarea 
                    name="description" 
                    id="eventDescription"
                    placeholder="What's this event about?"
                    required
                ></textarea>
            </div>
            
            <!-- Image Preview -->
            <div id="imagePreviewContainer" class="image-preview-container">
                <img id="imagePreview" class="image-preview" src="" alt="Preview">
                <button type="button" class="remove-image" onclick="removeImage()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <!-- Add to Post Section -->
            <div class="add-to-post">
                <span>Add to your event</span>
                <div class="add-options">
                    <button type="button" class="add-option" onclick="document.getElementById('imageInput').click()" title="Add photo">
                        <i class="fa-solid fa-images" style="color: #45bd62;"></i>
                    </button>
                    <button type="button" class="add-option" onclick="showDatePicker()" title="Set event date">
                        <i class="fa-solid fa-calendar" style="color: #f3425f;"></i>
                    </button>
                </div>
            </div>
            
            <!-- Post Button -->
            <button type="submit" class="post-button" id="postButton">Post Event</button>
        </form>
    </div>
</div>

<!-- Edit Event Modal -->
<div id="editEventModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Event</h2>
            <span class="close" onclick="closeEditEventModal()">&times;</span>
        </div>

        <!-- Hidden fields (not inside a <form> that could submit natively) -->
        <input type="hidden" id="edit_event_id">

        <!-- User Info -->
        <div class="user-info">
            <div class="user-avatar">{{ $actorInitial }}</div>
            <div class="user-details">
                <h4>{{ $actorDisplayName }}</h4>
                <div class="visibility">
                    <i class="fa-solid fa-earth-americas"></i>
                    <span>{{ $actorRoleLabel }} &bull; {{ $department }} Students</span>
                </div>
            </div>
        </div>

        <!-- Fields -->
        <div class="hidden-inputs">
            <input type="text"  id="edit_department" value="{{ $department }}" readonly style="display:none;">
            <input type="date"  id="edit_event_date" required>
            <input type="file"  id="editImageInput" accept="image/*">
        </div>

        <div class="post-input-area">
            <textarea id="edit_title"
                placeholder="Event title..."
                maxlength="100"
                style="font-size:1rem;min-height:50px;font-weight:600;"
            ></textarea>
            <textarea id="edit_description"
                placeholder="What's this event about?"
            ></textarea>
        </div>

        <!-- Image Preview -->
        <div id="editImagePreviewContainer" class="image-preview-container">
            <img id="editImagePreview" class="image-preview" src="" alt="Preview">
            <button type="button" class="remove-image" onclick="removeEditImage()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Add-to-post options -->
        <div class="add-to-post">
            <span>Add to your event</span>
            <div class="add-options">
                <button type="button" class="add-option" onclick="document.getElementById('editImageInput').click()" title="Add photo">
                    <i class="fa-solid fa-images" style="color:#45bd62;"></i>
                </button>
                <button type="button" class="add-option" onclick="showEditDatePicker()" title="Set event date">
                    <i class="fa-solid fa-calendar" style="color:#f3425f;"></i>
                </button>
            </div>
        </div>

        <!-- Update Button (type=button, calls JS directly — no form submit event) -->
        <button type="button" class="post-button" id="updateButton" onclick="doUpdateEvent()">Update post</button>
    </div>
</div>

@push('scripts')
<script>
var updateUrlTemplate = '{{ route("department-head.events.update", ["event" => "__ID__"]) }}';
var storeUrl = '{{ route("department-head.events.store") }}';
</script>
<script src="{{ asset('assets/dept-head/js/events.js') }}"></script>
@endpush
@endsection
