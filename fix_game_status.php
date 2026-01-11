<?php
require_once 'config.php';

if (!isLoggedIn()) {
    die('Please login first');
}

$shop_id = $_SESSION['shop_id'];

// Update all 'playing' games to 'finished' for this shop
$stmt = $pdo->prepare("UPDATE games SET status = 'finished' WHERE shop_id = ? AND status = 'playing'");
$stmt->execute([$shop_id]);
$updated = $stmt->rowCount();

echo "<h2>Fixed Game Statuses</h2>";
echo "Updated $updated games from 'playing' to 'finished'<br><br>";
echo "<a href='dashboard.php'>Go to Dashboard</a> | <a href='check_game_status.php'>Check Status Again</a>";
?>
