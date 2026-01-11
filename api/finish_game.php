<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $game_id = intval($_POST['game_id'] ?? 0);
    $win_card = $_POST['win_card'] ?? '';
    
    if ($game_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid game ID']);
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Get game info and company share
        // Ensure we only deduct from the logged-in agent who belongs to this shop
        $stmt = $pdo->prepare("SELECT g.*, u.id as agent_user_id 
                               FROM games g 
                               JOIN users u ON g.shop_id = u.shop_id 
                               WHERE g.id = ? AND g.status = 'playing' AND u.id = ? AND u.role = 'agent' FOR UPDATE");
        $stmt->execute([$game_id, $_SESSION['user_id']]);
        $game = $stmt->fetch();

        if (!$game) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Game not found or already finished']);
            exit();
        }

        $company_share = $game['house_cut'];
        $agent_id = $game['agent_user_id'];

        // 1. Update Game status to 'finished'
        $stmt = $pdo->prepare("UPDATE games SET status = 'finished', win_card = ? WHERE id = ?");
        $stmt->execute([$win_card, $game_id]);

        // 2. Finalize balance deduction
        $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$company_share, $agent_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Game finished and balance deducted']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to finish game: ' . $e->getMessage()]);
    }
}
?>
