<?php
require_once '../../../config/header.php';
require_once '../../../config/sidebar.php';

$db = new Database();

if (!isset($_GET['id'])) {
    echo "<script>window.location.href='tickets.php';</script>";
    exit;
}

$ticket_id = intval($_GET['id']);

// Yanıt Gönderme İşlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply_ticket') {
    $message = $_POST['message'] ?? '';
    // Durum güncelleme
    $new_status = $_POST['status'] ?? 'pending';
    
    if (!empty($message)) {
        // Yanıtı kaydet
        $sql = "INSERT INTO ticket_replies (ticket_id, replied_by_admin, message, is_internal) VALUES (?, ?, ?, ?)";
        // Varsayılan olarak is_internal 0 (Müşteriye açık), admin yanıtlıyor
        if ($db->Insert($sql, [$ticket_id, $_SESSION['admin_id'] ?? 1, $message, 0])) {
            // Ticket durumunu güncelle
            $db->Update("UPDATE support_tickets SET status = ?, updated_at = NOW() WHERE ticket_id = ?", [$new_status, $ticket_id]);
            echo "<script>Swal.fire('Başarılı', 'Yanıt başarıyla gönderildi.', 'success').then(() => { window.location.reload(); });</script>";
        } else {
             echo "<script>Swal.fire('Hata', 'Yanıt gönderilirken bir sorun oluştu.', 'error');</script>";
        }
    }
}

// Ticket detayını çek
$ticket = $db->getRow("SELECT st.*, c.company_name, c.contact_name, c.company_email, c.contact_email 
                       FROM support_tickets st 
                       LEFT JOIN customers c ON st.customer_id = c.customer_id 
                       WHERE st.ticket_id = ?", [$ticket_id]);

if (!$ticket) {
    echo "Talep bulunamadı.";
    exit;
}

// Yanıtları çek
$replies = $db->getRows("SELECT tr.*, a.full_name as admin_name, a.admin_pic
                         FROM ticket_replies tr 
                         LEFT JOIN admins a ON tr.replied_by_admin = a.admin_id
                         WHERE tr.ticket_id = ? 
                         ORDER BY tr.created_at ASC", [$ticket_id]);

?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack mr-3">
                 <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Destek Talebi #<?php echo $ticket->ticket_id; ?>
                    </h1>
                </div>
                 <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="tickets.php" class="btn btn-sm btn-secondary">Geri Dön</a>
                </div>
            </div>
        </div>
        
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap flex-sm-nowrap mb-6">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-1">
                                            <a href="#" class="text-gray-800 text-hover-primary fs-2 fw-bold me-3"><?php echo htmlspecialchars($ticket->subject); ?></a>
                                            <?php
                                                $statusClass = [
                                                    'open' => 'badge-light-danger',
                                                    'pending' => 'badge-light-warning',
                                                    'resolved' => 'badge-light-success',
                                                    'closed' => 'badge-light-secondary'
                                                ][$ticket->status] ?? 'badge-light-secondary';
                                                $statusText = [
                                                    'open' => 'Açık',
                                                    'pending' => 'Beklemede',
                                                    'resolved' => 'Çözüldü',
                                                    'closed' => 'Kapandı'
                                                ][$ticket->status] ?? ucfirst($ticket->status);
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?> me-auto"><?php echo $statusText; ?></span>
                                        </div>
                                        <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                            <a href="#" class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                                <i class="ki-duotone ki-profile-circle fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                <?php echo htmlspecialchars($ticket->contact_name); ?> (<?php echo htmlspecialchars($ticket->company_name); ?>)
                                            </a>
                                            <a href="#" class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                                <i class="ki-duotone ki-sms fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                <?php echo htmlspecialchars($ticket->contact_email); ?>
                                            </a>
                                            <a href="#" class="d-flex align-items-center text-gray-400 text-hover-primary mb-2">
                                                <i class="ki-duotone ki-time fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                <?php echo date('d.m.Y H:i', strtotime($ticket->created_at)); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap flex-stack">
                                    <div class="d-flex flex-column flex-grow-1 pe-8">
                                        <div class="d-flex flex-wrap">
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="fs-2 fw-bold"><?php echo ucfirst($ticket->priority); ?></div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-400">Öncelik</div>
                                            </div>
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="fs-2 fw-bold"><?php echo ucfirst($ticket->category ?? 'Genel'); ?></div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-400">Kategori</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="separator separator-dashed my-10"></div>
                        
                        <!-- Mesaj Geçmişi -->
                        <div class="mb-15">
                             <!-- İlk Mesaj (Müşteri Talebi) -->
                             <div class="d-flex flex-column mb-5 align-items-start">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="symbol symbol-35px symbol-circle">
                                       <span class="symbol-label bg-light-danger text-danger fs-6 fw-bolder">M</span>
                                    </div>
                                    <div class="ms-3">
                                        <a href="#" class="fs-5 fw-bold text-gray-800 text-hover-primary me-1"><?php echo htmlspecialchars($ticket->contact_name); ?></a>
                                        <span class="text-muted fs-7 mb-1"><?php echo date('d.m.Y H:i', strtotime($ticket->created_at)); ?></span>
                                    </div>
                                </div>
                                <div class="p-5 rounded bg-light-info text-dark fw-semibold mw-lg-400px text-start">
                                    <?php echo nl2br(htmlspecialchars($ticket->message ?? '')); ?>
                                </div>
                            </div>

                            <?php foreach ($replies as $reply): ?>
                                <?php if ($reply->replied_by_admin): ?>
                                    <!-- Admin Yanıtı -->
                                    <div class="d-flex flex-column mb-5 align-items-end">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="me-3">
                                                <span class="text-muted fs-7 mb-1"><?php echo date('d.m.Y H:i', strtotime($reply->created_at)); ?></span>
                                                <a href="#" class="fs-5 fw-bold text-gray-800 text-hover-primary ms-1">Biz (<?php echo htmlspecialchars($reply->admin_name); ?>)</a>
                                            </div>
                                            <div class="symbol symbol-35px symbol-circle">
                                                <img alt="Pic" src="../../../<?php echo htmlspecialchars($reply->admin_pic ?? 'dist/assets/media/avatars/300-1.jpg'); ?>" />
                                            </div>
                                        </div>
                                        <div class="p-5 rounded bg-light-primary text-dark fw-semibold mw-lg-400px text-end">
                                            <?php echo nl2br(htmlspecialchars($reply->message)); ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Müşteri Yanıtı -->
                                    <div class="d-flex flex-column mb-5 align-items-start">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="symbol symbol-35px symbol-circle">
                                                <span class="symbol-label bg-light-danger text-danger fs-6 fw-bolder">M</span>
                                            </div>
                                            <div class="ms-3">
                                                <a href="#" class="fs-5 fw-bold text-gray-800 text-hover-primary me-1">Müşteri</a>
                                                <span class="text-muted fs-7 mb-1"><?php echo date('d.m.Y H:i', strtotime($reply->created_at)); ?></span>
                                            </div>
                                        </div>
                                        <div class="p-5 rounded bg-light-info text-dark fw-semibold mw-lg-400px text-start">
                                            <?php echo nl2br(htmlspecialchars($reply->message)); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <!-- Yanıt Formu -->
                        <div class="card mb-5 mb-xl-10">
                            <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
                                <div class="card-title m-0">
                                    <h3 class="fw-bold m-0">Yanıtla</h3>
                                </div>
                            </div>
                            <div class="card-body border-top p-9">
                                <form action="" method="POST">
                                    <input type="hidden" name="action" value="reply_ticket">
                                    <div class="row mb-6">
                                        <label class="col-lg-2 col-form-label fw-semibold fs-6">Durum</label>
                                        <div class="col-lg-10 fv-row">
                                            <select name="status" class="form-select form-select-solid fw-bold">
                                                <option value="pending" <?php echo $ticket->status === 'pending' ? 'selected' : ''; ?>>Beklemede (Müşteri Yanıtı Bekleniyor)</option>
                                                <option value="resolved" <?php echo $ticket->status === 'resolved' ? 'selected' : ''; ?>>Çözüldü</option>
                                                <option value="closed" <?php echo $ticket->status === 'closed' ? 'selected' : ''; ?>>Kapat</option>
                                                <option value="open" <?php echo $ticket->status === 'open' ? 'selected' : ''; ?>>Açık (İşlem Devam Ediyor)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label class="col-lg-2 col-form-label fw-semibold fs-6">Mesaj</label>
                                        <div class="col-lg-10 fv-row">
                                            <textarea name="message" class="form-control form-control-solid" rows="6" placeholder="Yanıtınızı buraya yazın..." required></textarea>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                                        <button type="submit" class="btn btn-primary">Yanıt Gönder</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php require_once '../../../config/footer.php'; ?>
