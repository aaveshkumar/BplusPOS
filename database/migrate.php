<?php
/**
 * Database Migration Runner
 * Run POS database migrations
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Get database connection
$db = Database::getInstance()->getConnection();

echo "===========================================\n";
echo "B-Plus POS Database Migration\n";
echo "===========================================\n\n";

// Get all migration files
$migrationFiles = glob(__DIR__ . '/migrations/*.sql');
sort($migrationFiles);

if (empty($migrationFiles)) {
    die("Error: No migration files found!\n");
}

echo "Found " . count($migrationFiles) . " migration file(s)\n\n";

try {
    $totalExecuted = 0;
    $totalSkipped = 0;
    
    foreach ($migrationFiles as $migrationFile) {
        $fileName = basename($migrationFile);
        echo "Processing migration: $fileName\n";
        echo str_repeat('-', 50) . "\n";
        
        $sql = file_get_contents($migrationFile);
        
        if (empty($sql)) {
            echo "Skipping empty file\n\n";
            continue;
        }
        
        // Split into individual statements (rough split by semicolons)
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                // Remove comments and empty statements
                $stmt = preg_replace('/^--.*$/m', '', $stmt);
                $stmt = trim($stmt);
                return !empty($stmt) && !preg_match('/^(\/\*|\*)/', $stmt);
            }
        );

        $executedCount = 0;
        $skippedCount = 0;

        foreach ($statements as $statement) {
            if (empty(trim($statement))) {
                continue;
            }

            // Check if it's a CREATE TABLE statement
            if (preg_match('/CREATE TABLE IF NOT EXISTS `(\w+)`/i', $statement, $matches)) {
                $tableName = $matches[1];
                echo "Creating table: $tableName ... ";
                
                try {
                    $db->exec($statement);
                    echo "✓ Done\n";
                    $executedCount++;
                } catch (PDOException $e) {
                    if ($e->getCode() == '42S01') {
                        echo "⊘ Already exists\n";
                        $skippedCount++;
                    } else {
                        echo "✗ Error: " . $e->getMessage() . "\n";
                    }
                }
            } elseif (preg_match('/INSERT INTO/i', $statement)) {
                // Execute INSERT statements
                try {
                    $db->exec($statement);
                    $executedCount++;
                } catch (PDOException $e) {
                    // Ignore duplicate key errors for default data
                    if ($e->getCode() != '23000') {
                        echo "Warning: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        
        echo "Migration $fileName: $executedCount executed, $skippedCount skipped\n\n";
        $totalExecuted += $executedCount;
        $totalSkipped += $skippedCount;
    }

    echo "\n===========================================\n";
    echo "Migration Complete!\n";
    echo "Total statements executed: $totalExecuted\n";
    echo "Total statements skipped: $totalSkipped\n";
    echo "===========================================\n\n";

    // List all POS tables
    echo "POS Tables Created:\n";
    $stmt = $db->query("SHOW TABLES LIKE 'pos_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "  - $table\n";
    }

    echo "\nMigration successful! ✓\n";

} catch (PDOException $e) {
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
