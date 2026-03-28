<?php
require_once '../../config/header.php'; 
require_once '../../config/sidebar.php'; 

$dbName = isset($_GET['db']) ? $_GET['db'] : '';

if (empty($dbName)) {
    header('Location: /prolynweb/p/dist/toolbars/db_control.php');
    exit;
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <!-- Başlık -->
                <div class="row mb-6">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body p-9">
                                <div class="d-flex align-items-center">
                                    <a href="dist/toolbars/db_control.php" class="btn btn-sm btn-light-primary me-4">
                                        <i class="ki-duotone ki-arrow-left fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Geri
                                    </a>
                                    <div>
                                        <h1 class="mb-1">
                                            <i class="ki-duotone ki-data fs-2hx text-primary me-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                                <span class="path5"></span>
                                            </i>
                                            Veritabanı Detaylı Analiz
                                        </h1>
                                        <p class="text-muted fw-semibold fs-5 mb-0">
                                            <code><?= htmlspecialchars($dbName) ?></code>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                try {
                    $db = new Database($dbName);
                    
                    // Veritabanı boyutu
                    $sizeQuery = "
                        SELECT 
                            table_schema AS 'Database',
                            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size_MB',
                            ROUND(SUM(data_length + index_length) / 1024 / 1024 / 1024, 3) AS 'Size_GB'
                        FROM information_schema.TABLES 
                        WHERE table_schema = ?
                        GROUP BY table_schema
                    ";
                    $dbSize = $db->getRowAssoc($sizeQuery, [$dbName]);
                    
                    // Tablo detayları
                    $tablesQuery = "
                        SELECT 
                            TABLE_NAME,
                            TABLE_ROWS,
                            ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS Size_MB,
                            ENGINE,
                            TABLE_COLLATION,
                            CREATE_TIME,
                            UPDATE_TIME
                        FROM information_schema.TABLES 
                        WHERE TABLE_SCHEMA = ?
                        ORDER BY TABLE_NAME ASC
                    ";
                    $tables = $db->getRowsAssoc($tablesQuery, [$dbName]);
                    
                    // Her tablo için kolon bilgileri
                    $tableDetails = [];
                    foreach ($tables as $table) {
                        $columnsQuery = "
                            SELECT 
                                COLUMN_NAME,
                                COLUMN_TYPE,
                                DATA_TYPE,
                                CHARACTER_MAXIMUM_LENGTH,
                                NUMERIC_PRECISION,
                                IS_NULLABLE,
                                COLUMN_KEY,
                                EXTRA
                            FROM information_schema.COLUMNS
                            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                            ORDER BY ORDINAL_POSITION
                        ";
                        $columns = $db->getRowsAssoc($columnsQuery, [$dbName, $table['TABLE_NAME']]);
                        
                        // VARCHAR ve INT sayıları
                        $varcharCount = 0;
                        $intCount = 0;
                        $textCount = 0;
                        $dateCount = 0;
                        
                        foreach ($columns as $col) {
                            $type = strtolower($col['DATA_TYPE']);
                            if (strpos($type, 'varchar') !== false || strpos($type, 'char') !== false) {
                                $varcharCount++;
                            } elseif (strpos($type, 'int') !== false || strpos($type, 'bigint') !== false) {
                                $intCount++;
                            } elseif (strpos($type, 'text') !== false) {
                                $textCount++;
                            } elseif (strpos($type, 'date') !== false || strpos($type, 'time') !== false) {
                                $dateCount++;
                            }
                        }
                        
                        $tableDetails[$table['TABLE_NAME']] = [
                            'columns' => $columns,
                            'varchar_count' => $varcharCount,
                            'int_count' => $intCount,
                            'text_count' => $textCount,
                            'date_count' => $dateCount
                        ];
                    }
                ?>

                <!-- Özet İstatistikler -->
                <div class="row g-6 g-xl-9 mb-6">
                    <div class="col-lg-3 col-6">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column p-0">
                                <div class="flex-grow-1 card-p pb-0">
                                    <div class="d-flex flex-column">
                                        <span class="fs-4 fw-semibold text-gray-400">Veritabanı Boyutu</span>
                                        <span class="fs-2hx fw-bold text-primary me-2 lh-1 ls-n2">
                                            <?= $dbSize ? $dbSize['Size_MB'] : '0' ?> MB
                                        </span>
                                        <span class="text-gray-400 pt-1 fw-semibold fs-6">
                                            (<?= $dbSize ? $dbSize['Size_GB'] : '0' ?> GB)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column p-0">
                                <div class="flex-grow-1 card-p pb-0">
                                    <div class="d-flex flex-column">
                                        <span class="fs-4 fw-semibold text-gray-400">Toplam Tablo</span>
                                        <span class="fs-2hx fw-bold text-success me-2 lh-1 ls-n2">
                                            <?= count($tables) ?>
                                        </span>
                                        <span class="text-gray-400 pt-1 fw-semibold fs-6">Aktif Tablo</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column p-0">
                                <div class="flex-grow-1 card-p pb-0">
                                    <div class="d-flex flex-column">
                                        <span class="fs-4 fw-semibold text-gray-400">Toplam Kayıt</span>
                                        <span class="fs-2hx fw-bold text-info me-2 lh-1 ls-n2">
                                            <?= number_format((int)array_sum(array_column($tables, 'TABLE_ROWS'))) ?>
                                        </span>
                                        <span class="text-gray-400 pt-1 fw-semibold fs-6">Tüm Tablolar</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column p-0">
                                <div class="flex-grow-1 card-p pb-0">
                                    <div class="d-flex flex-column">
                                        <span class="fs-4 fw-semibold text-gray-400">Motor</span>
                                        <span class="fs-2hx fw-bold text-warning me-2 lh-1 ls-n2">
                                            <?= $tables[0]['ENGINE'] ?? 'N/A' ?>
                                        </span>
                                        <span class="text-gray-400 pt-1 fw-semibold fs-6">Storage Engine</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tablolar Listesi -->
                <div class="row mb-6">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-0 pt-6">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">
                                        <i class="ki-duotone ki-abstract-35 fs-2 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Tablo Detayları
                                    </span>
                                </h3>
                            </div>
                            <div class="card-body py-4">
                                <div class="table-responsive">
                                    <table id="kt_tables_table" class="table align-middle table-row-dashed fs-6 gy-5">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="min-w-150px">Tablo Adı</th>
                                                <th class="min-w-75px text-center">Satır</th>
                                                <th class="min-w-75px text-center">Boyut (MB)</th>
                                                <th class="min-w-75px text-center">VARCHAR</th>
                                                <th class="min-w-75px text-center">INT</th>
                                                <th class="min-w-75px text-center">TEXT</th>
                                                <th class="min-w-75px text-center">DATE</th>
                                                <th class="min-w-100px text-center">İşlem</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">
                                            <?php foreach ($tables as $table): ?>
                                            <?php $details = $tableDetails[$table['TABLE_NAME']]; ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-duotone ki-chart fs-2x text-primary me-3">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-gray-800 fw-bold"><?= htmlspecialchars($table['TABLE_NAME']) ?></span>
                                                            <span class="text-muted fs-7"><?= $table['ENGINE'] ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-light-info">
                                                        <?= number_format((int)($table['TABLE_ROWS'] ?? 0)) ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-light-primary">
                                                        <?= $table['Size_MB'] ?? '0.00' ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-light-success">
                                                        <?= $details['varchar_count'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-light-warning">
                                                        <?= $details['int_count'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-light-danger">
                                                        <?= $details['text_count'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-light-secondary">
                                                        <?= $details['date_count'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-light-primary" 
                                                            onclick='showTableColumns("<?= htmlspecialchars($table['TABLE_NAME']) ?>", <?= json_encode($details['columns'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>)'>
                                                        <i class="ki-duotone ki-eye fs-5 me-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                        </i>
                                                        Kolonlar
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                } catch (Exception $e) {
                    echo '<div class="row"><div class="col-12">';
                    echo '<div class="alert alert-danger d-flex align-items-center p-5">';
                    echo '<i class="ki-duotone ki-information fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>';
                    echo '<div class="d-flex flex-column"><h4 class="mb-1 text-danger">Hata</h4><span>' . htmlspecialchars($e->getMessage()) . '</span></div>';
                    echo '</div></div></div>';
                }
                ?>

            </div>
        </div>
    </div>

    <!-- Kolon Detay Modalı -->
    <div class="modal fade" id="kt_modal_columns" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">
                        <i class="ki-duotone ki-abstract-14 fs-2 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <span id="modal_table_name"></span> - Kolon Yapısı
                    </h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div id="kt_modal_columns_body"></div>
                </div>
            </div>
        </div>
    </div>

    <?php 
    require_once '../../config/footer.php'; 
    ?>

    <script>
    "use strict";

    var KTExtendedDB = function() {
        var table;
        var dt;

        var initTable = function() {
            dt = $("#kt_tables_table").DataTable({
                info: false,
                ordering: false,
                pageLength: 25,
                language: {
                    "sDecimal": ",",
                    "sEmptyTable": "Tabloda veri yok",
                    "sInfo": "_TOTAL_ kayıttan _START_ - _END_ arası",
                    "sInfoEmpty": "Kayıt yok",
                    "sInfoFiltered": "(_MAX_ kayıt içerisinden)",
                    "sLengthMenu": "Sayfada _MENU_ kayıt",
                    "sLoadingRecords": "Yükleniyor...",
                    "sProcessing": "İşleniyor...",
                    "sSearch": "Ara:",
                    "sZeroRecords": "Eşleşen kayıt yok",
                    "oPaginate": {
                        "sFirst": "İlk",
                        "sLast": "Son",
                        "sNext": "Sonraki",
                        "sPrevious": "Önceki"
                    }
                }
            });

            table = dt.$;
        }

        return {
            init: function() {
                initTable();
            }
        };
    }();

    function showTableColumns(tableName, columns) {
        document.getElementById('modal_table_name').textContent = tableName;
        
        var html = '<div class="table-responsive">';
        html += '<table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">';
        html += '<thead>';
        html += '<tr class="fw-bold text-muted bg-light">';
        html += '<th class="ps-4 min-w-150px">Kolon Adı</th>';
        html += '<th class="min-w-125px">Tip</th>';
        html += '<th class="min-w-100px text-center">Uzunluk</th>';
        html += '<th class="min-w-100px text-center">NULL</th>';
        html += '<th class="min-w-100px text-center">Key</th>';
        html += '<th class="min-w-100px">Extra</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';
        
        columns.forEach(function(col) {
            html += '<tr>';
            html += '<td class="ps-4"><span class="text-gray-800 fw-bold d-block">' + col.COLUMN_NAME + '</span></td>';
            html += '<td><code>' + col.COLUMN_TYPE + '</code></td>';
            html += '<td class="text-center">' + (col.CHARACTER_MAXIMUM_LENGTH || col.NUMERIC_PRECISION || '-') + '</td>';
            html += '<td class="text-center">' + (col.IS_NULLABLE === 'YES' ? '<span class="badge badge-light-success">YES</span>' : '<span class="badge badge-light-danger">NO</span>') + '</td>';
            html += '<td class="text-center">' + (col.COLUMN_KEY ? '<span class="badge badge-light-primary">' + col.COLUMN_KEY + '</span>' : '-') + '</td>';
            html += '<td>' + (col.EXTRA || '-') + '</td>';
            html += '</tr>';
        });
        
        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        
        document.getElementById('kt_modal_columns_body').innerHTML = html;
        
        var myModal = new bootstrap.Modal(document.getElementById('kt_modal_columns'));
        myModal.show();
    }

    KTUtil.onDOMContentLoaded(function() {
        KTExtendedDB.init();
    });
    </script>
</div>
