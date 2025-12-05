#!/usr/bin/env php
<?php

/**
 * Database Migration CLI Tool
 * 
 * Usage:
 *   php migrate.php up      - Run all pending migrations
 *   php migrate.php fresh   - Reset database and run all migrations
 *   php migrate.php status  - Show migration status
 */

require_once __DIR__ . '/vendor/autoload.php';

use PhpCopilot\Database\Database;
use PhpCopilot\Database\Migration;

// Database path
$dbPath = __DIR__ . '/storage/database.sqlite';

// Create database instance
$db = new Database($dbPath);
$migration = new Migration($db);

// Get command
$command = $argv[1] ?? 'up';

echo "\n";
echo "╔════════════════════════════════════════╗\n";
echo "║   The Code AI Agent - Migrations      ║\n";
echo "║   Powered by The Code Indonesia        ║\n";
echo "╚════════════════════════════════════════╝\n";
echo "\n";

try {
    switch ($command) {
        case 'up':
            echo "Running migrations...\n";
            echo "Database: $dbPath\n\n";
            $migration->run();
            echo "\n✓ All migrations completed successfully!\n";
            break;

        case 'fresh':
            echo "⚠️  WARNING: This will delete all data!\n";
            echo "Database: $dbPath\n";
            echo "\nAre you sure? (yes/no): ";
            
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);
            fclose($handle);
            
            if (trim(strtolower($line)) === 'yes') {
                echo "\n";
                $migration->fresh();
                echo "\n✓ Database reset completed!\n";
            } else {
                echo "\n✗ Operation cancelled.\n";
            }
            break;

        case 'status':
            echo "Migration Status\n";
            echo "Database: $dbPath\n";
            echo "Current Version: " . ($migration->getCurrentVersion() ?? 'None') . "\n\n";
            
            $executed = $migration->getExecutedMigrations();
            
            if (empty($executed)) {
                echo "No migrations have been executed yet.\n";
            } else {
                echo "Executed Migrations:\n";
                echo str_repeat('-', 80) . "\n";
                printf("%-20s %-40s %-20s\n", "Version", "Description", "Executed At");
                echo str_repeat('-', 80) . "\n";
                
                foreach ($executed as $mig) {
                    printf(
                        "%-20s %-40s %-20s\n",
                        $mig['version'],
                        substr($mig['description'], 0, 40),
                        $mig['executed_at']
                    );
                }
            }
            break;

        case 'help':
        case '--help':
        case '-h':
            echo "Usage: php migrate.php [command]\n\n";
            echo "Commands:\n";
            echo "  up      Run all pending migrations (default)\n";
            echo "  fresh   Reset database and run all migrations\n";
            echo "  status  Show migration status\n";
            echo "  help    Show this help message\n";
            break;

        default:
            echo "Unknown command: $command\n";
            echo "Run 'php migrate.php help' for usage information.\n";
            exit(1);
    }
} catch (\Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";
