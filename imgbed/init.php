<?php
/**
 * 图床初始化脚本
 * 检查环境并创建必要的目录
 */

// 禁止通过HTTP直接访问
if (isset($_SERVER['REQUEST_METHOD'])) {
    die('不允许通过HTTP直接访问此脚本');
}

echo "开始初始化图床应用...\n\n";

// 检查PHP版本
echo "检查PHP版本... ";
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    echo "失败\n";
    echo "PHP版本要求8.0.0或更高，当前版本为" . PHP_VERSION . "\n";
    exit(1);
} else {
    echo "通过 (PHP " . PHP_VERSION . ")\n";
}

// 检查必要的PHP扩展
echo "检查必要的PHP扩展...\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'fileinfo', 'json'];
$missing = [];

foreach ($requiredExtensions as $ext) {
    echo " - $ext: ";
    if (extension_loaded($ext)) {
        echo "已安装\n";
    } else {
        echo "未安装\n";
        $missing[] = $ext;
    }
}

if (!empty($missing)) {
    echo "\n错误: 缺少以下PHP扩展: " . implode(', ', $missing) . "\n";
    exit(1);
}

// 创建上传目录
echo "\n创建上传目录结构...\n";
$config = require __DIR__ . '/config/config.php';
$uploadPath = $config['upload']['storage_path'];

if (!is_dir($uploadPath)) {
    if (mkdir($uploadPath, 0777, true)) {
        echo "成功创建上传目录: $uploadPath\n";
    } else {
        echo "错误: 无法创建上传目录: $uploadPath\n";
        exit(1);
    }
} else {
    echo "上传目录已存在: $uploadPath\n";
}

// 检查上传目录权限
echo "检查上传目录权限... ";
if (is_writable($uploadPath)) {
    echo "可写\n";
} else {
    echo "不可写\n";
    echo "请确保Web服务器对此目录有写入权限\n";
    echo "可以运行: chmod -R 755 $uploadPath\n";
    exit(1);
}

// 创建年月日子目录（示例）
echo "创建日期子目录（示例）... ";
$datePath = $uploadPath . date('Y/m/d');
if (!is_dir($datePath)) {
    if (mkdir($datePath, 0777, true)) {
        echo "成功\n";
    } else {
        echo "失败，但不影响程序运行，将在上传时自动创建\n";
    }
} else {
    echo "已存在\n";
}

echo "\n初始化完成！\n";
echo "现在，您可以通过访问 /install 页面完成数据库配置。\n";
echo "或者手动编辑 config/config.php 文件设置数据库信息。\n\n";

exit(0); 