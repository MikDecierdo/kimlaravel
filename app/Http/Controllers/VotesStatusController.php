<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Vote;
use App\Models\CampusElection;
use App\Models\User;
use App\Traits\DeptVariantHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VotesStatusController extends Controller
{
    use DeptVariantHelper;

    public function studentList()
    {
        $user = Auth::guard('student')->user();
        $department = $user->department;
        $depts = $this->getDeptVariants($department);
        $visibleElectionDepts = $depts;
        if (!in_array('CSG', $visibleElectionDepts, true)) {
            $visibleElectionDepts[] = 'CSG';
        }

        // Get all elections visible to the student so status labels can explain current state.
        $activeElections = CampusElection::whereIn('department', $visibleElectionDepts)
            ->withCount('candidates')
            ->orderByDesc('created_at')
            ->get();

        // Add vote counts for each election
        foreach ($activeElections as $election) {
            $election->votes_count = Vote::whereHas('candidate', function ($query) use ($election) {
                $query->where('campus_election_id', $election->id);
            })->count();
        }

        return view('student.votes-status-list', compact('activeElections'));
    }

    public function studentIndex($electionId)
    {
        $user = Auth::guard('student')->user();
        $department = $user->department;
        $depts = $this->getDeptVariants($department);
        $visibleElectionDepts = $depts;
        if (!in_array('CSG', $visibleElectionDepts, true)) {
            $visibleElectionDepts[] = 'CSG';
        }

        // Get any election for student's department (active or ended)
        $activeElection = CampusElection::where('id', $electionId)
            ->whereIn('department', $visibleElectionDepts)
            ->with(['candidates' => function($query) {
                $query->withCount('votes');
            }])
            ->first();

        if (!$activeElection) {
            return redirect()->route('student.votes-status')->with('error', 'Election not found or not active.');
        }

        // Calculate statistics
        $stats = $this->calculateElectionStats($activeElection);
        
        // Get leading candidate
        $leadingCandidate = $this->getLeadingCandidate($activeElection);
        
        // Get top candidates
        $topCandidates = $this->getTopCandidates($activeElection, 4);
        
        // Calculate time left
        $timeLeft = $this->calculateTimeLeft($activeElection);

        return view('student.votes-status', compact('activeElection', 'stats', 'leadingCandidate', 'topCandidates', 'timeLeft'));
    }

    public function adminList()
    {
        // Get ALL elections across all departments (active, inactive, finished)
        $activeElections = CampusElection::withCount('candidates')
            ->orderBy('created_at', 'desc')
            ->get();

        // Add vote counts for each election
        foreach ($activeElections as $election) {
            $election->votes_count = Vote::whereHas('candidate', function ($query) use ($election) {
                $query->where('campus_election_id', $election->id);
            })->count();
        }

        return view('admin.votes-status-list', compact('activeElections'));
    }

    public function adminIndex($electionId)
    {
        // Admin can view any election regardless of active/date status
        $activeElection = CampusElection::where('id', $electionId)
            ->with(['candidates' => function($query) {
                $query->withCount('votes');
            }])
            ->first();

        if (!$activeElection) {
            return redirect()->route('admin.votes-status')->with('error', 'Election not found.');
        }

        // Calculate statistics
        $stats = $this->calculateElectionStats($activeElection);
        
        // Get leading candidate
        $leadingCandidate = $this->getLeadingCandidate($activeElection);
        
        // Get top candidates
        $topCandidates = $this->getTopCandidates($activeElection, 4);
        
        // Calculate time left
        $timeLeft = $this->calculateTimeLeft($activeElection);

        return view('admin.votes-status', compact('activeElection', 'stats', 'leadingCandidate', 'topCandidates', 'timeLeft'));
    }

    public function departmentHeadList()
    {
        $user = Auth::guard('department_head')->user();
        $department = $user->department;
        $depts = $this->getDeptVariants($department);
        $visibleElectionDepts = $depts;
        if (strtoupper(trim((string) $department)) !== 'CSG' && !in_array('CSG', $visibleElectionDepts, true)) {
            $visibleElectionDepts[] = 'CSG';
        }

        // Get all elections for department head's department
        $activeElections = CampusElection::whereIn('department', $visibleElectionDepts)
            ->withCount('candidates')
            ->orderByDesc('created_at')
            ->get();

        // Add vote counts for each election
        foreach ($activeElections as $election) {
            $election->votes_count = Vote::whereHas('candidate', function ($query) use ($election) {
                $query->where('campus_election_id', $election->id);
            })->count();
        }

        return view('department-head.votes-status-list', compact('activeElections'));
    }

    public function departmentHeadIndex($electionId)
    {
        $user = Auth::guard('department_head')->user();
        $department = $user->department;
        $depts = $this->getDeptVariants($department);
        $visibleElectionDepts = $depts;
        if (strtoupper(trim((string) $department)) !== 'CSG' && !in_array('CSG', $visibleElectionDepts, true)) {
            $visibleElectionDepts[] = 'CSG';
        }

        // Get specific election for department head's department
        $activeElection = CampusElection::where('id', $electionId)
            ->whereIn('department', $visibleElectionDepts)
            ->with(['candidates' => function($query) {
                $query->withCount('votes');
            }])
            ->first();

        if (!$activeElection) {
            return redirect()->route('department-head.votes-status')->with('error', 'Election not found.');
        }

        // Calculate statistics
        $stats = $this->calculateElectionStats($activeElection);
        
        // Get leading candidate
        $leadingCandidate = $this->getLeadingCandidate($activeElection);
        
        // Get top candidates
        $topCandidates = $this->getTopCandidates($activeElection, 4);
        
        // Calculate time left
        $timeLeft = $this->calculateTimeLeft($activeElection);

        return view('department-head.votes-status', compact('activeElection', 'stats', 'leadingCandidate', 'topCandidates', 'timeLeft'));
    }

    private function calculateElectionStats($election)
    {
        // Total votes cast
        $totalVotes = Vote::whereHas('candidate', function ($query) use ($election) {
            $query->where('campus_election_id', $election->id);
        })->count();

        // Total eligible voters (students + faculty voters in shared portal)
        $totalEligibleVoters = User::whereIn('role', ['student', 'staff'])->count();

        // Calculate turnout percentage
        $turnoutPercentage = $totalEligibleVoters > 0 
            ? round(($totalVotes / $totalEligibleVoters) * 100, 1) 
            : 0;

        return [
            'total_votes' => $totalVotes,
            'eligible_voters' => $totalEligibleVoters,
            'turnout_percentage' => $turnoutPercentage
        ];
    }

    private function getLeadingCandidate($election)
    {
        return Candidate::where('campus_election_id', $election->id)
            ->withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->first();
    }

    private function getTopCandidates($election, $limit = 4)
    {
        $totalVotes = Vote::whereHas('candidate', function ($query) use ($election) {
            $query->where('campus_election_id', $election->id);
        })->count();

        $candidates = Candidate::where('campus_election_id', $election->id)
            ->withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->limit($limit)
            ->get();

        // Calculate percentages
        foreach ($candidates as $candidate) {
            $candidate->percentage = $totalVotes > 0 
                ? round(($candidate->votes_count / $totalVotes) * 100, 1) 
                : 0;
        }

        return $candidates;
    }

    private function calculateTimeLeft($election)
    {
        $endDate = \Carbon\Carbon::parse($election->end_date);
        $now = now();

        if ($now >= $endDate) {
            return [
                'days' => 0,
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
                'formatted' => 'Ended',
                'total_seconds' => 0
            ];
        }

        $diff = $now->diff($endDate);
        
        return [
            'days' => $diff->days,
            'hours' => $diff->h,
            'minutes' => $diff->i,
            'seconds' => $diff->s,
            'formatted' => sprintf('%dd %02dh %02dm %02ds', $diff->days, $diff->h, $diff->i, $diff->s),
            'total_seconds' => $endDate->diffInSeconds($now)
        ];
    }
}
