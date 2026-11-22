<?php
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $errors = [];
    foreach ($lines as $line) {
        if (strpos($line, 'local.ERROR:') !== false) {
            $errors[] = $line;
        }
    }

    if (!empty($errors)) {
        echo "Top 3 Recent Errors:\n";
        $recent = array_slice($errors, -3);
        foreach ($recent as $err) {
            echo trim(substr($err, 0, 300)) . "...\n";
        }
    } else {
        echo "No Errors Found.";
    }
}
