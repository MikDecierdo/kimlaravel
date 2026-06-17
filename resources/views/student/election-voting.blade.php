@extends('layouts.student')

@section('student-content')
<header>
    <div class="header-title">
        <div>
            <h1>{{ $election->election_name }}</h1>
            <p>{{ $election->department }} Department - Cast your vote for each position</p>
        </div>
    </div>
</header>

<!-- Election Info Banner -->
<div style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; color: white;">
    @if($election->description)
        <p style="margin-bottom: 1rem; font-size: 1rem; opacity: 0.95;">{{ $election->description }}</p>
    @endif
    <div style="display: flex; gap: 2rem; flex-wrap: wrap; font-size: 0.9rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-calendar-days"></i>
            <span>{{ \Carbon\Carbon::parse($election->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($election->end_date)->format('M d, Y') }}</span>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-users"></i>
            <span>{{ $candidatesByPosition->count() }} Position{{ $candidatesByPosition->count() > 1 ? 's' : '' }}</span>
        </div>
    </div>
</div>

<!-- Voting Instructions -->
<div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
    <div style="display: flex; align-items: start; gap: 1rem;">
        <i class="fa-solid fa-info-circle" style="color: #856404; font-size: 1.5rem; margin-top: 0.25rem;"></i>
        <div style="color: #856404;">
            <strong style="display: block; margin-bottom: 0.5rem;">How to Vote:</strong>
            <ol style="margin: 0; padding-left: 1.5rem; line-height: 1.8;">
                <li>Select ONE candidate for each position</li>
                <li>Your selections will be marked as "Pending Review"</li>
                <li>After selecting all positions, a review modal will appear</li>
                <li>Review your choices and click "Submit All Votes" to finalize</li>
                <li><strong>Vote counts will update only after final submission</strong></li>
            </ol>
        </div>
    </div>
</div>

@if($candidatesByPosition->count() > 0)
    @foreach($candidatesByPosition as $position => $candidates)
        <div style="margin-bottom: 3rem;">
            <!-- Position Header — matches dept-head gradient style -->
            <div style="background: linear-gradient(135deg, #800020 0%, #A0153E 100%); color: white; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 600;">
                    <i class="fa-solid fa-award"></i> {{ $position }}
                    <span style="float: right; font-size: 0.9rem; opacity: 0.9;">({{ $candidates->count() }} {{ Str::plural('candidate', $candidates->count()) }})</span>
                </h3>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                @foreach($candidates as $candidate)
                    @php
                        $studentUser  = \App\Models\User::where('student_id', $candidate->student_id)->first();
                        $candYrLevel  = $studentUser ? ($studentUser->year_level ?? '') : '';
                        $candPhoto    = ($studentUser && $studentUser->profile_picture)
                                        ? $studentUser->profile_picture
                                        : ($candidate->image ? asset($candidate->image) : null);
                        $isSubmittedVote = in_array($candidate->id, $submittedVotes);
                        $isPendingVote   = collect($pendingVotes)->contains('candidate_id', $candidate->id);
                        $isSelf          = in_array($candidate->id, $selfCandidateIds);
                    @endphp
                    <div class="folder-card slide-top" data-position="{{ $position }}"
                         style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: transform 0.25s, box-shadow 0.25s; border: 3px solid {{ $isSelf ? '#7c3aed' : '#800020' }};">

                        <!-- Photo area — 260px matching dept-head -->
                        <div style="width: 100%; height: 260px; background: linear-gradient(135deg, #800020 0%, #A0153E 100%); position: relative; overflow: hidden;">
                            @if($candPhoto)
                                <img src="{{ $candPhoto }}" alt="{{ $candidate->first_name }}"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: white;">
                                    <i class="fa-solid fa-user" style="font-size: 5rem; opacity: 0.3;"></i>
                                </div>
                            @endif
                            <!-- Position label overlay at bottom -->
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 0.55rem 1rem; background: linear-gradient(to top, rgba(0,0,0,0.72) 0%, transparent 100%);">
                                <span style="font-size: 0.73rem; font-weight: 700; color: #FFC107; text-transform: uppercase; letter-spacing: 0.05em;">{{ $candidate->position }}</span>
                            </div>
                            @if($isSelf)
                                <div style="position: absolute; top: 10px; right: 10px; background: #7c3aed; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                                    <i class="fa-solid fa-user-check"></i> You
                                </div>
                            @elseif($isPendingVote)
                                <div style="position: absolute; top: 10px; right: 10px; background: #f59e0b; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                                    <i class="fa-solid fa-clock"></i> Pending
                                </div>
                            @elseif($isSubmittedVote)
                                <div style="position: absolute; top: 10px; right: 10px; background: #22c55e; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                                    <i class="fa-solid fa-check"></i> Voted
                                </div>
                            @endif
                        </div>

                        <!-- Content — centered, matching dept-head -->
                        <div style="padding: 1.1rem 1.25rem 1.4rem; text-align: center;">
                            <h3 style="font-size: 1.08rem; font-weight: 800; margin: 0 0 0.3rem; color: #111827;">
                                {{ $candidate->first_name }}@if($candidate->middle_name) {{ substr($candidate->middle_name, 0, 1) }}.@endif {{ $candidate->last_name }}
                            </h3>
                            <p style="font-size: 0.84rem; font-weight: 600; color: #475569; margin: 0 0 0.18rem;">Student ID: {{ $candidate->student_id }}</p>
                            <p style="font-size: 0.84rem; color: #64748b; margin: 0 0 0.8rem; min-height: 1.2em;">{{ $candYrLevel }}</p>

                            @if($candidate->description)
                                <p style="font-size: 0.82rem; color: #64748b; margin: 0 0 0.8rem; line-height: 1.5; font-style: italic; border-top: 1px solid #f1f5f9; padding-top: 0.6rem;">
                                    {{ Str::limit($candidate->description, 100) }}
                                </p>
                            @endif

                            <!-- Vote progress bar -->
                            <div style="height: 5px; background: #e5e7eb; border-radius: 3px; margin: 0.6rem 0 0.3rem; overflow: hidden;">
                                <div class="vote-progress-{{ $candidate->id }}" style="height: 100%; background: #22c55e; width: {{ $candidate->vote_percentage }}%; transition: width 1s ease;"></div>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.78rem; color: #94a3b8; margin-bottom: 1rem;">
                                <span class="vote-count-{{ $candidate->id }}">{{ $candidate->votes }} votes</span>
                                <span class="vote-percentage-{{ $candidate->id }}">{{ $candidate->vote_percentage }}%</span>
                            </div>

                            @if($isSelf && !$isSubmittedVote)
                                <button type="button"
                                        class="btn-vote btn-hover"
                                        style="width: 100%; padding: 0.55rem 1.5rem; border: 2px solid #7c3aed; border-radius: 50px; font-weight: 700; cursor: pointer; background: white; color: #7c3aed; font-size: 0.82rem; transition: all 0.25s; display: flex; align-items: center; justify-content: center; gap: 0.4rem;"
                                        data-candidate-id="{{ $candidate->id }}"
                                        data-candidate-name="{{ $candidate->full_name }}"
                                        data-candidate-image="{{ $candPhoto ?? '' }}"
                                        data-position="{{ $position }}"
                                        data-first-name="{{ $candidate->first_name }}">
                                    <i class="fa-solid fa-user-check"></i> Vote for Yourself
                                </button>
                            @elseif($isSelf && $isSubmittedVote)
                                <button class="btn-vote voted btn-hover"
                                        style="width: 100%; padding: 0.55rem 1.5rem; border: 2px solid #22c55e; border-radius: 50px; font-weight: 700; cursor: not-allowed; background: #22c55e; color: white; font-size: 0.82rem; display: flex; align-items: center; justify-content: center; gap: 0.4rem;"
                                        disabled>
                                    <i class="fa-solid fa-check-circle"></i> Vote Submitted
                                </button>
                            @elseif($isSubmittedVote)
                                <button class="btn-vote voted btn-hover"
                                        style="width: 100%; padding: 0.55rem 1.5rem; border: 2px solid #22c55e; border-radius: 50px; font-weight: 700; cursor: not-allowed; background: #22c55e; color: white; font-size: 0.82rem; display: flex; align-items: center; justify-content: center; gap: 0.4rem;"
                                        disabled>
                                    <i class="fa-solid fa-check-circle"></i> Vote Submitted
                                </button>
                            @else
                                <button type="button"
                                        class="btn-vote btn-hover"
                                        style="width: 100%; padding: 0.55rem 1.5rem; border: 2px solid #800020; border-radius: 50px; font-weight: 700; cursor: pointer; background: #800020; color: white; font-size: 0.82rem; transition: all 0.25s; display: flex; align-items: center; justify-content: center; gap: 0.4rem;"
                                        data-candidate-id="{{ $candidate->id }}"
                                        data-candidate-name="{{ $candidate->full_name }}"
                                        data-candidate-image="{{ $candPhoto ?? '' }}"
                                        data-position="{{ $position }}"
                                        data-first-name="{{ $candidate->first_name }}">
                                    <i class="fa-solid fa-check-to-slot"></i> Vote for {{ $candidate->first_name }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@else
    <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: var(--card-shadow);">
        <i class="fa-solid fa-users-slash" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
        <h3 style="color: var(--text-muted); margin-bottom: 0.5rem;">No Candidates Yet</h3>
        <p style="color: #aaa; font-size: 0.95rem;">No candidates have been added to this election yet.</p>
    </div>
@endif

<!-- Floating Submit Vote Button -->
<button id="floatingSubmitBtn" style="display: none; position: fixed; bottom: 2rem; right: 2rem; padding: 1rem 2rem; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; border: none; border-radius: 50px; font-size: 1rem; font-weight: 700; cursor: pointer; box-shadow: 0 8px 24px rgba(128, 0, 32, 0.4); transition: all 0.3s ease; z-index: 1000; display: flex; align-items: center; gap: 0.75rem;">
    <i class="fa-solid fa-paper-plane"></i>
    <span>Submit Vote</span>
    <span id="selectionCount" style="background: rgba(255,255,255,0.3); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">0/0</span>
</button>

<!-- Finished Voting Badge (shown after submission) -->
<div id="finishedVoteBox" style="display: none; position: fixed; bottom: 2rem; right: 2rem; padding: 1rem 1.6rem; background: linear-gradient(135deg, #15803d 0%, #16a34a 100%); color: white; border-radius: 16px; box-shadow: 0 8px 28px rgba(21,128,61,0.45); z-index: 1000; min-width: 230px; animation: finishedPop 0.4s cubic-bezier(.175,.885,.32,1.275);">
    <div style="display: flex; align-items: center; gap: 0.65rem; font-weight: 800; font-size: 0.98rem; margin-bottom: 0.3rem;">
        <i class="fa-solid fa-circle-check" style="font-size: 1.25rem;"></i>
        You've finished voting!
    </div>
    <div id="finishedVoteTime" style="font-size: 0.78rem; opacity: 0.85; padding-left: 1.9rem;"></div>
</div>

<!-- Vote Receipt Modal -->
<div id="receiptModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.72); align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div id="receiptBox" style="background:#fff; border-radius:16px; width:92%; max-width:520px; max-height:88vh; display:flex; flex-direction:column; box-shadow:0 24px 64px rgba(0,0,0,0.45); animation:modalSlideIn 0.28s ease; overflow:hidden;">

        <!-- Receipt header -->
        <div style="background:linear-gradient(135deg,#800020 0%,#3d0010 100%); padding:1.6rem 1.75rem 1.4rem; color:#fff; flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:1rem;">
                <div style="width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fa-solid fa-receipt" style="font-size:1.4rem;"></i>
                </div>
                <div>
                    <h2 style="margin:0;font-size:1.15rem;font-weight:800;letter-spacing:.01em;">Voting Receipt</h2>
                    <p style="margin:0;font-size:.8rem;opacity:.82;">{{ $election->election_name }}</p>
                </div>
            </div>
            <!-- Incomplete warning (hidden by default) -->
            <div id="receiptWarning" style="display:none;margin-top:1rem;background:rgba(255,255,255,.12);border-radius:8px;padding:.65rem 1rem;font-size:.82rem;align-items:center;gap:.55rem;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size:1rem;"></i>
                <span id="receiptWarningText"></span>
            </div>
        </div>

        <!-- Dashed separator (receipt teeth) -->
        <div style="height:14px;background:#fff;flex-shrink:0;position:relative;overflow:hidden;">
            <svg width="100%" height="14" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0,14 Q7,0 14,14 Q21,0 28,14 Q35,0 42,14 Q49,0 56,14 Q63,0 70,14 Q77,0 84,14 Q91,0 98,14 Q105,0 112,14 Q119,0 126,14 Q133,0 140,14 Q147,0 154,14 Q161,0 168,14 Q175,0 182,14 Q189,0 196,14 Q203,0 210,14 Q217,0 224,14 Q231,0 238,14 Q245,0 252,14 Q259,0 266,14 Q273,0 280,14 Q287,0 294,14 Q301,0 308,14 Q315,0 322,14 Q329,0 336,14 Q343,0 350,14 Q357,0 364,14 Q371,0 378,14 Q385,0 392,14 Q399,0 406,14 Q413,0 420,14 Q427,0 434,14 Q441,0 448,14 Q455,0 462,14 Q469,0 476,14 Q483,0 490,14 Q497,0 504,14 Q511,0 518,14 Q525,0 532,14" stroke="#800020" stroke-width="1.5" fill="none"/>
            </svg>
        </div>

        <!-- Receipt meta -->
        <div style="background:#fff;padding:.7rem 1.75rem .5rem;border-bottom:1px dashed #e5e7eb;flex-shrink:0;">
            <div style="display:flex;justify-content:space-between;font-size:.78rem;color:#6b7280;">
                <span><i class="fa-regular fa-calendar" style="margin-right:4px;"></i>{{ now()->format('M d, Y') }}</span>
                <span><i class="fa-regular fa-clock" style="margin-right:4px;"></i><span id="receiptTime"></span></span>
                <span><i class="fa-solid fa-building" style="margin-right:4px;"></i>{{ $election->department }}</span>
            </div>
        </div>

        <!-- Receipt body (scrollable) -->
        <div id="receiptItems" style="flex:1;overflow-y:auto;padding:1rem 1.75rem;background:#fff;"></div>

        <!-- Bottom teeth + footer -->
        <div style="flex-shrink:0;">
            <div style="height:14px;background:#fff;position:relative;overflow:hidden;">
                <svg width="100%" height="14" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                    <path d="M0,0 Q7,14 14,0 Q21,14 28,0 Q35,14 42,0 Q49,14 56,0 Q63,14 70,0 Q77,14 84,0 Q91,14 98,0 Q105,14 112,0 Q119,14 126,0 Q133,14 140,0 Q147,14 154,0 Q161,14 168,0 Q175,14 182,0 Q189,14 196,0 Q203,14 210,0 Q217,14 224,0 Q231,14 238,0 Q245,14 252,0 Q259,14 266,0 Q273,14 280,0 Q287,14 294,0 Q301,14 308,0 Q315,14 322,0 Q329,14 336,0 Q343,14 350,0 Q357,14 364,0 Q371,14 378,0 Q385,14 392,0 Q399,14 406,0 Q413,14 420,0 Q427,14 434,0 Q441,14 448,0 Q455,14 462,0 Q469,14 476,0 Q483,14 490,0 Q497,14 504,0 Q511,14 518,0 Q525,14 532,0" stroke="#800020" stroke-width="1.5" fill="none"/>
                </svg>
            </div>
            <div style="background:#f9fafb;border-top:1px solid #f1f5f9;padding:1.1rem 1.75rem;display:flex;gap:.75rem;">
                <button id="receiptCancelBtn"
                        style="flex:1;padding:.75rem;border:2px solid #d1d5db;background:#fff;color:#374151;border-radius:10px;font-weight:700;font-size:.9rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.5rem;transition:background .2s,border-color .2s;">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>
                <button id="receiptSubmitBtn"
                        style="flex:2;padding:.75rem;border:2px solid #800020;background:#800020;color:#fff;border-radius:10px;font-weight:700;font-size:.9rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.5rem;transition:background .2s,transform .15s;">
                    <i class="fa-solid fa-paper-plane"></i> Submit All Votes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── Review Modal ──────────────────────────────────── -->
<div id="reviewModal">
    <div class="review-box">
        <div class="review-header">
            <div class="review-icon"><i class="fa-solid fa-star"></i></div>
            <h2>How was the voting experience?</h2>
            <p>Your vote has been submitted! Share your feedback about this election.</p>
        </div>

        <!-- Stars -->
        <div class="star-row">
            <button class="star-btn" data-star="1" title="Poor">&#9733;</button>
            <button class="star-btn" data-star="2" title="Fair">&#9733;</button>
            <button class="star-btn" data-star="3" title="Good">&#9733;</button>
            <button class="star-btn" data-star="4" title="Very Good">&#9733;</button>
            <button class="star-btn" data-star="5" title="Excellent">&#9733;</button>
        </div>
        <div id="reviewRatingLabel"></div>

        <!-- Written review -->
        <textarea id="reviewText" class="review-textarea"
            placeholder="Write your review here (optional)..."></textarea>

        <div class="review-actions">
            <button id="reviewSkipBtn">Skip</button>
            <button id="reviewSubmitBtn"><i class="fa-solid fa-paper-plane"></i> Submit Review</button>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* folder-card — matches dept-head candidate card */
    .folder-card {
        position: relative;
    }
    .folder-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(128,0,32,0.18) !important;
    }
    .slide-top {
        animation: slide-top .5s cubic-bezier(.25,.46,.45,.94) both;
    }
    @keyframes slide-top {
        0%   { transform: translateY(80px); opacity: 0; }
        100% { transform: translateY(0);    opacity: 1; }
    }
    .btn-hover:hover:not(:disabled) {
        opacity: 0.88;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    @keyframes finishedPop {
        0%   { transform: scale(0.6) translateY(20px); opacity: 0; }
        100% { transform: scale(1)   translateY(0);    opacity: 1; }
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .receipt-row {
        display: flex;
        align-items: center;
        gap: .9rem;
        padding: .8rem 0;
        border-bottom: 1px dashed #e5e7eb;
    }
    .receipt-row:last-child { border-bottom: none; }
    .receipt-photo {
        width: 52px; height: 52px; border-radius: 8px; object-fit: cover;
        border: 2px solid #800020; flex-shrink: 0;
    }
    .receipt-photo-placeholder {
        width: 52px; height: 52px; border-radius: 8px; flex-shrink: 0;
        background: linear-gradient(135deg,#800020,#3d0010);
        display: flex; align-items: center; justify-content: center;
        color: rgba(255,255,255,.5);
    }
    .receipt-position {
        font-size: .7rem; text-transform: uppercase; font-weight: 700;
        color: #800020; letter-spacing: .04em; margin-bottom: 2px;
    }
    .receipt-name {
        font-size: .97rem; font-weight: 800; color: #111827; margin: 0;
    }
    .receipt-check {
        margin-left: auto; width: 28px; height: 28px; border-radius: 50%;
        background: #800020; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; flex-shrink: 0;
    }
    #receiptSubmitBtn:hover  { background: #5c0015 !important; border-color: #5c0015 !important; }
    #receiptCancelBtn:hover  { background: #f3f4f6 !important; }
    #receiptModal.open { display: flex !important; }

    /* ── Review Modal ────────────────────────────────── */
    #reviewModal {
        display: none;
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.55);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    #reviewModal.open { display: flex !important; }
    .review-box {
        background: #fff;
        border-radius: 20px;
        padding: 2rem 2rem 1.5rem;
        width: 100%;
        max-width: 460px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        animation: slideUp .3s ease;
    }
    @keyframes slideUp {
        from { transform: translateY(30px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }
    .review-header {
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .review-header .review-icon {
        width: 64px; height: 64px;
        background: linear-gradient(135deg,#800020,#A0153E);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem;
        color: #fff; font-size: 1.8rem;
    }
    .review-header h2 { font-size: 1.3rem; font-weight: 700; color: #111; margin: 0 0 .35rem; }
    .review-header p  { font-size: .9rem; color: #6b7280; margin: 0; }
    /* Stars */
    .star-row {
        display: flex; justify-content: center; gap: .5rem;
        margin-bottom: 1.25rem;
    }
    .star-btn {
        background: none; border: none; cursor: pointer;
        font-size: 2rem; color: #d1d5db;
        transition: color .15s, transform .15s;
        padding: 0; line-height: 1;
    }
    .star-btn.active, .star-btn:hover { color: #f59e0b; }
    .star-btn:hover { transform: scale(1.15); }
    #reviewRatingLabel {
        text-align: center; font-size: .85rem; color: #800020;
        font-weight: 600; min-height: 1.2rem; margin-bottom: 1rem;
    }
    .review-textarea {
        width: 100%; box-sizing: border-box;
        border: 1.5px solid #e5e7eb; border-radius: 10px;
        padding: .75rem 1rem; font-size: .9rem; resize: vertical;
        min-height: 90px; outline: none; font-family: inherit;
        transition: border-color .2s;
    }
    .review-textarea:focus { border-color: #800020; }
    .review-actions {
        display: flex; gap: .75rem; margin-top: 1.25rem;
    }
    #reviewSubmitBtn {
        flex: 1; padding: .75rem;
        background: #800020; color: #fff;
        border: none; border-radius: 10px; font-size: .95rem;
        font-weight: 600; cursor: pointer; transition: background .2s;
    }
    #reviewSubmitBtn:hover { background: #5c0015; }
    #reviewSkipBtn {
        padding: .75rem 1.25rem;
        background: #f3f4f6; color: #374151;
        border: none; border-radius: 10px; font-size: .95rem;
        font-weight: 500; cursor: pointer; transition: background .2s;
    }
    #reviewSkipBtn:hover { background: #e5e7eb; }
    .btn-vote {
        pointer-events: auto !important;
        z-index: 10;
    }

    .btn-vote:hover:not(.voted):not(.pending) {
        background: var(--primary) !important;
        color: white !important;
    }

    .btn-vote:disabled {
        opacity: 0.7;
        cursor: not-allowed !important;
    }
    
    .btn-vote:not(:disabled) {
        cursor: pointer !important;
    }
    
    .btn-vote.selected {
        background: var(--primary) !important;
        color: white !important;
    }
    
    #floatingSubmitBtn:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(128, 0, 32, 0.5);
    }
    
    #floatingSubmitBtn:active {
        transform: translateY(-1px);
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    #floatingSubmitBtn.pulse {
        animation: pulse 2s infinite;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing free-selection voting system...');
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const electionId = {{ $election->id }};
    const totalPositions = {{ $candidatesByPosition->count() }};
    
    // Track selected candidates by position
    let selectedVotes = {};
    
    const floatingBtn = document.getElementById('floatingSubmitBtn');
    const selectionCount = document.getElementById('selectionCount');
    const finishedBox  = document.getElementById('finishedVoteBox');
    const finishedTime = document.getElementById('finishedVoteTime');

    function lockAllVoteButtons() {
        document.querySelectorAll('.btn-vote:not(.voted)').forEach(btn => {
            btn.disabled = true;
            btn.style.cursor = 'not-allowed';
            btn.style.opacity = '0.45';
            btn.style.background = '#e5e7eb';
            btn.style.color = '#9ca3af';
            btn.style.borderColor = '#d1d5db';
            btn.innerHTML = '<i class="fa-solid fa-lock"></i> Voting Closed';
        });
    }

    function showFinishedButton(date) {
        floatingBtn.style.display = 'none';
        const fmt = date.toLocaleString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: 'numeric', minute: '2-digit', hour12: true
        });
        finishedTime.textContent = 'Submitted: ' + fmt;
        finishedBox.style.display = 'block';
        lockAllVoteButtons();
    }
    
    // Update floating button visibility and count
    function updateFloatingButton() {
        const selectedCount = Object.keys(selectedVotes).length;
        selectionCount.textContent = `${selectedCount}/${totalPositions}`;
        
        if (selectedCount > 0) {
            floatingBtn.style.display = 'flex';
            if (selectedCount === totalPositions) {
                floatingBtn.classList.add('pulse');
            } else {
                floatingBtn.classList.remove('pulse');
            }
        } else {
            floatingBtn.style.display = 'none';
        }
    }
    
    // Handle candidate selection/deselection
    function toggleCandidateSelection(btnElement) {
        if (btnElement.disabled || btnElement.classList.contains('voted')) {
            return;
        }
        
        const candidateId   = btnElement.dataset.candidateId;
        const candidateName  = btnElement.dataset.candidateName;
        const candidateImage = btnElement.dataset.candidateImage || '';
        const position       = btnElement.dataset.position;
        const firstName      = btnElement.dataset.firstName;
        
        // Check if this position already has a selection
        const currentSelection = selectedVotes[position];
        
        if (currentSelection && currentSelection.candidateId === candidateId) {
            // Deselect this candidate
            delete selectedVotes[position];
            btnElement.classList.remove('selected');
            btnElement.innerHTML = `<i class="fa-solid fa-check"></i> Vote for ${firstName}`;
            btnElement.style.background = 'white';
            btnElement.style.color = 'var(--primary)';
            console.log('Deselected:', candidateName);
        } else {
            // Deselect previous candidate for this position if any
            if (currentSelection) {
                const prevBtn = document.querySelector(`[data-candidate-id="${currentSelection.candidateId}"]`);
                if (prevBtn) {
                    prevBtn.classList.remove('selected');
                    prevBtn.innerHTML = `<i class="fa-solid fa-check"></i> Vote for ${prevBtn.dataset.firstName}`;
                    prevBtn.style.background = 'white';
                    prevBtn.style.color = 'var(--primary)';
                }
            }
            
            // Select new candidate
            selectedVotes[position] = {
                candidateId:   candidateId,
                candidateName: candidateName,
                image:         candidateImage,
                position:      position
            };
            
            btnElement.classList.add('selected');
            btnElement.innerHTML = `<i class="fa-solid fa-check-circle"></i> Selected`;
            btnElement.style.background = 'var(--primary)';
            btnElement.style.color = 'white';
            console.log('Selected:', candidateName, 'for', position);
        }
        
        updateFloatingButton();
    }
    
    // ── Receipt modal helpers ────────────────────────────
    const receiptModal  = document.getElementById('receiptModal');
    const receiptItems  = document.getElementById('receiptItems');
    const receiptWarn   = document.getElementById('receiptWarning');
    const receiptWarnTx = document.getElementById('receiptWarningText');
    const receiptTime   = document.getElementById('receiptTime');

    function openReceiptModal(showWarning, warnText) {
        // Set current time
        receiptTime.textContent = new Date().toLocaleTimeString('en-US', {hour:'numeric',minute:'2-digit',hour12:true});

        // Warning banner
        if (showWarning) {
            receiptWarnTx.textContent = warnText;
            receiptWarn.style.display = 'flex';
        } else {
            receiptWarn.style.display = 'none';
        }

        // Build receipt rows
        let html = '';
        for (const [position, vote] of Object.entries(selectedVotes)) {
            const hasPhoto = vote.image && vote.image.trim() !== '';
            html += `
                <div class="receipt-row">
                    ${ hasPhoto
                        ? `<img class="receipt-photo" src="${vote.image}" alt="${vote.candidateName}">`
                        : `<div class="receipt-photo-placeholder"><i class="fa-solid fa-user" style="font-size:1.3rem;"></i></div>`
                    }
                    <div style="flex:1;min-width:0;">
                        <div class="receipt-position">${position}</div>
                        <p class="receipt-name">${vote.candidateName}</p>
                    </div>
                    <div class="receipt-check"><i class="fa-solid fa-check"></i></div>
                </div>`;
        }
        receiptItems.innerHTML = html;
        receiptModal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeReceiptModal() {
        receiptModal.classList.remove('open');
        document.body.style.overflow = '';
    }

    // ── Review Modal ────────────────────────────────────────
    const reviewModal      = document.getElementById('reviewModal');
    const reviewStars      = document.querySelectorAll('.star-btn');
    const reviewRatingLbl  = document.getElementById('reviewRatingLabel');
    const reviewTextarea   = document.getElementById('reviewText');
    const reviewSubmitBtn  = document.getElementById('reviewSubmitBtn');
    const reviewSkipBtn    = document.getElementById('reviewSkipBtn');
    let   reviewRating     = 0;
    const ratingLabels     = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];

    function openReviewModal() {
        reviewRating = 0;
        reviewTextarea.value = '';
        reviewRatingLbl.textContent = '';
        reviewStars.forEach(s => s.classList.remove('active'));
        reviewModal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeReviewModal() {
        reviewModal.classList.remove('open');
        document.body.style.overflow = '';
    }

    function highlightStars(n) {
        reviewStars.forEach(s => {
            s.classList.toggle('active', parseInt(s.dataset.star) <= n);
        });
    }

    reviewStars.forEach(function(star) {
        star.addEventListener('mouseover', function() { highlightStars(parseInt(this.dataset.star)); reviewRatingLbl.textContent = ratingLabels[parseInt(this.dataset.star)]; });
        star.addEventListener('mouseleave', function() { highlightStars(reviewRating); reviewRatingLbl.textContent = reviewRating ? ratingLabels[reviewRating] : ''; });
        star.addEventListener('click', function() {
            reviewRating = parseInt(this.dataset.star);
            highlightStars(reviewRating);
            reviewRatingLbl.textContent = ratingLabels[reviewRating];
        });
    });

    function submitReview(doReload) {
        closeReviewModal();
        if (doReload) {
            _swalToast('success', 'Your vote has been submitted successfully!');
            setTimeout(() => location.reload(), 1400);
        }
    }

    reviewSkipBtn.addEventListener('click', function() { submitReview(true); });
    reviewModal.addEventListener('click', function(e) { if (e.target === reviewModal) submitReview(true); });

    reviewSubmitBtn.addEventListener('click', function() {
        if (!reviewRating) {
            reviewRatingLbl.style.color = '#ef4444';
            reviewRatingLbl.textContent = 'Please select a star rating';
            return;
        }
        reviewRatingLbl.style.color = '#800020';
        reviewSubmitBtn.disabled = true;
        reviewSubmitBtn.textContent = 'Submitting...';

        fetch(`/voting/election/${electionId}/review`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ rating: reviewRating, review: reviewTextarea.value.trim() || null })
        })
        .then(r => r.json())
        .then(d => {
            closeReviewModal();
            _swalToast('success', d.message || 'Thank you for your feedback!');
            setTimeout(() => location.reload(), 1400);
        })
        .catch(() => { closeReviewModal(); setTimeout(() => location.reload(), 400); });
    });
    // ── end Review Modal ────────────────────────────────────

    document.getElementById('receiptCancelBtn').addEventListener('click', closeReceiptModal);
    receiptModal.addEventListener('click', function(e){ if(e.target === receiptModal) closeReceiptModal(); });

    document.getElementById('receiptSubmitBtn').addEventListener('click', function() {
        closeReceiptModal();
        performSubmission();
    });

    // Submit all votes — opens receipt modal
    function submitAllVotes() {
        const selectedCount = Object.keys(selectedVotes).length;

        if (selectedCount === 0) {
            _swalWarn('No Selections', 'Please select at least one candidate before submitting.');
            return;
        }

        const warn = selectedCount < totalPositions;
        openReceiptModal(
            warn,
            warn ? `You selected ${selectedCount} of ${totalPositions} positions. You can still submit.` : ''
        );
    }

    // Show review modal before final submission (kept for compatibility)
    function showReviewAndConfirm() { submitAllVotes(); }
    
    // Perform the actual submission
    function performSubmission() {
        floatingBtn.disabled = true;
        floatingBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';
        
        const votesArray = Object.values(selectedVotes).map(vote => ({
            candidate_id: vote.candidateId,
            position: vote.position
        }));
        
        fetch(`/voting/election/${electionId}/submit`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                votes: votesArray
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Save submission timestamp for this election
                const now = new Date();
                localStorage.setItem('voted_election_{{ $election->id }}', now.toISOString());
                // Show finished badge, then open review popup
                showFinishedButton(now);
                setTimeout(() => openReviewModal(), 900);
            } else {
                floatingBtn.disabled = false;
                floatingBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Submit Vote</span> <span id="selectionCount" style="background: rgba(255,255,255,0.3); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">0/0</span>';
                _swalErr(data.message, 'Submission Failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            floatingBtn.disabled = false;
            floatingBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Submit Vote</span> <span id="selectionCount" style="background: rgba(255,255,255,0.3); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">0/0</span>';
            _swalErr('An error occurred while submitting votes. Please try again.');
        });
    }
    
    // Attach event listeners to vote buttons
    const voteButtons = document.querySelectorAll('.btn-vote:not(.voted)');
    console.log('Found vote buttons:', voteButtons.length);
    
    voteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleCandidateSelection(this);
        });
    });
    
    // Attach event listener to floating submit button
    floatingBtn.addEventListener('click', submitAllVotes);

    // If already voted, restore the finished badge from localStorage
    @if(count($submittedVotes) > 0)
    (function() {
        const stored = localStorage.getItem('voted_election_{{ $election->id }}');
        const date   = stored ? new Date(stored) : new Date();
        showFinishedButton(date);
    })();
    @endif

    console.log('Free-selection voting system initialized!');
});
</script>
@endpush
@endsection
