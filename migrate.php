<?php
require_once __DIR__ . '/config.php';

try {
    // Run migrations
    $migration_files = glob(__DIR__ . '/database/migration/*.php');
    foreach ($migration_files as $file) {
        require_once $file;
        $functionName = 'up_' . basename($file, '.php');
        if (function_exists($functionName)) {
            call_user_func($functionName, $pdo); // Call the up function
        }
    }

    // Run seeders
    $seeder_files = glob(__DIR__ . '/database/seeder/*.php');
    foreach ($seeder_files as $file) {
        require_once $file;
        $functionName = basename($file, '.php'); // Correct usage
        if (function_exists($functionName)) {
            call_user_func($functionName, $pdo);
        } else {
            echo "Function $functionName does not exist.\n"; // Debugging line
        }
    }

    echo "Migration and seeding completed successfully.\n";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "General error: " . $e->getMessage() . "\n";
}
?>
