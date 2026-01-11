<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT id, name, percentage, cut_percentage, cut_boundary FROM shops");
print_r($stmt->fetchAll());
?>
