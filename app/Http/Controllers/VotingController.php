<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Vote;
use App\Models\CampusElection;
use App\Models\ElectionReview;
use App\Traits\DeptVariantHelper;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VotingController extends Controller
{
    use DeptVariantHelper;

    public function index(Request $request)
    {
        $user = Auth::guard('student')->user();
        $department = $user->department;
        $visibleElectionDepts = $this->visibleElectionDepts($department);
        $now = now();
        
        // Show elections visible to the student/faculty voter:
        // - currently active elections (votable)
        // - finished elections (view-only cards)
        // - disabled elections (view-only cards)
        // No candidate records exposed at this stage
        $activeElections = CampusElection::whereIn('department', $visibleElectionDepts)
            ->where(function ($query) use ($now) {
                $query->where(function ($activeQuery) use ($now) {
                    $activeQuery->where('is_active', true)
                        ->where('start_date', '<=', $now)
                        ->where('end_date', '>=', $now);
                })->orWhere('end_date', '<', $now)
                  ->orWhere('is_active', false);
            })
            ->withCount('candidates')
            ->orderByDesc('created_at')
            ->get();

        // Map election_id => latest vote created_at for this student
        $votedElections = Vote::where('user_id', $user->id)
            ->whereHas('candidate.campusElection', fn($q) => $q->whereIn('department', $visibleElectionDepts))
            ->with('candidate:id,campus_election_id')
            ->get()
            ->groupBy(fn($v) => $v->candidate->campus_election_id)
            ->map(fn($votes) => $votes->max('created_at'));

        return view('student.voting', compact('activeElections', 'department', 'votedElections'));
    }

    public function showElection(CampusElection $election)
    {
        $user = Auth::guard('student')->user();
        $isGlobalElection = $this->isGlobalElection($election);
        
        // Security check: Ensure student can only access their department's elections
        if (!$this->canAccessElection($election, $user->department)) {
            abort(403, 'You can only view elections from your department.');
        }

        // Check if election is active and within date range
        if (!$election->is_active || 
            $election->start_date > now() || 
            $election->end_date < now() ||
            !$this->isVotingWindowOpen($election)) {
            abort(403, 'This election is not currently active or voting is outside the allowed time range.');
        }

        // Get candidates for this election, grouped by position
        $candidateQuery = Candidate::where('campus_election_id', $election->id);
        if (!$isGlobalElection) {
            $candidateQuery->whereIn('department', $this->getDeptVariants($user->department));
        }

        $candidatesByPosition = $candidateQuery->get()->groupBy('position');

        // Get user's votes for this election (both submitted and pending)
        $submittedVotes = Vote::where('user_id', $user->id)
            ->whereIn('candidate_id', function($query) use ($election) {
                $query->select('id')
                    ->from('candidates')
                    ->where('campus_election_id', $election->id);
            })
            ->pluck('candidate_id')
            ->toArray();

        // Get pending votes from session
        $pendingVotes = session()->get('pending_votes.' . $election->id, []);

        // IDs of candidates that belong to this student
        $selfCandidateIds = $user->student_id
            ? Candidate::where('campus_election_id', $election->id)
                ->where('student_id', $user->student_id)
                ->pluck('id')
                ->toArray()
            : [];

        return view('student.election-voting', compact('election', 'candidatesByPosition', 'submittedVotes', 'pendingVotes', 'selfCandidateIds'));
    }

    public function vote(Request $request, Candidate $candidate)
    {
        $user = Auth::guard('student')->user();
        $election = $candidate->campusElection;
        $isGlobalElection = $election ? $this->isGlobalElection($election) : false;

        // Check if candidate is from the same department as the student
        if (!$isGlobalElection && !$this->sameDept($candidate->department, $user->department)) {
            return response()->json([
                'success' => false,
                'message' => 'You can only vote for candidates in your department!'
            ], 403);
        }

        // Verify the candidate belongs to an active election
        if (!$candidate->campus_election_id) {
            return response()->json([
                'success' => false,
                'message' => 'This candidate is not assigned to any election!'
            ], 400);
        }

        
        if (!$election || !$election->is_active || 
            $election->start_date > now() || 
            $election->end_date < now() ||
            !$this->isVotingWindowOpen($election)) {
            return response()->json([
                'success' => false,
                'message' => 'This election is not currently active or voting is outside the allowed time range!'
            ], 400);
        }

        if (!$this->canAccessElection($election, $user->department)) {
            return response()->json([
                'success' => false,
                'message' => 'You can only vote in elections available to your department!'
            ], 403);
        }

        // Check if user already voted for this position in this election (submitted votes)
        $existingVote = Vote::where('user_id', $user->id)
            ->where('campus_election_id', $candidate->campus_election_id)
            ->where('position', $candidate->position)
            ->whereHas('candidate', function ($query) use ($candidate) {
                $query->where('position', $candidate->position)
                      ->where('campus_election_id', $candidate->campus_election_id);
            })
            ->first();

        if ($existingVote) {
            return response()->json([
                'success' => false,
                'message' => 'You have already voted for this position in this election!'
            ], 400);
        }

        // Check if user already has a pending vote for this position
        $pendingVotes = session()->get('pending_votes.' . $election->id, []);
        foreach ($pendingVotes as $vote) {
            if ($vote['position'] === $candidate->position) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already selected a candidate for this position!'
                ], 400);
            }
        }

        // Store vote in session (not yet submitted to database)
        $pendingVotes[] = [
            'candidate_id' => $candidate->id,
            'candidate_name' => $candidate->full_name,
            'position' => $candidate->position,
            'image' => $candidate->image ?? 'https://picsum.photos/seed/' . $candidate->id . '/300/200'
        ];
        session()->put('pending_votes.' . $election->id, $pendingVotes);

        // Get total positions for this election
        $positionsQuery = Candidate::where('campus_election_id', $election->id);
        if (!$isGlobalElection) {
            $positionsQuery->whereIn('department', $this->getDeptVariants($user->department));
        }

        $totalPositions = $positionsQuery->distinct('position')->count('position');

        return response()->json([
            'success' => true,
            'message' => "Selected {$candidate->full_name} for {$candidate->position}!",
            'pending_votes_count' => count($pendingVotes),
            'total_positions' => $totalPositions,
            'all_positions_voted' => count($pendingVotes) >= $totalPositions
        ]);
    }

    public function submitVotes(Request $request, CampusElection $election)
    {
        $user = Auth::guard('student')->user();
        $isGlobalElection = $this->isGlobalElection($election);

        // Security check
        if (!$this->canAccessElection($election, $user->department)) {
            return response()->json([
                'success' => false,
                'message' => 'You can only submit votes for your department elections!'
            ], 403);
        }

        if (!$this->isVotingWindowOpen($election)) {
            return response()->json([
                'success' => false,
                'message' => 'Voting is only allowed within the scheduled time range.'
            ], 403);
        }

        // Validate the request
        $request->validate([
            'votes' => 'required|array',
            'votes.*.candidate_id' => 'required|exists:candidates,id',
            'votes.*.position' => 'required|string'
        ]);

        $votesToSubmit = $request->input('votes', []);

        if (empty($votesToSubmit)) {
            return response()->json([
                'success' => false,
                'message' => 'No votes to submit!'
            ], 400);
        }

        $normalizedVotes = [];
        foreach ($votesToSubmit as $voteData) {
            $position = trim((string) ($voteData['position'] ?? ''));

            if ($position === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid vote position!'
                ], 400);
            }

            if (isset($normalizedVotes[$position])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate vote detected for ' . $position . '!'
                ], 400);
            }

            $normalizedVotes[$position] = $voteData;
        }

        $votesCount = 0;

        DB::beginTransaction();

        try {
            foreach ($normalizedVotes as $position => $voteData) {
                $candidateQuery = Candidate::where('id', $voteData['candidate_id'])
                    ->where('campus_election_id', $election->id)
                    ->where('position', $position);

                if (!$isGlobalElection) {
                    $candidateQuery->whereIn('department', $this->getDeptVariants($user->department));
                }

                $candidate = $candidateQuery->first();

                if (!$candidate) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid candidate selection!'
                    ], 400);
                }

                $existingVote = Vote::where('user_id', $user->id)
                    ->where('campus_election_id', $election->id)
                    ->where('position', $position)
                    ->lockForUpdate()
                    ->first();

                if ($existingVote) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'You have already voted for ' . $position . '!'
                    ], 400);
                }

                Vote::create([
                    'user_id' => $user->id,
                    'campus_election_id' => $election->id,
                    'position' => $position,
                    'candidate_id' => $candidate->id,
                ]);

                $candidate->increment('votes');
                $votesCount++;
            }

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();

            if ((string) $e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already submitted a vote for one of these positions.'
                ], 400);
            }

            throw $e;
        }

        // Clear any pending votes from session for this election
        session()->forget('pending_votes.' . $election->id);

        return response()->json([
            'success' => true,
            'message' => 'Your votes have been successfully submitted!',
            'votes_count' => $votesCount
        ]);
    }

    public function history()
    {
        $user = Auth::guard('student')->user();

        $votes = Vote::where('user_id', $user->id)
            ->with([
                'candidate' => function ($q) {
                    $q->select('id', 'campus_election_id', 'first_name', 'middle_name', 'last_name', 'position', 'department');
                },
                'candidate.campusElection' => function ($q) {
                    $q->select('id', 'election_name', 'department', 'start_date', 'end_date', 'is_active');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.voting-history', compact('votes'));
    }

    public function storeReview(Request $request, CampusElection $election)
    {
        $user = Auth::guard('student')->user();

        // Ensure the student actually voted in this election
        $voted = Vote::where('user_id', $user->id)
            ->whereHas('candidate', fn($q) => $q->where('campus_election_id', $election->id))
            ->exists();

        if (!$voted) {
            return response()->json(['success' => false, 'message' => 'You have not voted in this election.'], 403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        ElectionReview::updateOrCreate(
            ['user_id' => $user->id, 'campus_election_id' => $election->id],
            ['rating' => $request->rating, 'review' => $request->review]
        );

        return response()->json(['success' => true, 'message' => 'Thank you for your feedback!']);
    }

    public function getPendingVotes(CampusElection $election)
    {
        $user = Auth::guard('student')->user();

        // Security check
        if (!$this->canAccessElection($election, $user->department)) {
            return response()->json([
                'success' => false,
                'message' => 'You can only view your department elections!'
            ], 403);
        }

        // Get pending votes from session
        $pendingVotes = session()->get('pending_votes.' . $election->id, []);

        return response()->json([
            'success' => true,
            'votes' => $pendingVotes
        ]);
    }

    private function visibleElectionDepts(string $department): array
    {
        $depts = $this->getDeptVariants($department);
        if (!in_array('CSG', $depts, true)) {
            $depts[] = 'CSG';
        }

        return $depts;
    }

    private function isGlobalElection(CampusElection $election): bool
    {
        return strtoupper(trim((string) $election->department)) === 'CSG';
    }

    private function canAccessElection(CampusElection $election, string $department): bool
    {
        return $this->isGlobalElection($election) || $this->sameDept($election->department, $department);
    }

    private function isVotingWindowOpen(CampusElection $election, ?Carbon $now = null): bool
    {
        $now = $now ?: now();

        if (!$election->start_date || !$election->end_date) {
            return false;
        }

        $today = $now->toDateString();
        $startDate = $election->start_date->toDateString();
        $endDate = $election->end_date->toDateString();

        if ($today < $startDate || $today > $endDate) {
            return false;
        }

        $startTime = $this->normalizeVotingTime($election->voting_start_time ?? '08:00:00', '08:00:00');
        $endTime = $this->normalizeVotingTime($election->voting_end_time ?? '17:00:00', '17:00:00');
        $currentTime = $now->format('H:i:s');

        return $currentTime >= $startTime && $currentTime <= $endTime;
    }

    private function normalizeVotingTime(?string $value, string $fallback): string
    {
        $time = trim((string) ($value ?? ''));
        if ($time === '') {
            $time = $fallback;
        }

        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time . ':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }

        return $fallback;
    }
}
