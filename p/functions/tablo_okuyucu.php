<?php
/**
 * Prolyn - Tablo dosyası okuyucu (CSV dışındaki Excel biçimleri için)
 * -------------------------------------------------------------------
 * Harici kütüphane gerektirmez (paylaşımlı hosting uyumlu).
 *   .xlsx  → ZipArchive (ext-zip) + XMLReader
 *   .xls   → Excel 97-2003 (BIFF8) ikili biçimi, OLE2 kapsayıcısı
 * Dönüş: satırlar dizisi; her satır hücre metinlerinden oluşan dizi (0 tabanlı sütun sırası).
 *
 * Güvenlik/dayanıklılık: dosya içeriği imzayla doğrulanır (uzantıya güvenilmez),
 * açılmış boyut ve satır/sütun sayısı sınırlıdır (zip-bomb koruması), XML ağ erişimi kapalıdır,
 * hiçbir hücre değeri kod olarak çalıştırılmaz (formüller yalnızca önbelleğe alınmış değerleriyle okunur).
 *
 * Kullanım:
 *   $tur = tablo_dosya_turu($tmpYol);                 // 'xlsx' | 'xls' | 'csv'
 *   $satirlar = tablo_oku($tmpYol, $tur, ['firma_adi','title']);
 */

const TABLO_MAX_SATIR = 20000;          // okunacak en çok satır
const TABLO_MAX_SUTUN = 120;            // okunacak en çok sütun
const TABLO_MAX_XML_BAYT = 60 * 1024 * 1024;   // zip içindeki tek XML parçasının açılmış üst sınırı

class TabloHatasi extends RuntimeException {}

/** İçeriğin imzasına göre dosya türü. */
function tablo_dosya_turu(string $yol): string
{
    $fh = @fopen($yol, 'rb');
    $b = $fh ? (string)fread($fh, 8) : '';
    if ($fh) { fclose($fh); }
    if (strncmp($b, "PK\x03\x04", 4) === 0) { return 'xlsx'; }
    if ($b === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") { return 'xls'; }
    return 'csv';
}

/**
 * Dosyadaki ilk "uygun" sayfayı okur. Uygun sayfa: ilk dolu satırı (başlık) $basliklar'dan
 * en az birini içeren sayfa. Hiçbiri uymazsa ilk sayfa döner.
 * @param string[] $basliklar küçük harfli beklenen başlık adları
 */
function tablo_oku(string $yol, string $tur, array $basliklar = []): array
{
    if ($tur === 'xlsx') { return TabloXlsx::oku($yol, $basliklar); }
    if ($tur === 'xls')  { return TabloXls::oku($yol, $basliklar); }
    throw new TabloHatasi('Desteklenmeyen dosya türü.');
}

/** Başlık satırı beklenen adlardan birini içeriyor mu? */
function tablo_baslik_uyar(array $satir, array $basliklar): bool
{
    foreach ($satir as $h) {
        if (in_array(mb_strtolower(trim((string)$h), 'UTF-8'), $basliklar, true)) { return true; }
    }
    return false;
}

/** Sayıyı Excel gibi okunur metne çevirir: 902124944404 → "902124944404" (bilimsel gösterim yok). */
function tablo_sayi_metni(float $v): string
{
    if (is_nan($v) || is_infinite($v)) { return ''; }
    if (floor($v) === $v && abs($v) < 1e15) { return sprintf('%.0f', $v); }
    return rtrim(rtrim(sprintf('%.15F', $v), '0'), '.');
}

/* ======================================================================
 *  XLSX
 * ==================================================================== */
final class TabloXlsx
{
    public static function oku(string $yol, array $basliklar): array
    {
        if (!class_exists('ZipArchive')) {
            throw new TabloHatasi('Sunucuda ZIP desteği (php-zip) kapalı. Dosyayı CSV olarak kaydedip yükleyin.');
        }
        $zip = new ZipArchive();
        if ($zip->open($yol) !== true) { throw new TabloHatasi('Excel dosyası açılamadı (bozuk olabilir).'); }
        try {
            $sayfalar = self::sayfalar($zip);
            if (!$sayfalar) { throw new TabloHatasi('Excel dosyasında sayfa bulunamadı.'); }
            $ortak = null;   // ortak metinler yalnızca gerektiğinde yüklenir
            $secilen = null;
            foreach ($sayfalar as $s) {
                $ortak ??= self::ortakMetinler($zip);
                $ilk = self::sayfaOku($zip, $s['yol'], $ortak, 3);
                $baslik = self::ilkDoluSatir($ilk);
                if ($baslik !== null && tablo_baslik_uyar($baslik, $basliklar)) { $secilen = $s; break; }
            }
            $secilen ??= $sayfalar[0];
            $ortak ??= self::ortakMetinler($zip);
            return self::sayfaOku($zip, $secilen['yol'], $ortak, TABLO_MAX_SATIR + 1);
        } finally {
            $zip->close();
        }
    }

    private static function ilkDoluSatir(array $satirlar): ?array
    {
        foreach ($satirlar as $s) {
            foreach ($s as $h) { if (trim((string)$h) !== '') { return $s; } }
        }
        return null;
    }

    /** Zip içindeki bir parçayı güvenli biçimde okur (açılmış boyut denetimli). */
    private static function parca(ZipArchive $zip, string $ad): ?string
    {
        $st = $zip->statName($ad);
        if ($st === false) { return null; }
        if ($st['size'] > TABLO_MAX_XML_BAYT) { throw new TabloHatasi('Excel dosyası çok büyük (açılmış boyut sınırı aşıldı).'); }
        $v = $zip->getFromName($ad);
        return $v === false ? null : $v;
    }

    private static function xmlOkuyucu(string $xml): XMLReader
    {
        $r = new XMLReader();
        // LIBXML_NONET: ağa çıkma; varlık (entity) genişletme açılmaz
        if (!$r->XML($xml, 'UTF-8', LIBXML_NONET | LIBXML_COMPACT)) { throw new TabloHatasi('Excel XML içeriği okunamadı.'); }
        return $r;
    }

    /** @return array<int,array{ad:string,yol:string}> */
    private static function sayfalar(ZipArchive $zip): array
    {
        $wb = self::parca($zip, 'xl/workbook.xml');
        $rel = self::parca($zip, 'xl/_rels/workbook.xml.rels');
        if ($wb === null || $rel === null) { throw new TabloHatasi('Geçerli bir .xlsx dosyası değil.'); }
        $hedef = [];
        $r = self::xmlOkuyucu($rel);
        while ($r->read()) {
            if ($r->nodeType === XMLReader::ELEMENT && $r->localName === 'Relationship') {
                $hedef[(string)$r->getAttribute('Id')] = (string)$r->getAttribute('Target');
            }
        }
        $liste = [];
        $r = self::xmlOkuyucu($wb);
        while ($r->read()) {
            if ($r->nodeType === XMLReader::ELEMENT && $r->localName === 'sheet') {
                $state = (string)$r->getAttribute('state');
                $rid = (string)$r->getAttribute('r:id');
                if ($rid === '' || $state === 'hidden' || $state === 'veryHidden' || !isset($hedef[$rid])) { continue; }
                $t = $hedef[$rid];
                $yol = ($t !== '' && $t[0] === '/') ? ltrim($t, '/') : 'xl/' . $t;
                $liste[] = ['ad' => (string)$r->getAttribute('name'), 'yol' => $yol];
            }
        }
        return $liste;
    }

    /** @return string[] */
    private static function ortakMetinler(ZipArchive $zip): array
    {
        $xml = self::parca($zip, 'xl/sharedStrings.xml');
        if ($xml === null) { return []; }
        $out = [];
        $r = self::xmlOkuyucu($xml);
        while ($r->read()) {
            if ($r->nodeType === XMLReader::ELEMENT && $r->localName === 'si') {
                $out[] = self::siMetni($r);
            }
        }
        return $out;
    }

    /** <si> / <is> içindeki metni toplar (zengin metin parçaları birleşir, fonetik <rPh> atlanır). */
    private static function siMetni(XMLReader $r): string
    {
        if ($r->isEmptyElement) { return ''; }
        $derinlik = $r->depth;
        $metin = '';
        $fonetik = false;
        while ($r->read()) {
            if ($r->nodeType === XMLReader::END_ELEMENT && $r->depth === $derinlik) { break; }
            if ($r->nodeType === XMLReader::ELEMENT && $r->localName === 'rPh') { $fonetik = true; }
            if ($r->nodeType === XMLReader::END_ELEMENT && $r->localName === 'rPh') { $fonetik = false; }
            if (!$fonetik && $r->nodeType === XMLReader::ELEMENT && $r->localName === 't' && !$r->isEmptyElement) {
                $metin .= $r->readString();
            }
        }
        return $metin;
    }

    /** "AB12" → 27 (0 tabanlı sütun) */
    private static function sutunNo(string $ref): int
    {
        $n = 0;
        for ($i = 0, $l = strlen($ref); $i < $l; $i++) {
            $c = ord($ref[$i]);
            if ($c >= 65 && $c <= 90) { $n = $n * 26 + ($c - 64); }
            elseif ($c >= 97 && $c <= 122) { $n = $n * 26 + ($c - 96); }
            else { break; }
        }
        return max(0, $n - 1);
    }

    private static function sayfaOku(ZipArchive $zip, string $yol, array $ortak, int $enCokSatir): array
    {
        $xml = self::parca($zip, $yol);
        if ($xml === null) { throw new TabloHatasi('Excel sayfası okunamadı.'); }
        $r = self::xmlOkuyucu($xml);
        $satirlar = [];
        $sira = -1;
        while ($r->read()) {
            if ($r->nodeType !== XMLReader::ELEMENT || $r->localName !== 'row') { continue; }
            $rAttr = $r->getAttribute('r');
            $sira = ($rAttr !== null && ctype_digit($rAttr)) ? (int)$rAttr - 1 : $sira + 1;
            if (count($satirlar) >= $enCokSatir) { break; }
            $satir = [];
            if (!$r->isEmptyElement) {
                $rowDerinlik = $r->depth;
                $son = -1;
                while ($r->read()) {
                    if ($r->nodeType === XMLReader::END_ELEMENT && $r->depth === $rowDerinlik) { break; }
                    if ($r->nodeType !== XMLReader::ELEMENT || $r->localName !== 'c') { continue; }
                    $ref = (string)$r->getAttribute('r');
                    $tip = (string)$r->getAttribute('t');
                    $kol = $ref !== '' ? self::sutunNo($ref) : $son + 1;
                    $son = $kol;
                    $deger = '';
                    if (!$r->isEmptyElement) {
                        $hDerinlik = $r->depth;
                        while ($r->read()) {
                            if ($r->nodeType === XMLReader::END_ELEMENT && $r->depth === $hDerinlik) { break; }
                            if ($r->nodeType !== XMLReader::ELEMENT) { continue; }
                            if ($r->localName === 'v') { $deger = $r->isEmptyElement ? '' : $r->readString(); }
                            elseif ($r->localName === 'is') { $deger = self::siMetni($r); }
                        }
                    }
                    if ($kol >= TABLO_MAX_SUTUN) { continue; }
                    if ($tip === 's') { $deger = $ortak[(int)$deger] ?? ''; }
                    elseif ($tip === 'b') { $deger = $deger === '1' ? '1' : '0'; }
                    elseif ($tip === 'e') { $deger = ''; }
                    elseif ($tip === '' || $tip === 'n') { if ($deger !== '' && is_numeric($deger)) { $deger = tablo_sayi_metni((float)$deger); } }
                    $satir[$kol] = $deger;
                }
            }
            // seyrek diziyi 0..max aralığında doldur
            if ($satir) {
                $max = max(array_keys($satir));
                $tam = array_fill(0, $max + 1, '');
                foreach ($satir as $k => $v) { $tam[$k] = $v; }
                $satir = $tam;
            }
            // boş satırlar atlanan numaralar için de ara satır bırakmaz; yalnızca dolu/boş sırayı korur
            $satirlar[] = $satir;
        }
        return $satirlar;
    }
}

/* ======================================================================
 *  XLS (BIFF8, OLE2)
 * ==================================================================== */
final class TabloXls
{
    private string $d;            // tüm dosya
    private int $sektorBoyu;
    private array $fat = [];
    private array $miniFat = [];
    private string $miniAkim = '';
    private int $miniSinir = 4096;
    private int $miniBoyu = 64;

    public static function oku(string $yol, array $basliklar): array
    {
        $boyut = @filesize($yol);
        if ($boyut === false || $boyut > 25 * 1024 * 1024) { throw new TabloHatasi('Excel dosyası çok büyük.'); }
        $o = new self();
        $o->d = (string)file_get_contents($yol);
        $akim = $o->calismaKitabi();
        return $o->biff($akim, $basliklar);
    }

    /* ---- OLE2 kapsayıcı ---- */
    private function u16(string $s, int $o): int { return unpack('v', substr($s, $o, 2) . "\0\0")[1]; }
    private function u32(string $s, int $o): int { return unpack('V', substr($s, $o, 4) . "\0\0\0\0")[1]; }

    private function sektor(int $no): string
    {
        return substr($this->d, 512 + $no * $this->sektorBoyu, $this->sektorBoyu);
    }

    private function zincir(int $baslangic): string
    {
        $out = ''; $n = 0; $max = intdiv(strlen($this->d), max(1, $this->sektorBoyu)) + 2;
        while ($baslangic < 0xFFFFFFFA && $n++ < $max) {
            $out .= $this->sektor($baslangic);
            $baslangic = $this->fat[$baslangic] ?? 0xFFFFFFFE;
        }
        return $out;
    }

    private function miniZincir(int $baslangic, int $boyut): string
    {
        $out = ''; $n = 0; $max = intdiv(strlen($this->miniAkim), $this->miniBoyu) + 2;
        while ($baslangic < 0xFFFFFFFA && $n++ < $max) {
            $out .= substr($this->miniAkim, $baslangic * $this->miniBoyu, $this->miniBoyu);
            $baslangic = $this->miniFat[$baslangic] ?? 0xFFFFFFFE;
        }
        return substr($out, 0, $boyut);
    }

    private function calismaKitabi(): string
    {
        if (strlen($this->d) < 512 || substr($this->d, 0, 8) !== "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
            throw new TabloHatasi('Geçerli bir .xls dosyası değil.');
        }
        $this->sektorBoyu = 1 << $this->u16($this->d, 0x1E);
        $this->miniBoyu = 1 << $this->u16($this->d, 0x20);
        $this->miniSinir = $this->u32($this->d, 0x38);
        if ($this->sektorBoyu < 128 || $this->sektorBoyu > 4096) { throw new TabloHatasi('Bozuk .xls dosyası.'); }

        // FAT sektör listesi (başlıktaki 109 + DIFAT zinciri)
        $fatSektorleri = [];
        for ($i = 0; $i < 109; $i++) {
            $s = $this->u32($this->d, 0x4C + $i * 4);
            if ($s < 0xFFFFFFFA) { $fatSektorleri[] = $s; }
        }
        $difat = $this->u32($this->d, 0x44); $difatSayisi = $this->u32($this->d, 0x48);
        for ($k = 0; $k < $difatSayisi && $difat < 0xFFFFFFFA; $k++) {
            $blok = $this->sektor($difat);
            $girdi = intdiv($this->sektorBoyu, 4) - 1;
            for ($i = 0; $i < $girdi; $i++) {
                $s = $this->u32($blok, $i * 4);
                if ($s < 0xFFFFFFFA) { $fatSektorleri[] = $s; }
            }
            $difat = $this->u32($blok, $girdi * 4);
        }
        foreach ($fatSektorleri as $fs) {
            $blok = $this->sektor($fs);
            for ($i = 0, $l = intdiv(strlen($blok), 4); $i < $l; $i++) { $this->fat[] = $this->u32($blok, $i * 4); }
        }

        // Dizin
        $dizin = $this->zincir($this->u32($this->d, 0x30));
        $kok = null; $kitap = null;
        for ($o = 0; $o + 128 <= strlen($dizin); $o += 128) {
            $tip = ord($dizin[$o + 0x42]);
            $adUzun = $this->u16($dizin, $o + 0x40);
            $ad = mb_convert_encoding(substr($dizin, $o, max(0, $adUzun - 2)), 'UTF-8', 'UTF-16LE');
            $bas = $this->u32($dizin, $o + 0x74); $boy = $this->u32($dizin, $o + 0x78);
            if ($tip === 5) { $kok = [$bas, $boy]; }
            if ($tip === 2 && ($ad === 'Workbook' || $ad === 'Book')) { $kitap = [$bas, $boy]; }
        }
        if ($kitap === null) { throw new TabloHatasi('Excel çalışma kitabı bulunamadı (şifreli veya çok eski biçim olabilir).'); }

        if ($kitap[1] < $this->miniSinir) {
            // küçük akış: mini akıştan oku
            if ($kok === null) { throw new TabloHatasi('Bozuk .xls dosyası.'); }
            $this->miniAkim = $this->zincir($kok[0]);
            $mf = $this->zincir($this->u32($this->d, 0x3C));
            for ($i = 0, $l = intdiv(strlen($mf), 4); $i < $l; $i++) { $this->miniFat[] = $this->u32($mf, $i * 4); }
            return $this->miniZincir($kitap[0], $kitap[1]);
        }
        return substr($this->zincir($kitap[0]), 0, $kitap[1]);
    }

    /* ---- BIFF8 ---- */
    private function biff(string $s, array $basliklar): array
    {
        $n = strlen($s);
        // 1) kayıtları ayır: [tip, veri, başlangıç ofseti]
        $kayit = [];
        for ($o = 0; $o + 4 <= $n; ) {
            $tip = $this->u16($s, $o); $len = $this->u16($s, $o + 2);
            $kayit[] = [$tip, substr($s, $o + 4, $len), $o];
            $o += 4 + $len;
        }
        if (!$kayit || $kayit[0][0] !== 0x0809) { throw new TabloHatasi('Geçerli bir .xls dosyası değil.'); }
        if ($this->u16($kayit[0][1], 0) !== 0x0600) { throw new TabloHatasi('Yalnızca Excel 97-2003 (BIFF8) .xls dosyaları okunabilir. Dosyayı .xlsx veya CSV olarak kaydedin.'); }

        // 2) genel bölüm: sayfa listesi + ortak metinler
        $sayfalar = []; $sst = [];
        $ofsetIndeks = [];
        foreach ($kayit as $i => $k) { $ofsetIndeks[$k[2]] = $i; }
        for ($i = 0, $c = count($kayit); $i < $c; $i++) {
            [$tip, $v] = $kayit[$i];
            if ($tip === 0x0085 && strlen($v) >= 8) {   // BOUNDSHEET
                $gorunur = ord($v[4]); $tur = ord($v[5]);
                if ($tur === 0 && $gorunur === 0) { $sayfalar[] = $this->u32($v, 0); }
            } elseif ($tip === 0x00FC) {                // SST (+CONTINUE)
                $parca = [$v];
                while ($i + 1 < $c && $kayit[$i + 1][0] === 0x003C) { $parca[] = $kayit[++$i][1]; }
                $sst = $this->sstOku($parca);
            } elseif ($tip === 0x000A) { break; }       // genel bölüm sonu
        }
        if (!$sayfalar) { throw new TabloHatasi('Excel dosyasında sayfa bulunamadı.'); }

        // 3) sayfaları sırayla oku; başlığa uyan ilk sayfayı seç
        $ilk = null;
        foreach ($sayfalar as $ofset) {
            if (!isset($ofsetIndeks[$ofset])) { continue; }
            $satirlar = $this->sayfaOku($kayit, $ofsetIndeks[$ofset], $sst);
            $ilk ??= $satirlar;
            foreach ($satirlar as $satir) {
                $dolu = false; foreach ($satir as $h) { if (trim((string)$h) !== '') { $dolu = true; break; } }
                if ($dolu) { if (tablo_baslik_uyar($satir, $basliklar)) { return $satirlar; } break; }
            }
        }
        return $ilk ?? [];
    }

    /** SST: parçalar arası CONTINUE geçişlerinde karakter genişliği bayrağı yeniden okunur. */
    private function sstOku(array $parca): array
    {
        $sg = 0; $ps = 4 + 4;   // ilk iki alan: toplam, benzersiz sayısı
        $benzersiz = $this->u32($parca[0], 4);
        $out = [];
        $oku = function (int $adet) use (&$parca, &$sg, &$ps): string {
            $b = '';
            while ($adet > 0) {
                $kalan = strlen($parca[$sg]) - $ps;
                if ($kalan <= 0) { if (++$sg >= count($parca)) { break; } $ps = 0; continue; }
                $al = min($adet, $kalan);
                $b .= substr($parca[$sg], $ps, $al); $ps += $al; $adet -= $al;
            }
            return $b;
        };
        $atla = function (int $adet) use ($oku) { $oku($adet); };

        for ($i = 0; $i < $benzersiz && $i < 500000; $i++) {
            if ($sg >= count($parca) || ($sg === count($parca) - 1 && $ps >= strlen($parca[$sg]))) { break; }
            // ofset sınırında başlık alanı bölünmez (belirtim gereği)
            if (strlen($parca[$sg]) - $ps <= 0) { $sg++; $ps = 0; }
            $cch = $this->u16($oku(2), 0);
            $bayrak = ord($oku(1) ?: "\0");
            $genis = ($bayrak & 1) === 1; $ext = ($bayrak & 4) === 4; $zengin = ($bayrak & 8) === 8;
            $run = $zengin ? $this->u16($oku(2), 0) : 0;
            $extBoyu = $ext ? $this->u32($oku(4), 0) : 0;

            $metin = ''; $kalanKar = $cch;
            while ($kalanKar > 0) {
                $kalanBayt = strlen($parca[$sg]) - $ps;
                if ($kalanBayt <= 0) {
                    // CONTINUE: yeni parçanın ilk baytı genişlik bayrağı
                    if (++$sg >= count($parca)) { break; }
                    $ps = 1; $genis = (ord($parca[$sg][0]) & 1) === 1;
                    continue;
                }
                $kar = $genis ? min($kalanKar, intdiv($kalanBayt, 2)) : min($kalanKar, $kalanBayt);
                if ($kar <= 0) { break; }
                $ham = substr($parca[$sg], $ps, $genis ? $kar * 2 : $kar);
                $ps += strlen($ham);
                $metin .= $genis ? mb_convert_encoding($ham, 'UTF-8', 'UTF-16LE') : mb_convert_encoding($ham, 'UTF-8', 'ISO-8859-1');
                $kalanKar -= $kar;
            }
            if ($run) { $atla($run * 4); }
            if ($extBoyu) { $atla($extBoyu); }
            $out[] = $metin;
        }
        return $out;
    }

    private function rk(int $rk): float
    {
        if ($rk & 2) {
            $v = $rk >> 2;
            if ($v & 0x20000000) { $v -= 0x40000000; }    // 30 bit işaretli tamsayı
            $f = (float)$v;
        } else {
            $f = unpack('e', pack('VV', 0, $rk & 0xFFFFFFFC))[1];
        }
        return ($rk & 1) ? $f / 100 : $f;
    }

    private function sayfaOku(array $kayit, int $baslangic, array $sst): array
    {
        $hucre = [];
        $derin = 0; $satirSayisi = 0;
        for ($i = $baslangic, $c = count($kayit); $i < $c; $i++) {
            [$tip, $v] = $kayit[$i];
            if ($tip === 0x0809) { $derin++; continue; }
            if ($tip === 0x000A) { if (--$derin <= 0) { break; } continue; }
            if ($derin !== 1) { continue; }   // iç içe alt bölümleri (grafik vb.) atla
            $r = null; $kol = null; $deger = null;
            switch ($tip) {
                case 0x00FD:   // LABELSST
                    $r = $this->u16($v, 0); $kol = $this->u16($v, 2); $deger = $sst[$this->u32($v, 6)] ?? ''; break;
                case 0x0203:   // NUMBER
                    $r = $this->u16($v, 0); $kol = $this->u16($v, 2); $deger = tablo_sayi_metni(unpack('e', substr($v, 6, 8))[1]); break;
                case 0x027E:   // RK
                    $r = $this->u16($v, 0); $kol = $this->u16($v, 2); $deger = tablo_sayi_metni($this->rk($this->u32($v, 6))); break;
                case 0x00BD:   // MULRK
                    $r = $this->u16($v, 0); $ilkKol = $this->u16($v, 2);
                    $adet = intdiv(strlen($v) - 6, 6);
                    for ($j = 0; $j < $adet; $j++) {
                        $kk = $ilkKol + $j;
                        if ($kk < TABLO_MAX_SUTUN && $r < TABLO_MAX_SATIR + 1) { $hucre[$r][$kk] = tablo_sayi_metni($this->rk($this->u32($v, 4 + $j * 6 + 2))); }
                    }
                    break;
                case 0x0204:   // LABEL (eski tip metin)
                    $r = $this->u16($v, 0); $kol = $this->u16($v, 2); $cch = $this->u16($v, 6);
                    $bayrak = ord($v[8] ?? "\0"); $genis = ($bayrak & 1) === 1;
                    $ham = substr($v, 9, $genis ? $cch * 2 : $cch);
                    $deger = $genis ? mb_convert_encoding($ham, 'UTF-8', 'UTF-16LE') : mb_convert_encoding($ham, 'UTF-8', 'ISO-8859-1'); break;
                case 0x0006:   // FORMULA: yalnızca sayısal önbellek değeri; metin sonuçları (STRING kaydı) okunur
                    $r = $this->u16($v, 0); $kol = $this->u16($v, 2);
                    if (substr($v, 12, 2) !== "\xFF\xFF") { $deger = tablo_sayi_metni(unpack('e', substr($v, 6, 8))[1]); }
                    elseif (isset($kayit[$i + 1]) && $kayit[$i + 1][0] === 0x0207) {
                        $sv = $kayit[$i + 1][1]; $cch = $this->u16($sv, 0); $bayrak = ord($sv[2] ?? "\0"); $genis = ($bayrak & 1) === 1;
                        $ham = substr($sv, 3, $genis ? $cch * 2 : $cch);
                        $deger = $genis ? mb_convert_encoding($ham, 'UTF-8', 'UTF-16LE') : mb_convert_encoding($ham, 'UTF-8', 'ISO-8859-1');
                    }
                    break;
            }
            if ($deger !== null && $kol < TABLO_MAX_SUTUN && $r < TABLO_MAX_SATIR + 1) { $hucre[$r][$kol] = $deger; }
        }
        if (!$hucre) { return []; }
        ksort($hucre);
        $son = max(array_keys($hucre));
        $satirlar = [];
        for ($r = 0; $r <= $son; $r++) {
            if (!isset($hucre[$r])) { $satirlar[] = []; continue; }
            $max = max(array_keys($hucre[$r]));
            $tam = array_fill(0, $max + 1, '');
            foreach ($hucre[$r] as $k => $x) { $tam[$k] = $x; }
            $satirlar[] = $tam;
        }
        return $satirlar;
    }
}
