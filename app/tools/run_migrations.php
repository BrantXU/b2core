<?php
/**
 * Migration runner script
 * This script runs all database migrations in order
 */

// Define APP constant if not already defined
if (!defined('APP')) {
    define('APP', __DIR__ . '/');
}

// Load configuration
require_once APP . 'config.php';
require_once APP . 'lib/db.php';

// Initialize database connection
$db = new db($db_config);

// Get base URL configuration
// Handle CLI execution
if (php_sapi_name() === 'cli') {
    $baseUrl = '';
    $host = 'localhost';
    $protocol = 'http';
} else {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $dirName = dirname($scriptName);
    $baseUrl = $protocol . '://' . $host . ($dirName === '/' ? '' : $dirName);
    $baseUrl = rtrim($baseUrl, '/');
}

echo "Starting database migrations...\n";

// Get all migration files
$migrationsDir = APP . 'db/migrations/';
$migrationFiles = glob($migrationsDir . '*.php');

// Sort migrations by filename to ensure they run in order
usort($migrationFiles, function($a, $b) {
    return strcmp(basename($a), basename($b));
});

// Run each migration
foreach ($migrationFiles as $migrationFile) {
    echo "Running migration: " . basename($migrationFile) . "\n";
    
    // Include the migration file
    require_once $migrationFile;
    
    // Extract class name from filename
    $filename = basename($migrationFile, '.php');
    // Convert migration filename to class name
    // e.g., 003_create_user_tenant_table.php -> Migration_003_Create_User_Tenant_Table
    $className = 'Migration_' . str_replace(' ', '_', ucwords(str_replace('_', ' ', $filename)));
    
    // Instantiate migration class
    $migration = new $className($db);
    
    // Run the up() method
    if ($migration->up()) {
        echo "Migration " . basename($migrationFile) . " completed successfully.\n\n";
    } else {
        echo "Migration " . basename($migrationFile) . " failed.\n\n";
        exit(1);
    }
}

echo "All migrations completed successfully.\n";
?>