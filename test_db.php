<?php
$host = 'localhost';
$dbname = 'dallol_bingo';
$username = 'root';
$password = '';

echo "<h3>🔍 Testing Database Connection...</h3>";

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    echo "✅ Success: Connected to MySQL.<br>";
    
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Success: Database '$dbname' exists.<br>";
    } else {
        echo "❌ Error: Database '$dbname' DOES NOT EXIST. Please create it in phpMyAdmin.<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ CONNECTION FAILED: " . $e->getMessage();
}
?>
