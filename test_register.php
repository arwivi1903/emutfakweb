<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['company_name'] = 'Test Company';
$_POST['official_name'] = 'Test Company Ltd';
$_POST['contact_name'] = 'Ali Yilmaz';
$_POST['company_email'] = 'test@example.com';
$_POST['company_phone'] = '05551234567';
$_POST['admin_password'] = '123456';
$_POST['tax_office'] = 'Sisli';
$_POST['tax_number'] = '11111111111'; // invalid, but we'll bypass
$_POST['billing_address'] = 'Test Address';
$_POST['city_id'] = '34';
$_POST['district_id'] = '1';

ob_start();
// Replace function to always return true
$content = file_get_contents('register.php');
$content = str_replace('!firma_vno_is_valid($tax_number)', 'false', $content);
file_put_contents('register_patched.php', $content);

include 'register_patched.php';
$output = ob_get_clean();

preg_match('/<div class="fw-semibold text-danger">([^<]+)<\/div>/', $output, $matches);
echo "Message: " . ($matches[1] ?? 'Not found');
