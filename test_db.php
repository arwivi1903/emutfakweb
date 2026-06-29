<?php
$config = require 'p/config/db_config.php';
$pdo = new PDO("mysql:host=127.0.0.1;dbname={$config['db']};charset={$config['charset']}", $config['user'], $config['pass']);
$pdo->query("ALTER TABLE kurumlar ADD COLUMN city_id INT NULL AFTER billing_address");
$pdo->query("ALTER TABLE kurumlar ADD COLUMN district_id INT NULL AFTER city_id");
echo "Columns added to kurumlar.";
