<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT id, shop_id, status, commission_pool, house_cut, win_amount, total_pool, created_at FROM games ORDER BY id DESC LIMIT 5");
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "LAST 5 GAMES:\n";
foreach ($games as $g) {
    echo "ID: {$g['id']} | Shop: {$g['shop_id']} | Status: '{$g['status']}' | House Cut: {$g['house_cut']} | Pool: {$g['total_pool']} | Created: {$g['created_at']}\n";
}

echo "\nAGENT BALANCES:\n";
$stmt = $pdo->query("SELECT id, username, shop_id, balance FROM users WHERE role = 'agent'");
$agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($agents as $a) {
    echo "ID: {$a['id']} | Name: {$a['username']} | Shop: {$a['shop_id']} | Balance: {$a['balance']}\n";
}
?>
