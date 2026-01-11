<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop_id = $_SESSION['shop_id'];
    $user_id = $_SESSION['user_id'];
    $stake = floatval($_POST['stake'] ?? 0);
    $players = intval($_POST['players'] ?? 0);
    
    if ($stake <= 0 || $players <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid stake or player count']);
        exit();
    }

    // Calculate pool and cuts
    $total_pool = $stake * $players;
    
    // Get shop settings
    $stmt = $pdo->prepare("SELECT percentage, min_stake, cut_percentage, cut_boundary, status FROM shops WHERE id = ?");
    $stmt->execute([$shop_id]);
    $shop = $stmt->fetch();
    
    if (!$shop || $shop['status'] === 'inactive') {
        echo json_encode(['status' => 'error', 'message' => 'Shop is deactivated. Cannot start game.']);
        exit();
    }

    $min_stake = $shop['min_stake'] ?? 20;
    if ($stake < $min_stake) {
        echo json_encode(['status' => 'error', 'message' => 'Minimum stake is ' . $min_stake . ' ETB']);
        exit();
    }
    
    $shop_percentage = $shop['percentage'] ?? 0.20; // Company share % of commission pool
    $cut_percentage = $shop['cut_percentage'] ?? 0.20; // House cut % from total pool
    $cut_boundary = $shop['cut_boundary'] ?? 50; // Threshold for commission
    
    // 1. Calculate Commission Pool (House Cut from Total Pool)
    $commission_pool = 0;
    if ($total_pool > $cut_boundary) {
        $commission_pool = $total_pool * $cut_percentage;
    }
    
    // 2. Win Amount for the player
    $win_amount = $total_pool - $commission_pool;

    // 3. Company Share (What admin takes from agent's balance)
    $company_share = $commission_pool * $shop_percentage;
    
    // Check if agent has enough balance for the Company Share (No negative balance)
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user['balance'] <= 0 || $user['balance'] < $company_share) {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient balance. Need ' . number_format($company_share, 2) . ' ETB']);
        exit();
    }

    try {
        // Create game record with status 'playing'
        // commission_pool = 24.00, house_cut = 4.80 (for deduction)
        $stmt = $pdo->prepare("INSERT INTO games (shop_id, stake, players_count, total_pool, commission_pool, house_cut, win_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'playing')");
        $stmt->execute([$shop_id, $stake, $players, $total_pool, $commission_pool, $company_share, $win_amount]);
        $game_id = $pdo->lastInsertId();

        echo json_encode([
            'status' => 'success', 
            'game_id' => $game_id, 
            'house_cut' => $commission_pool, // Return full commission for display
            'win_amount' => $win_amount
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to start game: ' . $e->getMessage()]);
    }
}
?>
