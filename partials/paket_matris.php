<?php
/**
 * Paket karşılaştırma tablosu.
 * Gerekli: $packages (prolynweb_paketler çıktısı). Modül satırları modul_icerik.php'den gelir;
 * "pakette var mı" bilgisi paket_ozellikleri.allowed_modules'tan hesaplanır.
 */
$__moduller = prolynweb_modul_icerik();
$__destekAd = ['email' => 'E-posta desteği', 'priority' => 'Öncelikli destek', 'dedicated' => 'Özel destek'];
$__tik = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
?>
<div class="pk-tablo-sar" role="region" aria-label="Paketlere göre modül ve limit karşılaştırması" tabindex="0">
    <table class="pk-tablo">
        <thead>
            <tr>
                <th scope="col">Pakette neler var?</th>
                <?php foreach ($packages as $pk): ?>
                <th scope="col"><?= htmlspecialchars($pk['name']) ?><small>₺<?= prolynweb_tl($pk['yearly_total']) ?> / yıl</small></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr class="grup"><th colspan="<?= count($packages) + 1 ?>" scope="colgroup">Kullanım limitleri</th></tr>
            <tr>
                <th scope="row">Kullanıcı sayısı</th>
                <?php foreach ($packages as $pk): ?>
                <td><span class="pk-hucre deger"><?= $pk['max_users'] > 0 ? (int)$pk['max_users'] : 'Sınırsız' ?></span></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <th scope="row">Şube sayısı</th>
                <?php foreach ($packages as $pk): ?>
                <td><span class="pk-hucre deger"><?= $pk['max_branches'] > 0 ? (int)$pk['max_branches'] : 'Sınırsız' ?></span></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <th scope="row">Destek</th>
                <?php foreach ($packages as $pk): ?>
                <td><span class="pk-hucre deger"><?= htmlspecialchars($__destekAd[$pk['support'] ?? 'email'] ?? 'Teknik destek') ?><?= !empty($pk['support_hours']) ? ' · ' . (int)$pk['support_hours'] . ' sa.' : '' ?></span></td>
                <?php endforeach; ?>
            </tr>
            <tr class="grup"><th colspan="<?= count($packages) + 1 ?>" scope="colgroup">Modüller</th></tr>
            <?php foreach ($__moduller as $m):
                $kisa = implode(' · ', array_map(fn($x) => $x[0], array_slice($m['maddeler'], 0, 2)));
            ?>
            <tr>
                <th scope="row"><a href="moduller.php#<?= $m['slug'] ?>" style="color:inherit;text-decoration:none"><?= htmlspecialchars($m['ad']) ?></a><small><?= htmlspecialchars($m['durum'] === 'kismen' ? $m['durum_not'] : $kisa) ?></small></th>
                <?php foreach ($packages as $pk): $d = prolynweb_pakette_var($pk, $m['anahtarlar']); ?>
                <td>
                    <?php if ($d === 'tam'): ?><span class="pk-hucre tam"><?= $__tik ?><span class="visually-hidden">Dahil</span></span>
                    <?php elseif ($d === 'kismen'): ?><span class="pk-hucre kismen"><?= $__tik ?>Kısmen</span>
                    <?php else: ?><span class="pk-hucre yok" aria-label="Dahil değil">&ndash;</span><?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
