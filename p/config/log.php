<?php

/**
 * prolynweb - Logging Konfigürasyonu
 * 
 * Sistemin tamamında kullanılacak logging ayarları
 */

// Log dosya yolu
define('LOG_FILE_PATH', '/Applications/XAMPP/xamppfiles/logs/prolynweb.log');
define('LOG_ERROR_PATH', '/Applications/XAMPP/xamppfiles/logs/prolynweb_error.log');
define('LOG_DEBUG_PATH', '/Applications/XAMPP/xamppfiles/logs/prolynweb_debug.log');

// Log seviyeleri
define('LOG_LEVEL_DEBUG', 1);
define('LOG_LEVEL_INFO', 2);
define('LOG_LEVEL_WARNING', 3);
define('LOG_LEVEL_ERROR', 4);

// Aktif log seviyesi (daha düşük seviyeleri de kaydeder)
define('ACTIVE_LOG_LEVEL', LOG_LEVEL_DEBUG);

/**
 * Geliştirilmiş Logger Sınıfı
 */
class Logger {
    private static $instance = null;
    
    private function __construct() {
        $this->initLogFiles();
    }
    
    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Log dosyalarını başlat
     */
    private function initLogFiles() {
        $logDirs = [LOG_FILE_PATH, LOG_ERROR_PATH, LOG_DEBUG_PATH];
        
        foreach($logDirs as $logFile) {
            if(!file_exists($logFile)) {
                $dir = dirname($logFile);
                if(!is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }
                @touch($logFile);
                @chmod($logFile, 0666);
            }
        }
    }
    
    /**
     * Log yazma
     * 
     * @param string $message Mesaj
     * @param int $level Log seviyesi
     * @param string $file Dosya adı (opsiyonel)
     */
    public function write($message, $level = LOG_LEVEL_INFO, $file = null) {
        if($level < ACTIVE_LOG_LEVEL) {
            return false;
        }
        
        if($file === null) {
            $file = LOG_FILE_PATH;
        }
        
        $logFile = match($level) {
            LOG_LEVEL_DEBUG => LOG_DEBUG_PATH,
            LOG_LEVEL_ERROR => LOG_ERROR_PATH,
            default => $file
        };
        
        $timestamp = date('Y-m-d H:i:s');
        $levelName = $this->getLevelName($level);
        $userID = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'unknown';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $logEntry = sprintf(
            "[%s] [%s] [User: %s] [IP: %s] %s\n",
            $timestamp,
            $levelName,
            $userID,
            $ip,
            $message
        );
        
        @error_log($logEntry, 3, $logFile);
        return true;
    }
    
    /**
     * Seviye ismini al
     */
    private function getLevelName($level) {
        return match($level) {
            LOG_LEVEL_DEBUG => 'DEBUG',
            LOG_LEVEL_INFO => 'INFO',
            LOG_LEVEL_WARNING => 'WARNING',
            LOG_LEVEL_ERROR => 'ERROR',
            default => 'UNKNOWN'
        };
    }
    
    /**
     * Debug log
     */
    public function debug($message) {
        return $this->write($message, LOG_LEVEL_DEBUG);
    }
    
    /**
     * Info log
     */
    public function info($message) {
        return $this->write($message, LOG_LEVEL_INFO);
    }
    
    /**
     * Warning log
     */
    public function warning($message) {
        return $this->write($message, LOG_LEVEL_WARNING);
    }
    
    /**
     * Error log
     */
    public function error($message) {
        return $this->write($message, LOG_LEVEL_ERROR);
    }
}

// Singleton instance
$logger = Logger::getInstance();

?>
