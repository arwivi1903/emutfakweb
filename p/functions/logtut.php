<?php
    /**
     * PROLYN ERP - Logging (admin_logs)
     * Basit API: xlog($action, $details = '', $entityId = null, $admin_id = null, $db = null, $oldValue = null, $newValue = null, $entityType = null)
     * Hızlı API: xlogSimple($action, $details = '')
     */

    function xlog($action, $details = '', $entityId = null, $admin_id = null, $db = null, $oldValue = null, $newValue = null, $entityType = null)
    {
        // DB
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

        // User
        if ($admin_id === null) {
            $admin_id = isset($_SESSION['admin_id']) ? intval($_SESSION['admin_id']) : 0;
        }

        // Env
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        if ($path === null) {
            $path = '';
        }
        $path = substr($path, 0, 255);

        // Normalize action
        $action = substr(trim((string)$action), 0, 255);
        if ($action === '') { $action = 'UNKNOWN'; }

        // Maps
        $typeMap = [
            'ERROR' => 1,
            'LOGIN' => 1,
            'LOGOUT' => 1,
            'WARNING' => 2,
            'DELETE' => 2,
            'ADMIN' => 3,
            'SUCCESS' => 3,
            'CREATE' => 3,
            'UPDATE' => 3,
            'INFO' => 4,
            'DEBUG' => 5,
            'LOGIN_FAILED' => 2,
            'LOGIN_SUCCESS' => 3,
            'GIRIS_BASARISIZ' => 2,
            'GIRIS_BASARILI' => 3,
        ];
        $logType = $typeMap[strtoupper($action)] ?? 3; // Default: Admin Action

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
        ];
        $logStatus = $statusMap[strtoupper($action)] ?? 'info';

        // Log açıklaması - action'ı kullan, eğer details string ise onu da ekle
        $logDescription = substr($action, 0, 500);
        if ($details !== '' && !is_array($details)) {
            $detailsStr = (string)$details;
            if (strlen($logDescription) + strlen($detailsStr) + 3 <= 500) {
                $logDescription = $logDescription . ' - ' . $detailsStr;
            }
        }
        $logDescription = substr($logDescription, 0, 500);

        // Ek Veriler (AdditionalData)
        $additional = null;
        if (is_array($details)) {
            $additional = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        } elseif ($details !== '' && is_string($details) && strlen($details) > 100) {
            // Eğer details çok uzunsa additional_data'ya da ekle
            $additional = substr((string)$details, 0, 65535); // mediumtext limit
        }
        if ($additional !== null && strlen($additional) > 65535) {
            $additional = substr($additional, 0, 65535);
        }

        // Varlık (Entity)
        $entityId = $entityId ? intval($entityId) : null;
        $entityType = $entityType ? substr(trim((string)$entityType), 0, 100) : null;

        // Eski/Yeni Değerler (Old/New Values) - mediumtext için hazırla
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