<?php

namespace App\Http\Controllers;

use App\Models\CampusElection;
use App\Models\Candidate;
use App\Models\Event;
use App\Models\Staff;
use App\Models\Vote;
use App\Traits\DeptVariantHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use DeptVariantHelper;

    public function index()
    {
        // Use the active guard to determine role — Admin and Staff models
        // have no 'role' column, so we check guards directly.
        if (Auth::guard('admin')->check()) {
            return $this->adminDashboard();
        }

        if (Auth::guard('department_head')->check()) {
            return redirect()->route('department-head.dashboard');
        }

        if (Auth::guard('student')->check()) {
            $studentPortalUser = Auth::guard('student')->user();
            if ($this->isFacultyPortalUser($studentPortalUser)) {
                return redirect()->route('faculty.dashboard');
            }

            return $this->studentDashboard();
        }

        abort(403, 'Unauthorized access');
    }

    private function adminDashboard()
    {
        $stats = [
            'total_candidates' => Candidate::count(),
            'total_events' => Event::count(),
            'total_votes' => Vote::count(),
            'total_students' => \App\Models\User::where('role', 'student')->count()
        ];

        $recentEvents = Event::latest()->take(3)->get();

        return view('admin.dashboard', compact('stats', 'recentEvents'));
    }

    public function facultyDashboard()
    {
        $user = Auth::guard('student')->user();
        if (!$this->isFacultyPortalUser($user)) {
            return redirect()->route('dashboard');
        }

        return $this->studentDashboard('faculty');
    }

    private function studentDashboard(string $portalType = 'student')
    {
        $user = $this->getAuthenticatedUser();
        
        $stats = [
            'total_candidates' => Candidate::count(),
            'total_events' => Event::count(),
            'total_votes' => Vote::count()
        ];

        $depts = $this->getDeptVariants($user->department);
        $visibleElectionDepts = $depts;
        if (!in_array('CSG', $visibleElectionDepts, true)) {
            $visibleElectionDepts[] = 'CSG';
        }

        $recentEvents = Event::where(function($q) use ($visibleElectionDepts) {
            $q->whereIn('department', $visibleElectionDepts)
                  ->orWhere('department', 'General');
            })
            ->latest()
            ->take(3)
            ->get();

        // Per-position leading candidates from the active election
        $activeElection = CampusElection::where('is_active', true)
            ->whereIn('department', $visibleElectionDepts)
            ->with(['candidates' => fn($q) => $q->withCount('votes')])
            ->first();

        $topCandidates = collect();
        if ($activeElection) {
            $positions      = $activeElection->positions ?? [];
            $candidatesByPos = $activeElection->candidates->groupBy('position');
            if (empty($positions)) {
                $positions = $candidatesByPos->keys()->toArray();
            }
            foreach ($positions as $pos) {
                $leader = ($candidatesByPos[$pos] ?? collect())
                    ->sortByDesc('votes_count')
                    ->first();
                if ($leader) {
                    $topCandidates->push($leader);
                }
            }
        }

        return view('student.dashboard', compact('stats', 'recentEvents', 'topCandidates', 'activeElection', 'portalType'));
    }

    /**
     * Get the currently authenticated user from any guard
     */
    private function getAuthenticatedUser()
    {
        foreach (['admin', 'department_head', 'student', 'web'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }
        
        abort(401, 'Unauthenticated');
    }

    private function isFacultyPortalUser($user): bool
    {
        if (!$user || empty($user->email)) {
            return false;
        }

        return Staff::where('email', $user->email)
            ->where('is_department_head', false)
            ->where(function ($query) {
                $query->whereNull('position')
                    ->orWhereRaw('LOWER(position) in (?, ?)', ['faculty', 'none']);
            })
            ->exists();
    }
}
