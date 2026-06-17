<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Event;
use App\Models\User;
use App\Models\Staff;
use App\Models\CampusElection;
use Illuminate\Http\Request;
use App\Notifications\CandidateAddedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminController extends Controller
{
    public function candidates()
    {
        // All elections with their candidates for the folder nav (Dept → Election → Candidates)
        $elections = CampusElection::with(['candidates' => function ($q) {
            $q->orderBy('position')->orderBy('first_name');
        }])->withCount('candidates')->orderBy('department')->orderBy('election_name')->get();

        // Group elections by department for level-1 folder view
        $electionsByDept = $elections->groupBy('department');

        // Flat candidates list for the edit modal JS lookup
        $candidates = Candidate::all();

        // Active elections for the add-candidate modal dropdowns
        $activeElections = CampusElection::where('is_active', true)->get();

        return view('admin.candidates', compact('elections', 'electionsByDept', 'candidates', 'activeElections'));
    }

    public function storeCandidate(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'student_id' => 'required|string|max:50',
            'position' => 'required|string|max:255',
            'department' => 'required|string',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'image' => 'nullable|url'
        ]);

        // Verify the student exists and is from the specified department
        $student = $this->studentAccountsQuery()
            ->where('student_id', $validated['student_id'])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student ID not found in the system.'
            ], 400);
        }

        if ($student->department !== $validated['department']) {
            return response()->json([
                'success' => false,
                'message' => 'Student does not belong to the selected department.'
            ], 400);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('candidates', 'public');
            $validated['image'] = '/storage/' . $path;
            unset($validated['photo']);
        }

        $candidate = Candidate::create($validated);

        // Notify department heads of the same department
        $departmentHeads = Staff::where('department', $candidate->department)
            ->where('is_department_head', true)
            ->get();

        if ($departmentHeads->count() > 0) {
            Notification::send($departmentHeads, new CandidateAddedNotification($candidate, 'Admin'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Candidate added successfully!',
            'candidate' => $candidate
        ]);
    }

    public function updateCandidate(Request $request, Candidate $candidate)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'student_id' => 'required|string|max:50',
            'position' => 'required|string|max:255',
            'department' => 'required|string',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'image' => 'nullable|url'
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('candidates', 'public');
            $validated['image'] = '/storage/' . $path;
            unset($validated['photo']);
        }

        $candidate->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Candidate updated successfully!',
            'candidate' => $candidate
        ]);
    }

    public function destroyCandidate(Candidate $candidate)
    {
        $candidate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Candidate deleted successfully!'
        ]);
    }

    public function campusElections()
    {
        $elections = CampusElection::latest()->get();
        return view('admin.campus-elections', compact('elections'));
    }

    public function storeCampusElection(Request $request)
    {
        try {
            $validated = $request->validate([
                'department' => 'required|string',
                'election_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'positions' => 'required|array',
                'positions.*' => 'required|string|max:255',
                'candidate_registration_schema' => 'nullable|array',
                'registration_start_date' => 'required|date',
                'registration_end_date' => 'required|date|after_or_equal:registration_start_date|before:start_date',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'is_active' => 'nullable',
                'banner_image' => 'nullable|image|max:2048'
            ]);

            $validated['candidate_registration_schema'] =
                (is_array($validated['candidate_registration_schema'] ?? null) && count($validated['candidate_registration_schema']) > 0)
                    ? $validated['candidate_registration_schema']
                    : CampusElection::defaultCandidateRegistrationSchema();

            $validated['is_active'] = $request->has('is_active') && $request->input('is_active') !== 'false';

            if ($request->hasFile('banner_image')) {
                $path = $request->file('banner_image')->store('elections', 'public');
                $validated['banner_image'] = '/storage/' . $path;
            }

            $election = CampusElection::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Campus election added successfully!',
                'election' => $election
            ]);
        } catch (\Exception $e) {
            \Log::error('Campus Election Creation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateCampusElection(Request $request, CampusElection $campusElection)
    {
        try {
            $validated = $request->validate([
                'department' => 'required|string',
                'election_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'positions' => 'required|array',
                'positions.*' => 'required|string|max:255',
                'candidate_registration_schema' => 'nullable|array',
                'registration_start_date' => 'required|date',
                'registration_end_date' => 'required|date|after_or_equal:registration_start_date|before:start_date',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'is_active' => 'nullable',
                'banner_image' => 'nullable|image|max:2048'
            ]);

            $validated['candidate_registration_schema'] =
                (is_array($validated['candidate_registration_schema'] ?? null) && count($validated['candidate_registration_schema']) > 0)
                    ? $validated['candidate_registration_schema']
                    : ($campusElection->candidate_registration_schema ?: CampusElection::defaultCandidateRegistrationSchema());

            $validated['is_active'] = $request->has('is_active') && $request->input('is_active') !== 'false';

            if ($request->hasFile('banner_image')) {
                $path = $request->file('banner_image')->store('elections', 'public');
                $validated['banner_image'] = '/storage/' . $path;
            }

            $campusElection->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Campus election updated successfully!',
                'election' => $campusElection
            ]);
        } catch (\Exception $e) {
            \Log::error('Campus Election Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleCampusElection(Request $request, CampusElection $campusElection)
    {
        $campusElection->update([
            'is_active' => $request->input('is_active')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Election status updated successfully!'
        ]);
    }

    public function destroyCampusElection(CampusElection $campusElection)
    {
        $campusElection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Campus election deleted successfully!'
        ]);
    }

    public function events()
    {
        $events = Event::with(['user', 'likes'])->latest()->get();

        return view('admin.events', compact('events'));
    }

    public function students()
    {
        $students = $this->studentAccountsQuery()->get();
        return view('admin.students', compact('students'));
    }

    public function faculties()
    {
        $faculties = Staff::orderByDesc('is_department_head')
            ->orderBy('department')
            ->orderBy('name')
            ->get();

        $courses = $this->facultyCourses();

        return view('admin.faculties', compact('faculties', 'courses'));
    }

    public function storeFaculty(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email',
            'department' => ['required', 'string', Rule::in($this->facultyCourses())],
            'password' => 'required|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $middleName = trim((string) ($validated['middle_name'] ?? ''));
        $existingFaculty = $this->findFacultyByFullName(
            $validated['first_name'],
            $middleName,
            $validated['last_name']
        );

        if ($existingFaculty) {
            $sameEmail = strcasecmp((string) $existingFaculty->email, (string) $validated['email']) === 0;
            return response()->json([
                'success' => false,
                'message' => $sameEmail
                    ? 'A faculty account with the same first, middle, and last name already exists.'
                    : 'A faculty account with the same first, middle, and last name already exists. A different email cannot be used for the same faculty name.',
            ], 422);
        }

        $payload = [
            'name' => $validated['first_name'],
            'middle_name' => $middleName !== '' ? $middleName : null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'employee_id' => $this->generateFacultyEmployeeId(),
            'department' => $validated['department'],
            'position' => 'Faculty',
            'is_department_head' => false,
            'can_access_department_portal' => false,
            'can_access_faculty_system' => true,
            'department_portal_permissions' => [],
        ];

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('staff', 'public');
            $payload['profile_picture'] = '/storage/' . $path;
        }

        Staff::create($payload);

        return redirect()->route('admin.faculties')
            ->with('success', 'Faculty added successfully.');
    }

    public function updateFaculty(Request $request, Staff $faculty)
    {
        $this->assertNotDepartmentHead($faculty);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email,' . $faculty->id,
            'department' => ['required', 'string', Rule::in($this->facultyCourses())],
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $payload = [
            'name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'department' => $validated['department'],
            'position' => 'Faculty',
            'is_department_head' => false,
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = bcrypt($validated['password']);
        }

        if ($request->hasFile('photo')) {
            if (!empty($faculty->profile_picture)) {
                $oldPath = public_path($faculty->profile_picture);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $path = $request->file('photo')->store('staff', 'public');
            $payload['profile_picture'] = '/storage/' . $path;
        }

        $faculty->update($payload);

        return redirect()->route('admin.faculties')
            ->with('success', 'Faculty updated successfully.');
    }

    public function toggleFacultyStatus(Staff $faculty)
    {
        $this->assertNotDepartmentHead($faculty);

        $faculty->can_access_faculty_system = !(bool) $faculty->can_access_faculty_system;
        $faculty->save();

        return redirect()->route('admin.faculties')
            ->with('success', 'Faculty ' . ($faculty->can_access_faculty_system ? 'enabled' : 'disabled') . ' successfully.');
    }

    public function importFaculties(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $allowedCourses = $this->facultyCourses();

        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $imported = 0;
            $skipped = [];
            $errors = [];

            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex === 1) {
                    continue;
                }

                $firstName = trim((string) ($row['A'] ?? ''));
                $middleName = trim((string) ($row['B'] ?? ''));
                $lastName = trim((string) ($row['C'] ?? ''));
                $email = trim((string) ($row['D'] ?? ''));
                $course = strtoupper(trim((string) ($row['E'] ?? '')));
                $password = trim((string) ($row['F'] ?? ''));

                if (!$firstName && !$middleName && !$lastName && !$email && !$course) {
                    continue;
                }

                if (!$firstName || !$lastName || !$email || !$course) {
                    $errors[] = "Row {$rowIndex}: Missing required fields (First Name, Last Name, Email, Course).";
                    continue;
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Row {$rowIndex}: Invalid email '{$email}'.";
                    continue;
                }

                if (!in_array($course, $allowedCourses, true)) {
                    $errors[] = "Row {$rowIndex}: Invalid course '{$course}'. Allowed values: " . implode(', ', $allowedCourses) . '.';
                    continue;
                }

                $existingFaculty = $this->findFacultyByFullName($firstName, $middleName, $lastName);
                if ($existingFaculty) {
                    $sameEmail = strcasecmp((string) $existingFaculty->email, $email) === 0;
                    $skipped[] = $sameEmail
                        ? "Row {$rowIndex}: Faculty '{$firstName} {$middleName} {$lastName}' already exists - skipped."
                        : "Row {$rowIndex}: Faculty '{$firstName} {$middleName} {$lastName}' already exists with a different email - skipped.";
                    continue;
                }

                if (Staff::where('email', $email)->exists()) {
                    $skipped[] = "Row {$rowIndex}: Email '{$email}' already exists - skipped.";
                    continue;
                }

                if ($password === '') {
                    $password = 'password';
                }

                try {
                    Staff::create([
                        'name' => $firstName,
                        'middle_name' => $middleName !== '' ? $middleName : null,
                        'last_name' => $lastName,
                        'email' => $email,
                        'password' => bcrypt($password),
                        'employee_id' => $this->generateFacultyEmployeeId(),
                        'department' => $course,
                        'position' => 'Faculty',
                        'is_department_head' => false,
                        'can_access_department_portal' => false,
                        'department_portal_permissions' => [],
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowIndex}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
                'message' => "{$imported} faculty member(s) imported successfully.",
            ]);
        } catch (\Exception $e) {
            Log::error('importFaculties failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to read file: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function downloadFacultyTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Faculties');

        $headers = [
            'A1' => 'first_name',
            'B1' => 'middle_name',
            'C1' => 'last_name',
            'D1' => 'email',
            'E1' => 'course',
            'F1' => 'password (optional)',
        ];

        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->setCellValue('A2', '* Course must be one of: ' . implode(', ', $this->facultyCourses()) . '. Password is optional (default: password). Faculty ID is auto-generated in YYYYDD + sequence format (example: 2026041).');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9);
        $sheet->mergeCells('A2:F2');

        $writer = new Xlsx($spreadsheet);
        $filename = 'faculties_import_template.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        if (ob_get_length()) {
            ob_end_clean();
        }

        $writer->save('php://output');
        exit;
    }

    public function departmentHeads()
    {
        $departmentHeads = Staff::where('is_department_head', true)->get();
        $assignableFaculties = Staff::where('is_department_head', false)
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
            ]);

        return view('admin.department-heads', compact('departmentHeads', 'assignableFaculties'));
    }

    public function storeDepartmentHead(Request $request)
    {
        $validated = $request->validate([
            'faculty_id' => 'required|integer|exists:staff,id',
            'assign_as_csg_head' => 'nullable|boolean',
        ]);

        $faculty = Staff::findOrFail($validated['faculty_id']);
        $assignAsCsgHead = filter_var($validated['assign_as_csg_head'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $targetDepartment = strtoupper(trim((string) ($assignAsCsgHead ? 'CSG' : ($faculty->department ?? ''))));

        if ((bool) ($faculty->is_department_head ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected account is already a department head.',
            ], 422);
        }

        $existingDepartmentHead = Staff::where('is_department_head', true)
            ->where('id', '!=', $faculty->id)
            ->whereRaw('UPPER(TRIM(department)) = ?', [$targetDepartment])
            ->first();

        if ($existingDepartmentHead) {
            return response()->json([
                'success' => false,
                'message' => 'The ' . $targetDepartment . ' course already has an assigned department head. Unassign the current head first before assigning a new one.',
            ], 422);
        }

        $faculty->update([
            'is_department_head' => true,
            'position' => 'Department Head',
            'department' => $targetDepartment,
            'csg_original_department' => $assignAsCsgHead
                ? (strtoupper(trim((string) $faculty->department)) === 'CSG' ? ($faculty->csg_original_department ?: null) : $faculty->department)
                : null,
            'can_access_department_portal' => true,
            'department_portal_permissions' => Staff::DEPARTMENT_PORTAL_PERMISSIONS,
        ]);

        $departmentHead = $faculty->fresh();

        return response()->json([
            'success'        => true,
            'message'        => $assignAsCsgHead
                ? 'CSG department head assigned successfully from faculty.'
                : 'Department head assigned successfully from faculty.',
            'departmentHead' => $departmentHead
        ]);
    }

    public function updateDepartmentHead(Request $request, Staff $departmentHead)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'middle_name'     => 'nullable|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|email|unique:staff,email,' . $departmentHead->id,
            'password'        => 'nullable|string|min:8',
            'department'      => 'nullable|string',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_department_head'] = true;

        if (!array_key_exists('department', $validated) || $validated['department'] === null || $validated['department'] === '') {
            unset($validated['department']);
        }

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('staff', 'public');
            $validated['profile_picture'] = '/storage/' . $path;
        } else {
            unset($validated['profile_picture']);
        }

        $departmentHead->update($validated);

        return response()->json([
            'success'        => true,
            'message'        => 'Department head updated successfully!',
            'departmentHead' => $departmentHead->fresh()
        ]);
    }

    public function destroyDepartmentHead(Staff $departmentHead)
    {
        $isCurrentCsgDepartment = strtoupper(trim((string) ($departmentHead->department ?? ''))) === 'CSG';
        $fallbackOriginalDepartment = null;

        if ($isCurrentCsgDepartment) {
            $fallbackOriginalDepartment = User::where('id', $departmentHead->user_id)
                ->value('department');
        }

        $restoredDepartment = $departmentHead->csg_original_department
            ?: (($fallbackOriginalDepartment && strtoupper(trim((string) $fallbackOriginalDepartment)) !== 'CSG') ? $fallbackOriginalDepartment : null);

        $departmentHead->update([
            'is_department_head' => false,
            'position' => 'Faculty',
            'department' => $restoredDepartment ?: $departmentHead->department,
            'csg_original_department' => null,
            'can_access_department_portal' => false,
            'department_portal_permissions' => [],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Department head access removed. The account is now a faculty account.'
        ]);
    }

    private function studentAccountsQuery()
    {
        return User::query()
            ->where('role', 'student')
            ->whereNotIn('email', Staff::query()->select('email'));
    }

    private function assertNotDepartmentHead(Staff $faculty): void
    {
        if ((bool) ($faculty->is_department_head ?? false)) {
            abort(403, 'Department head accounts cannot be managed from the Faculties page.');
        }
    }

    private function facultyCourses(): array
    {
        return ['BSIT', 'CBAE', 'CRIM', 'CHTM', 'CTE', 'SHS', 'CSG'];
    }

    private function generateFacultyEmployeeId(): string
    {
        $prefix = now()->format('Yd');
        $idsForDay = Staff::where('employee_id', 'like', $prefix . '%')->pluck('employee_id');

        $maxSequence = 0;
        foreach ($idsForDay as $employeeId) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', (string) $employeeId, $matches)) {
                $sequence = (int) $matches[1];
                if ($sequence > $maxSequence) {
                    $maxSequence = $sequence;
                }
            }
        }

        return $prefix . (string) ($maxSequence + 1);
    }

    private function findFacultyByFullName(string $firstName, string $middleName, string $lastName): ?Staff
    {
        $normalizedFirstName = $this->normalizeFacultyNamePart($firstName);
        $normalizedMiddleName = $this->normalizeFacultyNamePart($middleName);
        $normalizedLastName = $this->normalizeFacultyNamePart($lastName);

        return Staff::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedFirstName])
            ->whereRaw('LOWER(TRIM(COALESCE(middle_name, ""))) = ?', [$normalizedMiddleName])
            ->whereRaw('LOWER(TRIM(last_name)) = ?', [$normalizedLastName])
            ->where('position', 'Faculty')
            ->first();
    }

    private function normalizeFacultyNamePart(string $value): string
    {
        return strtolower(trim($value));
    }
}
