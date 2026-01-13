<?php
require_once 'config.php';
if (!isLoggedIn()) { redirect('login.php'); }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT u.*, s.name as shop_name FROM users u LEFT JOIN shops s ON u.shop_id = s.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$me = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="static/game/css/base.css">
    <link rel="stylesheet" href="static/account/css/styles2.css">
    <link rel="stylesheet" href="static/account/css/framework.css">
    <link rel="stylesheet" href="static/account/css/style.css">
    <title>Settings - Dallol Bingo</title>
    <style>
        .editable-input { border: 1px solid #ccc; border-radius: 6px; padding: 5px 8px; margin-left: 6px; width: 100px; color: #000; }
        .save-btn { background: linear-gradient(90deg, #03c0ff, #ff8d50); border: none; border-radius: 8px; color: #fff; font-weight: 600; padding: 8px 20px; margin-top: 10px; cursor: pointer; transition: 0.3s; }
    </style>
</head>
<body class="dark">
    <section id="sidebar" class="hide">
        <a href="index.php" class="brand">
            <img src="static/game/icon/logo.png" style="width: 40px; margin-left: 10px; margin-right: 10px;" alt="">
            <span class="text">Dallol</span>
        </a>
        <ul class="side-menu top">
            <li><a href="dashboard.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
            <li><a href="index.php"><i class='bx bxs-right-arrow'></i><span class="text">Play Bingo</span></a></li>
            <li class="active"><a href="settings.php"><i class='bx bxs-cog'></i><span class="text">Settings</span></a></li>
            <li><a href="logout.php" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
        </ul>
    </section>

    <section id="content">
        <nav>
            <i class='bx bx-menu'></i>
            <div><h3 class="text-gradient"><span id="d">Dallol</span> Bin<span id="i">g</span>o!</h3></div>
            <div class="header">

            </div>
        </nav>

        <main>
            <div class="content w-full">
                <h1 class="relative">Profile</h1>
                <div class="profile-page m-20">
                    <div class="overview widget d-flex align-center">
                        <div class="avatar-box text-center p-20">
                            <img class="mb-10" src="static/game/icon/avatar.png" style="width: 100px; border-radius: 50%;" alt="">
                            <h3 class="m-0"><?php echo htmlspecialchars($me['username']); ?></h3>
                        </div>
                        <div class="info-box w-full text-center-mobile">
                            <div class="info-row p-20 d-flex align-center f-wrap">
                                <h4 class="c-grey fs-15 m-0 w-full" style="margin-bottom: 10px !important;">General Information</h4>
                                <div class="fs-14"><span class="c-grey">Shop:</span> <span><?php echo htmlspecialchars($me['shop_name'] ?? 'N/A'); ?></span></div>
                                <div class="fs-14"><span class="c-grey">Balance:</span> <span><?php echo number_format($me['balance'], 2); ?></span></div>
                                <div class="fs-14">
                                    <span class="c-grey">Jackpot %:</span>
                                    <input type="number" step="0.01" value="<?php echo number_format($me['jackpot_percentage'], 2); ?>" class="editable-input" id="jackpot_percentage"> %
                                </div>
                                <div class="fs-14">
                                    <span class="c-grey">Jackpot Amount:</span>
                                    <input type="number" step="0.01" value="<?php echo number_format($me['jackpot_amount'], 2); ?>" class="editable-input" id="jackpot_amount">
                                </div>
                            </div>
                            <div class="info-row p-20 d-flex align-center f-wrap">
                                <h4 class="c-grey fs-15 m-0 w-full">Game Information</h4>
                                <div class="fs-14"><span class="c-grey">Display:</span> <span id="display-status"><?php echo $me['show_game_info'] ? 'ON' : 'OFF'; ?></span></div>
                                <div class="fs-14">
                                    <label><input class="toggle-checkbox" type="checkbox" id="show_game_info" <?php echo $me['show_game_info'] ? 'checked' : ''; ?>><div class="toggle-switch relative"></div></label>
                                </div>
                            </div>
                            <button class="save-btn" onclick="saveSettings()">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </section>
    <script src="static/account/js/script.js"></script>
    <script src="/static/game/js/jquery-3.7.1.min.js"></script>
    <script>
        document.getElementById('show_game_info').addEventListener('change', function() {
            document.getElementById('display-status').innerText = this.checked ? 'ON' : 'OFF';
        });

        function saveSettings() {
            const jackpot_percentage = document.getElementById('jackpot_percentage').value;
            const jackpot_amount = document.getElementById('jackpot_amount').value;
            const show_game_info = document.getElementById('show_game_info').checked ? 1 : 0;

            const saveBtn = document.querySelector('.save-btn');
            saveBtn.innerText = 'Saving...';
            saveBtn.disabled = true;

            $.ajax({
                url: 'api/update_settings.php',
                type: 'POST',
                data: {
                    show_game_info: show_game_info,
                    jackpot_percentage: jackpot_percentage,
                    jackpot_amount: jackpot_amount
                },
                success: function(response) {
                    if (response.success) {
                        alert('Settings saved successfully!');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while saving settings.');
                },
                complete: function() {
                    saveBtn.innerText = 'Save Changes';
                    saveBtn.disabled = false;
                }
            });
        }
    </script>
</body>
</html>
