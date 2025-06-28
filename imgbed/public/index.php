<?php
/**
 * 图床入口文件
 * PHP 8.4 兼容
 */

// 优化：禁用PHP默认的会话缓存头，以便手动精细控制
session_cache_limiter('');
// 优化：开启Gzip压缩来减小页面体积
@ob_start('ob_gzhandler');

// 启动会话
session_start();

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

// 动态设置网站基础URL（如果配置中未指定）
if (empty($config['site']['base_url'])) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name = $_SERVER['SCRIPT_NAME'];
    $path = rtrim(dirname($script_name), '/\\');

    // 如果应用在子目录，则包含子目录路径
    if ($path === '/' || $path === '' || $path === '\\') {
        $config['site']['base_url'] = $protocol . $host;
    } else {
        $config['site']['base_url'] = $protocol . $host . $path;
    }
}

// 检查是否需要清理过期文件
$lastCheck = isset($_SESSION['last_cleanup_check']) ? $_SESSION['last_cleanup_check'] : 0;
$currentTime = time();

if ($currentTime - $lastCheck > $config['expiration']['check_interval']) {
    $_SESSION['last_cleanup_check'] = $currentTime;
    
    // 清理过期文件（后台执行）
    if (function_exists('fastcgi_finish_request')) {
        // 允许请求先返回给用户，然后再执行耗时操作
        register_shutdown_function(function() use ($config) {
            $mediaHandler = new \ImgBed\MediaHandler($config);
            // 清理并删除所有过期文件
            $mediaHandler->cleanupExpiredFiles();
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
        header('Cache-Control: public, max-age=300'); // 缓存5分钟
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 300) . ' GMT');
        require __DIR__ . '/templates/upload.php';
        break;
    
    // 关于页面    
    case $path == '/about':
        header('Cache-Control: public, max-age=3600'); // 缓存1小时
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
        require __DIR__ . '/templates/about.php';
        break;
        
    // 管理页面
    case $path == '/admin':
        // 管理页面包含敏感信息，禁止公共缓存
        header('Cache-Control: private, no-cache, no-store, must-revalidate');
        header('Expires: 0');
        header('Pragma: no-cache');
        require __DIR__ . '/templates/admin.php';
        break;
        
    // 文件查看
    case preg_match('#^/file/([a-z0-9]{32})(?:/.*)?$#i', $path, $matches):
        $fileId = $matches[1];
        $mediaHandler = new \ImgBed\MediaHandler($config);
        $file = $mediaHandler->getFile($fileId);
        
        if ($file) {
            $filePath = $config['upload']['storage_path'] . $file['filepath'];
            
            if (file_exists($filePath)) {
                // 根据配置策略决定缓存头
                $policy = $config['expiration']['expiration_policy'] ?? 'views';
                
                if ($policy === 'permanent') {
                    // 永久缓存策略：设置长期缓存头，对CDN友好
                    $cacheSeconds = 315360000; // 10 years
                    header('Cache-Control: public, max-age=' . $cacheSeconds . ', immutable');
                    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheSeconds) . ' GMT');
                    
                    if (!empty($file['filehash'])) {
                        header('ETag: "' . $file['filehash'] . '"');
                        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') == $file['filehash']) {
                            header("HTTP/1.1 304 Not Modified");
                            exit;
                        }
                    }
                } else {
                    // 浏览次数策略：禁止缓存，确保每次都计数
                    header('Cache-Control: no-store, no-cache, must-revalidate');
                    header('Expires: 0');
                }

                // 设置内容类型头
                header('Content-Type: ' . $file['filetype']);
                header('Content-Length: ' . $file['filesize']);
                header('Content-Disposition: inline; filename="' . htmlspecialchars($file['filename'], ENT_QUOTES, 'UTF-8') . '"');
                
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
            $mediaHandler = new \ImgBed\MediaHandler($config);
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
            $adminPassword = $_POST['admin_password'] ?? 'admin123';
            
            if (empty(trim($adminPassword))) {
                throw new \Exception('管理员密码不能为空');
            }

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
                'admin' => [
                    'password' => $adminPassword,
                    'session_expire' => 3600, // 会话过期时间（秒）
                ],
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
        
    // 处理管理设置更新
    case $path == '/admin/settings' && $_SERVER['REQUEST_METHOD'] === 'POST':
        // 1. 验证管理员身份
        $is_authorized = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
        if (!$is_authorized) {
            // 如果未授权，可以重定向到登录页或显示错误
            header('Location: /admin');
            exit;
        }

        // 2. 获取并验证新设置
        $new_policy = isset($_POST['cdn_friendly_mode']) && $_POST['cdn_friendly_mode'] === 'permanent' ? 'permanent' : 'views';
        
        // 使用filter_input进行安全的整数验证
        $max_views = filter_input(INPUT_POST, 'max_views', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $max_days = filter_input(INPUT_POST, 'max_days', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // 3. 安全地更新配置文件
        $configFilePath = __DIR__ . '/../config/config.php';
        if (file_exists($configFilePath) && is_writable($configFilePath)) {
            // 读取现有配置
            $currentConfig = require $configFilePath;
            
            // 更新策略值
            $currentConfig['expiration']['expiration_policy'] = $new_policy;
            
            // 如果验证通过，则更新值
            if ($max_views !== false) {
                $currentConfig['expiration']['max_views'] = $max_views;
            }
            if ($max_days !== false) {
                $currentConfig['expiration']['max_days'] = $max_days;
            }
            
            // 将更新后的配置数组转换为格式化的PHP代码字符串
            $newConfigContent = '<?php' . PHP_EOL . 'return ' . var_export($currentConfig, true) . ';';
            
            // 写回文件
            file_put_contents($configFilePath, $newConfigContent, LOCK_EX);
        }

        // 4. 重定向回管理页面
        header('Location: /admin');
        exit;
        
    // 处理"删除所有本地文件"
    case $path == '/admin/delete_all_files' && $_SERVER['REQUEST_METHOD'] === 'POST':
        // 1. 验证管理员身份
        $is_authorized = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
        if (!$is_authorized) {
            header('Location: /admin');
            exit;
        }

        // 2. 执行删除操作
        $mediaHandler = new \ImgBed\MediaHandler($config);
        $deleted_count = $mediaHandler->deleteAllLocalFiles();

        // 3. 将结果存储在会话中，以便在管理页面显示
        $_SESSION['last_action_result'] = [
            'success' => true,
            'message' => "操作成功，已删除 {$deleted_count} 个本地文件。"
        ];

        // 4. 重定向回管理页面
        header('Location: /admin');
        exit;
        
    // 404页面
    default:
        // 404页面可以短时间缓存，避免爬虫等造成过大压力
        header('Cache-Control: public, max-age=60'); // 缓存1分钟
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 60) . ' GMT');
        header('HTTP/1.0 404 Not Found');
        require __DIR__ . '/templates/404.php';
        break;
}