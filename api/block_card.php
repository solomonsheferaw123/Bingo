<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$card_id = $_GET['card'] ?? '';
$game_id = $_GET['game'] ?? '';

// For now, we just return success as we don't have a specific table for blocked claims.
// This could be extended to log the block in the database.

echo json_encode(['status' => 'success', 'message' => "Card $card_id blocked for game $game_id"]);
?>
