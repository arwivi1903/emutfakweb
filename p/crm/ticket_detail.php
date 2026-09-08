<?php
/**
 * Prolyn Admin Panel - Support Ticket Detail (Sohbet Ekranı)
 * Prolyn/p/crm/ticket_detail.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_support');
require_once '../config/sidebar.php';

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($ticket_id == 0) {
    echo "<script>window.location.href='crm/tickets.php';</script>";
    exit;
}

// POST İşlemleri (Yanıt Yazma veya Durum Güncelleme)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* Formlar csrfTokenField() ile token basıyordu ama hiçbir yerde
       doğrulanmıyordu — token üretmek tek başına koruma sağlamaz. */
    if (function_exists('checkCSRF')) {
        checkCSRF();
    }

    if (isset($_POST['action']) && $_POST['action'] === 'add_reply') {
        $message     = (string)($_POST['message'] ?? '');
        $is_internal = isset($_POST['is_internal']) ? 1 : 0;

        // Yanıtın sahibi oturumdaki yöneticidir (eskiden sabit 1 yazılıyordu;
        // tüm yanıtlar aynı kişiye ait görünüyordu).
        $admin_id = (int)($_SESSION['admin_id'] ?? 0);
        $ekVar    = !empty($_FILES['ek']['name'][0]);

        if (trim($message) !== '' || $ekVar) {
            // Insert() bu sınıfta doğrudan lastInsertId döner
            $reply_id = (int)$db->Insert(
                "INSERT INTO destek_cevaplari (ticket_id, replied_by_admin, message, is_internal, created_at)
                 VALUES (?, ?, ?, ?, NOW())",
                [$ticket_id, $admin_id ?: null, trim($message) !== '' ? $message : '(dosya eklendi)', $is_internal]
            );

            if ($ekVar) {
                destek_admin_ek_yukle($db, $_FILES['ek'], $ticket_id, $reply_id, (string)($_SESSION['full_name'] ?? 'Yönetici'), $admin_id);
            }

            // İç not müşteriye gitmediği için talebin "son yanıt" bilgisini değiştirmez
            if (!$is_internal) {
                $db->Update(
                    "UPDATE destek_talepleri
                     SET status = CASE WHEN status IN ('new','open') THEN 'in_progress' ELSE status END,
                         last_reply_at = NOW(), last_reply_by = 'admin', updated_at = NOW()
                     WHERE ticket_id = ?",
                    [$ticket_id]
                );
            }
        }

        echo "<script>window.location.href='crm/ticket_detail.php?id=$ticket_id&success=reply';</script>";
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $new_status = $_POST['status'];
        
        $db->Update("UPDATE destek_talepleri SET status = ?, updated_at = NOW() WHERE ticket_id = ?", [$new_status, $ticket_id]);
        if ($new_status === 'resolved' || $new_status === 'closed') {
            $db->Update("UPDATE destek_talepleri SET resolved_at = NOW(), closed_at = NOW() WHERE ticket_id = ?", [$ticket_id]);
        }
        
        echo "<script>window.location.href='crm/ticket_detail.php?id=$ticket_id&success=status';</script>";
        exit;
    }
}

// Talebi ve Müşteriyi Çek
$query = "
    SELECT t.*, k.company_name 
    FROM destek_talepleri t
    LEFT JOIN kurumlar k ON t.customer_id = k.customer_id
    WHERE t.ticket_id = ?
";
$ticket = $db->getRowAssoc($query, [$ticket_id]);

if (!$ticket) {
    echo "<script>window.location.href='crm/tickets.php';</script>";
    exit;
}

// Yanıtları Çek
/* Kiracı (ERP) kullanıcısı MERKEZ'de kayıtlı DEĞİLDİR; kurum_kullanicilari
   yalnızca panel üzerinden açılmış müşteri hesaplarını tutar. Kiracıdan gelen
   yanıtın sahibi bu yüzden replied_by_tenant_name kolonundan okunur. */
$repliesQuery = "
    SELECT c.*,
           y.full_name as admin_name,
           kk.full_name as cust_name
    FROM destek_cevaplari c
    LEFT JOIN yoneticiler y ON c.replied_by_admin = y.admin_id
    LEFT JOIN kurum_kullanicilari kk ON c.replied_by_customer = kk.user_id
    WHERE c.ticket_id = ?
    ORDER BY c.created_at ASC, c.reply_id ASC
";
$replies = $db->getRowsAssoc($repliesQuery, [$ticket_id]);

/* Ekler: talep gövdesine ait olanlar reply_id NULL, yanıtlara ait olanlar
   ilgili reply_id ile gelir. Tek sorguda çekilip gruplanıyor. */
$ekler = ['talep' => []];
try {
    foreach ((array)$db->getRowsAssoc("SELECT * FROM destek_ekleri WHERE ticket_id = ? ORDER BY ek_id ASC", [$ticket_id]) as $__ek) {
        $__k = empty($__ek['reply_id']) ? 'talep' : ('c' . (int)$__ek['reply_id']);
        $ekler[$__k][] = $__ek;
    }
} catch (Throwable $e) {
    // destek_ekleri henüz kurulmamışsa ekran eksiksiz çalışmaya devam eder
}

/** Ek listesini küçük önizleme kartları olarak basar. */
function destek_ek_bas(array $liste): void
{
    if (empty($liste)) { return; }
    echo '<div class="d-flex flex-wrap gap-3 mt-4">';
    foreach ($liste as $ek) {
        $url  = 'crm/ticket_file.php?ek=' . (int)$ek['ek_id'];
        $ad   = htmlspecialchars((string)$ek['dosya_ad']);
        $mime = (string)($ek['mime_tipi'] ?? '');
        $kb   = number_format(((int)$ek['boyut']) / 1024, 0, ',', '.');

        if (strpos($mime, 'image/') === 0) {
            echo '<a href="' . $url . '" target="_blank" class="d-block border border-gray-300 rounded overflow-hidden" style="width:140px" title="' . $ad . '">'
               . '<img src="' . $url . '" alt="' . $ad . '" style="width:100%;height:96px;object-fit:cover;display:block">'
               . '<span class="d-block text-truncate text-gray-700 fs-8 px-2 py-1">' . $ad . '</span></a>';
        } else {
            echo '<a href="' . $url . '" target="_blank" class="d-flex align-items-center gap-2 border border-gray-300 rounded px-3 py-2" title="' . $ad . '">'
               . '<i class="ki-duotone ki-file fs-2 text-danger"><span class="path1"></span><span class="path2"></span></i>'
               . '<span class="text-gray-800 fw-semibold fs-8">' . $ad . '</span>'
               . '<span class="text-muted fs-8">' . $kb . ' KB</span></a>';
        }
    }
    echo '</div>';
}

/** destek_ekleri'ne yönetici eki yazar; dosyayı kiracı tarafıyla aynı köke koyar. */
function destek_admin_ek_yukle($db, array $files, int $ticketId, ?int $replyId, string $ad, int $adminId): void
{
    $kok = function_exists('prolynweb_env') && trim((string)prolynweb_env('SUPPORT_UPLOAD_DIR', '')) !== ''
         ? rtrim((string)prolynweb_env('SUPPORT_UPLOAD_DIR', ''), '/')
         : dirname(dirname(__DIR__)) . '/uploads/destek';

    $dir = $kok . '/' . $ticketId;
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) { return; }
    if (!is_file($kok . '/.htaccess')) { @file_put_contents($kok . '/.htaccess', "Require all denied\n"); }

    $izinliUzanti = ['jpg','jpeg','png','gif','webp','pdf'];
    $izinliMime   = ['image/jpeg','image/png','image/gif','image/webp','application/pdf'];

    for ($i = 0; $i < count($files['name']); $i++) {
        if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) { continue; }
        if ((int)$files['size'][$i] > 5242880) { continue; }

        $orj = basename((string)$files['name'][$i]);
        $uz  = strtolower(pathinfo($orj, PATHINFO_EXTENSION));
        if (!in_array($uz, $izinliUzanti, true) || !is_uploaded_file($files['tmp_name'][$i])) { continue; }

        $mime = '';
        if (function_exists('finfo_open') && ($fi = finfo_open(FILEINFO_MIME_TYPE))) {
            $mime = (string)finfo_file($fi, $files['tmp_name'][$i]);
            finfo_close($fi);
        }
        if (!in_array($mime, $izinliMime, true)) { continue; }

        $dosya = bin2hex(random_bytes(8)) . '.' . $uz;
        if (!@move_uploaded_file($files['tmp_name'][$i], $dir . '/' . $dosya)) { continue; }
        @chmod($dir . '/' . $dosya, 0644);

        try {
            $db->Insert(
                "INSERT INTO destek_ekleri (ticket_id, reply_id, dosya_ad, dosya_yol, mime_tipi, boyut, yukleyen_tip, yukleyen_ad, yukleyen_id, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, 'admin', ?, ?, NOW())",
                [$ticketId, $replyId, $orj, $ticketId . '/' . $dosya, $mime, (int)$files['size'][$i], $ad, $adminId]
            );
        } catch (Throwable $e) {
            @unlink($dir . '/' . $dosya);
        }
    }
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Talep Detayı: #<?php echo htmlspecialchars($ticket['ticket_number']); ?>
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted"><a href="crm/tickets.php" class="text-muted text-hover-primary">Destek Talepleri</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Detay</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Başarılı</h4>
                        <span>İşlem başarıyla gerçekleştirildi.</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row g-5 g-xl-8">
                    
                    <!-- Sol Taraf: Sohbet ve İçerik -->
                    <div class="col-xl-8">
                        <div class="card shadow-sm mb-5">
                            <div class="card-header border-0 pt-6">
                                <h3 class="card-title fw-bold text-gray-800 fs-2"><?php echo htmlspecialchars($ticket['subject']); ?></h3>
                            </div>
                            <div class="card-body">
                                <p class="fs-5 text-gray-700" style="white-space: pre-wrap;"><?php echo htmlspecialchars($ticket['description']); ?></p>
                                <?php destek_ek_bas($ekler['talep'] ?? []); ?>
                                <div class="separator mt-5 mb-5"></div>
                                
                                <!-- Thread Başlangıcı -->
                                <div class="d-flex flex-column mb-8">
                                    <?php if(!empty($replies)): ?>
                                        <?php foreach($replies as $r): ?>
                                            <?php 
                                                $isAdmin = !empty($r['replied_by_admin']); 
                                                $alignClass = $isAdmin ? 'align-items-end' : 'align-items-start';
                                                $bgClass = $isAdmin ? 'bg-light-primary text-gray-800' : 'bg-light-success text-gray-800';
                                                
                                                if ($r['is_internal']) {
                                                    $bgClass = 'bg-light-warning text-gray-800 border border-warning border-dashed';
                                                }
                                                
                                                /* Kiracı yanıtında ad sırası: ERP kullanıcı adı → panel müşteri
                                                   hesabı → genel etiket. */
                                                $senderName = $isAdmin
                                                    ? (($r['admin_name'] ?? '') !== '' ? $r['admin_name'] : 'Sistem Yöneticisi')
                                                    : (!empty($r['replied_by_tenant_name']) ? $r['replied_by_tenant_name']
                                                        : (!empty($r['cust_name']) ? $r['cust_name'] : 'Müşteri'));
                                            ?>
                                            <div class="d-flex flex-column <?php echo $alignClass; ?> mb-5">
                                                <div class="d-flex align-items-center mb-2">
                                                    <?php if($isAdmin): ?>
                                                        <span class="text-muted fs-8 me-2"><?php echo date('d.m.Y H:i', strtotime($r['created_at'])); ?></span>
                                                        <span class="text-gray-800 fw-bold fs-7"><?php echo htmlspecialchars($senderName); ?> <i class="ki-duotone ki-verify fs-7 text-primary" title="Yönetici"><span class="path1"></span><span class="path2"></span></i></span>
                                                    <?php else: ?>
                                                        <span class="text-gray-800 fw-bold fs-7 me-2"><?php echo htmlspecialchars($senderName); ?></span>
                                                        <span class="text-muted fs-8"><?php echo date('d.m.Y H:i', strtotime($r['created_at'])); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="p-5 rounded <?php echo $bgClass; ?>" style="max-width: 80%;">
                                                    <?php if ($r['is_internal']): ?>
                                                        <div class="fw-bold text-warning mb-1 fs-8"><i class="ki-duotone ki-lock text-warning fs-8"></i> Sadece İç Not</div>
                                                    <?php endif; ?>
                                                    <span class="fs-6" style="white-space: pre-wrap;"><?php echo htmlspecialchars($r['message']); ?></span>
                                                    <?php destek_ek_bas($ekler['c' . (int)$r['reply_id']] ?? []); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center text-muted py-5">Henüz yanıt verilmemiş.</div>
                                    <?php endif; ?>
                                </div>
                                <!-- Thread Bitişi -->
                                
                                <?php if ($ticket['status'] !== 'closed' && $ticket['status'] !== 'resolved'): ?>
                                <div class="separator mt-5 mb-5"></div>
                                <!-- action'da id YOKTU: POST sonrası $_GET['id'] boş kalıyor ve
                                     sayfa talebe dönmeden listeye atıyordu. -->
                                <form action="crm/ticket_detail.php?id=<?php echo (int)$ticket_id; ?>" method="POST" enctype="multipart/form-data">
                            <?= csrfTokenField() ?>
                                    <input type="hidden" name="action" value="add_reply">
                                    <div class="mb-5">
                                        <label class="form-label fw-bold">Yanıtınız:</label>
                                        <textarea class="form-control form-control-solid" rows="4" name="message" placeholder="Müşteriye iletilecek yanıtı veya iç notunuzu buraya yazın..."></textarea>
                                    </div>
                                    <div class="mb-5">
                                        <label class="form-label fw-bold">Ek (ekran görüntüsü / PDF)</label>
                                        <input type="file" class="form-control form-control-solid" name="ek[]" multiple
                                               accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
                                        <div class="form-text">jpg, png, gif, webp, pdf &middot; dosya başına en fazla 5 MB</div>
                                    </div>
                                    <div class="d-flex flex-stack">
                                        <label class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" name="is_internal" value="1"/>
                                            <span class="form-check-label fw-semibold text-gray-700">Bu bir iç nottur (Müşteri görmez)</span>
                                        </label>
                                        <button type="submit" class="btn btn-primary">Yanıt Gönder</button>
                                    </div>
                                </form>
                                <?php else: ?>
                                    <div class="alert alert-warning">Bu talep kapatıldığı için yeni yanıt eklenemez.</div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>

                    <!-- Sağ Taraf: Talep Bilgileri ve Aksiyonlar -->
                    <div class="col-xl-4">
                        <div class="card shadow-sm mb-5">
                            <div class="card-header border-0 pt-6">
                                <h3 class="card-title fw-bold text-gray-800">Talep Bilgileri</h3>
                            </div>
                            <div class="card-body pt-5">
                                <div class="d-flex flex-column mb-5">
                                    <span class="text-muted fs-7 mb-1">Müşteri (Kurum)</span>
                                    <span class="text-gray-800 fw-bold fs-5"><?php echo htmlspecialchars($ticket['company_name']); ?></span>
                                </div>
                                <div class="d-flex flex-column mb-5">
                                    <span class="text-muted fs-7 mb-1">Talep No</span>
                                    <span class="text-gray-800 fw-bold fs-6">#<?php echo htmlspecialchars($ticket['ticket_number']); ?></span>
                                </div>
                                <div class="d-flex flex-column mb-5">
                                    <span class="text-muted fs-7 mb-1">Talebi Açan</span>
                                    <span class="text-gray-800 fw-bold fs-6"><?php echo htmlspecialchars($ticket['created_by_name'] ?? '-'); ?></span>
                                    <?php if (!empty($ticket['created_by_email'])): ?>
                                    <span class="text-muted fs-8"><?php echo htmlspecialchars($ticket['created_by_email']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($ticket['category'])): ?>
                                <div class="d-flex flex-column mb-5">
                                    <span class="text-muted fs-7 mb-1">Kategori</span>
                                    <span class="text-gray-800 fs-6"><?php echo htmlspecialchars($ticket['category']); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($ticket['tenant_db'])): ?>
                                <div class="d-flex flex-column mb-5">
                                    <span class="text-muted fs-7 mb-1">Kiracı Veritabanı</span>
                                    <span class="text-gray-700 fs-7"><?php echo htmlspecialchars($ticket['tenant_db']); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="d-flex flex-column mb-5">
                                    <span class="text-muted fs-7 mb-1">Oluşturulma</span>
                                    <span class="text-gray-800 fs-6"><?php echo date('d.m.Y H:i', strtotime($ticket['created_at'])); ?></span>
                                </div>
                                <div class="d-flex flex-column mb-5">
                                    <span class="text-muted fs-7 mb-1">Öncelik</span>
                                    <div>
                                        <?php 
                                            $pri_class = 'badge-light';
                                            if ($ticket['priority'] == 'high') $pri_class = 'badge-light-danger';
                                            elseif ($ticket['priority'] == 'medium') $pri_class = 'badge-light-warning';
                                            elseif ($ticket['priority'] == 'low') $pri_class = 'badge-light-success';
                                        ?>
                                        <span class="badge <?php echo $pri_class; ?> text-uppercase"><?php echo htmlspecialchars($ticket['priority']); ?></span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column mb-8">
                                    <span class="text-muted fs-7 mb-1">Durum</span>
                                    <div>
                                        <?php 
                                            $status_class = 'badge-light-primary';
                                            if ($ticket['status'] == 'open' || $ticket['status'] == 'new') $status_class = 'badge-light-info';
                                            elseif ($ticket['status'] == 'in_progress') $status_class = 'badge-light-warning';
                                            elseif ($ticket['status'] == 'resolved' || $ticket['status'] == 'closed') $status_class = 'badge-light-success';
                                        ?>
                                        <span class="badge <?php echo $status_class; ?> text-uppercase fs-6"><?php echo htmlspecialchars($ticket['status']); ?></span>
                                    </div>
                                </div>

                                <div class="separator mb-5"></div>
                                <h4 class="text-gray-800 fw-bold mb-4">Aksiyonlar</h4>
                                
                                <form action="crm/ticket_detail.php?id=<?php echo (int)$ticket_id; ?>" method="POST">
                            <?= csrfTokenField() ?>
                                    <input type="hidden" name="action" value="update_status">
                                    <div class="mb-3">
                                        <select class="form-select form-select-solid" name="status">
                                            <option value="new" <?php echo $ticket['status']=='new'?'selected':''; ?>>Yeni (New)</option>
                                            <option value="open" <?php echo $ticket['status']=='open'?'selected':''; ?>>Açık (Open)</option>
                                            <option value="in_progress" <?php echo $ticket['status']=='in_progress'?'selected':''; ?>>İşlemde (In Progress)</option>
                                            <option value="resolved" <?php echo $ticket['status']=='resolved'?'selected':''; ?>>Çözüldü (Resolved)</option>
                                            <option value="closed" <?php echo $ticket['status']=='closed'?'selected':''; ?>>Kapatıldı (Closed)</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary w-100">Durumu Güncelle</button>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once '../config/footer.php'; ?>
