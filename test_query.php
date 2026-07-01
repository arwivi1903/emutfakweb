<?php
require_once 'db.php';
$company_email = 'ab@aa.com';
$company_phone = '';
$tax_number = '11111111111';

$checkUser = $pdo->prepare("SELECT customer_id, company_email, company_phone, tax_number FROM kurumlar WHERE company_email = ? OR (company_phone = ? AND company_phone != '') OR tax_number = ?");
$checkUser->execute([$company_email, $company_phone, $tax_number]);
$existing = $checkUser->fetch();
var_dump($existing);
