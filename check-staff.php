<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== STAFF TABLE ===\n";
foreach (DB::select('SHOW COLUMNS FROM staff') as $c) {
    echo "{$c->Field} | null={$c->Null} | default=" . ($c->Default ?? 'NULL') . "\n";
}

echo "\n=== TEST staff::create (dry run validation) ===\n";
// Simulate what storeDepartmentHead would do
$data = [
    'name'       => 'Test',
    'last_name'  => 'User',
    'email'      => 'test_x9z@example.com',
    'password'   => bcrypt('password123'),
    'department' => 'IT',
];
try {
    $s = App\Models\Staff::create($data);
    echo "Created OK, id=" . $s->id . "\n";
    $s->delete();
    echo "Deleted OK\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
