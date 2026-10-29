<?php
/**
 * Dashboard Cache Helper
 * Simple file-based caching for dashboard metrics
 */

class DashboardCache {
    private static $cacheDir;
    
    public static function init() {
        // Use project directory instead of system temp to avoid permission issues
        self::$cacheDir = __DIR__ . '/../cache/dashboard';
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    /**
     * Get cached data or execute callable and cache result
     * @param string $key - Cache key
     * @param callable $callable - Function to execute if cache miss
     * @param int $ttl - Time to live in seconds (default 5 minutes)
     * @return mixed - Cached or fresh data
     */
    public static function remember($key, $callable, $ttl = 300) {
        self::init();
        
        $cacheFile = self::$cacheDir . '/' . md5($key) . '.cache';
        
        // Check if cache exists and is still valid
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
            $data = file_get_contents($cacheFile);
            return json_decode($data, true);
        }
        
        // Cache miss - execute callable
        $data = $callable();
        
        // Store in cache
        file_put_contents($cacheFile, json_encode($data));
        
        return $data;
    }
    
    /**
     * Clear specific cache key
     * @param string $key - Cache key to clear
     */
    public static function forget($key) {
        self::init();
        $cacheFile = self::$cacheDir . '/' . md5($key) . '.cache';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }
    
    /**
     * Clear all dashboard cache
     */
    public static function flush() {
        self::init();
        $files = glob(self::$cacheDir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    /**
     * Get cache statistics
     * @return array - Cache stats
     */
    public static function stats() {
        self::init();
        $files = glob(self::$cacheDir . '/*.cache');
        $totalSize = 0;
        $oldestFile = null;
        $newestFile = null;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            $mtime = filemtime($file);
            
            if ($oldestFile === null || $mtime < filemtime($oldestFile)) {
                $oldestFile = $file;
            }
            if ($newestFile === null || $mtime > filemtime($newestFile)) {
                $newestFile = $file;
            }
        }
        
        return [
            'total_files' => count($files),
            'total_size_kb' => round($totalSize / 1024, 2),
            'oldest_cache' => $oldestFile ? date('Y-m-d H:i:s', filemtime($oldestFile)) : null,
            'newest_cache' => $newestFile ? date('Y-m-d H:i:s', filemtime($newestFile)) : null
        ];
    }
}

// Usage example:
/*
require_once 'classes/dashboard_cache.class.php';

// In index.php
$revenueChartData = DashboardCache::remember('revenue_chart_30d', function() {
    return getRevenueChartData('30days');
}, 300); // Cache for 5 minutes

// Clear cache when data changes (e.g., after payment)
DashboardCache::forget('revenue_chart_30d');

// Clear all cache
DashboardCache::flush();
*/
