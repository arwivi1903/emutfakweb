<?php

date_default_timezone_set('Europe/Istanbul');

function date_turkey($mytime){
    if (empty($mytime)) {
        return "";
    }

    $en_months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $tr_months = array("Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık");

    $en_days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    $tr_days = array("Pazartesi", "Salı", "Çarşamba", "Perşembe", "Cuma", "Cumartesi", "Pazar");

    $mytime = str_replace($en_months, $tr_months, $mytime);
    $mytime = str_replace($en_days, $tr_days, $mytime);

    return $mytime;
}

function tr_date($tarih){
    if (!empty($tarih)) {
        $d = DateTime::createFromFormat('Y-m-d', $tarih, new DateTimeZone('Europe/Istanbul'));
        if ($d === false) {
            return "";
        }
        return date_format($d, 'd.m.Y');
    } else {
        return "";
    }
}

function tr_datetime($tarih){
    if (!empty($tarih)) {
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $tarih, new DateTimeZone('Europe/Istanbul'));
        if ($d === false) {
            return "";
        }
        return date_format($d, 'd.m.Y H:i:s');
    } else {
        return "";
    }
}

?>