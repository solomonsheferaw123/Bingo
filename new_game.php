<?php
require_once 'config.php';
if (!isLoggedIn()) { redirect('login.php'); }

$user_id = $_SESSION['user_id'];
$shop_id = $_SESSION['shop_id'];

// Get user info
// Get user info and shop rules
$stmt = $pdo->prepare("SELECT u.*, s.name as shop_name, s.percentage, s.min_stake, s.cut_percentage, s.cut_boundary 
                       FROM users u 
                       LEFT JOIN shops s ON u.shop_id = s.id 
                       WHERE u.id = ?");
$stmt->execute([$user_id]);
$me = $stmt->fetch();
$shop_percentage = $me['percentage'] ?? 0.20;
$min_stake = $me['min_stake'] ?? 20;
$cut_percentage = $me['cut_percentage'] ?? 0.20;
$cut_boundary = $me['cut_boundary'] ?? 100;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <title>New Game - Dallol Bingo</title>
    <link rel="stylesheet" href="static/game/css/base.css">
    <link rel="stylesheet" href="static/game/css/style2.css">
</head>

<body class="dark">
    <!-- Pass shop rules to JS -->
    <div id="shop-percentage" style="display: none;"><?php echo $shop_percentage; ?></div>
    <div id="shop-min-stake" style="display: none;"><?php echo $min_stake; ?></div>
    <div id="shop-cut-percentage" style="display: none;"><?php echo $cut_percentage; ?></div>
    <div id="shop-cut-boundary" style="display: none;"><?php echo $cut_boundary; ?></div>
    <nav>
        <h3 class="name">Dallol Bingo!</h3>
        <h3 class="balance <?php echo $me['balance'] <= 0 ? 'red' : ''; ?>">
            <?php echo number_format($me['balance'], 2); ?> ETB
        </h3>
    </nav>
    <div class="center-container">
        <div class="top-bar">
            <h2>New Game</h2>
        </div>
        
        <!-- Script2.js relies on these hidden elements -->
        <div id="game" style="display: none;"><?php echo rand(1000000, 9999999); ?></div>
        <div id="cashier" style="display: none;">False</div>
        <div id="shop-min-stake" style="display: none;"><?php echo $min_stake; ?></div>
        <div id="shop-cut-percentage" style="display: none;"><?php echo $cut_percentage; ?></div>
        <div id="shop-cut-boundary" style="display: none;"><?php echo $cut_boundary; ?></div>

        <form id="game-form">
            <div class="input-section">
                <div class="input-group">
                    <label for="game_display">Game ID: </label>
                    <input type="number" value="<?php echo rand(100, 999); ?>" id="game_display" readonly>
                </div>
                <div class="input-group">
                    <label for="stake">Bet Birr: </label>
                    <input type="number" id="stake" name="stake" min="<?php echo $min_stake; ?>" max="10000" value="<?php echo $min_stake; ?>" required>
                </div>
                <div class="input-group">
                    <label for="noplayer">No. of players: </label>
                    <input type="number" id="noplayer" value="0" readonly>
                </div>
                <div class="input-group">
                    <label for="win">Win Birr: </label>
                    <input type="number" id="win" value="0" placeholder="0" readonly>
                </div>
                <div class="input-group">
                    <label for="bonus">Bonus: </label>
                    <input type="checkbox" name="bonus" id="bonus">
                </div>
                <div class="input-group">
                    <label for="free">Free Hit: </label>
                    <input type="checkbox" name="free" id="free">
                </div>
                <div class="input-group">
                    <div class="custom-select">
                        <div class="select-box">
                            <span id="selectedPatterns">Choose Patterns</span>
                        </div>
                        <div class="options-container">
                            <div class="option" data-value="1">Lines</div>
                            <div class="option" data-value="2">Diagonals</div>
                            <div class="option" data-value="3">Outside box</div>
                            <div class="option" data-value="4">Inside box</div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="container" class="grid-container">
                <!-- Boxes will be added here by JS -->
            </div>

            <button type="submit" id="start-button" disabled>Confirm</button>
        </form>
        <div id="butns">
            <button id="more">100-200</button>
            <button id="clear">Clear</button>
        </div>
    </div>

    <footer id="footer" style="display: none; position: fixed; left: 0; bottom: 0; width: 100%; text-align: center;">
        <p>&copy; 2024 Dallol Technologies. All rights reserved.</p>
    </footer>

    <script src="static/game/js/jquery-3.7.1.min.js"></script>
    <script src="static/game/js/script2.js"></script>
</body>
</html>
