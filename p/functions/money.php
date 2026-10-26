<?php

function paraFormatla(float $miktar, string $kuruSymbol = "₺", string $noktaAyiraci = ",", string $binAyiraci = ".", int $ondalikBasamakSayisi = 2): string {
    // Sayısal değer ve aralık kontrolü
    if (!is_numeric($miktar)) {
        trigger_error('Para miktarı sayısal olmalıdır', E_USER_WARNING);
        return "0" . $noktaAyiraci . "00 " . $kuruSymbol;
    }

    // Negatif değer kontrolü
    if ($miktar < 0) {
        $miktar = abs($miktar); // Mutlak değer al, ancak '-' işareti göster
        $sign = '-';
    } else {
        $sign = '';
    }

    // Miktarı virgülden sonra belirtilen ondalık basamak sayısı kadar biçimlendiriyoruz
    $formatted = number_format($miktar, $ondalikBasamakSayisi, $noktaAyiraci, $binAyiraci);

    // Biçimlendirilmiş miktarın başına kur simgesini ekliyoruz
    return $sign . $formatted . " " . $kuruSymbol;
}


function paraFormatlaYazi(float $number, string $currency = 'Türk Lirası', string $subunit = 'Kuruş'): string {
    // Maksimum desteklenen değer kontrolü (999.999.999.999,99 TL)
    $maxSupported = 999999999999.99;
    if ($number > $maxSupported) {
        trigger_error('Maksimum desteklenen tutar: ' . $maxSupported, E_USER_WARNING);
        return "Girilen tutar çok büyük";
    }

    // Negatif sayı kontrolü
    if ($number < 0) {
        trigger_error('Negatif tutarlar desteklenmez', E_USER_WARNING);
        return "Negatif tutar desteklenmez";
    }

    // Sayı isimleri
    $units = [
        0 => '',
        1 => 'Bir',
        2 => 'İki',
        3 => 'Üç',
        4 => 'Dört',
        5 => 'Beş',
        6 => 'Altı',
        7 => 'Yedi',
        8 => 'Sekiz',
        9 => 'Dokuz',
        10 => 'On',
        20 => 'Yirmi',
        30 => 'Otuz',
        40 => 'Kırk',
        50 => 'Elli',
        60 => 'Altmış',
        70 => 'Yetmiş',
        80 => 'Seksen',
        90 => 'Doksan'
    ];

    // Basamak grupları
    $groups = ['', 'Bin ', 'Milyon ', 'Milyar ', 'Trilyon ', 'Katrilyon '];

    // Sayıyı tam ve kesirli kısımlara ayır
    $formattedNumber = number_format($number, 2, '.', '');
    [$wholePart, $fractionPart] = explode('.', $formattedNumber);

    // Ana para birimini çevir
    $mainText = convertWholeNumber($wholePart, $units, $groups);
    
    // Kuruş kısmını çevir
    $fractionText = convertFraction($fractionPart, $units, $subunit);

    // Sonucu birleştir
    $result = trim($mainText . ' ' . $currency);
    
    if (!empty($fractionText)) {
        $result .= ' ' . $fractionText;
    }

    return $result;
}

/**
 * Tam sayıyı yazıyla çevir (3'lü gruplar halinde)
 */
function convertWholeNumber(string $whole, array $units, array $groups): string {
    if (empty($whole) || !is_numeric($whole)) {
        return '';
    }

    // Sayıyı 3'lü gruplara ayırmak için sıfırlarla doldur
    $paddedLength = (int)ceil(strlen($whole) / 3) * 3;
    $whole = str_pad($whole, $paddedLength, '0', STR_PAD_LEFT);
    
    // 3'lü gruplara böl
    $chunks = str_split($whole, 3);
    $textParts = [];

    foreach ($chunks as $index => $chunk) {
        $groupIndex = count($chunks) - $index - 1;
        $groupText = convertThreeDigits($chunk, $units);

        if ($groupText !== '') {
            // "Bin" için özel kural: Bin grubunda sadece "Bir" varsa, "Bir" kelimesini atla
            if ($groupIndex === 1 && $groupText === 'Bir') {
                $textParts[] = 'Bin';
            } elseif ($groupIndex === 1 && strpos($groupText, 'Bir ') === 0) {
                // "Bir Yüz ..." gibi durumlarda "Bir"i kaldır
                $textParts[] = substr($groupText, 4) . ' Bin';
            } else {
                $textParts[] = trim($groupText . ' ' . $groups[$groupIndex]);
            }
        }
    }

    return implode(' ', $textParts);
}

/**
 * 3 haneli sayıyı yazıyla çevir
 */
function convertThreeDigits(string $chunk, array $units): string {
    if (strlen($chunk) !== 3 || !is_numeric($chunk)) {
        return '';
    }

    $hundreds = (int)$chunk[0];
    $tens = (int)$chunk[1];
    $ones = (int)$chunk[2];
    
    $text = '';

    // Yüzler basamağı
    if ($hundreds > 0) {
        $text .= ($hundreds === 1) ? 'Yüz' : $units[$hundreds] . ' Yüz';
    }

    // Onlar basamağı
    if ($tens > 0) {
        $text .= ($text !== '' ? ' ' : '') . $units[$tens * 10];
    }

    // Birler basamağı
    if ($ones > 0) {
        $text .= ($text !== '' ? ' ' : '') . $units[$ones];
    }

    return trim($text);
}

/**
 * Kesirli kısım (kuruş) yazıyla çevir
 */
function convertFraction(string $fraction, array $units, string $subunit): string {
    if (strlen($fraction) !== 2 || !is_numeric($fraction) || $fraction === '00') {
        return '';
    }

    $tens = (int)$fraction[0];
    $ones = (int)$fraction[1];
    
    $fractionText = '';
    
    if ($tens > 0) {
        $fractionText .= $units[$tens * 10];
    }
    
    if ($ones > 0) {
        $fractionText .= ($fractionText !== '' ? ' ' : '') . $units[$ones];
    }

    return trim($fractionText . ' ' . $subunit);
}

?>