<?php
    /**
     * EMUTFAK ERP - Logging (admin_logsnew)
     * Basit API: xlog($action, $details = '', $entityId = null, $admin_id = null, $db = null)
     * Hızlı API: xlogSimple($action, $details = '')
     */

    function xlog($action, $details = '', $entityId = null, $admin_id = null, $db = null)
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
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

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
        $logType = $typeMap[strtoupper($action)] ?? 0;

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

        // AdditionalData
        $additional = null;
        if (is_array($details)) {
            $additional = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        } elseif ($details !== '') {
            $additional = (string)$details;
        }
        if ($additional !== null) {
            $additional = substr($additional, 0, 2000);
        }

        // Entity
        $entityId = $entityId ? intval($entityId) : null;
        $entityType = null; // sade API
        $oldValue = null;
        $newValue = null;

        try {
            return $db->Insert(
                'INSERT INTO admin_logsnew (
                    LogType, LogDesc, LogStatus, UserID, IpAddress, UserAgent,
                    RequestMethod, RequestPath, OldValue, NewValue,
                    EntityType, EntityID, AdditionalData, LogDate
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
                [
                    $logType,
                    $action,
                    $logStatus,
                    $admin_id,
                    $ip,
                    $ua,
                    $method,
                    $path,
                    $oldValue,
                    $newValue,
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