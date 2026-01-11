<?php
require_once 'config.php';

try {
    $pdo->exec("ALTER TABLE games ADD COLUMN win_card VARCHAR(50) NULL");
    echo "Successfully added 'win_card' column to 'games' table.\n";
} catch (PDOException $e) {
    echo "Error (column might already exist): " . $e->getMessage() . "\n";
}
?>
