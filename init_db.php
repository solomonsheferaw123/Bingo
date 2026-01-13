<?php
require_once 'config.php';

echo "<h3>Initializing Database...</h3>";

try {
    $sql = file_get_contents(__DIR__ . '/database.sql');
    if (!$sql) {
        throw new Exception("Could not read database.sql");
    }

    // Split SQL into individual queries because exec() can sometimes fail with multiple queries depending on driver
    // But usually with PDO and MySQL valid SQL file, we can try running it directly or split it.
    // Let's try running it directly first as it's cleaner for properly formatted SQL files.
    // However, if the file has comments or delimiters, sometimes it's tricky.
    // database.sql looked simple enough.
    
    $pdo->exec($sql);
    echo "✅ Database schema imported successfully.<br>";
    
    // Checks
    $tables = ['users', 'shops', 'games', 'transactions'];
    foreach ($tables as $table) {
        // Use try-catch for safety
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists.<br>";
            
            // Check for specific columns in existing tables
            if ($table === 'shops') {
                $colCheck = $pdo->query("SHOW COLUMNS FROM shops LIKE 'status'");
                if ($colCheck->rowCount() == 0) {
                    $pdo->exec("ALTER TABLE shops ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
                    echo "✅ Added missing column 'status' to 'shops'.<br>";
                }
            }

            if ($table === 'games') {
                $colCheck = $pdo->query("SHOW COLUMNS FROM games LIKE 'commission_pool'");
                if ($colCheck->rowCount() == 0) {
                    $pdo->exec("ALTER TABLE games ADD COLUMN commission_pool DECIMAL(10,2) DEFAULT 0.00 AFTER total_pool");
                    echo "✅ Added missing column 'commission_pool' to 'games'.<br>";
                }
            }


            } else {
                 echo "❌ Table '$table' MISSING.<br>";
            }
        } catch (Exception $e) {
             echo "❌ Error checking '$table': " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<h4>Done. <a href='login.php'>Go to Login</a></h4>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
