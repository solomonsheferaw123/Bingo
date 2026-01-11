<?php
require_once 'config.php';

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if admin
if (!isAdmin()) {
    redirect('login.php');
}

// Handle Add Shop
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_shop') {
    $name = $_POST['name'] ?? '';
    $user = $_POST['user_name'] ?? '';
    $pass = $_POST['password'] ?? '';
    $phone = $_POST['phone_number'] ?? '';
    $percent = $_POST['percentage'] ?? 0.20;
    $prepaid = isset($_POST['prepaid']) ? 1 : 0;
    $cut_percentage = $_POST['cut_percentage'] ?? 0.20;
    $cut_boundary = $_POST['cut_boundary'] ?? 100;
    $min_stake = $_POST['min_stake'] ?? 20;
    
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO shops (name, percentage, prepaid, cut_percentage, cut_boundary, min_stake) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $percent, $prepaid, $cut_percentage, $cut_boundary, $min_stake]);
        $shopId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, shop_id, phone_number) VALUES (?, ?, 'agent', ?, ?)");
        $stmt->execute([$user, $pass, $shopId, $phone]);
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}

// Handle Edit Shop
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_shop') {
    $shopId = $_POST['shop_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $user = $_POST['user_name'] ?? '';
    $pass = $_POST['password'] ?? '';
    $percent = $_POST['percentage'] ?? 0.20;
    $prepaid = isset($_POST['prepaid']) ? 1 : 0;
    $cut_percentage = $_POST['cut_percentage'] ?? 0.20;
    $cut_boundary = $_POST['cut_boundary'] ?? 100;
    $min_stake = $_POST['min_stake'] ?? 20;
    $status = $_POST['status'] ?? 'active';
    
    if ($shopId > 0) {
        try {
            $pdo->beginTransaction();
            // Update shop
            $stmt = $pdo->prepare("UPDATE shops SET name = ?, percentage = ?, prepaid = ?, cut_percentage = ?, cut_boundary = ?, min_stake = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $percent, $prepaid, $cut_percentage, $cut_boundary, $min_stake, $status, $shopId]);
            
            // Update agent user
            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE shop_id = ? AND role = 'agent'");
            $stmt->execute([$user, $pass, $shopId]);
            
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    }
}

// Handle Toggle Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $shopId = $_POST['shop_id'] ?? 0;
    $newStatus = $_POST['status'] === 'active' ? 'inactive' : 'active';
    if ($shopId > 0) {
        $stmt = $pdo->prepare("UPDATE shops SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $shopId]);
    }
}

// Handle Add Balance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_balance') {
    $shopId = $_POST['shop_id'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    $txId = $_POST['transaction_id'] ?? '';
    if ($shopId > 0 && $amount > 0) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO transactions (shop_id, type, amount, transaction_id) VALUES (?, 'deposit', ?, ?)");
            $stmt->execute([$shopId, $amount, $txId]);
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE shop_id = ? AND role = 'agent'");
            $stmt->execute([$amount, $shopId]);
            $pdo->commit();
        } catch (Exception $e) { $pdo->rollBack(); }
    }
}

// Date filtering logic
$dateStart = null;
$dateEnd = null;
if (!empty($_POST['datefilter'])) {
    $parts = explode(' - ', $_POST['datefilter']);
    if (count($parts) === 2) {
        $dateStart = date('Y-m-d', strtotime($parts[0]));
        $dateEnd = date('Y-m-d', strtotime($parts[1]));
    }
}

$whereConditions = "status = 'finished'";
$dateParams = [];

if ($dateStart && $dateEnd) {
    $whereConditions .= " AND DATE(created_at) BETWEEN ? AND ?";
    $dateParams = [$dateStart, $dateEnd];
}

$whereClause = "WHERE " . $whereConditions;

// Fetch stats
$totalShops = $pdo->query("SELECT COUNT(*) FROM shops")->fetchColumn();
$totalDeposits = $pdo->query("SELECT SUM(amount) FROM transactions WHERE type = 'deposit'")->fetchColumn() ?: "0.00";

// House Earnings (Filtered)
$stmt = $pdo->prepare("SELECT SUM(commission_pool) FROM games $whereClause");
$stmt->execute($dateParams);
$totalEarnings = $stmt->fetchColumn() ?: "0.00";

$stmt = $pdo->prepare("SELECT SUM(commission_pool) FROM games WHERE status = 'finished' AND DATE(created_at) = ?");
$stmt->execute([date('Y-m-d')]);
$todayEarnings = $stmt->fetchColumn() ?: "0.00";

// Fetch shops with aggregated game stats (Filtered)
$today = date('Y-m-d');
$shops_query = "SELECT s.*, u.username as agent_name, u.password as agent_password, u.balance as agent_balance,
                 (SELECT SUM(amount) FROM transactions WHERE shop_id = s.id AND type = 'deposit') as total_deposited,
                 (SELECT COUNT(*) FROM games WHERE shop_id = s.id AND $whereConditions) as total_games_count,
                 (SELECT SUM(commission_pool) FROM games WHERE shop_id = s.id AND $whereConditions) as total_shop_earnings,
                 (SELECT COUNT(*) FROM games WHERE shop_id = s.id AND status = 'finished' AND DATE(created_at) = '$today') as today_games_count,
                 (SELECT SUM(commission_pool) FROM games WHERE shop_id = s.id AND status = 'finished' AND DATE(created_at) = '$today') as today_shop_earnings
          FROM shops s 
          LEFT JOIN users u ON s.id = u.shop_id AND u.role = 'agent'";

if ($dateStart && $dateEnd) {
    $stmt = $pdo->prepare($shops_query);
    $stmt->execute(array_merge($dateParams, $dateParams));
} else {
    $stmt = $pdo->prepare($shops_query);
    $stmt->execute();
}
$shops = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="static/agent/css/style.css">
    <link rel="stylesheet" href="static/game/css/lightpick.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
    <script src="static/game/js/lightpick.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <title>Dallol Bingo - Admin</title>
    <style>
        /* Action Buttons Styling */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 80px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .action-btn:active {
            transform: translateY(0);
        }

        .action-btn.edit {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            border: 1px solid #5a6268;
        }

        .action-btn.edit:hover {
            background: linear-gradient(135deg, #5a6268 0%, #3d4349 100%);
            border-color: #495057;
        }

        .action-btn.deposit {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: white;
            border: 1px solid #218838;
        }

        .action-btn.deposit:hover {
            background: linear-gradient(135deg, #218838 0%, #155724 100%);
            border-color: #1e7e34;
        }

        .action-btn.toggle {
            min-width: 100px;
            font-weight: 700;
        }

        .action-btn.activate {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            border: 1px solid #17a2b8;
        }

        .action-btn.activate:hover {
            background: linear-gradient(135deg, #138496 0%, #0c5460 100%);
            border-color: #117a8b;
        }

        .action-btn.deactivate {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: 1px solid #dc3545;
        }

        .action-btn.deactivate:hover {
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
            border-color: #bd2130;
        }

        /* Icon inside buttons */
        .action-btn i {
            margin-right: 5px;
            font-size: 14px;
        }

        /* Form layout improvements */
        .form-layout {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 90%;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="password"],
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
            transform: scale(1.2);
        }

        .form-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 10px;
            min-width: 120px;
        }

        .form-btn[type="submit"] {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .form-btn[type="button"] {
            background: #6c757d;
            color: white;
        }

        .form-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .form-btn:active {
            transform: translateY(0);
        }

        /* Blur background */
        .blur-background {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .blur-container {
            max-height: 90vh;
            overflow-y: auto;
            padding: 20px;
        }

        /* Checkbox styling */
        .form-group input[type="checkbox"] + label {
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }

        /* Status badge improvements */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-inactive {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="header">
      <div class="user-info">
          Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>
      </div>
      <div class="tittle">
        <h3>DALLOL BINGO</h3>
      </div>
      <div class="navbar">
        <ul>
              <li><a href="admin.php">Home</a></li>
              <li><a href="logout.php" class="logout-button">Logout</a></li>
          </ul>
      </div>
    </div>
    <div id="main" class="container">
        <div id="stats" class="stats">
            <div class="stat-box">
                <h3>Total Deposits</h3>
                <p><?php echo number_format($totalDeposits, 2); ?> ETB</p>
            </div>
            <div class="stat-box">
                <h3>Total Shops</h3>
                <p><?php echo $totalShops; ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Earning</h3>
                <p><?php echo number_format($totalEarnings, 2); ?> ETB</p>
            </div>
            <div class="stat-box">
                <h3>Today Earning</h3>
                <p><?php echo number_format($todayEarnings, 2); ?> ETB</p>
            </div>
        </div>
        <form class="nav-form" action="" method="post" id="filterForm">
            <input style="width: auto; margin-right: 20px" type="text" id="searchBox" placeholder="Search by name..." onkeyup="filterTable()" />
            <input style="width: auto; margin-right: 20px;" class="input-date" type="text" id="datepicker" name="datefilter" value="<?php echo htmlspecialchars($_POST['datefilter'] ?? ''); ?>"/>
            <input class="logout-button" type="submit" name="filter" value="Filter">
        </form>
        <div id="paginationButtons" style="margin-bottom: 20px;">
            <button class="pagination-button" id="add-new-shop-btn">Add New Shop</button>
        </div>
        <table id="gameTable" class="table_dispay">
          <thead class="text-muted">
            <tr>
                <th>Shop Name</th>
                <th>Percentage</th>
                <th>Prepaid</th>
                <th>Status</th>
                <th>Account</th>
                <th>Total Games</th>
                <th>Total Earning</th>
                <th>Today Game</th>
                <th>Today Earning</th>
                <th>Finance</th>
                <th>Action</th>
            </tr>
        </thead>
            <tbody>
                <?php foreach ($shops as $s): ?>
                <tr>
                    <td><?php echo htmlspecialchars($s['name']); ?></td>
                    <td><?php echo number_format($s['percentage'] * 100, 0); ?>%</td>
                    <td><?php echo $s['prepaid'] ? 'Yes' : 'No'; ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $s['status']; ?>">
                            <?php echo ucfirst($s['status']); ?>
                        </span>
                    </td>
                    <td><?php echo number_format($s['agent_balance'] ?? 0, 2); ?></td>
                    <td><?php echo $s['total_games_count']; ?></td>
                    <td><?php echo number_format($s['total_shop_earnings'] ?? 0, 2); ?></td>
                    <td><?php echo $s['today_games_count']; ?></td>
                    <td><?php echo number_format($s['today_shop_earnings'] ?? 0, 2); ?></td>
                    <td style="text-align: center;">
                        <button class="action-btn deposit" 
                            onclick="openDeposit(<?php echo $s['id']; ?>, '<?php echo addslashes($s['name']); ?>')">
                            <i class='bx bx-wallet'></i> Deposit
                        </button>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn edit" 
                                onclick="openEdit(<?php echo htmlspecialchars(json_encode($s)); ?>)">
                                <i class='bx bx-edit'></i> Edit
                            </button>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="shop_id" value="<?php echo $s['id']; ?>">
                                <input type="hidden" name="status" value="<?php echo $s['status']; ?>">
                                <button type="submit" class="action-btn toggle <?php echo $s['status'] === 'active' ? 'deactivate' : 'activate'; ?>">
                                    <i class='bx bx-<?php echo $s['status'] === 'active' ? 'power-off' : 'power'; ?>'></i>
                                    <?php echo $s['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div id="paginationButtons" style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <button id="prevButton" class="pagination-button">Previous</button>
            <button id="nextButton" class="pagination-button">Next</button>
        </div>
    </div>
    <div class="blur-background" id="blur-background">
        <div class="blur-container">
            <div class="form-layout" id="edit-shop" style="display: none;">
                <h3>Edit Shop</h3>
                <form id="edit-shop-form" method="POST">
                    <input type="hidden" name="action" value="edit_shop">
                    <input type="hidden" name="shop_id" id="edit_shop_id">
                    <div class="form-group">
                        <label for="edit_name">Name:</label>
                        <input type="text" name="name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_name">Agent Username:</label>
                        <input type="text" name="user_name" id="edit_user_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_password">Agent Password:</label>
                        <input type="text" name="password" id="edit_password" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_percentage">Percentage:</label>
                        <input type="number" step="0.01" name="percentage" id="edit_percentage" min="0.1" max="1.0" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_prepaid">Prepaid:</label>
                        <input type="checkbox" name="prepaid" id="edit_prepaid">
                    </div>
                    <div class="form-group">
                        <label for="edit_cut_percentage">Cut Percentage:</label>
                        <input type="number" step="0.01" name="cut_percentage" id="edit_cut_percentage" min="0.15" max="0.5" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_cut_boundary">Cut Boundary:</label>
                        <input type="number" name="cut_boundary" id="edit_cut_boundary" step="10" min="30" max="100" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_min_stake">Min stake:</label>
                        <input type="number" name="min_stake" id="edit_min_stake" step="5" min="10" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status:</label>
                        <select name="status" id="edit_status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div style="margin-top: 20px;">
                        <button type="submit" class="form-btn" style="background-color: #007bff;">Save Changes</button>
                        <button type="button" class="form-btn" onclick="closeModal()">Close</button>
                    </div>
                </form>
            </div>
            <div class="form-layout" id="add-new-shop" style="display: none;">
                <h3>Create New Shop</h3>
                <form id="add-new-shop-form" method="POST">
                    <input type="hidden" name="action" value="add_shop">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="user_name">User Name:</label>
                        <input type="text" name="user_name" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number:</label>
                        <input type="text" name="phone_number" required>
                    </div>
                    <div class="form-group">
                        <label for="percentage">Percentage:</label>
                        <input type="number" step="0.01" name="percentage" value="0.20" min="0.1" max="1.0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="prepaid">Prepaid:</label>
                        <input type="checkbox" name="prepaid">
                    </div>
                    <div class="form-group">
                        <label for="cut_percentage">Cut Percentage:</label>
                        <input type="number" step="0.01" name="cut_percentage" value="0.2" min="0.15" max="0.5" required>
                    </div>
                    <div class="form-group">
                        <label for="cut_boundary">Cut Boundary:</label>
                        <input type="number" name="cut_boundary" value="100" step="10" min="30" max="100" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="min_stake">Min stake:</label>
                        <input type="number" name="min_stake" step="5" value="20" min="10" required>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button type="submit" class="form-btn" style="background-color: #007bff;">Add Shop</button>
                        <button type="button" class="form-btn" onclick="closeModal()">Close</button>
                    </div>
                </form>
            </div>

            <div class="form-layout" id="add-balance" style="display: none;">
                <h3>Add Balance</h3>
                <form id="add-balance-form" method="POST">
                    <input type="hidden" name="action" value="add_balance">
                    <input type="hidden" name="shop_id" id="deposit_shop_id">
                    <div class="form-group">
                        <label for="account_type">Account Type:</label>
                        <select id="account-type" name="account_type" required>
                            <option value="manual">Manual / Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input id="deposit_shop_name" type="text" name="name" readonly>
                    </div>
                    <div class="form-group">
                        <label for="transaction_id">Transaction ID:</label>
                        <input type="text" name="transaction_id" id="transaction-id" placeholder="Enter Transaction ID">
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount:</label>
                        <input type="number" name="amount" id="amount" step="0.01" required>
                    </div>
                    <div style="margin-top: 20px;">
                        <button type="submit" class="form-btn" style="background-color: #007bff;">Add</button>
                        <button type="button" class="form-btn" onclick="closeModal()">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="loader-container" id="loader">
        <div class="loader"></div>
        <div class="loader"></div>
        <div class="loader"></div>
    </div>

    <script>
        new Lightpick({
            field: document.getElementById('datepicker'),
            singleDate: false,
            format: 'MM/DD/YYYY',
        });

        const blurBg = document.getElementById('blur-background');
        const addShopModal = document.getElementById('add-new-shop');
        const editShopModal = document.getElementById('edit-shop');
        const addBalanceModal = document.getElementById('add-balance');

        document.getElementById('add-new-shop-btn').onclick = () => {
            blurBg.style.display = 'block';
            addShopModal.style.display = 'block';
            editShopModal.style.display = 'none';
            addBalanceModal.style.display = 'none';
        }

        function openEdit(shop) {
            document.getElementById('edit_shop_id').value = shop.id;
            document.getElementById('edit_name').value = shop.name;
            document.getElementById('edit_user_name').value = shop.agent_name;
            document.getElementById('edit_password').value = shop.agent_password;
            document.getElementById('edit_percentage').value = shop.percentage;
            document.getElementById('edit_prepaid').checked = parseInt(shop.prepaid) === 1;
            document.getElementById('edit_cut_percentage').value = shop.cut_percentage;
            document.getElementById('edit_cut_boundary').value = shop.cut_boundary;
            document.getElementById('edit_min_stake').value = shop.min_stake;
            document.getElementById('edit_status').value = shop.status;
            
            blurBg.style.display = 'block';
            editShopModal.style.display = 'block';
            addShopModal.style.display = 'none';
            addBalanceModal.style.display = 'none';
        }

        function openDeposit(id, name) {
            document.getElementById('deposit_shop_id').value = id;
            document.getElementById('deposit_shop_name').value = name;
            blurBg.style.display = 'block';
            addBalanceModal.style.display = 'block';
            addShopModal.style.display = 'none';
        }

        function closeModal() {
            blurBg.style.display = 'none';
        }

        function filterTable() {
            const filter = document.getElementById("searchBox").value.toUpperCase();
            document.querySelectorAll("#gameTable tbody tr").forEach(row => {
                row.style.display = row.innerText.toUpperCase().includes(filter) ? "" : "none";
            });
        }

        window.onload = () => document.getElementById('loader').style.display = 'none';
        
        // Handle Transaction ID requirement
        const accountTypeSelect = document.getElementById("account-type");
        const transactionInput = document.getElementById("transaction-id");

        if (accountTypeSelect) {
            accountTypeSelect.addEventListener("change", function() {
                if(this.value === "manual") {
                    transactionInput.value = "";
                    transactionInput.required = false;
                    transactionInput.placeholder = "Transaction ID not required";
                } else {
                    transactionInput.required = true;
                    transactionInput.placeholder = "Enter Transaction ID";
                }
            });
        }
    </script>
</body>
</html>