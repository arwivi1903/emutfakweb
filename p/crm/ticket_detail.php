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
    if (isset($_POST['action']) && $_POST['action'] === 'add_reply') {
        $message = $_POST['message'];
        $is_internal = isset($_POST['is_internal']) ? 1 : 0;
        
        // Örnek admin_id (oturumdan alınmalı, şimdilik statik 1)
        $admin_id = 1;
        
        if (!empty(trim($message))) {
            $db->Insert("INSERT INTO destek_cevaplari (ticket_id, replied_by_admin, message, is_internal) VALUES (?, ?, ?, ?)", [
                $ticket_id, $admin_id, $message, $is_internal
            ]);
            
            // Talebi 'in_progress' yap (yönetici cevapladığı için)
            $db->Update("UPDATE destek_talepleri SET status = 'in_progress', updated_at = NOW() WHERE ticket_id = ? AND status IN ('new', 'open')", [$ticket_id]);
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
    LEFT JOIN kurumlar k ON t.customer_id = k.id
    WHERE t.ticket_id = ?
";
$ticket = $db->getRowAssoc($query, [$ticket_id]);

if (!$ticket) {
    echo "<script>window.location.href='crm/tickets.php';</script>";
    exit;
}

// Yanıtları Çek
$repliesQuery = "
    SELECT c.*, 
           y.full_name as admin_name,
           kk.full_name as cust_name
    FROM destek_cevaplari c
    LEFT JOIN yoneticiler y ON c.replied_by_admin = y.admin_id
    LEFT JOIN kurum_kullanicilari kk ON c.replied_by_customer = kk.user_id
    WHERE c.ticket_id = ?
    ORDER BY c.created_at ASC
";
$replies = $db->getRowsAssoc($repliesQuery, [$ticket_id]);
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
                                                
                                                $senderName = $isAdmin ? ($r['admin_name'] ?? 'Sistem Yöneticisi') : ($r['cust_name'] ?? 'Müşteri');
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
                                <form action="crm/ticket_detail.php" method="POST">
                            <?= csrfTokenField() ?>
                                    <input type="hidden" name="action" value="add_reply">
                                    <div class="mb-5">
                                        <label class="form-label fw-bold">Yanıtınız:</label>
                                        <textarea class="form-control form-control-solid" rows="4" name="message" required placeholder="Müşteriye iletilecek yanıtı veya iç notunuzu buraya yazın..."></textarea>
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
                                
                                <form action="crm/ticket_detail.php" method="POST">
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
