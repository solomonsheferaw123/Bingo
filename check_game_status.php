<?php
require_once 'config.php';

if (!isLoggedIn()) {
    die('Please login first');
}

$shop_id = $_SESSION['shop_id'];

// Get all games for this shop
$stmt = $pdo->prepare("SELECT id, stake, players_count, total_pool, commission_pool, house_cut, status, created_at FROM games WHERE shop_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$shop_id]);
$games = $stmt->fetchAll();

echo "<h2>Games for Shop ID: $shop_id</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Game ID</th><th>Stake</th><th>Players</th><th>Pool</th><th>Commission Pool</th><th>House Cut</th><th>Status</th><th>Created At</th></tr>";

foreach ($games as $game) {
    echo "<tr>";
    echo "<td>#{$game['id']}</td>";
    echo "<td>{$game['stake']}</td>";
    echo "<td>{$game['players_count']}</td>";
    echo "<td>{$game['total_pool']}</td>";
    echo "<td>{$game['commission_pool']}</td>";
    echo "<td>{$game['house_cut']}</td>";
    echo "<td><strong>{$game['status']}</strong></td>";
    echo "<td>{$game['created_at']}</td>";
    echo "</tr>";
}

echo "</table>";

// Show today's stats
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(commission_pool) as earnings FROM games WHERE shop_id = ? AND DATE(created_at) = ? AND status = 'finished'");
$stmt->execute([$shop_id, $today]);
$stats = $stmt->fetch();

echo "<h3>Today's Stats (status = 'finished' only)</h3>";
echo "Games Today: {$stats['count']}<br>";
echo "Earning Today: " . number_format($stats['earnings'] ?? 0, 2) . "<br>";

echo "<br><a href='dashboard.php'>Back to Dashboard</a>";
?>
