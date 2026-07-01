<?php
$db = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', 'RootPass1903!');
$dbs = $db->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
foreach($dbs as $d) {
    try {
        $count = $db->query("SELECT COUNT(*) FROM `$d`.`kurumlar`")->fetchColumn();
        echo "$d has $count rows\n";
    } catch(Exception $e) {}
}
