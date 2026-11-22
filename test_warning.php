<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/dashboard', 'GET')
);

// Mock login
$student = \App\Models\Student::first();
if (!$student) { echo "No student found\n"; exit; }
Auth::guard('web')->login($student);

$controller = new \App\Http\Controllers\Student\StudentDashboardController();
try {
    $view = $controller->index();
    echo "No Exception\n";
    echo $view->render();
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . " on line " . $e->getLine() . "\n";
}
