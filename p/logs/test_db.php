<?php
require_once '/Applications/XAMPP/xamppfiles/htdocs/prolynweb/p/config/header.php';
try {
    $rows = $db->getRows("SELECT * FROM oturumlar");
    echo "oturumlar table exists. Columns:\n";
    $cols = $db->getRows("SHOW COLUMNS FROM oturumlar");
    foreach($cols as $col) {
        echo $col->Field . " - " . $col->Type . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
