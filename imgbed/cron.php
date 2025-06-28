<?php
/**
 * 图床清理脚本
 * 定期清理过期文件
 * 建议通过Cron每小时运行一次:
 * 0 * * * * php /path/to/cron.php
 */

// 禁止通过HTTP直接访问
if (isset($_SERVER['REQUEST_METHOD'])) {
    die('不允许通过HTTP直接访问此脚本');
}

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 引入自动加载器
require_once __DIR__ . '/src/Autoloader.php';
\ImgBed\Autoloader::register();

// 清理文件
try {
    echo "开始清理过期文件...\n";
    
    $mediaHandler = new \ImgBed\MediaHandler();
    
    // 清理过期文件
    $expiredCount = $mediaHandler->cleanupExpiredFiles();
    echo "已标记 {$expiredCount} 个过期文件为删除状态\n";
    
    // 删除已标记的文件
    $deletedCount = $mediaHandler->deleteMarkedFiles();
    echo "已物理删除 {$deletedCount} 个文件\n";
    
    echo "清理完成！\n";
} catch (Exception $e) {
    echo "清理过程中发生错误: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0); 