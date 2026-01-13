<?php
require_once 'config.php';

echo "<h3>🛠️ Repairing Database Schema...</h3>";

try {
    // 1. Create games table if it doesn't exist at all
    $pdo->exec("CREATE TABLE IF NOT EXISTS games (
        id INT AUTO_INCREMENT PRIMARY KEY,
        shop_id INT NOT NULL,
        stake DECIMAL(10,2) NOT NULL,
        players_count INT NOT NULL,
        total_pool DECIMAL(10,2) NOT NULL,
        house_cut DECIMAL(10,2) NOT NULL,
        win_amount DECIMAL(10,2) NOT NULL,
        win_card VARCHAR(50),
        status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Table 'games' verified.<br>";

    // 2. Add missing columns to existing games table if they are missing
    $columns = [
        'total_pool' => "DECIMAL(10,2) NOT NULL",
        'house_cut' => "DECIMAL(10,2) NOT NULL",
        'win_amount' => "DECIMAL(10,2) NOT NULL"
    ];

    foreach ($columns as $column => $definition) {
        $check = $pdo->query("SHOW COLUMNS FROM games LIKE '$column'");
        if ($check->rowCount() == 0) {
            $pdo->exec("ALTER TABLE games ADD COLUMN $column $definition");
            echo "✅ Column '$column' added successfully.<br>";
        } else {
            echo "ℹ️ Column '$column' already exists.<br>";
        }
    }

    echo "<h4>🎉 Database is now fully updated! You can start the game now.</h4>";
    echo "<a href='new_game.php'>Return to New Game</a>";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
?>
