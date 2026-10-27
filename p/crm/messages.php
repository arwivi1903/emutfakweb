<?php
/**
 * Prolyn Admin Panel - Gelen Mesajlar (prolyn.net iletişim formu)
 * Liste, okuma, e-posta ile yanıt (mail kuyruğu), durum ve potansiyel müşteriye aktarma.
 */
require_once '../config/header.php';
Auth::requireModule('manage_tenants');

// Tablo (iletişim formu ilk mesajda oluşturur; panelden önce açılırsa burada da kurulur)
$db->TableOperations("CREATE TABLE IF NOT EXISTS iletisim_mesajlari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(191) NOT NULL, email VARCHAR(191) NOT NULL, konu VARCHAR(255) NOT NULL, mesaj TEXT NOT NULL,
    ip VARCHAR(45) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'new', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status), INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$kolonlar = array_column((array)$db->getRowsAssoc("SHOW COLUMNS FROM iletisim_mesajlari"), 'Field');
foreach ([
    'okundu_tarihi' => "ADD COLUMN okundu_tarihi DATETIME DEFAULT NULL",
    'yanit' => "ADD COLUMN yanit TEXT DEFAULT NULL",
    'yanit_tarihi' => "ADD COLUMN yanit_tarihi DATETIME DEFAULT NULL",
    'yanitlayan' => "ADD COLUMN yanitlayan INT DEFAULT NULL",
    'lead_id' => "ADD COLUMN lead_id INT DEFAULT NULL",
] as $k => $sql) {
    if (!in_array($k, $kolonlar, true)) { $db->TableOperations("ALTER TABLE iletisim_mesajlari {$sql}"); }
}

const MSG_DURUM = ['new' => ['Yeni', 'primary'], 'read' => ['Okundu', 'secondary'], 'replied' => ['Yanıtlandı', 'success'], 'archived' => ['Arşiv', 'dark'], 'spam' => ['Spam', 'danger']];
$flash = null;
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $mid = (int)($_POST['id'] ?? 0);
    $m = $mid ? $db->getRowAssoc("SELECT * FROM iletisim_mesajlari WHERE id = ?", [$mid]) : null;
    if ($m && $action === 'status' && isset(MSG_DURUM[$_POST['status'] ?? ''])) {
        $db->Update("UPDATE iletisim_mesajlari SET status = ? WHERE id = ?", [$_POST['status'], $mid]);
        $flash = ['success', 'Durum güncellendi: ' . MSG_DURUM[$_POST['status']][0] . '.'];
    }
    if ($m && $action === 'reply') {
        $yanit = trim(str_replace("\r\n", "\n", (string)($_POST['yanit'] ?? '')));
        if ($yanit === '' || mb_strlen($yanit) > 10000) {
            $flash = ['warning', 'Yanıt metni boş ya da çok uzun.'];
        } elseif (!filter_var($m['email'], FILTER_VALIDATE_EMAIL)) {
            $flash = ['danger', 'Gönderenin e-posta adresi geçersiz.'];
        } else {
            $gonderen = trim((string)($_SESSION['full_name'] ?? '')) ?: 'Prolyn';
            $govde = $yanit . "\n\n" . $gonderen . "\nProlyn | " . rtrim((string)prolynweb_env('BASE_URL', 'https://prolyn.net'), '/')
                . "\n\n----- " . date('d.m.Y H:i', strtotime($m['created_at'])) . " tarihli mesajınız -----\n" . $m['mesaj'];
            $ok = $db->Insert("INSERT INTO mail_kuyrugu (channel, recipient, subject, message, source, status, attempts, created_at) VALUES ('email', ?, ?, ?, 'iletisim_yanit', 'pending', 0, NOW())",
                [$m['email'], 'Re: ' . mb_substr($m['konu'], 0, 240), $govde]);
            if ($ok !== false) {
                $db->Update("UPDATE iletisim_mesajlari SET status = 'replied', yanit = ?, yanit_tarihi = NOW(), yanitlayan = ? WHERE id = ?", [$yanit, $_SESSION['admin_id'] ?? null, $mid]);
                $flash = ['success', 'Yanıt e-posta kuyruğuna eklendi; bir dakika içinde ' . $m['email'] . ' adresine gönderilir.'];
            } else {
                $flash = ['danger', 'Yanıt kuyruğa eklenemedi.'];
            }
        }
    }
    if ($m && $action === 'to_lead' && empty($m['lead_id'])) {
        $leadFile = __DIR__ . '/../functions/lead.php';
        if (is_readable($leadFile)) {
            require_once $leadFile;
            lead_sema_kur($db);
            $var = (int)$db->getColumn("SELECT lead_id FROM potansiyel_musteriler WHERE eposta = ? LIMIT 1", [strtolower($m['email'])]);
            if (!$var) {
                $token = lead_yeni_token();
                $db->Insert("INSERT INTO potansiyel_musteriler (firma_adi, yetkili, eposta, oncelik, puan, durum, kaynak, notlar, ret_token, son_iletisim) VALUES (?, ?, ?, 'A', 80, 'ilgileniyor', 'iletisim_formu', ?, ?, NOW())",
                    [$m['ad'], $m['ad'], strtolower($m['email']), 'İletişim formu: ' . $m['konu'] . "\n" . mb_substr($m['mesaj'], 0, 1500), $token]);
                $var = (int)$db->getColumn("SELECT lead_id FROM potansiyel_musteriler WHERE ret_token = ?", [$token]);
                if ($var) { lead_aktivite($db, $var, 'sistem', 'İletişim formu mesajından oluşturuldu (#' . $mid . ').'); }
            }
            if ($var) {
                $db->Update("UPDATE iletisim_mesajlari SET lead_id = ? WHERE id = ?", [$var, $mid]);
                $flash = ['success', 'Potansiyel müşteri listesine eklendi. Firma adını detayda düzeltebilirsiniz.'];
            }
        }
    }
    $id = $mid;
}

$sec = $id ? $db->getRowAssoc("SELECT * FROM iletisim_mesajlari WHERE id = ?", [$id]) : null;
if ($sec && $sec['status'] === 'new') {
    $db->Update("UPDATE iletisim_mesajlari SET status = 'read', okundu_tarihi = NOW() WHERE id = ?", [$id]);
    $sec['status'] = 'read';
}

$filtre = isset(MSG_DURUM[$_GET['durum'] ?? '']) ? $_GET['durum'] : '';
$q = trim((string)($_GET['q'] ?? ''));
$w = []; $p = [];
if ($filtre !== '') { $w[] = 'status = ?'; $p[] = $filtre; } else { $w[] = "status NOT IN ('archived','spam')"; }
if ($q !== '') { $w[] = '(ad LIKE ? OR email LIKE ? OR konu LIKE ? OR mesaj LIKE ?)'; $l = '%' . $q . '%'; array_push($p, $l, $l, $l, $l); }
$liste = $db->getRowsAssoc("SELECT id, ad, email, konu, LEFT(mesaj, 140) ozet, status, created_at FROM iletisim_mesajlari WHERE " . implode(' AND ', $w) . " ORDER BY id DESC LIMIT 200", $p);
$sayac = [];
foreach ((array)$db->getRowsAssoc("SELECT status, COUNT(*) n FROM iletisim_mesajlari GROUP BY status") as $r) { $sayac[$r['status']] = (int)$r['n']; }
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$qs = static fn(array $x) => http_build_query(array_filter(array_merge(['durum' => $filtre, 'q' => $q], $x), 'strlen'));

require_once '../config/sidebar.php';
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
  <div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
      <div class="app-container container-fluid d-flex flex-stack flex-wrap gap-3">
        <div>
          <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Gelen Mesajlar</h1>
          <span class="text-muted fs-7">prolyn.net iletişim formundan gelenler</span>
        </div>
        <form method="get" action="crm/messages.php" class="d-flex gap-2">
          <?php if ($filtre): ?><input type="hidden" name="durum" value="<?= $e($filtre) ?>"><?php endif; ?>
          <input type="text" name="q" value="<?= $e($q) ?>" class="form-control form-control-sm form-control-solid w-250px" placeholder="Ad, e-posta, konu, mesaj">
          <button class="btn btn-sm btn-light-primary">Ara</button>
        </form>
      </div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
      <div class="app-container container-fluid">
        <?php if ($flash): ?><div class="alert alert-<?= $e($flash[0]) ?> p-5 mb-6 fw-semibold"><?= $e($flash[1]) ?></div><?php endif; ?>

        <ul class="nav nav-pills mb-6 gap-2">
          <li class="nav-item"><a class="nav-link btn btn-sm <?= $filtre === '' ? 'active btn-primary' : 'btn-light' ?>" href="crm/messages.php?<?= $e($qs(['durum' => ''])) ?>">Gelen kutusu <span class="badge badge-light ms-1"><?= ($sayac['new'] ?? 0) + ($sayac['read'] ?? 0) + ($sayac['replied'] ?? 0) ?></span></a></li>
          <?php foreach (MSG_DURUM as $k => $d): ?>
          <li class="nav-item"><a class="nav-link btn btn-sm <?= $filtre === $k ? 'active btn-primary' : 'btn-light' ?>" href="crm/messages.php?<?= $e($qs(['durum' => $k])) ?>"><?= $e($d[0]) ?> <span class="badge badge-light-<?= $d[1] ?> ms-1"><?= (int)($sayac[$k] ?? 0) ?></span></a></li>
          <?php endforeach; ?>
        </ul>

        <div class="row g-6">
          <div class="col-xl-5">
            <div class="card">
              <div class="card-body p-0">
                <?php if (!$liste): ?><div class="text-center text-muted py-15">Mesaj yok.</div><?php endif; ?>
                <div class="d-flex flex-column">
                <?php foreach ((array)$liste as $r): $aktif = $sec && (int)$sec['id'] === (int)$r['id']; ?>
                  <a href="crm/messages.php?<?= $e($qs(['id' => $r['id']])) ?>" class="d-block px-6 py-5 border-bottom border-gray-200 text-decoration-none <?= $aktif ? 'bg-light-primary' : '' ?>">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <span class="<?= $r['status'] === 'new' ? 'fw-bolder text-gray-900' : 'fw-semibold text-gray-700' ?> fs-6"><?= $e($r['ad']) ?></span>
                      <span class="text-muted fs-8"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></span>
                    </div>
                    <div class="<?= $r['status'] === 'new' ? 'fw-bold text-gray-900' : 'text-gray-700' ?> fs-7 mb-1"><?= $e($r['konu']) ?></div>
                    <div class="text-muted fs-8 text-truncate"><?= $e($r['ozet']) ?></div>
                    <span class="badge badge-light-<?= MSG_DURUM[$r['status']][1] ?? 'secondary' ?> fs-9 mt-2"><?= $e(MSG_DURUM[$r['status']][0] ?? $r['status']) ?></span>
                  </a>
                <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-7">
            <?php if (!$sec): ?>
              <div class="card"><div class="card-body text-center text-muted py-20">Okumak için soldan bir mesaj seçin.</div></div>
            <?php else: ?>
            <div class="card mb-6">
              <div class="card-header align-items-center">
                <div class="card-title flex-column align-items-start py-4">
                  <h3 class="fw-bold mb-1"><?= $e($sec['konu']) ?></h3>
                  <span class="text-muted fs-7"><?= $e($sec['ad']) ?> · <a href="mailto:<?= $e($sec['email']) ?>"><?= $e($sec['email']) ?></a> · <?= date('d.m.Y H:i', strtotime($sec['created_at'])) ?></span>
                </div>
                <span class="badge badge-light-<?= MSG_DURUM[$sec['status']][1] ?? 'secondary' ?>"><?= $e(MSG_DURUM[$sec['status']][0] ?? $sec['status']) ?></span>
              </div>
              <div class="card-body">
                <div class="fs-6 text-gray-800 mb-6" style="white-space:pre-line"><?= $e($sec['mesaj']) ?></div>
                <div class="text-muted fs-8">IP: <?= $e($sec['ip'] ?: '-') ?></div>
                <?php if (!empty($sec['yanit'])): ?>
                  <div class="separator my-6"></div>
                  <div class="fw-bold mb-2">Gönderilen yanıt <span class="text-muted fw-normal fs-8"><?= $sec['yanit_tarihi'] ? date('d.m.Y H:i', strtotime($sec['yanit_tarihi'])) : '' ?></span></div>
                  <div class="bg-light rounded p-4 fs-7" style="white-space:pre-line"><?= $e($sec['yanit']) ?></div>
                <?php endif; ?>
              </div>
              <div class="card-footer d-flex flex-wrap gap-2 py-4">
                <?php foreach (['read' => 'Okundu', 'archived' => 'Arşivle', 'spam' => 'Spam'] as $k => $t): if ($sec['status'] === $k) continue; ?>
                <form method="post" action="crm/messages.php?<?= $e($qs(['id' => $sec['id']])) ?>"><?= csrfTokenField() ?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= (int)$sec['id'] ?>"><input type="hidden" name="status" value="<?= $k ?>"><button class="btn btn-sm btn-light<?= $k === 'spam' ? '-danger' : '' ?>"><?= $t ?></button></form>
                <?php endforeach; ?>
                <?php if (empty($sec['lead_id'])): ?>
                <form method="post" action="crm/messages.php?<?= $e($qs(['id' => $sec['id']])) ?>"><?= csrfTokenField() ?><input type="hidden" name="action" value="to_lead"><input type="hidden" name="id" value="<?= (int)$sec['id'] ?>"><button class="btn btn-sm btn-light-success">Potansiyel müşterilere ekle</button></form>
                <?php else: ?>
                <a class="btn btn-sm btn-light-success" href="crm/lead_detail.php?id=<?= (int)$sec['lead_id'] ?>">Potansiyel müşteri kaydı</a>
                <?php endif; ?>
              </div>
            </div>

            <form method="post" action="crm/messages.php?<?= $e($qs(['id' => $sec['id']])) ?>" class="card">
              <?= csrfTokenField() ?><input type="hidden" name="action" value="reply"><input type="hidden" name="id" value="<?= (int)$sec['id'] ?>">
              <div class="card-header"><h3 class="card-title fw-bold">E-postayla yanıtla</h3></div>
              <div class="card-body">
                <div class="text-muted fs-7 mb-3">Alıcı: <?= $e($sec['email']) ?> · Konu: Re: <?= $e($sec['konu']) ?></div>
                <textarea name="yanit" rows="7" class="form-control form-control-solid" required placeholder="Merhaba <?= $e($sec['ad']) ?>,&#10;&#10;"></textarea>
                <div class="form-text">İmzanız ve kişinin orijinal mesajı otomatik eklenir. Gönderim mail kuyruğu üzerinden yapılır.</div>
              </div>
              <div class="card-footer text-end py-4"><button class="btn btn-primary">Yanıtı gönder</button></div>
            </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require_once '../config/footer.php'; ?>
</div>
