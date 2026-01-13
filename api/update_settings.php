<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $show_game_info = isset($_POST['show_game_info']) ? (int)$_POST['show_game_info'] : 0;
    $jackpot_percentage = isset($_POST['jackpot_percentage']) ? (float)$_POST['jackpot_percentage'] : 0.00;
    $jackpot_amount = isset($_POST['jackpot_amount']) ? (float)$_POST['jackpot_amount'] : 0.00;

    try {
        $stmt = $pdo->prepare("UPDATE users SET show_game_info = ?, jackpot_percentage = ?, jackpot_amount = ? WHERE id = ?");
        $stmt->execute([$show_game_info, $jackpot_percentage, $jackpot_amount, $user_id]);
        echo json_encode(['success' => true, 'message' => 'Settings updated successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
