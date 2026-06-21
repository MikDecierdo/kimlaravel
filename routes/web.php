<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VotingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DepartmentHeadController;
use App\Http\Controllers\VotesStatusController;
use App\Http\Controllers\StudentCandidateApplicationController;
use App\Http\Controllers\StudentNotificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Models\ElectionReview;
use App\Models\Vote;
use App\Models\CampusElection;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    if (auth()->guard('department_head')->check()) {
        return redirect()->route('department-head.dashboard');
    }
    if (auth()->guard('student')->check()) {
        return redirect()->route('dashboard');
    }
    $reviews = ElectionReview::with(['user', 'campusElection'])
        ->whereNotNull('review')
        ->where('review', '!=', '')
        ->orderBy('created_at', 'desc')
        ->limit(6)
        ->get();
    $voteCount    = Vote::count();
    $electionCount = CampusElection::count();
    $studentCount  = User::where('role', 'student')->where('approval_status', 'approved')->count();
    return view('landing', compact('reviews', 'voteCount', 'electionCount', 'studentCount'));
})->name('landing');

// Role-Based Authentication Routes
Route::get('/login/admin', [LoginController::class, 'showAdminLoginForm'])->name('login.admin');
Route::post('/login/admin', [LoginController::class, 'loginAdmin'])->middleware('throttle:login-admin');

Route::get('/login/department-head', [LoginController::class, 'showDepartmentHeadLoginForm'])->name('login.department-head');
Route::post('/login/department-head', [LoginController::class, 'loginDepartmentHead'])->middleware('throttle:login-department-head');

Route::get('/login/student', [LoginController::class, 'showStudentLoginForm'])->name('login.student');
Route::post('/login/student', [LoginController::class, 'loginStudent'])->middleware('throttle:login-student');

// Legacy route redirects to student login
Route::get('/login', function () {
    return redirect('/login/student');
})->name('login');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth:student')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->intended(route('dashboard'))->with('success', 'Email verified successfully!');
})->middleware(['auth:student', 'signed'])->name('verification.verify');

Route::post('/email/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('success', 'Verification link resent!');
})->middleware(['auth:student', 'throttle:6,1'])->name('verification.resend');

// Authenticated Routes - Student Guard
Route::middleware(['auth:student', 'validate.session:student'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/faculty/dashboard', [DashboardController::class, 'facultyDashboard'])->name('faculty.dashboard');
    
    // Student Routes
    Route::middleware(['student'])->group(function () {
        Route::get('/voting', [VotingController::class, 'index'])->name('voting');
        Route::get('/voting/election/{election}', [VotingController::class, 'showElection'])->name('voting.election');
        Route::post('/vote/{candidate}', [VotingController::class, 'vote'])->name('vote');
        Route::get('/voting/election/{election}/pending-votes', [VotingController::class, 'getPendingVotes'])->name('voting.pending');
        Route::post('/voting/election/{election}/submit', [VotingController::class, 'submitVotes'])->name('voting.submit');
        Route::post('/voting/election/{election}/review', [VotingController::class, 'storeReview'])->name('voting.review');
        Route::get('/voting/history', [VotingController::class, 'history'])->name('voting.history');
        Route::get('/votes-status', [VotesStatusController::class, 'studentList'])->name('student.votes-status');
        Route::get('/votes-status/{election}', [VotesStatusController::class, 'studentIndex'])->name('student.votes-status.show');
        Route::get('/events', [EventController::class, 'index'])->name('events');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::post('/events/{event}/like', [EventController::class, 'toggleLike'])->name('events.like');
        Route::post('/events/{event}/comment', [EventController::class, 'addComment'])->name('events.comment');
        Route::get('/events/{event}/comments', [EventController::class, 'getComments'])->name('events.comments');
        Route::get('/profile', [\App\Http\Controllers\StudentProfileController::class, 'edit'])->name('student.profile');
        Route::post('/profile', [\App\Http\Controllers\StudentProfileController::class, 'update'])->name('student.profile.update');
        Route::post('/profile/verify-password', [\App\Http\Controllers\StudentProfileController::class, 'verifyPasswordChange'])->name('student.profile.verify-password');
        Route::post('/profile/resend-code', [\App\Http\Controllers\StudentProfileController::class, 'resendVerificationCode'])->name('student.profile.resend-code');
        Route::get('/candidate-applications', [StudentCandidateApplicationController::class, 'index'])->name('student.candidate-applications');
        Route::post('/candidate-applications', [StudentCandidateApplicationController::class, 'store'])->name('student.candidate-applications.store');
        Route::get('/candidate-applications/statuses', [StudentCandidateApplicationController::class, 'statuses'])->name('student.candidate-applications.statuses');
        Route::get('/notifications', [StudentNotificationController::class, 'index'])->name('student.notifications.index');
        Route::post('/notifications/mark-all-read', [StudentNotificationController::class, 'markAllRead'])->name('student.notifications.mark-all-read');
    });
});

// Admin Routes - Admin Guard
Route::middleware(['auth:admin', 'validate.session:admin'])->group(function () {
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/candidates', [AdminController::class, 'candidates'])->name('candidates');
        Route::post('/candidates', [AdminController::class, 'storeCandidate'])->name('candidates.store');
        Route::put('/candidates/{candidate}', [AdminController::class, 'updateCandidate'])->name('candidates.update');
        Route::delete('/candidates/{candidate}', [AdminController::class, 'destroyCandidate'])->name('candidates.destroy');
        
        Route::get('/campus-elections', [AdminController::class, 'campusElections'])->name('campus-elections');
        Route::post('/campus-elections', [AdminController::class, 'storeCampusElection'])->name('campus-elections.store');
        Route::put('/campus-elections/{campusElection}', [AdminController::class, 'updateCampusElection'])->name('campus-elections.update');
        Route::patch('/campus-elections/{campusElection}/toggle', [AdminController::class, 'toggleCampusElection'])->name('campus-elections.toggle');
        Route::delete('/campus-elections/{campusElection}', [AdminController::class, 'destroyCampusElection'])->name('campus-elections.destroy');
        
        Route::get('/events', [AdminController::class, 'events'])->name('events');
        Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
        
        Route::get('/students', [AdminController::class, 'students'])->name('students');

        Route::get('/faculties', [AdminController::class, 'faculties'])->name('faculties');
        Route::post('/faculties', [AdminController::class, 'storeFaculty'])->name('faculties.store');
        Route::put('/faculties/{faculty}', [AdminController::class, 'updateFaculty'])->name('faculties.update');
        Route::patch('/faculties/{faculty}/status', [AdminController::class, 'toggleFacultyStatus'])->name('faculties.status');
        Route::post('/faculties/import', [AdminController::class, 'importFaculties'])->name('faculties.import');
        Route::get('/faculties/template', [AdminController::class, 'downloadFacultyTemplate'])->name('faculties.template');
        
        Route::get('/department-heads', [AdminController::class, 'departmentHeads'])->name('department-heads');
        Route::post('/department-heads', [AdminController::class, 'storeDepartmentHead'])->name('department-heads.store');
        Route::post('/department-heads/{departmentHead}', [AdminController::class, 'updateDepartmentHead'])->name('department-heads.update');
        Route::delete('/department-heads/{departmentHead}', [AdminController::class, 'destroyDepartmentHead'])->name('department-heads.destroy');
        
        Route::get('/votes-status', [VotesStatusController::class, 'adminList'])->name('votes-status');
        Route::get('/votes-status/{election}', [VotesStatusController::class, 'adminIndex'])->name('votes-status.show');
    });
});

// Department Head Routes - Department Head Guard
Route::middleware(['auth:department_head', 'validate.session:department_head'])->group(function () {
    Route::middleware(['department_head'])->prefix('department-head')->name('department-head.')->group(function () {
        Route::get('/dashboard', [DepartmentHeadController::class, 'dashboard'])->name('dashboard');
        Route::get('/candidates', [DepartmentHeadController::class, 'candidates'])->name('candidates');
        Route::post('/candidates', [DepartmentHeadController::class, 'storeCandidate'])->name('candidates.store');
        Route::get('/candidate-registrations', [DepartmentHeadController::class, 'candidateRegistrations'])->name('candidate-registrations.index');
        Route::post('/candidate-registrations', [DepartmentHeadController::class, 'storeCandidateRegistration'])->name('candidate-registrations.store');
        Route::post('/candidate-registrations/{registration}/confirm', [DepartmentHeadController::class, 'confirmCandidateRegistration'])->name('candidate-registrations.confirm');
        Route::post('/candidate-registrations/{registration}/decline', [DepartmentHeadController::class, 'declineCandidateRegistration'])->name('candidate-registrations.decline');
        Route::post('/candidates/{candidate}', [DepartmentHeadController::class, 'updateCandidate'])->name('candidates.update');
        Route::delete('/candidates/{candidate}', [DepartmentHeadController::class, 'destroyCandidate'])->name('candidates.destroy');
        Route::get('/campus-elections', [DepartmentHeadController::class, 'campusElections'])->name('campus-elections');
        Route::post('/campus-elections', [DepartmentHeadController::class, 'storeCampusElection'])->name('campus-elections.store');
        Route::post('/campus-elections/{election}', [DepartmentHeadController::class, 'updateCampusElection'])->name('campus-elections.update');
        Route::patch('/campus-elections/{id}/toggle', [DepartmentHeadController::class, 'toggleCampusElection'])->name('campus-elections.toggle');
        Route::get('/election-winners', [DepartmentHeadController::class, 'electionWinners'])->name('election-winners');
        Route::get('/election-winners/{election}', [DepartmentHeadController::class, 'showElectionWinner'])->name('election-winners.show');
        Route::get('/events', [DepartmentHeadController::class, 'events'])->name('events');
        Route::post('/events', [DepartmentHeadController::class, 'storeEvent'])->name('events.store');
        Route::post('/events/{event}', [DepartmentHeadController::class, 'updateEvent'])->name('events.update');
        Route::delete('/events/{event}', [DepartmentHeadController::class, 'destroyEvent'])->name('events.destroy');
        Route::post('/events/{event}/like', [DepartmentHeadController::class, 'toggleEventLike'])->name('events.like');
        
        Route::get('/votes-status', [VotesStatusController::class, 'departmentHeadList'])->name('votes-status');
        Route::get('/votes-status/{election}', [VotesStatusController::class, 'departmentHeadIndex'])->name('votes-status.show');
        
        Route::get('/students', [DepartmentHeadController::class, 'students'])->name('students');
        Route::get('/faculty', [DepartmentHeadController::class, 'faculty'])->name('faculty');
        Route::post('/faculty', [DepartmentHeadController::class, 'storeFaculty'])->name('faculty.store');
        Route::post('/faculty/{faculty}', [DepartmentHeadController::class, 'updateFaculty'])->name('faculty.update');
        Route::patch('/faculty/{faculty}/access', [DepartmentHeadController::class, 'toggleFacultyAccess'])->name('faculty.access');
        Route::get('/students/list', [DepartmentHeadController::class, 'getStudentsList'])->name('students.list');
        Route::post('/students', [DepartmentHeadController::class, 'storeStudent'])->name('students.store');
        Route::post('/students/import', [DepartmentHeadController::class, 'importStudents'])->name('students.import');
        Route::get('/students/template', [DepartmentHeadController::class, 'downloadStudentTemplate'])->name('students.template');
        Route::put('/students/{student}', [DepartmentHeadController::class, 'updateStudent'])->name('students.update');
        Route::patch('/students/{student}/status', [DepartmentHeadController::class, 'toggleStudentStatus'])->name('students.status');
        Route::delete('/students/{student}', [DepartmentHeadController::class, 'destroyStudent'])->name('students.destroy');

        // Student registration requests
        Route::get('/student-requests', [DepartmentHeadController::class, 'studentRequests'])->name('student-requests');
        Route::patch('/student-requests/{user}/approve', [DepartmentHeadController::class, 'approveStudent'])->name('student-requests.approve');
        Route::patch('/student-requests/{user}/deny', [DepartmentHeadController::class, 'denyStudent'])->name('student-requests.deny');

        Route::get('/profile', [DepartmentHeadController::class, 'editProfile'])->name('profile');
        Route::post('/profile', [DepartmentHeadController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/verify-password', [DepartmentHeadController::class, 'verifyPasswordChange'])->name('profile.verify-password');
        Route::post('/profile/resend-code', [DepartmentHeadController::class, 'resendVerificationCode'])->name('profile.resend-code');
    });
});
