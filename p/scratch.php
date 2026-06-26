<?php
require_once 'classes/allClass.php';
$db = new Database('prolyn_master');
$stmt = $db->TableOperations("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo "TABLE: " . $row[0] . "\n";
    $cols = $db->TableOperations("SHOW COLUMNS FROM " . $row[0]);
    while ($col = $cols->fetch(PDO::FETCH_NUM)) {
        echo "  - " . $col[0] . " (" . $col[1] . ")\n";
    }
}
