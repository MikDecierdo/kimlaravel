@extends('layouts.student')

@section('title', 'Events')

@section('student-content')
@php $student = auth()->user(); @endphp

<style>
/* ─── Layout ──────────────────────────────────────────── */
.ev-page { max-width: 660px; margin: 0 auto; padding: 0 0 2rem; }

/* ─── Feed header ─────────────────────────────────────── */
.ev-head { margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 2px solid #e4e6eb; }
.ev-head h1 { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0 0 0.15rem; }
.ev-head p  { font-size: 0.88rem; color: #64748b; margin: 0; }

/* ─── Card ────────────────────────────────────────────── */
.ev-card {
    background: #fff; border-radius: 12px; margin-bottom: 1.25rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.1), 0 0 0 1px rgba(0,0,0,.04);
}

/* ─── Card header ─────────────────────────────────────── */
.ev-card-head { padding: 14px 16px 8px; display: flex; align-items: flex-start; gap: 10px; }
.ev-avatar {
    width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg,#800020,#A0153E);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 700; font-size: 1.1rem;
    overflow: hidden; border: 2px solid #f1f5f9;
}
.ev-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ev-author-name {
    font-size: 0.94rem; font-weight: 700; color: #050505; margin: 0 0 3px;
    display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
}
.ev-badge {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 2px 8px; border-radius: 20px; font-size: 0.68rem;
    font-weight: 700; background: #800020; color: #fff;
}
.ev-meta { font-size: 0.77rem; color: #65676b; display: flex; align-items: center; gap: 4px; }
.ev-meta i { font-size: 0.68rem; }

/* ─── Date chip ───────────────────────────────────────── */
.ev-chip {
    display: inline-flex; align-items: center; gap: 5px; margin: 0 16px 10px;
    background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
    font-size: 0.74rem; font-weight: 600; padding: 3px 10px; border-radius: 20px;
}

/* ─── Content ─────────────────────────────────────────── */
.ev-body { padding: 0 16px 12px; }
.ev-title { font-size: 1.05rem; font-weight: 700; color: #050505; margin: 0 0 5px; line-height: 1.35; }
.ev-desc  { font-size: 0.94rem; color: #1c1e21; line-height: 1.6; margin: 0; white-space: pre-wrap; word-break: break-word; }

/* ─── Image ───────────────────────────────────────────── */
.ev-img-wrap { position: relative; cursor: pointer; background: #f0f2f5; max-height: 500px; overflow: hidden; }
.ev-img-wrap img { width: 100%; display: block; object-fit: cover; max-height: 500px; transition: opacity .2s; }
.ev-img-wrap:hover img { opacity: .93; }
.ev-img-overlay {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,.45));
    color: #fff; font-size: 0.8rem; font-weight: 600;
    padding: 18px 12px 10px; opacity: 0; transition: opacity .2s;
    display: flex; align-items: center; gap: 6px;
}
.ev-img-wrap:hover .ev-img-overlay { opacity: 1; }

/* ─── Stats bar ───────────────────────────────────────── */
.ev-stats {
    padding: 6px 16px; display: flex; align-items: center;
    justify-content: space-between; font-size: 0.83rem; color: #65676b;
}
.ev-rxn-icons { display: inline-flex; align-items: center; gap: 2px; }
.ev-rxn-dot {
    width: 20px; height: 20px; border-radius: 50%; border: 2px solid #fff;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .65rem; margin-left: -4px;
}
.ev-rxn-dot:first-child { margin-left: 0; }
.ev-rxn-count { margin-left: 5px; cursor: pointer; }
.ev-cmt-count { cursor: pointer; }
.ev-cmt-count:hover, .ev-rxn-count:hover { text-decoration: underline; }

/* ─── Divider ─────────────────────────────────────────── */
.ev-divider { border: none; border-top: 1px solid #e4e6eb; margin: 0 16px; }

/* ─── Action row ──────────────────────────────────────── */
.ev-actions { display: flex; padding: 4px 8px; }
.ev-act-wrap { position: relative; flex: 1; }
.ev-act {
    width: 100%; display: flex; align-items: center; justify-content: center;
    gap: 6px; padding: 8px 4px; border: none; background: none; cursor: pointer;
    border-radius: 8px; font-size: 0.88rem; font-weight: 600; color: #65676b;
    transition: background .15s, color .15s;
}
.ev-act:hover { background: #f2f3f5; color: #050505; }
.ev-act.reacted-like   { color: #1877f2; }
.ev-act.reacted-haha   { color: #f7b125; }
.ev-act.reacted-love   { color: #f33e58; }
.ev-act i { font-size: 1.05rem; }

/* ─── Reaction picker ─────────────────────────────────── */
.ev-rxn-picker {
    display: none; position: absolute; bottom: calc(100% + 6px); left: 50%;
    transform: translateX(-50%); background: #fff; border-radius: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,.2); padding: 8px 12px;
    gap: 10px; z-index: 100; white-space: nowrap;
}
.ev-rxn-picker.open { display: flex; animation: rxnPop .15s ease; }
@keyframes rxnPop { from { opacity:0; transform: translateX(-50%) scale(.8); } to { opacity:1; transform: translateX(-50%) scale(1); } }
.ev-rxn-opt {
    font-size: 1.55rem; cursor: pointer; transition: transform .15s;
    display: flex; flex-direction: column; align-items: center; gap: 2px;
}
.ev-rxn-opt span { font-size: .6rem; font-weight: 700; color: #65676b; }
.ev-rxn-opt:hover { transform: scale(1.3) translateY(-4px); }
.ev-rxn-opt:hover span { color: #050505; }

/* ─── Comments section ────────────────────────────────── */
.ev-comments { padding: 8px 16px 12px; border-top: 1px solid #e4e6eb; display: none; }
.ev-cmt-item { display: flex; gap: 8px; margin-bottom: 10px; }
.ev-cmt-avatar {
    width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg,#800020,#A0153E);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 700; font-size: .8rem;
}
.ev-cmt-bubble { background: #f0f2f5; border-radius: 16px; padding: 7px 12px; max-width: 100%; }
.ev-cmt-author { font-size: .8rem; font-weight: 700; color: #050505; margin: 0 0 2px; }
.ev-cmt-text   { font-size: .88rem; color: #1c1e21; margin: 0; word-break: break-word; }
.ev-cmt-time   { font-size: .72rem; color: #65676b; margin-top: 3px; padding-left: 4px; }

.ev-cmt-input-row { display: flex; gap: 8px; align-items: center; margin-top: 8px; }
.ev-cmt-input {
    flex: 1; background: #f0f2f5; border: none; border-radius: 20px;
    padding: 9px 16px; font-size: .88rem; outline: none; color: #050505;
    font-family: inherit;
}
.ev-cmt-input::placeholder { color: #bcc0c4; }
.ev-cmt-send {
    width: 34px; height: 34px; border-radius: 50%; border: none;
    background: #800020; color: #fff; cursor: pointer; font-size: .9rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: background .2s;
}
.ev-cmt-send:hover { background: #5c0015; }

/* ─── Image lightbox ──────────────────────────────────── */
.ev-lightbox {
    display: none; position: fixed; inset: 0; z-index: 9990;
    background: rgba(0,0,0,.85); align-items: center; justify-content: center;
}
.ev-lightbox.open { display: flex; animation: lbFade .2s; }
@keyframes lbFade { from { opacity:0; } to { opacity:1; } }
.ev-lightbox-close {
    position: fixed; top: 18px; right: 24px; font-size: 2rem; color: #e4e6eb;
    cursor: pointer; line-height: 1; background: none; border: none; z-index: 9995;
}
.ev-lightbox-close:hover { color: #fff; }
.ev-lightbox-inner {
    display: flex; max-width: 92vw; max-height: 90vh;
    background: #242526; border-radius: 12px; overflow: hidden;
    box-shadow: 0 8px 48px rgba(0,0,0,.6);
}
.ev-lightbox-img { max-width: 72vw; max-height: 90vh; object-fit: contain; background: #18191a; display: block; }
.ev-lightbox-cap { flex: 1; padding: 22px; color: #e4e6eb; overflow-y: auto; min-width: 200px; max-width: 340px; }
.ev-lightbox-cap h3 { font-size: .94rem; font-weight: 700; margin: 0 0 4px; }
.ev-lightbox-cap .ev-lb-sub { font-size: .78rem; color: #b0b3b8; margin: 0 0 14px; }
.ev-lightbox-cap h2 { font-size: 1.1rem; font-weight: 700; margin: 0 0 8px; }
.ev-lightbox-cap p  { font-size: .88rem; color: #b0b3b8; line-height: 1.6; margin: 0; }
@media (max-width: 680px) {
    .ev-lightbox-inner { flex-direction: column; max-width: 97vw; }
    .ev-lightbox-img   { max-width: 100%; }
    .ev-lightbox-cap   { max-width: 100%; }
}

/* ─── Share sheet ─────────────────────────────────────── */
.ev-share-overlay {
    display: none; position: fixed; inset: 0; z-index: 9991;
    background: rgba(0,0,0,.5); align-items: flex-end; justify-content: center;
}
.ev-share-overlay.open { display: flex; }
.ev-share-sheet {
    background: #fff; border-radius: 20px 20px 0 0; padding: 1.4rem 1.4rem 2rem;
    width: 100%; max-width: 640px; animation: shareUp .25s;
}
@keyframes shareUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
.ev-share-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: .3rem; }
.ev-share-top h3 { font-size: 1rem; font-weight: 700; margin: 0; color: #1e293b; }
.ev-share-close { background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #9ca3af; line-height: 1; }
.ev-share-close:hover { color: #374151; }
.ev-share-sub   { font-size: .82rem; color: #64748b; margin: .1rem 0 1.1rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ev-share-btns  { display: grid; grid-template-columns: repeat(4,1fr); gap: .75rem; margin-bottom: 1.2rem; }
.ev-share-btn {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    padding: .8rem .3rem; border-radius: 12px; border: none; cursor: pointer;
    color: #fff; font-size: .75rem; font-weight: 700; transition: transform .15s, opacity .15s;
}
.ev-share-btn:hover { transform: translateY(-3px); opacity: .9; }
.ev-share-btn i { font-size: 1.5rem; }
.ev-copy-row    { display: flex; gap: .5rem; }
.ev-copy-input  { flex: 1; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: .5rem .75rem; font-size: .82rem; color: #374151; outline: none; background: #f9fafb; font-family: inherit; }
.ev-copy-btn    { padding: .5rem 1rem; background: #800020; color: #fff; border: none; border-radius: 8px; font-weight: 700; font-size: .82rem; cursor: pointer; white-space: nowrap; }
.ev-copy-btn:hover { background: #5c0015; }

/* ─── Empty state ─────────────────────────────────────── */
.ev-empty { text-align: center; padding: 4rem 1rem; color: #94a3b8; }
.ev-empty i { font-size: 3rem; margin-bottom: 1rem; display: block; }
.ev-empty h3 { font-size: 1.1rem; font-weight: 700; color: #64748b; margin: 0 0 .4rem; }
.ev-empty p  { font-size: .88rem; margin: 0; }
</style>

<div class="ev-page">

    {{-- Page heading --}}
    <div class="ev-head">
        <h1><i class="fa-solid fa-calendar-star" style="color:#800020;margin-right:.4rem;"></i>{{ $student->department ?? '' }} &amp; CSG Events</h1>
        <p>Department Head announcements &amp; CSG global event posts</p>
    </div>

    @forelse($events as $event)
    @php
        /* poster info */
        $poster      = $event->staff;
        $posterName  = $poster
            ? trim(($poster->name ?? '') . ($poster->last_name ? ' '.$poster->last_name : ''))
            : ($event->department . ' Event Post');
        $posterPhoto = ($poster && $poster->profile_picture) ? $poster->profile_picture : null;
        $posterInit  = strtoupper(substr($posterName, 0, 1));

        if ($poster) {
            $isFacultyAccessPoster = !(bool) ($poster->is_department_head ?? false);
            $posterBadgeLabel = $isFacultyAccessPoster ? 'Faculty Access' : 'Department Head';
            $posterBadgeIcon  = $isFacultyAccessPoster ? 'fa-user-shield' : 'fa-chalkboard-user';
            $posterBadgeStyle = $isFacultyAccessPoster
                ? 'background:#0f766e;color:#fff;'
                : 'background:#800020;color:#fff;';
        } else {
            $posterBadgeLabel = 'Department Post';
            $posterBadgeIcon  = 'fa-bullhorn';
            $posterBadgeStyle = 'background:#64748b;color:#fff;';
        }

        /* reactions */
        $rxnCounts   = $event->likes->groupBy('reaction_type')->map->count();
        $rxnTotal    = $event->likes->count();
        $myReaction  = $event->userReaction($student->id ?? 0);
        /* routes */
        $likeUrl     = route('events.like',    $event->id);
        $commentUrl  = route('events.comment', $event->id);
    @endphp

    <div class="ev-card" id="ev-card-{{ $event->id }}">

        {{-- Header --}}
        <div class="ev-card-head">
            <div class="ev-avatar">
                @if($posterPhoto)
                    <img src="{{ $posterPhoto }}" alt="{{ $posterName }}"
                         onerror="this.style.display='none'">
                @else
                    {{ $posterInit }}
                @endif
            </div>
            <div>
                <div class="ev-author-name">
                    {{ $posterName }}
                    <span class="ev-badge" style="{{ $posterBadgeStyle }}"><i class="fa-solid {{ $posterBadgeIcon }}" style="font-size:.6rem;"></i> {{ $posterBadgeLabel }}</span>
                </div>
                <div class="ev-meta">
                    <i class="fa-solid fa-globe"></i>
                    <span>{{ $event->created_at->diffForHumans() }}</span>
                    <span>&bull;</span>
                    <span>{{ $event->department }} Dept.</span>
                </div>
            </div>
        </div>

        {{-- Date chip --}}
        <div class="ev-chip">
            <i class="fa-regular fa-calendar-check"></i>
            Event date: {{ $event->event_date->format('M d, Y') }}
        </div>

        {{-- Body --}}
        <div class="ev-body">
            <h2 class="ev-title">{{ $event->title }}</h2>
            <p class="ev-desc">{{ $event->description }}</p>
        </div>

        {{-- Image --}}
        @if($event->image)
        <div class="ev-img-wrap"
             onclick="openLightbox(
                 '{{ asset('storage/'.$event->image) }}',
                 '{{ e($posterName) }}',
                 '{{ e($event->created_at->diffForHumans()) }}',
                 '{{ e($event->title) }}',
                 '{{ e(Str::limit($event->description, 300)) }}'
             )">
            <img src="{{ asset('storage/'.$event->image) }}" alt="{{ $event->title }}" loading="lazy">
            <div class="ev-img-overlay"><i class="fa-solid fa-expand"></i> View full image</div>
        </div>
        @endif

        {{-- Reaction / comment counts --}}
        <div class="ev-stats">
            <div class="ev-rxn-icons" id="rxn-display-{{ $event->id }}">
                @if($rxnTotal > 0)
                    @if($rxnCounts->get('like',0) > 0)<span class="ev-rxn-dot" style="background:#1877f2;"><i class="fa-solid fa-thumbs-up" style="color:#fff;font-size:.55rem;"></i></span>@endif
                    @if($rxnCounts->get('haha',0) > 0)<span class="ev-rxn-dot" style="background:#f7b125;"><i class="fa-solid fa-face-laugh" style="color:#fff;font-size:.55rem;"></i></span>@endif
                    @if($rxnCounts->get('love',0) > 0)<span class="ev-rxn-dot" style="background:#f33e58;"><i class="fa-solid fa-heart" style="color:#fff;font-size:.55rem;"></i></span>@endif
                    <span class="ev-rxn-count">{{ $rxnTotal }}</span>
                @endif
            </div>
            <div class="ev-cmt-count" onclick="toggleComments({{ $event->id }})">
                @if($event->comments_count > 0)
                    {{ $event->comments_count }} {{ Str::plural('comment', $event->comments_count) }}
                @endif
            </div>
        </div>

        <hr class="ev-divider">

        {{-- Action buttons --}}
        <div class="ev-actions">

            {{-- Reaction button + picker --}}
            <div class="ev-act-wrap">
                <button class="ev-act {{ $myReaction ? 'reacted-'.$myReaction : '' }}"
                        id="rxn-btn-{{ $event->id }}"
                        data-event-id="{{ $event->id }}"
                        data-url="{{ $likeUrl }}"
                        data-reaction="{{ $myReaction }}"
                        onmouseenter="openPicker({{ $event->id }})"
                        onmouseleave="schedulePicker({{ $event->id }})"
                        onclick="quickReact({{ $event->id }})">
                    @if($myReaction === 'like')
                        <i class="fa-solid fa-thumbs-up"></i><span>Like</span>
                    @elseif($myReaction === 'haha')
                        <i class="fa-solid fa-face-laugh"></i><span>Haha</span>
                    @elseif($myReaction === 'love')
                        <i class="fa-solid fa-heart"></i><span>Love</span>
                    @else
                        <i class="fa-regular fa-thumbs-up"></i><span>Like</span>
                    @endif
                </button>
                <div class="ev-rxn-picker" id="picker-{{ $event->id }}"
                     onmouseenter="clearPickerTimer()"
                     onmouseleave="schedulePicker({{ $event->id }})">
                    <div class="ev-rxn-opt" onclick="pickReact({{ $event->id }},'like')">👍<span>Like</span></div>
                    <div class="ev-rxn-opt" onclick="pickReact({{ $event->id }},'haha')">😂<span>Haha</span></div>
                    <div class="ev-rxn-opt" onclick="pickReact({{ $event->id }},'love')">❤️<span>Love</span></div>
                </div>
            </div>

            {{-- Comment --}}
            <button class="ev-act" onclick="toggleComments({{ $event->id }})">
                <i class="fa-regular fa-comment"></i><span>Comment</span>
            </button>

            {{-- Share --}}
            <button class="ev-act" onclick="openShare({{ $event->id }}, '{{ e($event->title) }}')">
                <i class="fa-solid fa-share"></i><span>Share</span>
            </button>

        </div>

        {{-- Comments section --}}
        <div class="ev-comments" id="comments-{{ $event->id }}">

            {{-- Existing comments --}}
            <div id="cmt-list-{{ $event->id }}">
                @foreach($event->comments as $c)
                <div class="ev-cmt-item">
                    <div class="ev-cmt-avatar">{{ strtoupper(substr($c->user?->name ?? '?', 0, 1)) }}</div>
                    <div>
                        <div class="ev-cmt-bubble">
                            <p class="ev-cmt-author">{{ $c->user?->name ?? 'Unknown' }}</p>
                            <p class="ev-cmt-text">{{ $c->comment }}</p>
                        </div>
                        <div class="ev-cmt-time">{{ $c->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Input row --}}
            <div class="ev-cmt-input-row">
                <div class="ev-cmt-avatar">{{ strtoupper(substr($student->name ?? '?', 0, 1)) }}</div>
                <input  class="ev-cmt-input"
                        id="cmt-input-{{ $event->id }}"
                        data-url="{{ $commentUrl }}"
                        data-event-id="{{ $event->id }}"
                        placeholder="Write a comment…"
                        onkeydown="if(event.key==='Enter'){event.preventDefault();postComment({{ $event->id }});}">
                <button class="ev-cmt-send" onclick="postComment({{ $event->id }})">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>

    </div>{{-- /.ev-card --}}
    @empty
    <div class="ev-empty">
        <i class="fa-solid fa-calendar-xmark"></i>
        <h3>No events posted yet</h3>
        <p>Your department has no event posts yet. Check back soon!</p>
    </div>
    @endforelse

</div>{{-- /.ev-page --}}

{{-- ─── Image Lightbox ─────────────────────────────────── --}}
<div class="ev-lightbox" id="ev-lightbox" onclick="closeLightbox()">
    <button class="ev-lightbox-close" onclick="closeLightbox()">&times;</button>
    <div class="ev-lightbox-inner" onclick="event.stopPropagation()">
        <img class="ev-lightbox-img" id="lb-img" src="" alt="">
        <div class="ev-lightbox-cap">
            <h3 id="lb-author"></h3>
            <p class="ev-lb-sub" id="lb-time"></p>
            <h2 id="lb-title"></h2>
            <p id="lb-desc"></p>
        </div>
    </div>
</div>

{{-- ─── Share Sheet ─────────────────────────────────────── --}}
<div class="ev-share-overlay" id="ev-share-overlay" onclick="closeShare()">
    <div class="ev-share-sheet" onclick="event.stopPropagation()">
        <div class="ev-share-top">
            <h3><i class="fa-solid fa-share-nodes" style="margin-right:6px;"></i>Share Post</h3>
            <button class="ev-share-close" onclick="closeShare()">&times;</button>
        </div>
        <p class="ev-share-sub" id="share-title-text"></p>
        <div class="ev-share-btns">
            <button class="ev-share-btn" style="background:#1877f2;" onclick="doShare('facebook')">
                <i class="fa-brands fa-facebook-f"></i>Facebook
            </button>
            <button class="ev-share-btn" style="background:linear-gradient(135deg,#0099ff,#a033ff);" onclick="doShare('messenger')">
                <i class="fa-brands fa-facebook-messenger"></i>Messenger
            </button>
            <button class="ev-share-btn" style="background:#2aabee;" onclick="doShare('telegram')">
                <i class="fa-brands fa-telegram"></i>Telegram
            </button>
            <button class="ev-share-btn" style="background:#7360f2;" onclick="doShare('viber')">
                <i class="fa-brands fa-viber"></i>Viber
            </button>
        </div>
        <div class="ev-copy-row">
            <input class="ev-copy-input" id="share-link-input" type="text" readonly>
            <button class="ev-copy-btn" id="copy-btn" onclick="copyLink()">
                <i class="fa-solid fa-copy"></i> Copy
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
/* ═══════════════════════════════════════════════════════════
   CSRF
═══════════════════════════════════════════════════════════ */
var CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* ═══════════════════════════════════════════════════════════
   LIGHTBOX
═══════════════════════════════════════════════════════════ */
function openLightbox(src, author, time, title, desc) {
    document.getElementById('lb-img').src    = src;
    document.getElementById('lb-author').textContent = author;
    document.getElementById('lb-time').textContent   = time;
    document.getElementById('lb-title').textContent  = title;
    document.getElementById('lb-desc').textContent   = desc;
    document.getElementById('ev-lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('ev-lightbox').classList.remove('open');
    document.body.style.overflow = '';
}

/* ═══════════════════════════════════════════════════════════
   SHARE
═══════════════════════════════════════════════════════════ */
var _shareUrl = '', _shareTitle = '';
function openShare(id, title) {
    _shareUrl   = window.location.origin + window.location.pathname.replace(/\/$/, '') + '#ev-card-' + id;
    _shareTitle = title;
    document.getElementById('share-title-text').textContent = title;
    document.getElementById('share-link-input').value       = _shareUrl;
    document.getElementById('copy-btn').innerHTML           = '<i class="fa-solid fa-copy"></i> Copy';
    document.getElementById('ev-share-overlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeShare() {
    document.getElementById('ev-share-overlay').classList.remove('open');
    document.body.style.overflow = '';
}
function doShare(platform) {
    var u = encodeURIComponent(_shareUrl);
    var t = encodeURIComponent(_shareTitle);
    var map = {
        facebook:  'https://www.facebook.com/sharer/sharer.php?u=' + u,
        messenger: 'fb-messenger://share?link=' + u,
        telegram:  'https://t.me/share/url?url=' + u + '&text=' + t,
        viber:     'viber://forward?text=' + encodeURIComponent(_shareTitle + ' - ' + _shareUrl)
    };
    if (map[platform]) window.open(map[platform], '_blank');
}
function copyLink() {
    var input = document.getElementById('share-link-input');
    var btn   = document.getElementById('copy-btn');
    navigator.clipboard ? navigator.clipboard.writeText(input.value) : (input.select(), document.execCommand('copy'));
    btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
    setTimeout(function(){ btn.innerHTML = '<i class="fa-solid fa-copy"></i> Copy'; }, 2000);
}

/* ═══════════════════════════════════════════════════════════
   REACTIONS
═══════════════════════════════════════════════════════════ */
var _pickerTimer = null;

function openPicker(id) {
    clearTimeout(_pickerTimer);
    document.getElementById('picker-' + id).classList.add('open');
}
function schedulePicker(id) {
    _pickerTimer = setTimeout(function(){ closePicker(id); }, 400);
}
function clearPickerTimer() { clearTimeout(_pickerTimer); }
function closePicker(id) {
    var el = document.getElementById('picker-' + id);
    if (el) el.classList.remove('open');
}

function quickReact(id) {
    var btn = document.getElementById('rxn-btn-' + id);
    var cur = btn.dataset.reaction || '';
    sendReaction(id, cur || 'like');
}
function pickReact(id, type) {
    closePicker(id);
    sendReaction(id, type);
}

function sendReaction(id, type) {
    var btn = document.getElementById('rxn-btn-' + id);
    var url = btn.dataset.url;

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ reaction_type: type })
    })
    .then(function(r) {
        if (!r.ok) { console.error('Like failed:', r.status); return null; }
        return r.json();
    })
    .then(function(data) {
        if (!data || !data.success) return;

        var icon  = btn.querySelector('i');
        var label = btn.querySelector('span');

        // Remove old reaction classes
        btn.classList.remove('reacted-like','reacted-haha','reacted-love');

        if (data.reacted) {
            btn.dataset.reaction = data.reaction_type;
            btn.classList.add('reacted-' + data.reaction_type);
            var icons  = {like:'fa-solid fa-thumbs-up', haha:'fa-solid fa-face-laugh', love:'fa-solid fa-heart'};
            var labels = {like:'Like', haha:'Haha', love:'Love'};
            icon.className  = icons[data.reaction_type];
            label.textContent = labels[data.reaction_type];
        } else {
            btn.dataset.reaction = '';
            icon.className  = 'fa-regular fa-thumbs-up';
            label.textContent = 'Like';
        }

        updateRxnDisplay(id, data.reactions);
    })
    .catch(function(err){ console.error('Reaction error:', err); });
}

function updateRxnDisplay(id, rxn) {
    var el = document.getElementById('rxn-display-' + id);
    if (!el) return;
    if (!rxn || rxn.total === 0) { el.innerHTML = ''; return; }
    var html = '';
    if (rxn.like > 0) html += '<span class="ev-rxn-dot" style="background:#1877f2;"><i class="fa-solid fa-thumbs-up" style="color:#fff;font-size:.55rem;"></i></span>';
    if (rxn.haha > 0) html += '<span class="ev-rxn-dot" style="background:#f7b125;"><i class="fa-solid fa-face-laugh" style="color:#fff;font-size:.55rem;"></i></span>';
    if (rxn.love > 0) html += '<span class="ev-rxn-dot" style="background:#f33e58;"><i class="fa-solid fa-heart" style="color:#fff;font-size:.55rem;"></i></span>';
    html += '<span class="ev-rxn-count">' + rxn.total + '</span>';
    el.innerHTML = html;
}

/* ═══════════════════════════════════════════════════════════
   COMMENTS
═══════════════════════════════════════════════════════════ */
function toggleComments(id) {
    var sec   = document.getElementById('comments-' + id);
    var input = document.getElementById('cmt-input-' + id);
    var open  = sec.style.display === 'block';
    sec.style.display = open ? 'none' : 'block';
    if (!open && input) { input.focus(); }
}

function postComment(id) {
    var input = document.getElementById('cmt-input-' + id);
    var text  = input.value.trim();
    if (!text) return;

    var url = input.dataset.url;

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ comment: text })
    })
    .then(function(r) {
        if (!r.ok) { console.error('Comment failed:', r.status); return null; }
        return r.json();
    })
    .then(function(data) {
        if (!data || !data.success) return;
        input.value = '';

        var list  = document.getElementById('cmt-list-' + id);
        var init  = data.comment.user ? data.comment.user.name.charAt(0).toUpperCase() : '?';
        var uname = data.comment.user ? data.comment.user.name : 'You';
        var html  =
            '<div class="ev-cmt-item">' +
                '<div class="ev-cmt-avatar">' + init + '</div>' +
                '<div>' +
                    '<div class="ev-cmt-bubble">' +
                        '<p class="ev-cmt-author">' + uname + '</p>' +
                        '<p class="ev-cmt-text">' + escHtml(data.comment.comment) + '</p>' +
                    '</div>' +
                    '<div class="ev-cmt-time">just now</div>' +
                '</div>' +
            '</div>';
        list.insertAdjacentHTML('beforeend', html);

        /* update comment count */
        var card = document.getElementById('ev-card-' + id);
        if (card) {
            var countEl = card.querySelector('.ev-cmt-count');
            if (countEl) {
                var n = (parseInt(countEl.textContent) || 0) + 1;
                countEl.textContent = n + ' ' + (n === 1 ? 'comment' : 'comments');
            }
        }
    })
    .catch(function(err){ console.error('Comment error:', err); });
}

/* ── helper: escape HTML in user-supplied text ─────────── */
function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── close modals on Escape ────────────────────────────── */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeLightbox(); closeShare(); }
});
</script>
@endpush

@endsection
