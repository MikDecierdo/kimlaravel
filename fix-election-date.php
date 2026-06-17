<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Update the IT election end date
$election = \App\Models\CampusElection::where('department', 'IT')->first();

if ($election) {
    $newEndDate = now()->addDays(7); // Extend by 7 days
    $election->end_date = $newEndDate;
    $election->save();
    
    echo "✅ SUCCESS!\n\n";
    echo "Updated election: {$election->election_name}\n";
    echo "New end date: {$newEndDate}\n";
    echo "\nThe IT election should now be visible to students!\n";
} else {
    echo "❌ No IT election found\n";
}
