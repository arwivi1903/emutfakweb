<?php
require_once __DIR__ . '/classes/allClass.php';
$db = new Database();
$plans = $db->getRowsAssoc("SELECT * FROM paket_ozellikleri ORDER BY feature_id ASC");
print_r($plans);
