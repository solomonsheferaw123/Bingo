<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (!empty($user) && !empty($pass)) {
        // Check user and join with shop to check status
        $stmt = $pdo->prepare("SELECT u.*, s.status as shop_status 
                               FROM users u 
                               LEFT JOIN shops s ON u.shop_id = s.id 
                               WHERE u.username = ?");
        $stmt->execute([$user]);
        $userData = $stmt->fetch();

        if ($userData && $pass === $userData['password']) {
            // Check if shop is active for agent roles
            if ($userData['role'] === 'agent' && $userData['shop_status'] === 'inactive') {
                $error = 'Your account has been deactivated. Please contact +251926828937.';
            } else {
                $_SESSION['user_id'] = $userData['id'];
                $_SESSION['username'] = $userData['username'];
                $_SESSION['role'] = $userData['role'];
                $_SESSION['shop_id'] = $userData['shop_id'];

                if ($userData['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            }
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Please enter both username and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: rgba(3, 192, 255, 0.5);
        }
        
        .login-container {
            display: flex;
            flex-direction: row;
            width: auto;
            height: 420px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 10px 10px rgba(0, 0, 0, 0.5);
        }
        
        .left {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 400px;
            height: 400px;
            padding: 20px;
        }
        
        .login-form h2 {
            margin-bottom: 20px;
            text-align: center;
            font-size: 30px;
            color: #a45b28;
        }
        
        .input-group {
            margin-bottom: 15px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .input-group input {
            width: 300px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 300px;
        }
        
        button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 5px;
            margin-bottom: 15px;
            text-align: center;
            width: 300px;
        }

        .error {
            color: red;
            background-color: #ffeaea;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
            width: 300px;
            border: 1px solid #ffcccc;
        }

        .img-logo {
            width: 420px;
            height: 420px;
            border-radius: 8px;
            box-shadow: -10px 0 15px -5px rgba(0, 0, 0, 0.5);
            object-fit: cover;
        }

        .right {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="left">
            <form class="login-form" method="POST" action="login.php">
                <h2>Login</h2>
                
                <?php if ($error): ?>
                    <div class="error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php else: ?>
                    <p class="error-message">Enter username and password</p>
                <?php endif; ?>
                
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Login</button>
            </form>
        </div>
        <div class="right">
<img src="static/game/icon/login.jpg" alt="Dallol Bingo" class="img-logo">        </div>
    </div>
</body>
</html>