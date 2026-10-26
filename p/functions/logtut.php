<?php
/**
 * PROLYN ERP - Unified logging helper
 * Basit API: xlog($action, $details = '', $entityId = null, $admin_id = null, $db = null, $oldValue = null, $newValue = null, $entityType = null)
 */

function xlogGetTableCache(Database $db)
{
    static $cache = [];

    $dbName = $db->MYSQL_DB ?? 'default';
    if (isset($cache[$dbName])) {
        return $cache[$dbName];
    }

    $tables = [
        'lognew' => false,
        'logtypesnew' => false,
        'logtypes' => false,
        'yonetici_loglari' => false,
        'admin_logs' => false,
    ];

    foreach ($tables as $table => $exists) {
        $row = $db->getRow('SHOW TABLES LIKE ?', [$table]);
        $tables[$table] = !empty($row);
    }

    $cache[$dbName] = $tables;
    return $tables;
}

function xlogResolveType(Database $db, $logType, array $tableCache)
{
    $typeMap = [
        'ERROR' => 1,
        'LOGIN' => 1,
        'LOGOUT' => 1,
        'WARNING' => 2,
        'DELETE' => 2,
        'ADMIN' => 3,
        'SUCCESS' => 3,
        'CREATE' => 3,
        'INSERT' => 3,
        'UPDATE' => 3,
        'INFO' => 4,
        'DEBUG' => 5,
        'LOGIN_FAILED' => 2,
        'LOGIN_SUCCESS' => 3,
        'GIRIS_BASARISIZ' => 2,
        'GIRIS_BASARILI' => 3,
    ];

    if (is_numeric($logType)) {
        $resolved = (int)$logType;
    } else {
        $resolved = $typeMap[strtoupper((string)$logType)] ?? 3;
    }

    if ($tableCache['logtypesnew']) {
        $row = $db->getRow('SELECT id FROM logtypesnew WHERE id = ? AND del_status = 1', [$resolved]);
        if (empty($row)) {
            $resolved = 3;
        }
    } elseif ($tableCache['logtypes']) {
        $row = $db->getRow('SELECT id FROM logtypes WHERE id = ? AND status = 1', [$resolved]);
        if (empty($row)) {
            $resolved = 3;
        }
    }

    return $resolved;
}

function xlog($action, $details = '', $entityId = null, $admin_id = null, $db = null, $oldValue = null, $newValue = null, $entityType = null)
{
    if ($db === null) {
        global $db;
        if (!isset($db) || $db === null) {
            try {
                $db = new Database();
            } catch (Exception $e) {
                error_log('xlog: Database bağlantı hatası - ' . $e->getMessage());
                return false;
            }
        }
    }

    if ($admin_id === null) {
        $admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if ($path === null) {
        $path = '';
    }
    $path = substr($path, 0, 255);

    $action = substr(trim((string)$action), 0, 255);
    if ($action === '') {
        $action = 'UNKNOWN';
    }

    $statusMap = [
        'ERROR' => 'error',
        'LOGIN_FAILED' => 'error',
        'GIRIS_BASARISIZ' => 'error',
        'WARNING' => 'warning',
        'DELETE' => 'warning',
        'SUCCESS' => 'success',
        'LOGIN_SUCCESS' => 'success',
        'GIRIS_BASARILI' => 'success',
        'UPDATE' => 'success',
        'CREATE' => 'success',
        'INSERT' => 'success',
    ];
    $logStatus = $statusMap[strtoupper($action)] ?? 'info';

    $logDescription = substr($action, 0, 500);
    if ($details !== '' && !is_array($details)) {
        $detailsStr = (string)$details;
        if (strlen($logDescription) + strlen($detailsStr) + 3 <= 500) {
            $logDescription .= ' - ' . $detailsStr;
        }
    }
    $logDescription = substr($logDescription, 0, 500);

    $additional = null;
    if (is_array($details)) {
        $additional = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    } elseif ($details !== '' && is_string($details) && strlen($details) > 100) {
        $additional = substr((string)$details, 0, 65535);
    }
    if ($additional !== null && strlen($additional) > 65535) {
        $additional = substr($additional, 0, 65535);
    }

    $entityId = $entityId ? (int)$entityId : null;
    $entityType = $entityType ? substr(trim((string)$entityType), 0, 100) : null;

    $oldVal = null;
    if ($oldValue !== null) {
        if (is_array($oldValue) || is_object($oldValue)) {
            $oldVal = json_encode($oldValue, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        } else {
            $oldVal = (string)$oldValue;
        }
        if (strlen($oldVal) > 65535) {
            $oldVal = substr($oldVal, 0, 65535);
        }
    }

    $newVal = null;
    if ($newValue !== null) {
        if (is_array($newValue) || is_object($newValue)) {
            $newVal = json_encode($newValue, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        } else {
            $newVal = (string)$newValue;
        }
        if (strlen($newVal) > 65535) {
            $newVal = substr($newVal, 0, 65535);
        }
    }

    try {
        $tableCache = xlogGetTableCache($db);
        $logType = xlogResolveType($db, $action, $tableCache);

        if ($tableCache['lognew']) {
            return $db->Insert(
                'INSERT INTO lognew (
                    LogType, LogDesc, LogStatus, UserID, IpAddress, UserAgent,
                    RequestMethod, RequestPath, OldValue, NewValue,
                    EntityType, EntityID, AdditionalData
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $logType,
                    $logDescription,
                    $logStatus,
                    $admin_id,
                    $ip,
                    $ua,
                    $method,
                    $path,
                    $oldVal,
                    $newVal,
                    $entityType,
                    $entityId,
                    $additional
                ]
            );
        }

        if ($tableCache['yonetici_loglari']) {
            return $db->Insert(
                'INSERT INTO yonetici_loglari (
                    log_type, log_description, log_status, admin_id, ip_address, user_agent,
                    request_method, request_path, old_value, new_value,
                    entity_type, entity_id, additional_data, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DEFAULT)',
                [
                    $logType,
                    $logDescription,
                    $logStatus,
                    $admin_id,
                    $ip,
                    $ua,
                    $method,
                    $path,
                    $oldVal,
                    $newVal,
                    $entityType,
                    $entityId,
                    $additional
                ]
            );
        }

        if ($tableCache['admin_logs']) {
            return $db->Insert(
                'INSERT INTO admin_logs (
                    log_type, log_description, log_status, admin_id, ip_address, user_agent,
                    request_method, request_path, old_value, new_value,
                    entity_type, entity_id, additional_data, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DEFAULT)',
                [
                    $logType,
                    $logDescription,
                    $logStatus,
                    $admin_id,
                    $ip,
                    $ua,
                    $method,
                    $path,
                    $oldVal,
                    $newVal,
                    $entityType,
                    $entityId,
                    $additional
                ]
            );
        }

        error_log('xlog: Uygun log tablosu bulunamadı (lognew/yonetici_loglari/admin_logs).');
        return false;
    } catch (Exception $e) {
        error_log('xlog Hatası: ' . $e->getMessage());
        return false;
    }
}

function xlogSimple($action, $details = '')
{
    return xlog($action, $details);
}

?>