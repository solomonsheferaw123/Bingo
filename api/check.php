<?php
require_once '../config.php';
header('Content-Type: application/json');

$card_id = intval($_GET['card'] ?? 0);
$called_numbers = json_decode($_GET['called'] ?? '[]', true);
$patterns = json_decode($_GET['patterns'] ?? '[]', true);
$selected_players = json_decode($_GET['players'] ?? '[]', true);

// 1. Check if the card is actually playing
if (!empty($selected_players) && !in_array($card_id, $selected_players)) {
    echo json_encode(['result' => [['message' => 'not a player']]]);
    exit();
}

// Load cards
$cards_file = 'cards.json';
if (!file_exists($cards_file)) {
    echo json_encode(['result' => [['message' => 'Cards data missing']]]);
    exit();
}

$cards_data = json_decode(file_get_contents($cards_file), true);
$card = null;
foreach ($cards_data as $c) {
    if ($c['id'] == $card_id) {
        $card = $c;
        break;
    }
}

if (!$card) {
    echo json_encode(['result' => [['message' => 'Card not found']]]);
    exit();
}

// Convert card to 5x5 grid for easier checking (Rows)
$grid = [];
for ($i = 0; $i < 5; $i++) {
    $grid[$i] = [
        $card['B'][$i],
        $card['I'][$i],
        $card['N'][$i],
        $card['G'][$i],
        $card['O'][$i]
    ];
}

// Helper to check if a number is called
$is_called = function($val) use ($called_numbers) {
    return $val === 'FREE' || $val === 0 || $val === '0' || in_array($val, $called_numbers);
};

$winning_positions = []; // 1-indexed (1-25)
$has_bingo = false;

// Check Full House (Always check this)
$full_house = true;
$all_pos = [];
for ($r = 0; $r < 5; $r++) {
    for ($c = 0; $c < 5; $c++) {
        $all_pos[] = ($r * 5) + $c + 1;
        if (!$is_called($grid[$r][$c])) {
            $full_house = false;
        }
    }
}

// If no patterns are passed, default to standard ones (1=Lines, 2=Diagonals)
if (empty($patterns) || !is_array($patterns)) {
    $patterns = ['1', '2', '3', '4'];
}

if ($full_house) {
    $has_bingo = true;
    $winning_positions = $all_pos;
} else {
    // Check specific patterns if not full house
    foreach ($patterns as $p) {
        if ($p == '1') { // Lines (Horizontal & Vertical)
            // Horizontal
            for ($r = 0; $r < 5; $r++) {
                $line = true;
                $line_pos = [];
                for ($c = 0; $c < 5; $c++) {
                    $line_pos[] = ($r * 5) + $c + 1;
                    if (!$is_called($grid[$r][$c])) $line = false;
                }
                if ($line) { $has_bingo = true; $winning_positions = array_merge($winning_positions, $line_pos); }
            }
            // Vertical
            for ($c = 0; $c < 5; $c++) {
                $line = true;
                $line_pos = [];
                for ($r = 0; $r < 5; $r++) {
                    $line_pos[] = ($r * 5) + $c + 1;
                    if (!$is_called($grid[$r][$c])) $line = false;
                }
                if ($line) { $has_bingo = true; $winning_positions = array_merge($winning_positions, $line_pos); }
            }
        }
        if ($p == '2') { // Diagonals
            // Main diagonal
            $diag1 = true; $diag1_pos = [];
            for ($i = 0; $i < 5; $i++) {
                $diag1_pos[] = ($i * 5) + $i + 1;
                if (!$is_called($grid[$i][$i])) $diag1 = false;
            }
            if ($diag1) { $has_bingo = true; $winning_positions = array_merge($winning_positions, $diag1_pos); }
            
            // Anti-diagonal
            $diag2 = true; $diag2_pos = [];
            for ($i = 0; $i < 5; $i++) {
                $diag2_pos[] = ($i * 5) + (4-$i) + 1;
                if (!$is_called($grid[$i][4-$i])) $diag2 = false;
            }
            if ($diag2) { $has_bingo = true; $winning_positions = array_merge($winning_positions, $diag2_pos); }
        }
        if ($p == '3') { // Four Corners
            $corners = [1, 5, 21, 25];
            $won = true;
            foreach ($corners as $pos) {
                $r = floor(($pos-1)/5);
                $c = ($pos-1)%5;
                if (!$is_called($grid[$r][$c])) $won = false;
            }
            if ($won) { $has_bingo = true; $winning_positions = array_merge($winning_positions, $corners); }
        }
        if ($p == '4') { // Inside Box (Corners of inner 3x3)
            $box = [7, 9, 17, 19];
            $won = true;
            foreach ($box as $pos) {
                $r = floor(($pos-1)/5);
                $c = ($pos-1)%5;
                if (!$is_called($grid[$r][$c])) $won = false;
            }
            if ($won) { $has_bingo = true; $winning_positions = array_merge($winning_positions, $box); }
        }
    }
}

$winning_positions = array_values(array_unique($winning_positions));

$last_called = count($called_numbers) > 0 ? $called_numbers[count($called_numbers) - 1] : null;
$message = 'No Bingo';
$is_pass = false;

if ($has_bingo) {
    // Check if the last called number is part of the winning positions
    $pattern_numbers = [];
    foreach ($grid as $r => $row) {
        foreach ($row as $c => $val) {
            $pos = ($r * 5) + $c + 1;
            if (in_array($pos, $winning_positions)) {
                $pattern_numbers[] = $val;
            }
        }
    }
    
    if ($last_called !== null && in_array($last_called, $pattern_numbers)) {
        $message = 'Good Bingo';
    } else {
        $message = 'Pass Bingo';
        $is_pass = true;
    }
}

$response = [
    'result' => [
        [
            'message' => $message,
            'card_name' => $card_id,
            'card' => $grid,
            'winning_numbers' => $winning_positions,
            'bonus' => 0,
            'free' => 0,
            'jackpot_won' => false,
            'is_pass' => $is_pass,
            'remaining_numbers' => $has_bingo ? 0 : 1,
            'last_number' => $last_called
        ]
    ],
    'game' => [
        'id' => 1
    ]
];

echo json_encode($response);
?>
