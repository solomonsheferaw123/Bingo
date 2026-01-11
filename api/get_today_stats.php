<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$shop_id = $_SESSION['shop_id'];
$today = date('Y-m-d');

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_games, SUM(commission_pool) as total_earnings 
                           FROM games 
                           WHERE shop_id = ? AND DATE(created_at) = ? AND status = 'finished'");
    $stmt->execute([$shop_id, $today]);
    $stats = $stmt->fetch();
    
    echo json_encode([
        'status' => 'success',
        'games_today' => $stats['total_games'] ?? 0,
        'earning_today' => $stats['total_earnings'] ?? 0
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
