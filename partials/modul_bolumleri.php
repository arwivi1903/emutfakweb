<?php
/**
 * Ana sayfa modül bölümleri: her modül için illüstrasyon + anlatım + "hangi pakette?"
 * Gerekli: $packages. Veri: modul_icerik.php. Çizimler: partials/ill/<slug>.php
 */
$__moduller = prolynweb_modul_icerik();
$__tik = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
$__cizgi = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 12h12"/></svg>';
?>
<div class="mod-nav-sar" aria-label="Modüller">
    <div class="container-xxl">
        <nav class="mod-nav" aria-label="Modüllere git">
            <?php foreach ($__moduller as $m): ?>
            <a href="#<?= $m['slug'] ?>" data-mod="<?= $m['slug'] ?>" style="--m:var(--m-<?= $m['renk'] ?>)"><i></i><?= htmlspecialchars($m['ad']) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</div>

<?php foreach ($__moduller as $m): ?>
<section class="mod" id="<?= $m['slug'] ?>" style="--m:var(--m-<?= $m['renk'] ?>)" aria-labelledby="b-<?= $m['slug'] ?>">
    <div class="container-xxl">
        <div class="mod-kutu">
            <figure class="ill mod-gorsel" aria-hidden="false">
                <?php require __DIR__ . '/ill/' . $m['slug'] . '.php'; ?>
            </figure>
            <div class="mod-metin">
                <div class="mod-etiket"><i></i><?= htmlspecialchars($m['ad']) ?><?php if ($m['durum'] === 'kismen'): ?> <span class="mod-rozet">Bir kısmı yakında</span><?php endif; ?></div>
                <h2 class="mod-baslik" id="b-<?= $m['slug'] ?>"><?= htmlspecialchars($m['baslik']) ?></h2>
                <p class="mod-ozet"><?= htmlspecialchars($m['ozet']) ?></p>
                <ul class="mod-maddeler">
                    <?php foreach ($m['maddeler'] as $md): ?>
                    <li><b><?= htmlspecialchars($md[0]) ?></b><span><?= htmlspecialchars($md[1]) ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <p class="mod-ornek"><b>Örneğin:</b> <?= htmlspecialchars($m['ornek']) ?></p>
                <div class="mod-paket">
                    <span class="mod-paket-etiket">Hangi pakette?</span>
                    <?php foreach ($packages as $pk): $d = prolynweb_pakette_var($pk, $m['anahtarlar']); ?>
                    <span class="pk-d <?= $d ?>"><?= $d === 'yok' ? $__cizgi : $__tik ?><?= htmlspecialchars($pk['name']) ?><?= $d === 'kismen' ? ' (kısmen)' : ($d === 'yok' ? ' (yok)' : '') ?></span>
                    <?php endforeach; ?>
                    <a class="mod-link" href="moduller.php#<?= $m['slug'] ?>">Tüm özellikler</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endforeach; ?>
