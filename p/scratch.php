<?php
require_once '../db.php';
try {
    $stmt = $pdo->query("SHOW TABLES IN prolyn_master");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in prolyn_master:\n";
    print_r($tables);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
