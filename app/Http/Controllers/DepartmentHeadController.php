<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Models\CampusElection;
use App\Models\User;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Event;
use App\Models\CandidateRegistration;
use App\Traits\DeptVariantHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Notifications\CandidateAddedNotification;
use App\Notifications\CandidateRegistrationApprovedNotification;
use App\Notifications\CandidateRegistrationDeclinedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DepartmentHeadController extends Controller
{
    use DeptVariantHelper;

    public function dashboard()
    {
        $user = $this->currentDepartmentHeadUser();
        $department = $user->department;
        $depts = $this->staffDepts($user);
        $visibleElectionDepts = $this->visibleElectionDepts($user);
        
        $elections = CampusElection::whereIn('department', $visibleElectionDepts)->latest()->get();
        $candidates = Candidate::whereIn('department', $depts)->get();
        
        $stats = [
            'total_elections' => $elections->count(),
            'active_elections' => $elections->where('is_active', true)->count(),
            'total_candidates' => $candidates->count(),
            'total_votes' => $candidates->sum('votes'),
        ];

        // Per-position leading candidates from the active election
        $activeElection = CampusElection::where('is_active', true)
            ->whereIn('department', $visibleElectionDepts)
            ->with(['candidates' => fn($q) => $q->withCount('votes')])
            ->first();

        $topCandidates = collect();
        if ($activeElection) {
            $positions       = $activeElection->positions ?? [];
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

        return view('department-head.dashboard', compact('stats', 'elections', 'candidates', 'department', 'topCandidates', 'activeElection'));
    }

    public function candidates()
    {
        $user = $this->currentDepartmentHeadUser();
        $department = $user->department;
        $depts = $this->staffDepts($user);
        $visibleElectionDepts = $this->visibleElectionDepts($user);
        
        // Candidate management follows the head's own department scope for elections.
        $elections = CampusElection::whereIn('department', $visibleElectionDepts)
            ->with(['candidates' => function($query) {
                $query->orderBy('position')->orderBy('first_name');
            }])
            ->withCount('candidates')
            ->latest()
            ->get();

        // Group candidates by election and position
        foreach ($elections as $election) {
            $election->candidatesByPosition = $election->candidates->groupBy('position');
        }
        
        return view('department-head.candidates', compact('elections', 'department'));
    }

    public function campusElections()
    {
        $user = $this->currentDepartmentHeadUser();
        $department = $user->department;
        $visibleElectionDepts = $this->visibleElectionDepts($user);
        
        $elections = CampusElection::whereIn('department', $visibleElectionDepts)
            ->withCount('candidates')
            ->latest()
            ->get();
        
        return view('department-head.campus-elections', compact('elections', 'department'));
    }

    public function electionWinners()
    {
        $user = $this->currentDepartmentHeadUser();
        $department = $user->department;
        $visibleElectionDepts = $this->visibleElectionDepts($user);

        $elections = CampusElection::whereIn('department', $visibleElectionDepts)
            ->whereDate('end_date', '<=', now()->toDateString())
            ->with(['candidates' => function ($query) {
                $query->withCount('votes');
            }])
            ->orderByDesc('end_date')
            ->get()
            ->map(fn ($election) => $this->hydrateElectionWinners($election));

        return view('department-head.election-winners', compact('elections', 'department'));
    }

    public function showElectionWinner(CampusElection $election)
    {
        $user = $this->currentDepartmentHeadUser();
        $department = $user->department;
        $visibleElectionDepts = $this->visibleElectionDepts($user);

        abort_unless(in_array($election->department, $visibleElectionDepts, true), 404);
        abort_unless($election->end_date && $election->end_date->isPast(), 404);

        $election->load(['candidates' => function ($query) {
            $query->withCount('votes');
        }]);

        $election = $this->hydrateElectionWinners($election);

        return view('department-head.election-winner-show', compact('election', 'department'));
    }

    private function hydrateElectionWinners(CampusElection $election): CampusElection
    {
        $positions = $election->positions ?? [];
        $candidatesByPosition = $election->candidates->groupBy('position');

        if (empty($positions)) {
            $positions = $candidatesByPosition->keys()->toArray();
        }

        $winnerPositions = collect($positions)
            ->map(function ($position) use ($candidatesByPosition) {
                $winner = ($candidatesByPosition[$position] ?? collect())
                    ->sortByDesc('votes_count')
                    ->first();

                return [
                    'position' => $position,
                    'winner' => $winner,
                ];
            })
            ->filter(fn ($item) => $item['winner'])
            ->values();

        $election->winnerPositions = $winnerPositions;
        $election->winnerSections = $this->buildWinnerSections($winnerPositions);

        return $election;
    }

    private function buildWinnerSections($winnerPositions): array
    {
        $positionOrder = [
            'President',
            'Vice President',
            'Vice President Internal',
            'Vice President External',
            'Secretary',
            'Treasurer',
            'Auditor',
            'PIO',
            'PIO Internal',
            'PIO External',
        ];

        $sections = [];
        $usedPositions = [];

        foreach ($positionOrder as $position) {
            $item = $winnerPositions->firstWhere('position', $position);

            if (!$item) {
                continue;
            }

            $sections[] = [
                'label' => 'Executive Officers',
                'items' => [$item],
            ];
            $usedPositions[] = $position;
            break;
        }

        $vicePresidentItems = $winnerPositions->filter(function ($item) {
            return str_contains($item['position'], 'Vice President');
        })->values();

        if ($vicePresidentItems->isNotEmpty()) {
            $sections[] = [
                'label' => 'Vice Presidents',
                'items' => $vicePresidentItems->all(),
            ];
            $usedPositions = array_merge($usedPositions, $vicePresidentItems->pluck('position')->all());
        }

        $operationsItems = $winnerPositions->filter(function ($item) use ($usedPositions) {
            return !in_array($item['position'], $usedPositions, true)
                && !str_contains($item['position'], 'Representative');
        })->values();

        if ($operationsItems->isNotEmpty()) {
            $sections[] = [
                'label' => 'Other Officers',
                'items' => $operationsItems->all(),
            ];
            $usedPositions = array_merge($usedPositions, $operationsItems->pluck('position')->all());
        }

        $representatives = $winnerPositions->filter(function ($item) {
            return str_contains($item['position'], 'Representative');
        })->values();

        if ($representatives->isNotEmpty()) {
            $sections[] = [
                'label' => 'Representatives',
                'items' => $representatives->all(),
            ];
        }

        $otherItems = $winnerPositions->reject(function ($item) use ($usedPositions) {
            return in_array($item['position'], $usedPositions, true)
                || str_contains($item['position'], 'Representative');
        })->values();

        if ($otherItems->isNotEmpty()) {
            $sections[] = [
                'label' => 'Additional Winners',
                'items' => $otherItems->all(),
            ];
        }

        return $sections;
    }

    public function storeCampusElection(Request $request)
    {
        $user = $this->requireDepartmentPortalPermission('create_election');
        
        $validated = $request->validate([
            'department' => 'required|string',
            'election_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'registration_start_date' => 'required|date',
            'registration_end_date' => 'required|date|after_or_equal:registration_start_date|before:start_date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'voting_start_time' => 'required|date_format:H:i',
            'voting_end_time' => 'required|date_format:H:i|after:voting_start_time',
            'positions' => 'required|array',
            'positions.*' => 'required|string',
            'candidate_registration_schema' => 'nullable|array',
            'partylist_teams' => 'nullable|array',
            'partylist_teams.*.name' => 'nullable|string|max:255',
            'partylist_teams.*.tagline' => 'nullable|string|max:255',
            'banner_image' => 'nullable|image|max:2048',
            'is_active' => 'nullable',
        ]);

        $validated['candidate_registration_schema'] =
            (is_array($validated['candidate_registration_schema'] ?? null) && count($validated['candidate_registration_schema']) > 0)
                ? $validated['candidate_registration_schema']
                : CampusElection::defaultCandidateRegistrationSchema();
        $validated['partylist_teams'] = CampusElection::normalizePartylistTeams($validated['partylist_teams'] ?? []);

        // Ensure election management rights for the target department.
        if (!$this->canManageElectionDepartment($user, $validated['department'])) {
            $message = $this->isGlobalElectionDepartment($validated['department'])
                ? 'Only the CSG department can create CSG elections.'
                : 'You can only create elections for your department.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('elections', 'public');
            $validated['banner_image'] = '/storage/' . $path;
        }

        $validated['is_active'] = $request->has('is_active') ? filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN) : false;

        $election = CampusElection::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Campus election created successfully!',
            'election' => $election
        ]);
    }

    public function updateCampusElection(Request $request, CampusElection $election)
    {
        $user = $this->requireDepartmentPortalPermission('create_election');
        
        // Ensure election management rights for this election department.
        if (!$this->canManageElectionDepartment($user, $election->department)) {
            $message = $this->isGlobalElectionDepartment($election->department)
                ? 'Only the CSG department can update CSG elections.'
                : 'You can only update elections for your department.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        $validated = $request->validate([
            'department' => 'required|string',
            'election_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'registration_start_date' => 'required|date',
            'registration_end_date' => 'required|date|after_or_equal:registration_start_date|before:start_date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'voting_start_time' => 'required|date_format:H:i',
            'voting_end_time' => 'required|date_format:H:i|after:voting_start_time',
            'positions' => 'required|array',
            'positions.*' => 'required|string',
            'candidate_registration_schema' => 'nullable|array',
            'partylist_teams' => 'nullable|array',
            'partylist_teams.*.name' => 'nullable|string|max:255',
            'partylist_teams.*.tagline' => 'nullable|string|max:255',
            'banner_image' => 'nullable|image|max:2048',
            'is_active' => 'nullable',
        ]);

        $validated['candidate_registration_schema'] =
            (is_array($validated['candidate_registration_schema'] ?? null) && count($validated['candidate_registration_schema']) > 0)
                ? $validated['candidate_registration_schema']
                : ($election->candidate_registration_schema ?: CampusElection::defaultCandidateRegistrationSchema());
        $validated['partylist_teams'] = CampusElection::normalizePartylistTeams($validated['partylist_teams'] ?? ($election->partylist_teams ?? []));

        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('elections', 'public');
            $validated['banner_image'] = '/storage/' . $path;
        }

        $validated['is_active'] = $request->has('is_active') ? filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN) : $election->is_active;

        $election->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Campus election updated successfully!',
            'election' => $election
        ]);
    }

    public function toggleCampusElection(Request $request, $id)
    {
        $user = $this->requireDepartmentPortalPermission('create_election');
        $election = CampusElection::findOrFail($id);
        
        // Ensure election management rights for this election department.
        if (!$this->canManageElectionDepartment($user, $election->department)) {
            $message = $this->isGlobalElectionDepartment($election->department)
                ? 'Only the CSG department can modify CSG elections.'
                : 'You can only modify elections for your department.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        if ($election->end_date && $election->end_date->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Finished elections can no longer be enabled or disabled.'
            ], 422);
        }

        $election->is_active = !$election->is_active;
        $election->save();

        return response()->json([
            'success' => true,
            'message' => 'Election status updated successfully!',
            'is_active' => $election->is_active
        ]);
    }

    public function storeCandidate(Request $request)
    {
        $user = $this->requireDepartmentPortalPermission('add_candidates');

        try {
        $validated = $request->validate([
            'campus_election_id' => 'required|exists:campus_elections,id',
            'user_id' => 'required|exists:users,id',
            'position' => 'required|string|max:255',
            'partylist' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:250',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Get the student
        $student = User::findOrFail($validated['user_id']);

        // Ensure student role
        if ($student->role !== 'student' || $this->isLinkedStaffAccount($student)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid student selection.'
            ], 403);
        }

        // Ensure the student has a student_id
        if (!$student->student_id) {
            return response()->json([
                'success' => false,
                'message' => 'The selected student does not have a valid Student ID.'
            ], 422);
        }

        $description = trim((string) ($validated['description'] ?? ''));

        $election = CampusElection::findOrFail($validated['campus_election_id']);
        $isGlobalElection = $this->isGlobalElectionDepartment($election->department);

        if (!$this->canAccessRegistrationElection($user, $election)) {
            $message = 'You can only add candidates to elections visible to your department scope.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        if (!$this->canSeeStudentForRegistrationElection($user, $student, $election)) {
            $message = $isGlobalElection
                ? 'For CSG elections, you can only add confirmed applicants from your own department.'
                : 'Selected student does not belong to the current election department.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 422);
        }

        $allowedPositions = array_values(array_filter((array) ($election->positions ?? []), fn ($position) => is_string($position) && trim($position) !== ''));
        if (!in_array($validated['position'], $allowedPositions, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected position is not available in the current election.'
            ], 422);
        }

        $allowedPartylists = array_values(array_filter(array_map(function ($team) {
            return trim((string) ($team['name'] ?? ''));
        }, (array) ($election->partylist_teams ?? []))));

        $selectedPartylist = trim((string) ($validated['partylist'] ?? ''));
        if ($selectedPartylist !== '' && !in_array($selectedPartylist, $allowedPartylists, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected partylist is not available in the current election.'
            ], 422);
        }

        $candidateDepartment = $isGlobalElection
            ? strtoupper(trim((string) ($student->department ?? $election->department ?? '')))
            : $election->department;

        // Check if student is already a candidate in this election
        $existingCandidate = Candidate::where('campus_election_id', $validated['campus_election_id'])
            ->where('student_id', $student->student_id)
            ->first();

        if ($existingCandidate) {
            return response()->json([
                'success' => false,
                'message' => 'This student is already a candidate in this election.'
            ], 422);
        }

        $approvedRegistration = CandidateRegistration::where('campus_election_id', $election->id)
            ->where('user_id', $student->id)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->latest('id')
            ->first();

        if (!$approvedRegistration) {
            return response()->json([
                'success' => false,
                'message' => 'This student must be confirmed first from Registration List before adding to the election.'
            ], 422);
        }

        if (!empty($approvedRegistration->position) && $approvedRegistration->position !== $validated['position']) {
            return response()->json([
                'success' => false,
                'message' => 'Selected position must match the confirmed registration position (' . $approvedRegistration->position . ').'
            ], 422);
        }

        // CSG global election rule:
        // If a student is already a candidate in any non-CSG election,
        // they cannot be added to a CSG election.
        // Cross-election candidacy is allowed. We only prevent duplicates in the same election.

        // Use the User model's dedicated name fields
        $firstName  = $student->name;
        $middleName = $student->middle_name ?: null;
        $lastName   = $student->last_name ?: $student->name; // fallback to name if last_name missing

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('candidates', 'public');
            $validated['image'] = '/storage/' . $path;
            unset($validated['photo']);
        }

        $candidate = Candidate::create([
            'campus_election_id' => $validated['campus_election_id'],
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'student_id' => $student->student_id,
            'position' => $validated['position'],
            'partylist' => $selectedPartylist !== '' ? $selectedPartylist : null,
            'department' => $candidateDepartment,
            'description' => $description,
            'image' => $validated['image'] ?? null,
        ]);

        $approvedRegistration->update([
            'status' => 'completed',
            'reviewed_by_staff_id' => $user->id,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Candidate added to election successfully!',
            'candidate' => $candidate
        ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('storeCandidate error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding the candidate: ' . $e->getMessage()
            ], 500);
        }
    }

    public function candidateRegistrations(Request $request)
    {
        $user = $this->requireDepartmentPortalPermission('add_candidates');
        $campusElectionId = (int) $request->query('campus_election_id');

        if ($campusElectionId > 0) {
            $requestedElection = CampusElection::find($campusElectionId);
            if (!$requestedElection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Election not found.'
                ], 404);
            }

            if (!$this->canAccessRegistrationElection($user, $requestedElection)) {
                $message = 'You can only review registrations for elections visible to your department scope.';

                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 403);
            }
        }

        $this->syncPendingApplicationsToRegistrations($user, $campusElectionId);

        $query = CandidateRegistration::query()
            ->with([
                'election:id,election_name,department,positions',
                'student:id,name,middle_name,last_name,student_id,year_level,department,email,profile_picture',
            ])
            ->where('status', 'pending')
            ->latest();

        if ($campusElectionId > 0) {
            $query->where('campus_election_id', $campusElectionId);
        }

        $registrations = $query->get()->filter(function (CandidateRegistration $registration) use ($user) {
            $election = $registration->election;
            $student = $registration->student;
            if (!$election) {
                return false;
            }

            if (!$this->canAccessRegistrationElection($user, $election)) {
                return false;
            }

            return $this->canSeeStudentForRegistrationElection($user, $student, $election);
        })->values();

        $positionStatsByElection = [];
        $electionIds = $registrations->pluck('campus_election_id')->unique()->values();
        foreach ($electionIds as $electionId) {
            $positionStatsByElection[$electionId] = Candidate::where('campus_election_id', $electionId)
                ->selectRaw('position, COUNT(*) as total')
                ->groupBy('position')
                ->pluck('total', 'position')
                ->toArray();
        }

        $payload = $registrations->map(function (CandidateRegistration $registration) use ($positionStatsByElection) {
            $student = $registration->student;
            $election = $registration->election;
            $fullName = trim(implode(' ', array_filter([
                $student?->name,
                $student?->middle_name,
                $student?->last_name,
            ])));

            $electionPositions = array_values(array_filter((array) ($election->positions ?? []), fn ($position) => is_string($position) && trim($position) !== ''));
            $positionStatsRaw = $positionStatsByElection[$registration->campus_election_id] ?? [];
            $positionStats = array_map(function ($position) use ($positionStatsRaw, $registration) {
                return [
                    'position' => $position,
                    'total_candidates' => (int) ($positionStatsRaw[$position] ?? 0),
                    'is_requested_position' => $position === $registration->position,
                ];
            }, $electionPositions);

            return [
                'id' => $registration->id,
                'campus_election_id' => $registration->campus_election_id,
                'election_name' => $election->election_name,
                'election_department' => $election->department,
                'election_positions' => $electionPositions,
                'position_stats' => $positionStats,
                'requested_position' => $registration->position,
                'description' => $registration->description,
                'submitted_at' => optional($registration->created_at)->toDateTimeString(),
                'student' => [
                    'id' => $student?->id,
                    'name' => $fullName,
                    'student_id' => $student?->student_id,
                    'year_level' => $student?->year_level,
                    'department' => $student?->department,
                    'email' => $student?->email,
                    'profile_picture' => $student?->profile_picture,
                ],
            ];
        })->values();

        return response()->json([
            'success' => true,
            'registrations' => $payload,
        ]);
    }

    private function syncPendingApplicationsToRegistrations(Staff $staff, int $campusElectionId = 0): void
    {
        $applicationsQuery = CandidateApplication::query()
            ->whereRaw('UPPER(status) = ?', ['PENDING'])
            ->with([
                'election:id,department,positions',
                'user:id,student_id,department,role',
            ]);

        if ($campusElectionId > 0) {
            $applicationsQuery->where('election_id', $campusElectionId);
        }

        $applications = $applicationsQuery->get();

        foreach ($applications as $application) {
            $election = $application->election;
            $student = $application->user;

            if (!$election || !$student || !$student->student_id) {
                continue;
            }

            if (!$this->canAccessRegistrationElection($staff, $election)) {
                continue;
            }

            if (!$this->canSeeStudentForRegistrationElection($staff, $student, $election)) {
                continue;
            }

            $existingPending = CandidateRegistration::where('status', 'pending')
                ->where('campus_election_id', $application->election_id)
                ->where('user_id', $application->user_id)
                ->exists();

            if ($existingPending) {
                continue;
            }

            $responses = (array) ($application->form_responses ?? []);
            $allowedPositions = array_values(array_filter((array) ($election->positions ?? []), fn ($position) => is_string($position) && trim($position) !== ''));

            $requestedPosition = trim((string) ($responses['position'] ?? ''));
            if ($requestedPosition === '' && count($allowedPositions) > 0) {
                foreach ($responses as $value) {
                    $candidateValue = trim((string) $value);
                    if (in_array($candidateValue, $allowedPositions, true)) {
                        $requestedPosition = $candidateValue;
                        break;
                    }
                }
            }

            if ($requestedPosition === '' || !in_array($requestedPosition, $allowedPositions, true)) {
                continue;
            }

            $description = trim((string) ($responses['platform_statement'] ?? $responses['description'] ?? ''));

            CandidateRegistration::create([
                'campus_election_id' => $application->election_id,
                'user_id' => $application->user_id,
                'position' => $requestedPosition,
                'description' => $description,
                'status' => 'pending',
                'submitted_by_staff_id' => null,
            ]);
        }
    }

    public function storeCandidateRegistration(Request $request)
    {
        $user = $this->requireDepartmentPortalPermission('add_candidates');

        $validated = $request->validate([
            'campus_election_id' => 'required|exists:campus_elections,id',
            'user_id' => 'required|exists:users,id',
            'position' => 'required|string|max:255',
            'description' => 'nullable|string|max:250',
        ]);

        $student = User::findOrFail($validated['user_id']);
        $election = CampusElection::findOrFail($validated['campus_election_id']);
        $description = trim((string) ($validated['description'] ?? ''));
        $isGlobalElection = $this->isGlobalElectionDepartment($election->department);

        if (!$student->student_id || $student->role !== 'student' || $this->isLinkedStaffAccount($student)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid student selection.'
            ], 403);
        }

        if (strtolower((string) ($student->approval_status ?? '')) !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only accepted students can be submitted for candidate registration.'
            ], 422);
        }

        if (!$this->canAccessRegistrationElection($user, $election)) {
            $message = 'You can only submit registrations for elections visible to your department scope.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        if (!$this->canSeeStudentForRegistrationElection($user, $student, $election)) {
            $message = $isGlobalElection
                ? 'For CSG elections, you can only manage applicants from your own department.'
                : 'Selected student does not belong to the current election department.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 422);
        }

        $allowedPositions = array_values(array_filter((array) ($election->positions ?? []), fn ($position) => is_string($position) && trim($position) !== ''));
        if (!in_array($validated['position'], $allowedPositions, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected position is not available in the current election.'
            ], 422);
        }

        $alreadyCandidate = Candidate::where('campus_election_id', $election->id)
            ->where('student_id', $student->student_id)
            ->exists();

        if ($alreadyCandidate) {
            return response()->json([
                'success' => false,
                'message' => 'This student is already a candidate in this election.'
            ], 422);
        }

        $alreadyPending = CandidateRegistration::where('status', 'pending')
            ->where('campus_election_id', $election->id)
            ->where('user_id', $student->id)
            ->exists();

        if ($alreadyPending) {
            return response()->json([
                'success' => false,
                'message' => 'This student already has a pending registration in this election.'
            ], 422);
        }

        CandidateRegistration::create([
            'campus_election_id' => $election->id,
            'user_id' => $student->id,
            'position' => $validated['position'],
            'description' => $description,
            'status' => 'pending',
            'submitted_by_staff_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Candidate registration submitted. Please confirm it from the Registration list before it is added.'
        ]);
    }

    public function confirmCandidateRegistration(CandidateRegistration $registration)
    {
        $user = $this->requireDepartmentPortalPermission('add_candidates');

        if ($registration->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This registration has already been processed.'
            ], 422);
        }

        $registration->load(['election', 'student']);
        $election = $registration->election;
        $student = $registration->student;

        if (!$election || !$student) {
            return response()->json([
                'success' => false,
                'message' => 'Registration data is incomplete.'
            ], 422);
        }

        $isGlobalElection = $this->isGlobalElectionDepartment($election->department);
        if (!$this->canAccessRegistrationElection($user, $election)) {
            $message = 'You can only confirm registrations for elections visible to your department scope.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        if (!$student->student_id || $student->role !== 'student' || $this->isLinkedStaffAccount($student)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid student selection.'
            ], 403);
        }

        if (strtolower((string) ($student->approval_status ?? '')) !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only accepted students can be confirmed as candidates.'
            ], 422);
        }

        if (!$this->canSeeStudentForRegistrationElection($user, $student, $election)) {
            $message = $isGlobalElection
                ? 'For CSG elections, you can only manage applicants from your own department.'
                : 'Selected student does not belong to the current election department.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 422);
        }

        $allowedPositions = array_values(array_filter((array) ($election->positions ?? []), fn ($position) => is_string($position) && trim($position) !== ''));
        if (!in_array($registration->position, $allowedPositions, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected position is not available in the current election.'
            ], 422);
        }

        $existingCandidate = Candidate::where('campus_election_id', $election->id)
            ->where('student_id', $student->student_id)
            ->first();

        if ($existingCandidate) {
            return response()->json([
                'success' => false,
                'message' => 'This student is already a candidate in this election.'
            ], 422);
        }

        // Cross-election candidacy is allowed. We only prevent duplicates in the same election.

        $registration->update([
            'status' => 'approved',
            'reviewed_by_staff_id' => $user->id,
            'reviewed_at' => now(),
        ]);

        CandidateApplication::where('user_id', $student->id)
            ->where('election_id', $election->id)
            ->whereRaw('UPPER(status) = ?', ['PENDING'])
            ->update(['status' => 'APPROVED']);

        $student->notify(new CandidateRegistrationApprovedNotification($registration));

        return response()->json([
            'success' => true,
            'message' => 'Candidate registration confirmed successfully. Add the candidate from Submit Registration to include them in the election.'
        ]);
    }

    public function declineCandidateRegistration(Request $request, CandidateRegistration $registration)
    {
        $user = $this->requireDepartmentPortalPermission('add_candidates');

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        $reason = trim((string) $validated['reason']);

        if ($registration->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This registration has already been processed.'
            ], 422);
        }

        $registration->load(['election', 'student']);
        $election = $registration->election;
        $student = $registration->student;

        if (!$election || !$student) {
            return response()->json([
                'success' => false,
                'message' => 'Registration data is incomplete.'
            ], 422);
        }

        if (!$this->canAccessRegistrationElection($user, $election)) {
            $message = 'You can only decline registrations for elections visible to your department scope.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        if (!$this->canSeeStudentForRegistrationElection($user, $student, $election)) {
            $message = $this->isGlobalElectionDepartment($election->department)
                ? 'For CSG elections, you can only manage applicants from your own department.'
                : 'Selected student does not belong to the current election department.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        $registration->update([
            'status' => 'rejected',
            'reviewed_by_staff_id' => $user->id,
            'reviewed_at' => now(),
            'decline_reason' => $reason,
        ]);

        CandidateApplication::where('user_id', $registration->user_id)
            ->where('election_id', $registration->campus_election_id)
            ->whereRaw('UPPER(status) = ?', ['PENDING'])
            ->update([
                'status' => 'REJECTED',
                'decision_description' => $reason,
            ]);

        $student->notify(new CandidateRegistrationDeclinedNotification($registration, $reason));

        return response()->json([
            'success' => true,
            'message' => 'Candidate registration declined successfully.'
        ]);
    }

    public function updateCandidate(Request $request, Candidate $candidate)
    {
        $user = $this->requireDepartmentPortalPermission('add_candidates');

        $candidateElection = $candidate->campusElection;
        if (!$candidateElection) {
            return response()->json([
                'success' => false,
                'message' => 'Candidate is not linked to a valid election.'
            ], 422);
        }

        if (!$this->canManageElectionDepartment($user, $candidateElection->department)) {
            $message = $this->isGlobalElectionDepartment($candidateElection->department)
                ? 'Only the CSG department can update candidates in CSG elections.'
                : 'You can only update candidates in your department.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        // Check if only description is being updated
        if ($request->has('description') && count($request->all()) <= 2) { // 2 = _method + description
            $validated = $request->validate([
                'description' => 'nullable|string|max:250',
                'partylist' => 'nullable|string|max:255',
            ]);

            $validated['description'] = trim((string) ($validated['description'] ?? ''));
            $validated['partylist'] = trim((string) ($validated['partylist'] ?? ''));

            $allowedPartylists = array_values(array_filter(array_map(function ($team) {
                return trim((string) ($team['name'] ?? ''));
            }, (array) ($candidateElection->partylist_teams ?? []))));

            if ($validated['partylist'] !== '' && !in_array($validated['partylist'], $allowedPartylists, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected partylist is not available in the current election.'
                ], 422);
            }

            $validated['partylist'] = $validated['partylist'] !== '' ? $validated['partylist'] : null;
            
            $candidate->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Candidate advocacy updated successfully!',
                'candidate' => $candidate
            ]);
        }

        // Full update
        $validated = $request->validate([
            'campus_election_id' => 'required|exists:campus_elections,id',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'student_id' => 'required|string|max:50|unique:candidates,student_id,' . $candidate->id,
            'partylist' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:250',
            'photo' => 'nullable|image|max:2048',
        ]);

        $validated['description'] = trim((string) ($validated['description'] ?? ''));

        // Verify the election belongs to the head's department
        $election = CampusElection::findOrFail($validated['campus_election_id']);
        if (!$this->canManageElectionDepartment($user, $election->department)) {
            $message = $this->isGlobalElectionDepartment($election->department)
                ? 'Only the CSG department can assign candidates to CSG elections.'
                : 'You can only assign candidates to elections in your department.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('candidates', 'public');
            $validated['image'] = '/storage/' . $path;
            unset($validated['photo']);
        }

        $allowedPartylists = array_values(array_filter(array_map(function ($team) {
            return trim((string) ($team['name'] ?? ''));
        }, (array) ($election->partylist_teams ?? []))));

        $selectedPartylist = trim((string) ($validated['partylist'] ?? ''));
        if ($selectedPartylist !== '' && !in_array($selectedPartylist, $allowedPartylists, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected partylist is not available in the current election.'
            ], 422);
        }

        $validated['partylist'] = $selectedPartylist !== '' ? $selectedPartylist : null;

        $candidate->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Candidate updated successfully!',
            'candidate' => $candidate
        ]);
    }

    public function destroyCandidate(Candidate $candidate)
    {
        $user = $this->requireDepartmentPortalPermission('add_candidates');

        $candidateElection = $candidate->campusElection;
        if (!$candidateElection) {
            return response()->json([
                'success' => false,
                'message' => 'Candidate is not linked to a valid election.'
            ], 422);
        }

        if (!$this->canManageElectionDepartment($user, $candidateElection->department)) {
            $message = $this->isGlobalElectionDepartment($candidateElection->department)
                ? 'Only the CSG department can remove candidates from CSG elections.'
                : 'You can only delete candidates in your department.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        $candidate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Candidate deleted successfully!'
        ]);
    }

    public function events()
    {
        $user = $this->currentDepartmentHeadUser();
        $staffProfile = $this->currentDepartmentHeadStaff();
        $department = $user->department;
        $visibleEventDepts = $this->visibleElectionDepts($user);
        $manageableDepartments = $this->staffDepts($user);
        $isDepartmentHeadActor = (bool) ($staffProfile->is_department_head ?? false);
        $scopeLabel = $this->isCsgDepartmentHead($user)
            ? 'CSG department'
            : ($department . ' department + CSG global posts');
        
        $events = Event::whereIn('department', $visibleEventDepts)
            ->with(['likes', 'staff'])
            ->latest()
            ->get();

        // Backfill legacy posts where staff_id was not stored yet.
        $legacyPosterIds = $events
            ->filter(fn (Event $event) => !$event->staff && !empty($event->user_id))
            ->pluck('user_id')
            ->unique()
            ->values();

        if ($legacyPosterIds->isNotEmpty()) {
            $legacyPosterMap = Staff::whereIn('id', $legacyPosterIds)->get()->keyBy('id');
            $events->each(function (Event $event) use ($legacyPosterMap) {
                if (!$event->staff && !empty($event->user_id) && $legacyPosterMap->has($event->user_id)) {
                    $event->setRelation('staff', $legacyPosterMap->get($event->user_id));
                }
            });
        }

        $events->each(function (Event $event) use ($staffProfile, $isDepartmentHeadActor, $manageableDepartments) {
            $ownsEvent = ((int) ($event->staff_id ?? 0) === (int) $staffProfile->id)
                || (empty($event->staff_id) && (int) ($event->user_id ?? 0) === (int) $staffProfile->id);

            $event->can_manage_event = $isDepartmentHeadActor
                ? in_array($event->department, $manageableDepartments, true)
                : $ownsEvent;
        });
        
        return view('department-head.events', compact('events', 'department', 'manageableDepartments', 'scopeLabel', 'staffProfile', 'isDepartmentHeadActor'));
    }

    public function storeEvent(Request $request)
    {
        $user = $this->requireDepartmentPortalPermission('post_events');
        $staffProfile = $this->currentDepartmentHeadStaff();
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department' => 'required|string',
            'event_date' => 'required|date',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Ensure the department matches the head's department
        if (!in_array($validated['department'], $this->staffDepts($user))) {
            return response()->json([
                'success' => false,
                'message' => 'You can only create events for your department.'
            ], 403);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('events', 'public');
            $validated['image'] = $imagePath;
        }

        try {
            // Staff-owned posts should use staff_id as the source of ownership.
            $validated['user_id'] = null;
            $validated['staff_id'] = $staffProfile->id;
            $event = Event::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully!',
                'event' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create event: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateEvent(Request $request, Event $event)
    {
        $user = $this->requireDepartmentPortalPermission('post_events');

        if (!$this->canManageEventRecord($user, $event)) {
            $message = (bool) ($user->is_department_head ?? false)
                ? 'You can only update events in your department.'
                : 'You can only update events that you posted.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        try {
            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'department'  => 'required|string',
                'event_date'  => 'required|date',
                'description' => 'required|string',
                'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                if ($event->image) {
                    \Storage::disk('public')->delete($event->image);
                }
                $imagePath = $request->file('image')->store('events', 'public');
                $validated['image'] = $imagePath;
            }

            $event->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully!',
                'event'   => $event->fresh()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update event: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyEvent(Event $event)
    {
        $user = $this->requireDepartmentPortalPermission('post_events');
        
        if (!$this->canManageEventRecord($user, $event)) {
            $message = (bool) ($user->is_department_head ?? false)
                ? 'You can only delete events in your department.'
                : 'You can only delete events that you posted.';

            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully!'
        ]);
    }

    public function toggleEventLike(Request $request, Event $event)
    {
        $user = $this->currentDepartmentHeadUser();
        if (!in_array($event->department, $this->visibleElectionDepts($user), true)) {
            return response()->json([
                'success' => false,
                'message' => 'You can only react to events visible to your department.'
            ], 403);
        }
        
        // Check if user already liked the event
        $existingLike = $event->likes()->where('user_id', $user->id)->first();
        
        if ($existingLike) {
            // Unlike
            $existingLike->delete();
            return response()->json([
                'success' => true,
                'liked' => false,
                'likes_count' => $event->likes()->count(),
                'message' => 'Event unliked!'
            ]);
        } else {
            // Like
            $event->likes()->create(['user_id' => $user->id]);
            return response()->json([
                'success' => true,
                'liked' => true,
                'likes_count' => $event->likes()->count(),
                'message' => 'Event liked!'
            ]);
        }
    }

    // Student Management Methods
    public function students()
    {
        $user = $this->requireDepartmentPortalPermission('approve_students');
        $department = $user->department;
        $depts = $this->staffDepts($user);
        $isCsgHead = $this->isCsgDepartmentHead($user);
        
        $studentsQuery = $this->studentAccountsQuery()->with('student');
        if (!$isCsgHead) {
            $studentsQuery->whereIn('department', $depts);
        }

        $students = $studentsQuery
            ->orderBy('department')
            ->orderBy('name')
            ->get();
        
        return view('department-head.students', compact('students', 'department', 'isCsgHead'));
    }

    public function getStudentsList()
    {
        $user = $this->currentDepartmentHeadUser();
        $depts = $this->staffDepts($user);
        $campusElectionId = (int) request()->query('campus_election_id', 0);

        // CSG department head can see students from all departments
        $query = $this->studentAccountsQuery()
            ->where('approval_status', 'approved')
            ->whereNotNull('student_id');

        if (!$this->isCsgDepartmentHead($user)) {
            $query->whereIn('department', $depts);
        }

        if ($campusElectionId > 0) {
            $election = CampusElection::find($campusElectionId);
            if (!$election) {
                return response()->json([
                    'success' => false,
                    'message' => 'Election not found.',
                ], 404);
            }

            if (!$this->canAccessRegistrationElection($user, $election)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to access this election.',
                ], 403);
            }

            if (!$this->isGlobalElectionDepartment($election->department)) {
                $query->whereIn('department', $this->getDeptVariants((string) $election->department));
            }

            $query->whereExists(function ($sub) use ($campusElectionId) {
                $sub->select(DB::raw(1))
                    ->from('candidate_registrations as cr')
                    ->whereColumn('cr.user_id', 'users.id')
                    ->where('cr.campus_election_id', $campusElectionId)
                    ->whereRaw('LOWER(cr.status) = ?', ['approved']);
            });

            $query->whereNotExists(function ($sub) use ($campusElectionId) {
                $sub->select(DB::raw(1))
                    ->from('candidates as c')
                    ->whereColumn('c.student_id', 'users.student_id')
                    ->where('c.campus_election_id', $campusElectionId);
            });
        }

        $students = $query->orderBy('name')
            ->get(['id', 'name', 'middle_name', 'last_name', 'student_id', 'year_level', 'email', 'profile_picture', 'department', 'approval_status']);

        return response()->json([
            'success' => true,
            'students' => $students
        ]);
    }

    public function storeStudent(Request $request)
    {
        $user       = $this->requireDepartmentPortalPermission('approve_students');
        $department = $user->department;

        if ($this->isCsgDepartmentHead($user)) {
            return redirect()->route('department-head.students')
                ->with('error', 'CSG department head has view-only access in Manage Students.');
        }

        // Validate — ValidationException bubbles up naturally so field errors display properly
        $validated = $request->validate([
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'year_level'  => 'required|string',
            'password'    => 'required|string|min:8|confirmed',
            'photo'       => 'nullable|image|max:2048',
        ]);

        $studentId = $this->generateStudentId();

        try {
            // Handle photo upload
            $profilePicture = null;
            if ($request->hasFile('photo')) {
                $path           = $request->file('photo')->store('students', 'public');
                $profilePicture = '/storage/' . $path;
            }

            // Create user record with separate name fields
            $student = User::create([
                'name'            => $validated['first_name'],
                'middle_name'     => $validated['middle_name'] ?? null,
                'last_name'       => $validated['last_name'],
                'email'           => $validated['email'],
                'student_id'      => $studentId,
                'password'        => bcrypt($validated['password']),
                'role'            => 'student',
                'department'      => $department,
                'year_level'      => $validated['year_level'],
                'profile_picture' => $profilePicture,
            ]);

            // Create the students profile table record
            Student::create([
                'user_id'         => $student->id,
                'student_id'      => $studentId,
                'department'      => $department,
                'year_level'      => $validated['year_level'],
                'enrollment_date' => now(),
                'status'          => 'active',
            ]);

            return redirect()->route('department-head.students')
                ->with('success', 'Student added successfully!');

        } catch (\Exception $e) {
            Log::error('storeStudent failed', [
                'error'   => $e->getMessage(),
                'data'    => $validated,
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error adding student: ' . $e->getMessage());
        }
    }

    public function updateStudent(Request $request, User $student)
    {
        $user = $this->requireDepartmentPortalPermission('approve_students');

        if ($this->isCsgDepartmentHead($user)) {
            return redirect()->route('department-head.students')
                ->with('error', 'CSG department head has view-only access in Manage Students.');
        }
        
        // Ensure the student belongs to the same department
        if (!in_array($student->department, $this->staffDepts($user)) || $student->role !== 'student' || $this->isLinkedStaffAccount($student)) {
            return redirect()->route('department-head.students')
                ->with('error', 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $student->id,
            'student_id' => 'required|string|unique:users,student_id,' . $student->id,
            'year_level' => 'required|string',
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|max:2048',
        ]);
        
        // Build update array with separate name fields
        $updateData = [
            'name'        => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name'   => $validated['last_name'],
            'email'       => $validated['email'],
            'student_id'  => $validated['student_id'],
            'year_level'  => $validated['year_level'],
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($student->profile_picture && file_exists(public_path($student->profile_picture))) {
                unlink(public_path($student->profile_picture));
            }
            $path = $request->file('photo')->store('students', 'public');
            $updateData['profile_picture'] = '/storage/' . $path;
        }

        if (!empty($validated['password'])) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $student->update($updateData);

        // Sync students profile table record
        Student::updateOrCreate(
            ['user_id' => $student->id],
            [
                'student_id'  => $validated['student_id'],
                'department'  => $student->department,
                'year_level'  => $validated['year_level'],
            ]
        );

        return redirect()->route('department-head.students')
            ->with('success', 'Student updated successfully!');
    }

    public function destroyStudent(User $student)
    {
        $user = $this->requireDepartmentPortalPermission('approve_students');

        if ($this->isCsgDepartmentHead($user)) {
            return redirect()->route('department-head.students')
                ->with('error', 'CSG department head has view-only access in Manage Students.');
        }
        
        // Ensure the student belongs to the same department
        if (!in_array($student->department, $this->staffDepts($user)) || $student->role !== 'student' || $this->isLinkedStaffAccount($student)) {
            return redirect()->route('department-head.students')
                ->with('error', 'Unauthorized action.');
        }
        
        $student->delete();
        
        return redirect()->route('department-head.students')
            ->with('success', 'Student deleted successfully!');
    }

    public function toggleStudentStatus(User $student)
    {
        $user = $this->requireDepartmentPortalPermission('approve_students');

        if ($this->isCsgDepartmentHead($user)) {
            return redirect()->route('department-head.students')
                ->with('error', 'CSG department head has view-only access in Manage Students.');
        }

        if (!in_array($student->department, $this->staffDepts($user)) || $student->role !== 'student' || $this->isLinkedStaffAccount($student)) {
            return redirect()->route('department-head.students')
                ->with('error', 'Unauthorized action.');
        }

        $studentProfile = Student::firstOrCreate(
            ['user_id' => $student->id],
            [
                'student_id' => $student->student_id,
                'department' => $student->department,
                'year_level' => $student->year_level,
                'enrollment_date' => now(),
                'status' => 'active',
            ]
        );

        $nextStatus = $studentProfile->status === 'active' ? 'inactive' : 'active';
        $studentProfile->update(['status' => $nextStatus]);

        return redirect()->route('department-head.students')
            ->with('success', 'Student ' . ($nextStatus === 'active' ? 'enabled' : 'disabled') . ' successfully!');
    }

    public function editProfile()
    {
        $staff = $this->currentDepartmentHeadStaff();
        return view('department-head.profile', compact('staff'));
    }

    // ── Student Registration Requests ──────────────────────────

    public function studentRequests()
    {
        $user       = $this->requireDepartmentPortalPermission('approve_students');
        $department = $user->department;

        // Map old department codes → new codes so existing staff accounts
        // still see students who registered under the updated department values.
        $deptAliasMap = [
            'IT'          => 'BSIT',
            'BSBA'        => 'CBAE',
            'EDUC'        => 'CTE',
            'ENGINEERING' => 'BSIT',
            'NURSING'     => 'CHTM',
            'PSYCHOLOGY'  => 'CBAE',
            'ACCOUNTANCY' => 'CBAE',
            // new values map to themselves (pass-through)
            'BSIT' => 'BSIT',
            'CBAE' => 'CBAE',
            'CRIM' => 'CRIM',
            'CHTM' => 'CHTM',
            'CTE'  => 'CTE',
            'SHS'  => 'SHS',
        ];

        // Include both the staff's stored value AND the mapped new value
        $mapped  = $deptAliasMap[$department] ?? $department;
        $depts   = array_unique([$department, $mapped]);

        $baseStudentQuery = $this->studentAccountsQuery()->whereIn('department', $depts);

        $pending  = (clone $baseStudentQuery)->where('approval_status', 'pending')->latest()->get();
        $approved = (clone $baseStudentQuery)->where('approval_status', 'approved')->latest()->take(30)->get();
        $denied   = (clone $baseStudentQuery)->where('approval_status', 'denied')->latest()->take(30)->get();

        return view('department-head.student-requests', compact('pending', 'approved', 'denied', 'department'));
    }

    private function staffDepts($staff): array
    {
        return $this->getDeptVariants($staff->department ?? '');
    }

    private function visibleElectionDepts($staff): array
    {
        $depts = $this->staffDepts($staff);
        if (!$this->isCsgDepartmentHead($staff) && !in_array('CSG', $depts, true)) {
            $depts[] = 'CSG';
        }

        return array_values(array_unique($depts));
    }

    private function isGlobalElectionDepartment(?string $department): bool
    {
        $normalized = strtoupper(trim((string) $department));
        $compact = preg_replace('/[^A-Z]/', '', $normalized);

        return in_array($compact, ['CSG', 'CENTRALSTUDENTGOVERNMENT'], true);
    }

    private function isCsgDepartmentHead($staff): bool
    {
        $normalized = strtoupper(trim((string) ($staff->department ?? '')));
        $compact = preg_replace('/[^A-Z]/', '', $normalized);

        return in_array($compact, ['CSG', 'CENTRALSTUDENTGOVERNMENT'], true);
    }

    private function canManageElectionDepartment(Staff $staff, ?string $department): bool
    {
        if ($this->isGlobalElectionDepartment($department)) {
            return $this->isCsgDepartmentHead($staff);
        }

        return in_array(strtoupper(trim((string) $department)), $this->staffDepts($staff), true);
    }

    private function canAccessRegistrationElection(Staff $staff, CampusElection $election): bool
    {
        if ($this->isGlobalElectionDepartment($election->department)) {
            // CSG registration list is visible to every department head,
            // but applicant rows are still filtered by the head's own department.
            return true;
        }

        return in_array(strtoupper(trim((string) $election->department)), $this->staffDepts($staff), true);
    }

    private function canSeeStudentForRegistrationElection(Staff $staff, ?User $student, CampusElection $election): bool
    {
        if (!$student) {
            return false;
        }

        if ($this->isGlobalElectionDepartment($election->department)) {
            if ($this->isCsgDepartmentHead($staff)) {
                // CSG head can review all applicants in CSG elections.
                return true;
            }

            return $this->sameDept((string) ($student->department ?? ''), (string) ($staff->department ?? ''));
        }

        return $this->sameDept((string) ($student->department ?? ''), (string) ($election->department ?? ''));
    }

    private function canManageEventRecord(Staff $staff, Event $event): bool
    {
        if (!in_array($event->department, $this->staffDepts($staff), true)) {
            return false;
        }

        if ((bool) ($staff->is_department_head ?? false)) {
            return true;
        }

        $staffId = (int) $staff->id;
        if ((int) ($event->staff_id ?? 0) === $staffId) {
            return true;
        }

        // Legacy fallback: early staff posts were stored in user_id.
        return empty($event->staff_id) && (int) ($event->user_id ?? 0) === $staffId;
    }

    public function approveStudent(User $user)
    {
        $staff = $this->requireDepartmentPortalPermission('approve_students');
        if (!in_array($user->department, $this->staffDepts($staff)) || $user->role !== 'student' || $this->isLinkedStaffAccount($user)) {
            return back()->with('error', 'Unauthorized action.');
        }
        $user->update(['approval_status' => 'approved']);
        return back()->with('success', "Account for {$user->name} has been approved.");
    }

    public function denyStudent(User $user)
    {
        $staff = $this->requireDepartmentPortalPermission('approve_students');
        if (!in_array($user->department, $this->staffDepts($staff)) || $user->role !== 'student' || $this->isLinkedStaffAccount($user)) {
            return back()->with('error', 'Unauthorized action.');
        }
        $user->update(['approval_status' => 'denied']);
        return back()->with('success', "Account for {$user->name} has been denied.");
    }

    private function studentAccountsQuery()
    {
        return User::query()
            ->where('role', 'student')
            ->whereNotIn('email', Staff::query()->select('email'));
    }

    private function isLinkedStaffAccount(User $user): bool
    {
        return Staff::where('email', $user->email)->exists();
    }

    public function faculty()
    {
        $staff = $this->currentDepartmentHeadUser();
        $this->assertDepartmentHeadOnly($staff);

        $facultyMembers = Staff::whereIn('department', $this->staffDepts($staff))
            ->where('is_department_head', false)
            ->where('can_access_department_portal', true)
            ->orderByDesc('created_at')
            ->get();

        $assignableFaculties = Staff::whereIn('department', $this->staffDepts($staff))
            ->where('is_department_head', false)
            ->where('can_access_department_portal', false)
            ->orderBy('name')
            ->orderBy('last_name')
            ->get([
                'id',
                'name',
                'middle_name',
                'last_name',
                'email',
                'employee_id',
                'department',
                'profile_picture',
                'can_access_department_portal',
                'department_portal_permissions',
            ]);

        $permissionLabels = $this->departmentPortalPermissionLabels();

        return view('department-head.faculty', compact('facultyMembers', 'assignableFaculties', 'permissionLabels'));
    }

    public function storeFaculty(Request $request)
    {
        $staff = $this->currentDepartmentHeadUser();
        $this->assertDepartmentHeadOnly($staff);

        $permissionKeys = array_keys($this->departmentPortalPermissionLabels());

        $validated = $request->validate([
            'faculty_id' => 'required|integer|exists:staff,id',
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in($permissionKeys)],
        ]);

        $permissions = array_values(array_unique($validated['permissions'] ?? []));

        $faculty = Staff::findOrFail($validated['faculty_id']);
        $this->assertAssignableFaculty($staff, $faculty);

        if ((bool) ($faculty->can_access_department_portal ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected faculty already has portal access.',
            ], 422);
        }

        $faculty->update([
            'position' => 'Faculty',
            'can_access_department_portal' => true,
            'department_portal_permissions' => $permissions,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Faculty access assigned successfully.',
            'faculty' => $faculty->fresh(),
        ]);
    }

    public function updateFaculty(Request $request, Staff $faculty)
    {
        $staff = $this->currentDepartmentHeadUser();
        $this->assertDepartmentHeadOnly($staff);
        $this->assertAssignableFaculty($staff, $faculty);

        $permissionKeys = array_keys($this->departmentPortalPermissionLabels());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email,' . $faculty->id,
            'password' => 'nullable|string|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in($permissionKeys)],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'department_portal_permissions' => array_values(array_unique($validated['permissions'] ?? [])),
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = bcrypt($validated['password']);
        }

        if ($request->hasFile('profile_picture')) {
            if (!empty($faculty->profile_picture)) {
                $oldPath = public_path($faculty->profile_picture);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $path = $request->file('profile_picture')->store('staff', 'public');
            $payload['profile_picture'] = '/storage/' . $path;
        }

        $faculty->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Faculty access account updated successfully.',
            'faculty' => $faculty->fresh(),
        ]);
    }

    public function toggleFacultyAccess(Staff $faculty)
    {
        $staff = $this->currentDepartmentHeadUser();
        $this->assertDepartmentHeadOnly($staff);
        $this->assertAssignableFaculty($staff, $faculty);

        $faculty->can_access_department_portal = !(bool) $faculty->can_access_department_portal;
        $faculty->save();

        return response()->json([
            'success' => true,
            'message' => 'Faculty portal access ' . ($faculty->can_access_department_portal ? 'enabled' : 'disabled') . ' successfully.',
            'can_access_department_portal' => (bool) $faculty->can_access_department_portal,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $this->currentDepartmentHeadUser();
        $staff = $this->currentDepartmentHeadStaff();

        $rules = [
            'name'            => 'required|string|max:255',
            'middle_name'     => 'nullable|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email,' . $user->id . '|unique:staff,email,' . $staff->id,
            'phone_number'    => 'nullable|string|max:20',
            'office_location' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'current_password'=> 'nullable|string',
            'password'        => 'nullable|string|min:8|confirmed',
        ];

        $validated = $request->validate($rules);

        // Password change — verify current password first
        if (!empty($validated['password'])) {
            if (empty($request->current_password) || !\Illuminate\Support\Facades\Hash::check($request->current_password, $staff->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }
        unset($validated['current_password']);

        // Profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old file if it exists
            if ($staff->profile_picture) {
                $oldPath = public_path($staff->profile_picture);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $path = $request->file('profile_picture')->store('staff', 'public');
            $validated['profile_picture'] = '/storage/' . $path;
        } else {
            unset($validated['profile_picture']);
        }

        $user->update([
            'name' => $validated['name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'profile_picture' => $validated['profile_picture'] ?? $user->profile_picture,
            ...(isset($validated['password']) ? ['password' => $validated['password']] : []),
        ]);

        $staff->update([
            'name' => $validated['name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? $staff->phone_number,
            'office_location' => $validated['office_location'] ?? $staff->office_location,
            'profile_picture' => $validated['profile_picture'] ?? $staff->profile_picture,
            ...(isset($validated['password']) ? ['password' => $validated['password']] : []),
        ]);

        return back()->with('success', 'Profile updated successfully!');
    }

    // ── Bulk Import Students from Excel ────────────────────────────────
    public function importStudents(Request $request)
    {
        $user = $this->requireDepartmentPortalPermission('approve_students');

        if ($this->isCsgDepartmentHead($user)) {
            return response()->json([
                'success' => false,
                'message' => 'CSG department head has view-only access in Manage Students.'
            ], 403);
        }

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $department = $user->department;

        try {
            $file       = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true);

            // Skip header row (row 1)
            $imported = 0;
            $skipped  = [];
            $errors   = [];

            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex === 1) continue; // header

                // Normalise column keys (A-F)
                $studentId  = trim($row['A'] ?? '');
                $firstName  = trim($row['B'] ?? '');
                $middleName = trim($row['C'] ?? '') ?: null;
                $lastName   = trim($row['D'] ?? '');
                $email      = trim($row['E'] ?? '');
                $yearLevel  = trim($row['F'] ?? '');
                $password   = trim($row['G'] ?? '');

                if (!$studentId && !$firstName && !$lastName && !$email) continue; // blank row

                // Basic field checks
                if (!$firstName || !$lastName || !$email || !$yearLevel) {
                    $errors[] = "Row {$rowIndex}: Missing required fields (First Name, Last Name, Email, Year Level).";
                    continue;
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Row {$rowIndex}: Invalid email '{$email}'.";
                    continue;
                }

                if (!$studentId) {
                    $studentId = $this->generateStudentId();
                }

                // Duplicate check
                if (User::where('email', $email)->exists()) {
                    $skipped[] = "Row {$rowIndex}: Email '{$email}' already exists — skipped.";
                    continue;
                }
                if (User::where('student_id', $studentId)->exists()) {
                    $skipped[] = "Row {$rowIndex}: Student ID '{$studentId}' already exists — skipped.";
                    continue;
                }

                // Default password
                if (!$password) {
                    $password = 'password';
                }

                try {
                    $student = User::create([
                        'name'            => $firstName,
                        'middle_name'     => $middleName,
                        'last_name'       => $lastName,
                        'email'           => $email,
                        'student_id'      => $studentId,
                        'password'        => bcrypt($password),
                        'role'            => 'student',
                        'department'      => $department,
                        'year_level'      => $yearLevel,
                        'profile_picture' => null,
                    ]);

                    Student::create([
                        'user_id'         => $student->id,
                        'student_id'      => $studentId,
                        'department'      => $department,
                        'year_level'      => $yearLevel,
                        'enrollment_date' => now(),
                        'status'          => 'active',
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowIndex}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success'  => true,
                'imported' => $imported,
                'skipped'  => $skipped,
                'errors'   => $errors,
                'message'  => "{$imported} student(s) imported successfully.",
            ]);

        } catch (\Exception $e) {
            Log::error('importStudents failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to read file: ' . $e->getMessage(),
            ], 422);
        }
    }

    // ── Download Excel Template ─────────────────────────────────────────
    public function downloadStudentTemplate()
    {
        $this->requireDepartmentPortalPermission('approve_students');

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Students');

        // Headers
        $headers = ['A1' => 'student_id (optional)', 'B1' => 'first_name', 'C1' => 'middle_name', 'D1' => 'last_name', 'E1' => 'email', 'F1' => 'year_level', 'G1' => 'password (optional)'];
        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Note row (row 2 — no sample data)
        $sheet->setCellValue('A2', '* student_id is optional. If blank, the system will auto-generate a YYMMDD-based ID. Password is optional. If blank, the default password will be: password');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9);
        $sheet->mergeCells('A2:G2');

        $writer = new Xlsx($spreadsheet);
        $filename = 'students_import_template.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        ob_end_clean();
        $writer->save('php://output');
        exit;
    }

    private function generateStudentId(): string
    {
        $prefix = now()->format('ymd');
        $candidate = $prefix;
        $sequence = 1;

        while (User::where('student_id', $candidate)->exists() || Student::where('student_id', $candidate)->exists()) {
            $candidate = $prefix . $sequence;
            $sequence++;
        }

        return $candidate;
    }

    private function currentDepartmentHeadUser(): Staff
    {
        $staff = Auth::guard('department_head')->user();

        if (!$staff instanceof Staff || (!((bool) ($staff->is_department_head ?? false)) && !((bool) ($staff->can_access_department_portal ?? false)))) {
            abort(403, 'Unauthorized access.');
        }

        return $staff;
    }

    private function requireDepartmentPortalPermission(string $permission): Staff
    {
        $staff = $this->currentDepartmentHeadUser();

        if ($staff->hasDepartmentPortalPermission($permission)) {
            return $staff;
        }

        $labels = $this->departmentPortalPermissionLabels();
        $message = 'Unauthorized action.';
        if (isset($labels[$permission])) {
            $message = 'You are not authorized to ' . strtolower($labels[$permission]) . '.';
        }

        if (request()->expectsJson() || request()->ajax()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => $message,
            ], 403));
        }

        abort(403, $message);
    }

    private function assertDepartmentHeadOnly(Staff $staff): void
    {
        if ((bool) ($staff->is_department_head ?? false)) {
            return;
        }

        abort(403, 'Only department heads can manage faculty access.');
    }

    private function assertAssignableFaculty(Staff $staff, Staff $faculty): void
    {
        if ((bool) ($faculty->is_department_head ?? false)) {
            abort(403, 'Department head accounts cannot be managed from this page.');
        }

        if (!in_array($faculty->department, $this->staffDepts($staff), true)) {
            abort(403, 'You can only manage faculty in your department.');
        }
    }

    private function departmentPortalPermissionLabels(): array
    {
        return [
            'create_election' => 'Create Elections',
            'add_candidates' => 'Add Candidates',
            'post_events' => 'Post Events',
            'approve_students' => 'Approve Students',
        ];
    }

    private function currentDepartmentHeadStaff(): Staff
    {
        return $this->currentDepartmentHeadUser();
    }

    private function generateEmployeeId(): string
    {
        $maxId = (int) Staff::max('id') + 1;

        return 'EMP-' . str_pad((string) $maxId, 4, '0', STR_PAD_LEFT);
    }
}


