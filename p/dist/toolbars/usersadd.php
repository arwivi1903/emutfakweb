<?php 
require_once '../../config/header.php'; 
require_once '../../config/sidebar.php'; 
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <!-- ==================== İÇERİK BURAYA ==================== -->

                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Yeni Admin Kullanıcı Ekle</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">Sistem yöneticisini sisteme ekleyin</span>
                        </h3>
                        <div class="card-toolbar">
                            <a href="userslist.php" class="btn btn-sm btn-light-secondary">
                                <i class="ki-duotone ki-arrow-left fs-2"></i>
                                Geri Dön
                            </a>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        
                        <!-- Form -->
                        <form id="addUserForm" class="form">
                            <div class="row mb-6">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-2">Kullanıcı Adı <span class="text-danger">*</span></label>
                                    <input type="text" name="admin_username" id="adminUsername" class="form-control form-control-solid" 
                                           placeholder="Kullanıcı adını girin" required>
                                    <small class="form-text text-muted">Sistem giriş için kullanılacak benzersiz ad</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-2">E-mail Adresi <span class="text-danger">*</span></label>
                                    <input type="email" name="admin_email" id="adminEmail" class="form-control form-control-solid" 
                                           placeholder="E-mail adresini girin" required>
                                    <small class="form-text text-muted">Geçerli ve benzersiz bir e-mail adresi olmalıdır</small>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-2">Şifre <span class="text-danger">*</span></label>
                                    <input type="password" name="admin_password" id="adminPassword" class="form-control form-control-solid" 
                                           placeholder="Şifre belirleyin" required>
                                    <small class="form-text text-muted">En az 8 karakter, büyük/küçük harf ve sayı içermeli</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-2">Şifre Tekrar <span class="text-danger">*</span></label>
                                    <input type="password" name="admin_password_confirm" id="adminPasswordConfirm" 
                                           class="form-control form-control-solid" placeholder="Şifresini tekrar girin" required>
                                    <small class="form-text text-muted">Şifrelerin eşleştiğini kontrol edin</small>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-2">Ad Soyad</label>
                                    <input type="text" name="admin_name" id="adminName" class="form-control form-control-solid" 
                                           placeholder="Ad soyad girin">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-2">Telefon</label>
                                    <input type="tel" name="admin_phone" id="adminPhone" class="form-control form-control-solid" 
                                           placeholder="Telefon numarası girin">
                                </div>
                            </div>

                            <div class="row mb-6">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-2">Yönetici Seviyesi <span class="text-danger">*</span></label>
                                    <select name="admin_level" id="adminLevel" class="form-select form-select-solid" required>
                                        <option value="">-- Seçiniz --</option>
                                        <option value="1">Super Admin (Tüm Erişim)</option>
                                        <option value="2" selected>Admin (Sınırlı Erişim)</option>
                                        <option value="3">Operatör (Temel Erişim)</option>
                                    </select>
                                    <small class="form-text text-muted">Kullanıcının sistem izinlerini belirleyin</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-2">Durum <span class="text-danger">*</span></label>
                                    <select name="admin_status" id="adminStatus" class="form-select form-select-solid" required>
                                        <option value="1" selected>Aktif</option>
                                        <option value="0">Pasif</option>
                                    </select>
                                    <small class="form-text text-muted">Kullanıcının sistem erişim durumu</small>
                                </div>
                            </div>

                            <!-- Uyarı Mesajları -->
                            <div id="formAlert" style="display: none;" class="alert alert-dismissible fade show" role="alert">
                                <div id="formAlertMessage"></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>

                            <!-- Butonlar -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-3">
                                        <a href="userslist.php" class="btn btn-light-secondary">
                                            <i class="ki-duotone ki-cross fs-2"></i>
                                            İptal
                                        </a>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="ki-duotone ki-check fs-2"></i>
                                            Ekle
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                    </div>
                </div>

                <!-- ==================== İÇERİK SONU ==================== -->

            </div>
        </div>
    </div>
    <?php 
require_once '../../config/footer.php'; 
?>
</div>

<script>
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Form validasyonu
    const username = document.getElementById('adminUsername').value.trim();
    const email = document.getElementById('adminEmail').value.trim();
    const password = document.getElementById('adminPassword').value;
    const passwordConfirm = document.getElementById('adminPasswordConfirm').value;
    const name = document.getElementById('adminName').value.trim();
    const phone = document.getElementById('adminPhone').value.trim();
    const level = document.getElementById('adminLevel').value;
    const status = document.getElementById('adminStatus').value;

    // Boş kontrol
    if (!username || !email || !password) {
        showAlert('error', 'Lütfen tüm zorunlu alanları doldurunuz');
        return;
    }

    // Şifre kontrol
    if (password !== passwordConfirm) {
        showAlert('error', 'Şifreler eşleşmiyor');
        return;
    }

    if (password.length < 8) {
        showAlert('error', 'Şifre en az 8 karakter olmalıdır');
        return;
    }

    // E-posta format kontrolü
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showAlert('error', 'Geçerli bir e-posta adresi girin');
        return;
    }

    // Submit butonunu devre dışı bırak
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Ekleniyor...';

    // API'ye gönder
    const formData = {
        admin_username: username,
        admin_email: email,
        admin_password: password,
        admin_name: name,
        admin_phone: phone,
        admin_level: parseInt(level),
        admin_status: parseInt(status)
    };

    fetch('../api/usersapi.php?action=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || 'Kullanıcı başarılı bir şekilde eklendi');
            setTimeout(() => {
                window.location.href = 'userslist.php';
            }, 1500);
        } else {
            showAlert('error', data.message || 'Bir hata oluştu');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Kullanıcı eklenirken hata oluştu');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

function showAlert(type, message) {
    const alertDiv = document.getElementById('formAlert');
    const messageDiv = document.getElementById('formAlertMessage');
    
    const alertClass = type === 'success' 
        ? 'alert-success' 
        : (type === 'error' ? 'alert-danger' : 'alert-warning');
    
    alertDiv.className = `alert alert-dismissible fade show ${alertClass}`;
    messageDiv.textContent = message;
    alertDiv.style.display = 'block';
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>