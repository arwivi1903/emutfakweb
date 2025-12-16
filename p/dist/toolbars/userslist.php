<?php 
// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Giriş kontrolü - login olmamışsa login sayfasına yönlendir
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../login.php', true, 302);
    exit;
}

require_once '../../config/header.php'; 
require_once '../../config/sidebar.php'; 
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Admin Kullanıcıları</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">Sistem yöneticilerini yönetin</span>
                        </h3>
                        <div class="card-toolbar">
                            <a href="usersadd.php" class="btn btn-sm btn-light-primary">
                                <i class="ki-duotone ki-plus fs-2"></i>
                                Yeni Kullanıcı Ekle
                            </a>
                        </div>
                    </div>
                    <div class="card-body py-4">

                        <!-- Arama ve Filtreleme -->
                        <div class="row mb-6">
                            <div class="col-md-6">
                                <input type="text" id="searchInput" class="form-control form-control-solid"
                                    placeholder="Adı, E-mail veya Kullanıcı Adı ile Arayın...">
                            </div>
                            <div class="col-md-3">
                                <select id="sortSelect" class="form-select form-select-solid">
                                    <option value="admin_id">Tarih (En Yeni)</option>
                                    <option value="admin_username">Kullanıcı Adı</option>
                                    <option value="admin_email">E-mail</option>
                                    <option value="admin_status">Durum</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="searchBtn" class="btn btn-light-primary w-100">
                                    <i class="ki-duotone ki-magnifier fs-2"></i>
                                    Ara
                                </button>
                            </div>
                        </div>

                        <!-- Tablo -->
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 gy-7">
                                <thead>
                                    <tr class="fw-bold fs-6 text-gray-800 border-bottom border-gray-200">
                                        <th>ID</th>
                                        <th>Kullanıcı Adı</th>
                                        <th>E-mail</th>
                                        <th>Ad Soyad</th>
                                        <th>Telefon</th>
                                        <th>Seviye</th>
                                        <th>Durum</th>
                                        <th>Oluş. Tarihi</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-6">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Yükleniyor...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-6">
                            <div>
                                <span id="totalRecords" class="text-muted">Toplam: 0</span>
                            </div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination m-0" id="paginationContainer">
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php 
require_once '../../config/footer.php'; 
?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let currentSearch = '';
    let currentSort = 'admin_id';

    // Sayfayı yükle
    loadUsers();

    // Arama ve filtreleme event'leri
    document.getElementById('searchBtn').addEventListener('click', function() {
        currentSearch = document.getElementById('searchInput').value;
        currentSort = document.getElementById('sortSelect').value;
        currentPage = 1;
        loadUsers();
    });

    // Input yazılırken otomatik arama (key input)
    document.getElementById('searchInput').addEventListener('input', function(e) {
        currentSearch = this.value;
        currentSort = document.getElementById('sortSelect').value;
        currentPage = 1;
        loadUsers();
    });

    // Sort dropdown değiştiğinde
    document.getElementById('sortSelect').addEventListener('change', function(e) {
        currentSort = this.value;
        currentSearch = document.getElementById('searchInput').value;
        currentPage = 1;
        loadUsers();
    });

    // Enter tuşu ile arama
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });

    // Kullanıcıları yükle
    function loadUsers() {
        const params = new URLSearchParams({
            action: 'list',
            search: currentSearch,
            sort: currentSort,
            order: 'DESC',
            page: currentPage,
            limit: 10
        });

        fetch(`/emutfakweb/p/dist/api/usersapi.php?${params}`)
            .then(response => {
                if (!response.ok) {
                    console.error('HTTP Error:', response.status, response.statusText);
                    if (response.status === 401) {
                        window.location.href = '/emutfakweb/p/login.php';
                        return null;
                    }
                }
                return response.json();
            })
            .then(data => {
                if (!data) return;

                if (data.success) {
                    renderTable(data.data);
                    updatePagination(data.pagination);
                    document.getElementById('totalRecords').textContent =
                    `Toplam: ${data.pagination.total}`;
                } else {
                    if (data.redirect) {
                        window.location.href = '/emutfakweb/p/login.php';
                    } else {
                        showError(data.message || 'Veriler yüklenirken hata oluştu');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Veriler yüklenirken hata oluştu - ' + error.message);
            });
    }

    // Tabloyu oluştur
    function renderTable(users) {
        const tbody = document.getElementById('usersTableBody');

        if (users.length === 0) {
            tbody.innerHTML =
                '<tr><td colspan="9" class="text-center text-muted py-6">Kayıt bulunamadı</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(user => {
            const statusBadge = user.status === 'active' ?
                '<span class="badge badge-light-success">Aktif</span>' :
                user.status === 'inactive' ?
                '<span class="badge badge-light-warning">Pasif</span>' :
                '<span class="badge badge-light-danger">Askıya Alındı</span>';

            const roleText = {
                'superadmin': 'Super Admin',
                'support': 'Destek',
                'financial': 'Mali'
            } [user.role] || 'Bilinmeyen';

            const createDate = new Date(user.created_at).toLocaleDateString('tr-TR');

            return `<tr>
                        <td>
                            <span class="text-dark fw-bold text-hover-primary">${user.admin_id}</span>
                        </td>
                        <td>
                            <span class="text-dark fw-bold">${user.email}</span>
                        </td>
                        <td>
                            <span class="text-gray-600">${user.email}</span>
                        </td>
                        <td>
                            <span>${user.full_name || '-'}</span>
                        </td>
                        <td>
                            <span>${user.role || '-'}</span>
                        </td>
                        <td>
                            <span class="badge badge-light-info">${roleText}</span>
                        </td>
                        <td>${statusBadge}</td>
                        <td>${createDate}</td>
                        <td>
                            <a href="usersedit.php?id=${user.admin_id}" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary">
                                <i class="ki-duotone ki-pencil fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </a>
                            <button type="button" class="btn btn-sm btn-icon btn-bg-light btn-active-color-danger" 
                                    onclick="deleteUser(${user.admin_id})">
                                <i class="ki-duotone ki-trash fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                </i>
                            </button>
                        </td>
                    </tr>`;
        }).join('');
    }

    // Pagination güncelle
    function updatePagination(pagination) {
        const container = document.getElementById('paginationContainer');
        container.innerHTML = '';

        // Önceki sayfası
        if (pagination.page > 1) {
            container.innerHTML +=
                `<li class="page-item"><button class="page-link" onclick="goToPage(${pagination.page - 1})">Önceki</button></li>`;
        }

        // Sayfa numaraları
        const startPage = Math.max(1, pagination.page - 2);
        const endPage = Math.min(pagination.totalPages, pagination.page + 2);

        if (startPage > 1) {
            container.innerHTML +=
                `<li class="page-item"><button class="page-link" onclick="goToPage(1)">1</button></li>`;
            if (startPage > 2) {
                container.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const active = i === pagination.page ? 'active' : '';
            container.innerHTML +=
                `<li class="page-item ${active}"><button class="page-link" onclick="goToPage(${i})">${i}</button></li>`;
        }

        if (endPage < pagination.totalPages) {
            if (endPage < pagination.totalPages - 1) {
                container.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            container.innerHTML +=
                `<li class="page-item"><button class="page-link" onclick="goToPage(${pagination.totalPages})">${pagination.totalPages}</button></li>`;
        }

        // Sonraki sayfası
        if (pagination.page < pagination.totalPages) {
            container.innerHTML +=
                `<li class="page-item"><button class="page-link" onclick="goToPage(${pagination.page + 1})">Sonraki</button></li>`;
        }
    }

    // Global fonksiyonlar
    window.goToPage = function(page) {
        currentPage = page;
        loadUsers();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };

    window.deleteUser = function(userId) {
        if (confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')) {
            fetch('/emutfakweb/p/dist/api/usersapi.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        admin_id: userId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        loadUsers();
                    } else {
                        alert('Hata: ' + (data.message || 'Bilinmeyen hata'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Hata: Siliş işlemi başarısız oldu');
                });
        }
    };

    function showError(message) {
        const tbody = document.getElementById('usersTableBody');
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger py-6">${message}</td></tr>`;
    }
});
</script>