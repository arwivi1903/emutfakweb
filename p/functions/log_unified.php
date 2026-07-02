<?php

/**
 * Aktif tenant DB + master DB loglarini tek listede birlestirir.
 *
 * Donen satir formati:
 * - log_id
 * - log_type
 * - log_description
 * - log_status
 * - admin_id
 * - ip_address
 * - user_agent
 * - request_method
 * - request_path
 * - old_value
 * - new_value
 * - entity_type
 * - entity_id
 * - additional_data
 * - created_at
 * - source_db
 * - source_table
 */
function unified_logs_get_master_db_name(): string
{
    static $masterDb = null;

    if ($masterDb !== null) {
        return $masterDb;
    }

    $masterDb = 'prolyn_master';
    $configPath = __DIR__ . '/../config/db_config.php';
    if (file_exists($configPath)) {
        $cfg = require $configPath;
        if (is_array($cfg) && !empty($cfg['db'])) {
            $masterDb = (string)$cfg['db'];
        }
    }

    return $masterDb;
}

function unified_logs_normalize_row($row, string $sourceDb, string $sourceTable): object
{
    $obj = new stdClass();
    $obj->log_id = $row->log_id ?? $row->LogID ?? null;
    $obj->log_type = (int)($row->log_type ?? $row->LogType ?? 0);
    $obj->log_description = (string)($row->log_description ?? $row->LogDesc ?? '');
    $obj->log_status = (string)($row->log_status ?? $row->LogStatus ?? 'info');
    $obj->admin_id = (int)($row->admin_id ?? $row->UserID ?? 0);
    $obj->ip_address = (string)($row->ip_address ?? $row->IpAddress ?? '');
    $obj->user_agent = (string)($row->user_agent ?? $row->UserAgent ?? '');
    $obj->request_method = (string)($row->request_method ?? $row->RequestMethod ?? 'GET');
    $obj->request_path = (string)($row->request_path ?? $row->RequestPath ?? '/');
    $obj->old_value = $row->old_value ?? $row->OldValue ?? null;
    $obj->new_value = $row->new_value ?? $row->NewValue ?? null;
    $obj->entity_type = (string)($row->entity_type ?? $row->EntityType ?? '');
    $obj->entity_id = $row->entity_id ?? $row->EntityID ?? null;
    $obj->additional_data = $row->additional_data ?? $row->AdditionalData ?? null;
    $obj->created_at = (string)($row->created_at ?? $row->LogDate ?? '');
    $obj->source_db = $sourceDb;
    $obj->source_table = $sourceTable;

    return $obj;
}

function unified_logs_fetch_from_db(Database $db, int $adminId = 0, int $limit = 500): array
{
    $items = [];
    $dbName = (string)($db->MYSQL_DB ?? 'unknown_db');

    if (method_exists($db, 'tableExists') && $db->tableExists('lognew')) {
        $sql = 'SELECT
                    LogID, LogType, LogDesc, LogStatus, UserID,
                    IpAddress, UserAgent, RequestMethod, RequestPath,
                    OldValue, NewValue, EntityType, EntityID, AdditionalData, LogDate
                FROM lognew';
        $params = [];

        if ($adminId > 0) {
            $sql .= ' WHERE UserID = ?';
            $params[] = $adminId;
        }

        $sql .= ' ORDER BY LogDate DESC LIMIT ' . (int)$limit;
        $rows = $db->getRows($sql, $params);

        foreach ($rows as $row) {
            $items[] = unified_logs_normalize_row($row, $dbName, 'lognew');
        }
    } elseif (method_exists($db, 'tableExists') && $db->tableExists('yonetici_loglari')) {
        $sql = 'SELECT
                    log_id, log_type, log_description, log_status, admin_id,
                    ip_address, user_agent, request_method, request_path,
                    old_value, new_value, entity_type, entity_id, additional_data, created_at
                FROM yonetici_loglari';
        $params = [];

        if ($adminId > 0) {
            $sql .= ' WHERE admin_id = ?';
            $params[] = $adminId;
        }

        $sql .= ' ORDER BY created_at DESC LIMIT ' . (int)$limit;
        $rows = $db->getRows($sql, $params);

        foreach ($rows as $row) {
            $items[] = unified_logs_normalize_row($row, $dbName, 'yonetici_loglari');
        }
    }

    return $items;
}

function unified_logs_fetch(int $adminId = 0, int $limit = 500, bool $includeMaster = true): array
{
    $all = [];

    $tenantDbName = (string)($_SESSION['database_name'] ?? unified_logs_get_master_db_name());
    $tenantDb = new Database($tenantDbName);
    $all = array_merge($all, unified_logs_fetch_from_db($tenantDb, $adminId, $limit));

    if ($includeMaster) {
        $masterDbName = unified_logs_get_master_db_name();
        if ($masterDbName !== $tenantDbName) {
            $masterDb = new Database($masterDbName);
            $all = array_merge($all, unified_logs_fetch_from_db($masterDb, $adminId, $limit));
        }
    }

    usort($all, static function ($a, $b) {
        $t1 = strtotime((string)$a->created_at);
        $t2 = strtotime((string)$b->created_at);
        return $t2 <=> $t1;
    });

    if ($limit > 0 && count($all) > $limit) {
        $all = array_slice($all, 0, $limit);
    }

    return $all;
}
