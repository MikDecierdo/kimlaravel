<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CAMPUS ELECTIONS CHECK ===\n\n";

// Get all elections
$elections = \App\Models\CampusElection::all();

if ($elections->isEmpty()) {
    echo "❌ NO ELECTIONS FOUND IN DATABASE\n\n";
} else {
    echo "📊 Found " . $elections->count() . " election(s):\n\n";
    
    foreach ($elections as $election) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "ID: {$election->id}\n";
        echo "Name: {$election->election_name}\n";
        echo "Department: {$election->department}\n";
        echo "Active: " . ($election->is_active ? '✅ YES' : '❌ NO') . "\n";
        echo "Start Date: {$election->start_date}\n";
        echo "End Date: {$election->end_date}\n";
        
        // Check if dates are valid
        $now = now();
        $isInDateRange = $election->start_date <= $now && $election->end_date >= $now;
        echo "Date Range Valid: " . ($isInDateRange ? '✅ YES' : '❌ NO') . "\n";
        
        // Count candidates
        $candidateCount = \App\Models\Candidate::where('campus_election_id', $election->id)->count();
        echo "Candidates: {$candidateCount}\n";
        
        // Show candidates
        if ($candidateCount > 0) {
            $candidates = \App\Models\Candidate::where('campus_election_id', $election->id)->get();
            echo "  Candidates List:\n";
            foreach ($candidates as $candidate) {
                echo "    - {$candidate->full_name} ({$candidate->position}) - Dept: {$candidate->department}\n";
            }
        }
        
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }
}

// Check students
echo "\n=== STUDENTS CHECK ===\n\n";
$students = \App\Models\User::where('role', 'student')->get();

if ($students->isEmpty()) {
    echo "❌ NO STUDENTS FOUND\n";
} else {
    echo "Found " . $students->count() . " student(s):\n\n";
    foreach ($students as $student) {
        echo "- {$student->name} (Dept: {$student->department})\n";
    }
}

echo "\n=== WHAT STUDENT WOULD SEE ===\n\n";

if ($students->isNotEmpty()) {
    $student = $students->first();
    echo "Checking as: {$student->name} (Department: {$student->department})\n\n";
    
    $activeElections = \App\Models\CampusElection::where('is_active', true)
        ->where('department', $student->department)
        ->where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->get();
    
    if ($activeElections->isEmpty()) {
        echo "❌ NO ELECTIONS VISIBLE TO THIS STUDENT\n";
        echo "\nReasons:\n";
        echo "1. Election must be ACTIVE (is_active = 1)\n";
        echo "2. Election department must match student department: '{$student->department}'\n";
        echo "3. Current date must be between start_date and end_date\n";
        echo "   Current date: " . now() . "\n";
    } else {
        echo "✅ {$activeElections->count()} election(s) visible:\n";
        foreach ($activeElections as $election) {
            echo "  - {$election->election_name}\n";
        }
    }
}
