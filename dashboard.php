<?php
require_once 'config.php';
// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

if (!isLoggedIn()) { redirect('login.php'); }

$user_id = $_SESSION['user_id'];
$shop_id = $_SESSION['shop_id'];

// Get user info
$stmt = $pdo->prepare("SELECT u.*, s.name as shop_name FROM users u LEFT JOIN shops s ON u.shop_id = s.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$me = $stmt->fetch();

// Fetch totals for TODAY
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) as total_games, SUM(commission_pool) as total_earnings, SUM(total_pool) as total_volume 
                       FROM games 
                       WHERE shop_id = ? AND DATE(created_at) = ? AND status = 'finished'");
$stmt->execute([$shop_id, $today]);
$stats_today = $stmt->fetch();

$total_games = $stats_today['total_games'] ?? 0;
$total_earnings = $stats_today['total_earnings'] ?? 0;

// Fetch recent games
$stmt = $pdo->prepare("SELECT * FROM games WHERE shop_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$shop_id]);
$recentGames = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/static/game/css/base.css">
    <link rel="stylesheet" href="/static/account/css/styles2.css">
    <link rel="stylesheet" href="/boxicons.min.css">
    <title>Dashboard - Dallol Bingo</title>
</head>
<body class="dark">
    <section id="sidebar" class="hide">
        <a href="/index.php" class="brand">
            <img src="/static/game/icon/logo.png" style="width: 40px; margin-left: 10px; margin-right: 10px;" alt="">
            <span class="text">Dallol</span>
        </a>
        <ul class="side-menu top">
            <li class="active"><a href="/dashboard.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
            <li><a href="/index.php"><i class='bx bxs-right-arrow'></i><span class="text">Play Bingo</span></a></li>
            <li><a href="/settings.php"><i class='bx bxs-cog'></i><span class="text">Settings</span></a></li>
            <li><a href="/logout.php" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
        </ul>
    </section>

    <section id="content">
        <nav>
            <i class='bx bx-menu'></i>
            <div><h3>Dallol Bingo!</h3></div>
            <div class="header">


                <input type="checkbox" id="switch-mode" hidden>
                <label for="switch-mode" class="switch-mode"></label>
                <i id="full-screen" class="bx bx-fullscreen"></i>
            </div>
        </nav>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Dashboard</h1>
                    <ul class="breadcrumb">
                        <li><a href="#"><?php echo htmlspecialchars($me['shop_name'] ?? 'No Shop'); ?></a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Overview</a></li>
                    </ul>
                </div>
            </div>

            <ul class="box-info">
                <li>
                    <i class='bx bxs-joystick'></i>
                    <span class="text">
                        <h3><?php echo $total_games; ?></h3>
                        <p>Games Today</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-dollar-circle'></i>
                    <span class="text">
                        <h3>ETB <?php echo number_format($total_earnings, 2); ?></h3>
                        <p>Earning Today</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-folder-open'></i>
                    <span class="text">
                        <h3>ETB <?php echo number_format($me['balance'], 2); ?></h3>
                        <p>Available Balance</p>
                    </span>
                </li>
            </ul>

            <div class="table-data">
                <div class="order">
                    <div class="head"><h3>Recent Games</h3></div>
                    <table>
                        <thead>
                            <tr>
                                <th>Game ID</th>
                                <th>Players</th>
                                <th>Stake</th>
                                <th>Pool</th>
                                <th>House Cut</th>
                                <th>Win Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentGames as $game): ?>
                            <tr>
                                <td>#<?php echo $game['id']; ?></td>
                                <td><?php echo $game['players_count']; ?></td>
                                <td><?php echo number_format($game['stake'], 2); ?></td>
                                <td><?php echo number_format($game['total_pool'], 2); ?></td>
                                <td><?php echo number_format($game['house_cut'], 2); ?></td>
                                <td><?php echo number_format($game['win_amount'], 2); ?></td>
                                <td><span class="status <?php echo strtolower($game['status']); ?>"><?php echo ucfirst($game['status']); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </section>
    <script src="static/account/js/script.js"></script>
</body>
</html>
