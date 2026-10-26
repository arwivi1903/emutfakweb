<?php
/**
 * Blog içeriği için güvenli, hafif biçimlendirme.
 * Desteklenen: "## Başlık", "### Alt başlık", "- madde", "1. madde", "> not kutusu",
 * **kalın**, *italik*, [metin](https://adres) ve paragraf. Tüm metin önce kaçırılır;
 * HTML yazılamaz. Eski düz metin yazılar paragraf olarak görünür.
 */

function blog_slugify(string $s): string
{
    $tr = ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'İ' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u', 'Ç' => 'c', 'Ğ' => 'g', 'Ö' => 'o', 'Ş' => 's', 'Ü' => 'u'];
    $s = strtolower(strtr($s, $tr));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-') ?: 'bolum';
}

function blog_inline(string $t): string
{
    $t = htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
    $t = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $t);
    $t = preg_replace('/(?<![\*\w])\*(?!\s)(.+?)(?<!\s)\*(?![\*\w])/u', '<em>$1</em>', $t);
    $t = preg_replace_callback('/\[([^\]]+)\]\(((?:https?:\/\/|\/)[^\s)]+|[a-z0-9\-]+(?:\.php)?(?:\?[^\s)]*)?)\)/iu', static function ($m) {
        $url = html_entity_decode($m[2], ENT_QUOTES, 'UTF-8');
        $ext = preg_match('#^https?://#i', $url) && !preg_match('#^https?://(www\.)?prolyn\.net#i', $url);
        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . ($ext ? ' target="_blank" rel="noopener nofollow"' : '') . '>' . $m[1] . '</a>';
    }, $t);
    return $t;
}

/** @return array{html:string,toc:array<int,array{id:string,text:string}>,kelime:int} */
function blog_render(string $metin): array
{
    $satirlar = preg_split('/\r\n|\r|\n/', trim($metin));
    $html = ''; $toc = []; $para = []; $liste = null; $kullanilan = [];
    $kapat = static function () use (&$html, &$para, &$liste) {
        if ($para) { $html .= '<p>' . implode('<br>', array_map('blog_inline', $para)) . '</p>'; $para = []; }
        if ($liste) { $html .= '<' . $liste['tur'] . '>' . implode('', array_map(static fn($i) => '<li>' . blog_inline($i) . '</li>', $liste['ogeler'])) . '</' . $liste['tur'] . '>'; $liste = null; }
    };
    foreach ($satirlar as $s) {
        $t = rtrim($s);
        if (trim($t) === '') { $kapat(); continue; }
        if (preg_match('/^(#{2,3})\s+(.+)$/u', $t, $m)) {
            $kapat();
            $metinB = trim($m[2]);
            $id = blog_slugify($metinB); $taban = $id; $n = 2;
            while (isset($kullanilan[$id])) { $id = $taban . '-' . $n++; }
            $kullanilan[$id] = true;
            $tag = strlen($m[1]) === 2 ? 'h2' : 'h3';
            if ($tag === 'h2') { $toc[] = ['id' => $id, 'text' => $metinB]; }
            $html .= '<' . $tag . ' id="' . $id . '">' . blog_inline($metinB) . '</' . $tag . '>';
            continue;
        }
        if (preg_match('/^\s*[-•]\s+(.+)$/u', $t, $m)) {
            if ($para) { $kapat(); }
            if (!$liste || $liste['tur'] !== 'ul') { $kapat(); $liste = ['tur' => 'ul', 'ogeler' => []]; }
            $liste['ogeler'][] = $m[1];
            continue;
        }
        if (preg_match('/^\s*\d+[.)]\s+(.+)$/u', $t, $m)) {
            if ($para) { $kapat(); }
            if (!$liste || $liste['tur'] !== 'ol') { $kapat(); $liste = ['tur' => 'ol', 'ogeler' => []]; }
            $liste['ogeler'][] = $m[1];
            continue;
        }
        if (preg_match('/^>\s?(.*)$/u', $t, $m)) {
            $kapat();
            $html .= '<blockquote>' . blog_inline($m[1]) . '</blockquote>';
            continue;
        }
        if ($liste) { $kapat(); }
        $para[] = $t;
    }
    $kapat();
    $kelime = count(preg_split('/\s+/u', trim(strip_tags($metin))) ?: []);
    return ['html' => $html, 'toc' => $toc, 'kelime' => $kelime];
}

function blog_okuma_suresi(string $metin): int
{
    $k = count(preg_split('/\s+/u', trim(strip_tags($metin))) ?: []);
    return max(1, (int)round($k / 200));
}
