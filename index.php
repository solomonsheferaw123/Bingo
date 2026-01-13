<?php
require_once 'config.php';
if (!isLoggedIn()) { redirect('login.php'); }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT u.*, s.name as shop_name FROM users u LEFT JOIN shops s ON u.shop_id = s.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$me = $stmt->fetch();

$game_id = $_GET['game_id'] ?? null;
$game_data = null;
if ($game_id) {
    $stmt = $pdo->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->execute([$game_id]);
    $game_data = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="boxicons.min.css">
    <title>Home</title>
    <!-- Add your CSS files here -->
    <link rel="stylesheet" href="static/game/css/base.css">
    <link rel="stylesheet" href="static/game/css/styles.css">
    <!-- Add your JavaScript files here -->
</head>
<body class="dark">
    
<section id="sidebar" class="hide">
    <a href="/" class="brand">
        <img src="static/game/icon/logo.png" style="width: 40px; margin-left: 10px; margin-right: 10px;" alt="">
        <span class="text">Dallol</span>
    </a>
    <ul class="side-menu top">
        <li id="dashboard">
            <a href="dashboard.php">
                <i class='bx bxs-dashboard' ></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
        <li id="index">
            <a href="index.php">
                <i class='bx bxs-right-arrow' ></i>
                <span class="text">Play Bingo</span>
            </a>
        </li>
        <li>
            <a>
                <i class='bx bxs-doughnut-chart' ></i>
                <span class="text">Win History</span>
            </a>
        </li>
        <li>
            <a>
                <i class='bx bxs-show' ></i>
                <span class="text">View Cartela</span>
            </a>
        </li>
    </ul>
    <ul class="side-menu">
        <li id="setting">
            <a href="settings.php">
                <i class='bx bxs-cog' ></i>
                <span class="text">Settings</span>
            </a>
        </li>
        <li>
            <a href="logout.php" class="logout">
                <i class='bx bxs-log-out-circle' ></i>
                <span class="text">Logout</span>
            </a>
        </li>
    </ul>
</section>

<section id="content">
    <nav>
        <i class='bx bx-menu' ></i>
        <div>
            <h3 class="text-gradient"><span id="d">Dallol</span> Bin<span id="i">g</span>o!</h3>
        </div>
        <div class="header">
            <select name="lang" id="lang">
                <option value="am">F-am</option>
                <option value="mm">M-am</option>
                <option value="mm2">M-am-2</option>
                <option value="om">om</option>
                <option value="tg">tg</option>
                <option value="ai">AI</option>
            </select>
            <input type="checkbox" id="switch-mode" hidden>
            <label for="switch-mode" class="switch-mode"></label>
            <i id="full-screen" class="bx bx-fullscreen"></i>
        </div>    
    </nav>

    <main>

        <div class="bingo-container" id="bingo-container" style="display: <?php echo $game_data ? 'block' : 'none'; ?>;">
            <div class="bingo-stat" style="visibility: <?php echo $me['show_game_info'] ? 'visible' : 'hidden'; ?>;">
                <h3 class="text-gradient big">BINGO</h3>
                <div class="stat-box">
                    GAME <span id="game-id-display"><?php echo $game_data ? '#' . $game_data['id'] : ''; ?></span>
                </div>
                <div class="stat-box">
                    STAKE <span id="stake-display"><?php echo $game_data ? number_format($game_data['stake'], 2) : '0.00'; ?></span>
                </div>
                <div class="stat-box">
                    WIN PRICE <span id="win-display"><?php echo $game_data ? number_format($game_data['win_amount'], 2) : '0.00'; ?></span>
                </div>
                <div id="total-called" class="stat-box">
                    0 CALLED
                </div>
            </div>
            <div class="bingo-panel">
                <div class="bingo-num-container" id="bingo-num-container">
                    
                    <div class="bingo-row">
                        <div class="letter" data-letter="B">B</div>
                        <?php for($i=1; $i<=15; $i++): ?>
                        <div class="number" data-number="<?php echo $i; ?>"><?php echo $i; ?></div>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="bingo-row">
                        <div class="letter" data-letter="I">I</div>
                        <?php for($i=16; $i<=30; $i++): ?>
                        <div class="number" data-number="<?php echo $i; ?>"><?php echo $i; ?></div>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="bingo-row">
                        <div class="letter" data-letter="N">N</div>
                        <?php for($i=31; $i<=45; $i++): ?>
                        <div class="number" data-number="<?php echo $i; ?>"><?php echo $i; ?></div>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="bingo-row">
                        <div class="letter" data-letter="G">G</div>
                        <?php for($i=46; $i<=60; $i++): ?>
                        <div class="number" data-number="<?php echo $i; ?>"><?php echo $i; ?></div>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="bingo-row">
                        <div class="letter" data-letter="O">O</div>
                        <?php for($i=61; $i<=75; $i++): ?>
                        <div class="number" data-number="<?php echo $i; ?>"><?php echo $i; ?></div>
                        <?php endfor; ?>
                    </div>
                    
                </div>
                <div id="called-numbers" class="called-numbers" style="display: none;">
                    <div class="last-called" id="last-called"><p id="last-letter"></p><p id="last-num"></p></div>
                    <div class="last-called-numbers" id="lastCalledNumbers">
                    </div>
                    <div class="view-all">
                        <button class="cutm-btn-2" id="viewAllCalledButton">view all</button>
                    </div>
                </div>
            </div>
            <div class="action-panel">
                <div class="action-con">
                    <div class="actions">
                        <button id="start-auto-play"  style="display: none;" class="cutm-btn">START AUTO PLAY</button>
                        <button id="call-next"   style="display: none;"  class="cutm-btn">CALL NEXT</button>
                        
                        <button id="finsh"   style="display: none;"  class="cutm-btn">FINISH</button>
                        <a id="start-new-game" href="new_game.php"  class="cutm-btn">START NEW GAME</a>
                        <audio id="audioPlayer" hidden>
                            <source src="static/game/audio/shuffle.mp3" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                        <button id="shuffle" class="cutm-btn">SHUFFLE</button>
                    </div>
                    <div class="actions">
                        <div class="form-group">
                            <input type="range" id="callingSpeed" name="callingSpeed" min="3" max="12" value="6">
                            <p id="callingSpeedTxt">Auto call 4 secounds</p>
                        </div>
                        <div id="game-id" style="display: none;"><?php echo $game_data ? '#' . $game_data['id'] : ''; ?></div>
                        <input id="check-num" type="number" name="card" value="" min="1" max="200" placeholder="Enter cartela">
                        <button id="check-btn" class="cutm-btn">CHECK</button>
                    </div>
                </div>
                
                <div class="winner">
                    <div>
                        <div>WIN MONEY</div>
                        <div id="win_amount_display"><?php echo $game_data ? number_format($game_data['win_amount'], 2) . ' Birr' : '0.00 Birr'; ?></div>
                    </div>
                    <img src="static/game/icon/money.png" alt="">
                </div>
            </div>
        </div>
        <div class="blur-background" id="blur-background">
            
        </div>

    </main>

</section>

<div class="loader-container" id="loader">
    <div class="loader"></div>
    <div class="loader"></div>
    <div class="loader"></div>
</div>

<div class="congrats-banner" id="bonus_animation" style="display: none;">
    <div class="congrats-text">
        <span class="congrats-message">CONGRATULATIONS!</span>
        <span class="congrats-bonnes" id="bonus_text">1st Prince Bonus</span> 
        <span class="congrats-winner">Winner</span>
    </div>
</div>

<div class="congrats-banner" id="free_hit" style="display: none;">
    <div class="congrats-text">
        <span class="congrats-message">CONGRATULATIONS!</span>
        <span class="congrats-bonnes" id="free_hit_text">Card number 51</span> 
        <span class="congrats-winner">Next Game Free</span>
    </div>
</div>

<div id="viewAllCalledModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeViewAllCalled">&times;</span>
        <h2>All Recently Called Numbers</h2>
        <div id="recentlyCalledNumbers" class="recently-called-numbers"></div>
    </div>
</div>

<div class="congrats-banner" id="jackpot" style="display: none;">
    <div class="congrats-text">
        <span class="congrats-message">CONGRATULATIONS!</span>
        <span class="congrats-bonnes" id="jackpot_text">$ 1000 $</span> 
        <span class="congrats-winner">Jackpot Winner</span>
    </div>
</div>

<canvas id="confetti-canvas" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;"></canvas>

    <footer id="footer" style="display: none; position: fixed; left: 0; bottom: 0; width: 100%; text-align: center;">
        <p>&copy; 2024 Dallol Technologies. All rights reserved.</p>
    </footer>    
    
<script src="static/game/js/jquery-3.7.1.min.js"></script>
<script src="static/game/js/script_v2.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015" integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ==" data-cf-beacon='{"version":"2024.11.0","token":"82127c80251a413996afd2b92f399aa5","r":1,"server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
</body>
</html>
