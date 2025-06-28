<?php
/**
 * 图床入口文件
 * PHP 8.4 兼容
 */

// 显示错误信息（生产环境中应关闭）
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 引入自动加载器
require_once __DIR__ . '/../src/Autoloader.php';
\ImgBed\Autoloader::register();

// --- Installation Lock ---
$lockFilePath = __DIR__ . '/../config/install.lock';
$requestUriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/' && $basePath !== '\\') {
    $requestUriPath = substr($requestUriPath, strlen($basePath));
}

if (file_exists($lockFilePath)) {
    // 如果锁文件存在，阻止访问安装页面
    if (in_array($requestUriPath, ['/install', '/do-install'])) {
        header('Location: ' . $basePath);
        exit;
    }
} else {
    // 如果锁文件不存在，强制跳转到安装页面
    if (!in_array($requestUriPath, ['/install', '/do-install'])) {
        header('Location: ' . rtrim($basePath, '/') . '/install');
        exit;
    }
}

// 初始化应用
$config = require __DIR__ . '/../config/config.php';

// 检查是否需要清理过期文件
$lastCheck = isset($_SESSION['last_cleanup_check']) ? $_SESSION['last_cleanup_check'] : 0;
$currentTime = time();

if ($currentTime - $lastCheck > $config['expiration']['check_interval']) {
    $_SESSION['last_cleanup_check'] = $currentTime;
    
    // 清理过期文件（后台执行）
    if (function_exists('fastcgi_finish_request')) {
        // 允许请求先返回给用户，然后再执行耗时操作
        register_shutdown_function(function() {
            $mediaHandler = new \ImgBed\MediaHandler();
            $mediaHandler->cleanupExpiredFiles();
            $mediaHandler->deleteMarkedFiles();
        });
    }
}

// 路由处理
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = dirname($_SERVER['SCRIPT_NAME']);

// 如果网站不在根目录，去除基础路径
if ($basePath !== '/' && $basePath !== '\\') {
    $requestUri = substr($requestUri, strlen($basePath));
}

// 分割路径和查询参数
$uriParts = explode('?', $requestUri);
$path = $uriParts[0];

// 路由配置
switch (true) {
    // 首页（上传页面）
    case $path == '/' || $path == '':
        require __DIR__ . '/templates/upload.php';
        break;
        
    // 文件查看
    case preg_match('#^/file/([a-z0-9]{32})(?:/.*)?$#i', $path, $matches):
        $fileId = $matches[1];
        $mediaHandler = new \ImgBed\MediaHandler();
        $file = $mediaHandler->getFile($fileId);
        
        if ($file) {
            $filePath = $config['upload']['storage_path'] . $file['filepath'];
            
            if (file_exists($filePath)) {
                // 设置内容类型头
                header('Content-Type: ' . $file['filetype']);
                header('Content-Length: ' . $file['filesize']);
                header('Content-Disposition: inline; filename="' . $file['filename'] . '"');
                
                // 输出文件内容
                readfile($filePath);
                exit;
            }
        }
        
        // 文件不存在
        header('HTTP/1.0 404 Not Found');
        require __DIR__ . '/templates/404.php';
        break;
        
    // 处理上传
    case $path == '/upload' && $_SERVER['REQUEST_METHOD'] === 'POST':
        $response = ['success' => false];
        
        if (isset($_FILES['file'])) {
            $mediaHandler = new \ImgBed\MediaHandler();
            $result = $mediaHandler->uploadFile($_FILES['file']);
            
            if (isset($result['error'])) {
                $response['error'] = $result['error'];
            } else {
                $response = [
                    'success' => true,
                    'file' => $result
                ];
            }
        } else {
            $response['error'] = '未接收到文件';
        }
        
        // 返回JSON响应
        header('Content-Type: application/json');
        echo json_encode($response);
        break;
        
    // 安装页面
    case $path == '/install':
        require __DIR__ . '/templates/install.php';
        break;
        
    // 执行安装
    case $path == '/do-install' && $_SERVER['REQUEST_METHOD'] === 'POST':
        $response = ['success' => false];
        
        // 验证数据库连接
        try {
            $dbHost = $_POST['db_host'] ?? 'localhost';
            $dbUser = $_POST['db_user'] ?? '';
            $dbPass = $_POST['db_pass'] ?? '';
            $dbName = $_POST['db_name'] ?? '';
            $dbPrefix = $_POST['db_prefix'] ?? 'imgbed_';
            
            // 尝试连接数据库
            $pdo = new PDO(
                "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
                $dbUser,
                $dbPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // 读取SQL文件
            $sql = file_get_contents(__DIR__ . '/../database/schema.sql');
            
            // 替换前缀
            $sql = str_replace('{prefix}', $dbPrefix, $sql);
            
            // 执行SQL
            $pdo->exec($sql);
            
            // --- 更稳健的配置写入 ---
            $newConfigContent = '<?php return ' . var_export([
                'db' => [
                    'host' => $dbHost,
                    'username' => $dbUser,
                    'password' => $dbPass,
                    'dbname' => $dbName,
                    'charset' => 'utf8mb4',
                    'prefix' => $dbPrefix,
                ],
                'upload' => $config['upload'],
                'expiration' => $config['expiration'],
                'site' => $config['site'],
            ], true) . ';';
            
            file_put_contents(__DIR__ . '/../config/config.php', $newConfigContent);
            
            // 创建锁文件
            file_put_contents($lockFilePath, date('Y-m-d H:i:s'));
            
            $response = ['success' => true];
        } catch (PDOException $e) {
            $response['error'] = '数据库连接失败: ' . $e->getMessage();
        } catch (Exception $e) {
            $response['error'] = '安装失败: ' . $e->getMessage();
        }
        
        // 返回JSON响应
        header('Content-Type: application/json');
        echo json_encode($response);
        break;
        
    // 404页面
    default:
        header('HTTP/1.0 404 Not Found');
        require __DIR__ . '/templates/404.php';
        break;
}