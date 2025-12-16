<?php 
require_once '../../config/header.php'; 
require_once '../../config/sidebar.php'; 

// ID parameter kontrolü
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userId <= 0) {
    header('Location: userslist.php');
    exit;
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <!-- ==================== İÇERİK BURAYA ==================== -->

                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Admin Kullanıcıyı Düzenle</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">Kullanıcı bilgilerini güncelleyin</span>
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
                        <form id="editUserForm" class="form">
                            <input type="hidden" name="admin_id" id="adminId" value="<?php echo htmlspecialchars($userId); ?>">

                            <!-- Loading -->
                            <div id="loadingDiv" class="text-center py-6">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Yükleniyor...</span>
                                </div>
                            </div>

                            <!-- Form Content -->
                            <div id="formContent" style="display: none;">
                                <div class="row mb-6">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold mb-2">Kullanıcı Adı</label>
                                        <input type="text" id="adminUsername" class="form-control form-control-solid" 
                                               placeholder="Kullanıcı adı" disabled>
                                        <small class="form-text text-muted">Kullanıcı adı değiştirilemez</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold mb-2">E-mail Adresi <span class="text-danger">*</span></label>
                                        <input type="email" name="admin_email" id="adminEmail" class="form-control form-control-solid" 
                                               placeholder="E-mail adresini girin" required>
                                        <small class="form-text text-muted">Geçerli ve benzersiz bir e-mail adresi</small>
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
                                            <option value="2">Admin (Sınırlı Erişim)</option>
                                            <option value="3">Operatör (Temel Erişim)</option>
                                        </select>
                                        <small class="form-text text-muted">Kullanıcının sistem izinleri</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold mb-2">Durum <span class="text-danger">*</span></label>
                                        <select name="admin_status" id="adminStatus" class="form-select form-select-solid" required>
                                            <option value="1">Aktif</option>
                                            <option value="0">Pasif</option>
                                        </select>
                                        <small class="form-text text-muted">Kullanıcının sistem erişim durumu</small>
                                    </div>
                                </div>

                                <hr class="my-8">
                                <h5 class="fw-bold mb-4">Şifre Değişikliği</h5>

                                <div class="row mb-6">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold mb-2">Yeni Şifre (İsteğe Bağlı)</label>
                                        <input type="password" name="admin_password" id="adminPassword" class="form-control form-control-solid" 
                                               placeholder="Yeni şifre belirleyin (boş bırakılırsa değişmez)">
                                        <small class="form-text text-muted">Değiştirmek istiyorsanız yeni şifre girin</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold mb-2">Şifre Tekrar</label>
                                        <input type="password" name="admin_password_confirm" id="adminPasswordConfirm" 
                                               class="form-control form-control-solid" placeholder="Şifresini tekrar girin">
                                        <small class="form-text text-muted">Şifrelerin eşleştiğini kontrol edin</small>
                                    </div>
                                </div>

                                <!-- Meta Bilgiler -->
                                <div class="row mb-6">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold mb-2">Oluş. Tarihi</label>
                                        <input type="text" id="adminCreateDate" class="form-control form-control-solid" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold mb-2">Son Güncellenme</label>
                                        <input type="text" id="adminUpdateDate" class="form-control form-control-solid" disabled>
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
                                                Güncelle
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Error State -->
                            <div id="errorDiv" style="display: none;" class="alert alert-danger">
                                <div id="errorMessage"></div>
                                <div class="mt-3">
                                    <a href="userslist.php" class="btn btn-sm btn-light-secondary">
                                        <i class="ki-duotone ki-arrow-left fs-2"></i>
                                        Geri Dön
                                    </a>
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
const userId = document.getElementById('adminId').value;

// Sayfa yüklendiğinde kullanıcı verilerini çek
document.addEventListener('DOMContentLoaded', function() {
    loadUserData();
});

function loadUserData() {
    fetch(`../api/usersapi.php?action=detail&id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateForm(data.data);
                document.getElementById('loadingDiv').style.display = 'none';
                document.getElementById('formContent').style.display = 'block';
            } else {
                showError(data.message || 'Kullanıcı verileri yüklenirken hata oluştu');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Kullanıcı verileri yüklenirken hata oluştu');
        });
}

function populateForm(user) {
    document.getElementById('adminUsername').value = user.admin_username || '';
    document.getElementById('adminEmail').value = user.admin_email || '';
    document.getElementById('adminName').value = user.admin_name || '';
    document.getElementById('adminPhone').value = user.admin_phone || '';
    document.getElementById('adminLevel').value = user.admin_level || '2';
    document.getElementById('adminStatus').value = user.admin_status || '1';
    document.getElementById('adminCreateDate').value = new Date(user.admin_create_date).toLocaleString('tr-TR');
    document.getElementById('adminUpdateDate').value = new Date(user.admin_update_date).toLocaleString('tr-TR');
}

function showError(message) {
    document.getElementById('loadingDiv').style.display = 'none';
    document.getElementById('formContent').style.display = 'none';
    document.getElementById('errorDiv').style.display = 'block';
    document.getElementById('errorMessage').textContent = message;
}

// Form submit
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Form validasyonu
    const email = document.getElementById('adminEmail').value.trim();
    const password = document.getElementById('adminPassword').value;
    const passwordConfirm = document.getElementById('adminPasswordConfirm').value;
    const level = document.getElementById('adminLevel').value;
    const status = document.getElementById('adminStatus').value;

    // E-posta boş mu
    if (!email) {
        showAlert('error', 'E-posta adresi gerekli');
        return;
    }

    // E-posta format kontrolü
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showAlert('error', 'Geçerli bir e-posta adresi girin');
        return;
    }

    // Şifre değiştiriliyorsa kontrol et
    if (password !== '') {
        if (password !== passwordConfirm) {
            showAlert('error', 'Şifreler eşleşmiyor');
            return;
        }
        if (password.length < 8) {
            showAlert('error', 'Şifre en az 8 karakter olmalıdır');
            return;
        }
    }

    // Submit butonunu devre dışı bırak
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Güncelleniyor...';

    // API'ye gönder
    const formData = {
        admin_id: parseInt(userId),
        admin_email: email,
        admin_name: document.getElementById('adminName').value.trim(),
        admin_phone: document.getElementById('adminPhone').value.trim(),
        admin_level: parseInt(level),
        admin_status: parseInt(status)
    };

    // Şifre boş değilse ekle
    if (password !== '') {
        formData.admin_password = password;
    }

    fetch('../api/usersapi.php?action=update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || 'Kullanıcı başarılı bir şekilde güncellendi');
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
        showAlert('error', 'Kullanıcı güncellenirken hata oluştu');
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