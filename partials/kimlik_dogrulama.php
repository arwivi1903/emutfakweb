<?php
/**
 * T.C. Kimlik No ve Vergi Kimlik No doğrulama yardımcıları.
 *
 * ÖNEMLİ SINIR: Bu fonksiyonlar numaranın MATEMATİKSEL olarak geçerli olup
 * olmadığını (resmî kontrol basamağı algoritması) denetler. Numaranın gerçekten
 * var olan bir kişiye/mükellefe ait olduğunu doğrulamaz; bunun için NVİ KPS
 * (T.C. kimlik: ad, soyad, doğum yılı gerekir) ve GİB/e-Fatura mükellef sorgusu
 * gibi resmî servis entegrasyonu gerekir.
 */

if (!function_exists('prolyn_kimlik_normalize')) {
    /** Boşluk, tire, nokta vb. her şeyi atıp yalnızca rakamları bırakır. */
    function prolyn_kimlik_normalize($deger): string
    {
        return preg_replace('/\D+/', '', (string)$deger);
    }
}

if (!function_exists('prolyn_tckn_gecerli')) {
    /** T.C. Kimlik No: 11 hane, ilk hane 0 olamaz, 10. ve 11. haneler algoritmaya uymalı. */
    function prolyn_tckn_gecerli(string $no): bool
    {
        if (!preg_match('/^[1-9][0-9]{10}$/', $no)) { return false; }
        if (preg_match('/^(\d)\1{10}$/', $no)) { return false; } // 11111111111 gibi
        $d = array_map('intval', str_split($no));
        $tekler = $d[0] + $d[2] + $d[4] + $d[6] + $d[8];
        $ciftler = $d[1] + $d[3] + $d[5] + $d[7];
        // PHP'de % negatif sonuç verebilir; 0-9 aralığına çekiyoruz.
        $onuncu = ((($tekler * 7) - $ciftler) % 10 + 10) % 10;
        if ($onuncu !== $d[9]) { return false; }
        $onbirinci = array_sum(array_slice($d, 0, 10)) % 10;
        return $onbirinci === $d[10];
    }
}

if (!function_exists('prolyn_vkn_gecerli')) {
    /** Vergi Kimlik No: 10 hane, GİB kontrol basamağı algoritması. */
    function prolyn_vkn_gecerli(string $no): bool
    {
        if (!preg_match('/^[0-9]{10}$/', $no)) { return false; }
        if (preg_match('/^(\d)\1{9}$/', $no)) { return false; } // 0000000000, 1111111111 ...
        $d = array_map('intval', str_split($no));
        $toplam = 0;
        for ($i = 0; $i < 9; $i++) {
            $v = ($d[$i] + (9 - $i)) % 10;
            $vv = ($v * (2 ** (9 - $i))) % 9;
            if ($v !== 0 && $vv === 0) { $vv = 9; }
            $toplam += $vv;
        }
        $kontrol = (10 - ($toplam % 10)) % 10;
        return $kontrol === $d[9];
    }
}

if (!function_exists('prolyn_kimlik_no_incele')) {
    /**
     * Kayıt formundaki "Vergi No / TC Kimlik No" alanını çözümler.
     * @return array{gecerli:bool, tip:string, no:string, hata:string}
     *         tip: 'vkn' (10 hane) | 'tckn' (11 hane) | ''
     */
    function prolyn_kimlik_no_incele($ham): array
    {
        $no = prolyn_kimlik_normalize($ham);
        $uzunluk = strlen($no);
        if ($uzunluk === 10) {
            return prolyn_vkn_gecerli($no)
                ? ['gecerli' => true, 'tip' => 'vkn', 'no' => $no, 'hata' => '']
                : ['gecerli' => false, 'tip' => 'vkn', 'no' => $no, 'hata' => 'Girdiğiniz Vergi Kimlik No geçerli değil. Lütfen numarayı kontrol edin.'];
        }
        if ($uzunluk === 11) {
            return prolyn_tckn_gecerli($no)
                ? ['gecerli' => true, 'tip' => 'tckn', 'no' => $no, 'hata' => '']
                : ['gecerli' => false, 'tip' => 'tckn', 'no' => $no, 'hata' => 'Girdiğiniz T.C. Kimlik No geçerli değil. Lütfen numarayı kontrol edin.'];
        }
        return ['gecerli' => false, 'tip' => '', 'no' => $no, 'hata' => 'Vergi No 10 haneli, T.C. Kimlik No 11 haneli olmalıdır.'];
    }
}
