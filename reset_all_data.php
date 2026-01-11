<?php
require_once 'config.php';

// Check if admin
if (!isAdmin()) {
    die('Unauthorized');
}

try {
    $pdo->beginTransaction();
    
    // Delete all games
    $pdo->exec("DELETE FROM games");
    
    // Delete all transactions
    $pdo->exec("DELETE FROM transactions");
    
    // Reset all agent balances to 0
    $pdo->exec("UPDATE users SET balance = 0 WHERE role = 'agent'");
    
    $pdo->commit();
    
    echo "Success! All data has been reset:<br>";
    echo "- All games deleted<br>";
    echo "- All transactions deleted<br>";
    echo "- All agent balances set to 0<br>";
    echo "<br><a href='admin.php'>Go back to Admin Dashboard</a>";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
