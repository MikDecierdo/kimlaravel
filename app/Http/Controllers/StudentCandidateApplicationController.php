<?php

namespace App\Http\Controllers;

use App\Models\CandidateApplication;
use App\Models\CandidateRegistration;
use App\Models\CampusElection;
use App\Traits\DeptVariantHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentCandidateApplicationController extends Controller
{
    use DeptVariantHelper;

    public function index()
    {
        $student = Auth::guard('student')->user();
        $elections = $this->buildElectionCollection($student);
        $applications = $this->buildStudentApplications($student);

        return view('student.candidate-applications', compact('elections', 'applications'));
    }

    public function statuses()
    {
        $student = Auth::guard('student')->user();
        $applications = $this->buildStudentApplications($student);

        $payload = $applications->map(function (CandidateApplication $application) {
            $responses = (array) ($application->form_responses ?? []);
            $description = trim((string) ($application->decision_description ?? ''));
            if ($description === '') {
                $description = trim((string) ($responses['platform_statement'] ?? $responses['description'] ?? ''));
            }

            return [
                'id' => $application->id,
                'status' => strtoupper((string) $application->status),
                'election_name' => optional($application->election)->election_name ?? 'Election removed',
                'department' => optional($application->election)->department ?? 'N/A',
                'submitted_at' => optional($application->submitted_at)->format('M d, Y h:i A') ?? optional($application->created_at)->format('M d, Y h:i A'),
                'position' => $responses['position'] ?? 'N/A',
                'description' => $description !== '' ? $description : 'N/A',
            ];
        })->values();

        return response()->json([
            'success' => true,
            'applications' => $payload,
        ]);
    }

    public function store(Request $request)
    {
        $student = Auth::guard('student')->user();

        $validated = $request->validate([
            'election_id' => 'required|exists:campus_elections,id',
            'form_responses' => 'required|array',
        ]);

        $election = CampusElection::findOrFail($validated['election_id']);
        $today = now()->toDateString();

        if (!$this->canStudentAccessElection($student->department ?? '', $election)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to apply for this election.',
            ], 403);
        }

        if ($this->deriveRegistrationStatus($election, $today) !== 'OPEN') {
            return response()->json([
                'success' => false,
                'message' => 'Registration period is not active for this election.',
            ], 422);
        }

        $alreadyApplied = CandidateApplication::where('user_id', $student->id)
            ->where('election_id', $election->id)
            ->exists();

        if ($alreadyApplied) {
            return response()->json([
                'success' => false,
                'message' => 'You already submitted an application for this election.',
            ], 422);
        }

        $schema = is_array($election->candidate_registration_schema) && count($election->candidate_registration_schema) > 0
            ? $election->candidate_registration_schema
            : CampusElection::defaultCandidateRegistrationSchema();

        $responses = (array) $validated['form_responses'];

        $statementOfPurpose = trim((string) ($responses['platform_statement'] ?? $responses['statement_of_purpose'] ?? ''));
        if ($statementOfPurpose !== '') {
            $responses['platform_statement'] = $statementOfPurpose;
            $responses['statement_of_purpose'] = $statementOfPurpose;
        }

        // Keep canonical student profile keys present so schema-required readonly fields always validate.
        $responses['full_name'] = trim((string) ($responses['full_name'] ?? implode(' ', array_filter([$student->name, $student->middle_name, $student->last_name]))));
        $responses['student_id'] = (string) ($responses['student_id'] ?? $student->student_id ?? '');
        $responses['year_level'] = (string) ($responses['year_level'] ?? $student->year_level ?? '');
        $responses['department'] = (string) ($responses['department'] ?? $student->department ?? '');

        foreach ($schema as $field) {
            if (!is_array($field)) {
                continue;
            }

            $key = trim((string) ($field['key'] ?? ''));
            if ($key === '') {
                continue;
            }

            $isRequired = (bool) ($field['required'] ?? false);
            $value = $responses[$key] ?? null;

            if ($isRequired && (is_null($value) || trim((string) $value) === '')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please complete all required fields before submitting.',
                ], 422);
            }

            if (($field['type'] ?? '') === 'select' && ($field['source'] ?? '') === 'election_positions') {
                $allowed = array_values(array_filter((array) ($election->positions ?? []), fn ($position) => is_string($position) && trim($position) !== ''));
                if (!in_array((string) $value, $allowed, true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected position is not available in this election.',
                    ], 422);
                }
            }
        }

        $normalizedResponses = array_merge($responses, [
            'full_name' => trim(implode(' ', array_filter([$student->name, $student->middle_name, $student->last_name]))),
            'student_id' => $student->student_id,
            'year_level' => $student->year_level,
            'department' => $student->department,
        ]);

        CandidateApplication::create([
            'user_id' => $student->id,
            'student_id' => (string) ($student->student_id ?? ''),
            'election_id' => $election->id,
            'form_responses' => $normalizedResponses,
            'status' => 'PENDING',
            'submitted_at' => now(),
        ]);

        $allowedPositions = array_values(array_filter((array) ($election->positions ?? []), fn ($position) => is_string($position) && trim($position) !== ''));
        $requestedPosition = trim((string) ($normalizedResponses['position'] ?? ''));

        if ($requestedPosition === '' && count($allowedPositions) > 0) {
            foreach ($normalizedResponses as $value) {
                $candidateValue = trim((string) $value);
                if (in_array($candidateValue, $allowedPositions, true)) {
                    $requestedPosition = $candidateValue;
                    break;
                }
            }
        }

        if ($requestedPosition !== '' && in_array($requestedPosition, $allowedPositions, true)) {
            $pendingRegistration = CandidateRegistration::where('status', 'pending')
                ->where('campus_election_id', $election->id)
                ->where('user_id', $student->id)
                ->first();

            if ($pendingRegistration) {
                $pendingRegistration->update([
                    'position' => $requestedPosition,
                    'description' => trim((string) ($normalizedResponses['platform_statement'] ?? '')),
                ]);
            } else {
                CandidateRegistration::create([
                    'campus_election_id' => $election->id,
                    'user_id' => $student->id,
                    'position' => $requestedPosition,
                    'description' => trim((string) ($normalizedResponses['platform_statement'] ?? '')),
                    'status' => 'pending',
                    'submitted_by_staff_id' => null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully. Your department head can now review it in the Registration List.',
        ]);
    }

    private function deriveRegistrationStatus(CampusElection $election, string $today): string
    {
        $start = optional($election->registration_start_date)->toDateString()
            ?: optional($election->start_date)->toDateString();
        $end = optional($election->registration_end_date)->toDateString()
            ?: optional($election->end_date)->toDateString();

        if (!(bool) $election->is_active) {
            return 'CLOSED';
        }

        if ($start && $today < $start) {
            return 'UPCOMING';
        }

        if ($end && $today > $end) {
            return 'CLOSED';
        }

        return 'OPEN';
    }

    private function buildElectionCollection($student)
    {
        $departmentVariants = $this->visibleElectionDepts($student->department ?? '');
        $today = now()->toDateString();

        return CampusElection::whereIn('department', $departmentVariants)
            ->orderByDesc('start_date')
            ->get()
            ->map(function (CampusElection $election) use ($today) {
                $status = $this->deriveRegistrationStatus($election, $today);
                $schema = is_array($election->candidate_registration_schema) && count($election->candidate_registration_schema) > 0
                    ? $election->candidate_registration_schema
                    : CampusElection::defaultCandidateRegistrationSchema();

                $election->registration_status = $status;
                $election->candidate_registration_schema = $schema;

                return $election;
            })
            ->values();
    }

    private function buildStudentApplications($student)
    {
        return CandidateApplication::with('election:id,election_name,department,registration_start_date,registration_end_date,start_date,end_date,is_active')
            ->where('user_id', $student->id)
            ->latest('submitted_at')
            ->get();
    }

    private function canStudentAccessElection(string $department, CampusElection $election): bool
    {
        $variants = $this->visibleElectionDepts($department);
        return in_array(strtoupper(trim((string) $election->department)), $variants, true);
    }

    private function visibleElectionDepts(string $department): array
    {
        $depts = $this->getDeptVariants($department);
        if (!in_array('CSG', $depts, true)) {
            $depts[] = 'CSG';
        }

        return array_values(array_unique($depts));
    }
}
