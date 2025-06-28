<?php
// ##################################################################
// ##  所有PHP逻辑必须在任何HTML输出之前处理（包括header.php） ##
// ##################################################################

// 登录处理
$login_error = null;
if (isset($_POST['login'])) {
    $input_password = $_POST['password'] ?? '';
    if ($input_password === $config['admin']['password']) {
        // 登录成功
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        
        // 重定向以避免表单重复提交和URL中的查询参数
        header('Location: /admin');
        exit;
    } else {
        $login_error = '密码不正确，请重试';
    }
}

// 注销处理
if (isset($_GET['logout'])) {
    // 清除会话
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_login_time']);
    
    // 重定向到管理页面
    header('Location: /admin');
    exit;
}

// 检查会话过期
if (isset($_SESSION['admin_logged_in']) && isset($_SESSION['admin_login_time'])) {
    $session_lifetime = time() - $_SESSION['admin_login_time'];
    if ($session_lifetime > $config['admin']['session_expire']) {
        // 会话已过期
        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['admin_login_time']);
        $login_error = '会话已过期，请重新登录';
    } else {
        // 更新登录时间
        $_SESSION['admin_login_time'] = time();
    }
}

// 检查是否已登录
$is_authorized = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// 检查并显示上次操作的结果
$last_action_result = null;
if (isset($_SESSION['last_action_result'])) {
    $last_action_result = $_SESSION['last_action_result'];
    unset($_SESSION['last_action_result']); // 显示后立即清除，防止刷新时重复显示
}

// 处理删除操作
$deletion_result = null;
if ($is_authorized && isset($_POST['action'])) {
    $mediaHandler = new \ImgBed\MediaHandler($config);
    
    if ($_POST['action'] === 'cleanup_expired') {
        // 清理并删除所有过期文件
        $deleted_count = $mediaHandler->cleanupExpiredFiles();
        $deletion_result = [
            'success' => true,
            'deleted_count' => $deleted_count,
            'message' => "已成功清理并删除 {$deleted_count} 个过期文件"
        ];
    }
}

// 获取当前存储统计
$storage_stats = [];
if ($is_authorized) {
    $db = \ImgBed\Database::getInstance();
    $total_files = $db->fetch("SELECT COUNT(*) as count FROM {prefix}media WHERE deleted = 0");
    $total_files_count = $total_files ? $total_files['count'] : 0;
    
    // 注意：由于新逻辑不再使用'deleted'标记，此统计仅用于展示可能存在的旧数据
    $marked_files = $db->fetch("SELECT COUNT(*) as count FROM {prefix}media WHERE deleted = 1");
    $marked_files_count = $marked_files ? $marked_files['count'] : 0;
    
    $expired_views = $db->fetch("SELECT COUNT(*) as count FROM {prefix}media WHERE deleted = 0 AND views >= :max_views", [
        'max_views' => $config['expiration']['max_views']
    ]);
    $expired_views_count = $expired_views ? $expired_views['count'] : 0;
    
    $expired_days = $db->fetch("SELECT COUNT(*) as count FROM {prefix}media WHERE deleted = 0 AND created_at < :expiration_date", [
        'expiration_date' => date('Y-m-d H:i:s', strtotime("-{$config['expiration']['max_days']} days"))
    ]);
    $expired_days_count = $expired_days ? $expired_days['count'] : 0;
    
    $storage_stats = [
        'total_files' => $total_files_count,
        'marked_files' => $marked_files_count,
        'expired_views' => $expired_views_count,
        'expired_days' => $expired_days_count
    ];
}

// ##################################################################
// ##  PHP逻辑处理结束，现在可以开始输出HTML内容                 ##
// ##################################################################
require_once __DIR__ . '/header.php';
?>

<div class="flex flex-col items-center">
    <div class="text-center mb-8 animate__animated animate__fadeIn">
        <h1 class="text-4xl font-bold gradient-heading mb-2">系统管理</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300">手动清理文件和系统维护</p>
    </div>
    
    <?php if (!$is_authorized): ?>
        <!-- 未登录状态 - 登录表单 -->
        <div class="card bg-base-100 shadow-xl w-full max-w-md animate__animated animate__fadeIn">
            <div class="card-body">
                <h2 class="card-title flex items-center">
                    <i class="fas fa-lock text-primary mr-2"></i> 
                    管理员登录
                </h2>
                <p class="py-2">请输入管理员密码以访问管理功能。</p>
                
                <?php if ($login_error): ?>
                    <div class="alert alert-error mb-4">
                        <div>
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?php echo $login_error; ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="post" class="mt-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">管理密码</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password" class="input input-bordered w-full pr-10" required autofocus />
                            <button type="button" class="toggle-password absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <label class="label">
                            <span class="label-text-alt text-info">默认密码可在配置文件中找到和修改</span>
                        </label>
                    </div>
                    
                    <div class="form-control mt-6">
                        <button type="submit" name="login" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt mr-2"></i> 登录
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- 已登录状态 -->
        <div class="w-full max-w-4xl mb-4">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <span><i class="fas fa-user-shield mr-2"></i>已登录管理员模式</span>
                </div>
                <a href="/admin?logout=1" class="btn btn-sm btn-outline btn-error">
                    <i class="fas fa-sign-out-alt mr-2"></i> 安全退出
                </a>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-4xl">
            <!-- 存储统计卡片 -->
            <div class="card bg-base-100 shadow-xl animate__animated animate__fadeIn">
                <div class="card-body">
                    <h2 class="card-title flex items-center">
                        <i class="fas fa-chart-pie text-primary mr-2"></i> 
                        存储统计
                    </h2>
                    
                    <div class="stats stats-vertical shadow mt-4">
                        <div class="stat">
                            <div class="stat-title">活跃文件</div>
                            <div class="stat-value text-primary"><?php echo $storage_stats['total_files']; ?></div>
                            <div class="stat-desc">未过期的文件</div>
                        </div>
                        
                        <div class="stat">
                            <div class="stat-title">标记删除</div>
                            <div class="stat-value text-secondary"><?php echo $storage_stats['marked_files']; ?></div>
                            <div class="stat-desc">旧数据，新版已不再使用此标记</div>
                        </div>
                        
                        <div class="stat">
                            <div class="stat-title">访问次数已过期</div>
                            <div class="stat-value text-warning"><?php echo $storage_stats['expired_views']; ?></div>
                            <div class="stat-desc">访问次数 >= <?php echo $config['expiration']['max_views']; ?> 次</div>
                        </div>
                        
                        <div class="stat">
                            <div class="stat-title">时间已过期</div>
                            <div class="stat-value text-warning"><?php echo $storage_stats['expired_days']; ?></div>
                            <div class="stat-desc">存储 >= <?php echo $config['expiration']['max_days']; ?> 天</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-4">
                        <div>
                            <i class="fas fa-clock"></i>
                            <span>最后更新时间: <?php echo date('Y-m-d H:i:s'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 清理操作卡片 -->
            <div class="card bg-base-100 shadow-xl animate__animated animate__fadeIn animate__delay-1s">
                <div class="card-body">
                    <h2 class="card-title flex items-center">
                        <i class="fas fa-trash-alt text-error mr-2"></i> 
                        清理操作
                    </h2>
                    
                    <?php if ($deletion_result): ?>
                        <div class="alert alert-<?php echo $deletion_result['success'] ? 'success' : 'error'; ?> mb-4">
                            <div>
                                <i class="fas fa-<?php echo $deletion_result['success'] ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                                <span><?php echo $deletion_result['message']; ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($last_action_result): ?>
                        <div class="alert alert-<?php echo $last_action_result['success'] ? 'success' : 'error'; ?> mb-4">
                            <div>
                                <i class="fas fa-<?php echo $last_action_result['success'] ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                                <span><?php echo $last_action_result['message']; ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="space-y-4">
                        <form method="post" class="form-control" onsubmit="return confirm('确定要清理所有过期文件吗？此操作将永久删除文件和数据库记录，不可撤销！');">
                            <input type="hidden" name="action" value="cleanup_expired">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-broom mr-2"></i>
                                清理所有过期文件
                            </button>
                            <label class="label">
                                <span class="label-text-alt">此操作将删除所有达到访问次数或存储时间上限的文件</span>
                            </label>
                        </form>

                        <div class="divider">或</div>

                        <form method="post" action="/admin/delete_all_files" class="form-control" onsubmit="return confirm('【高危操作】确定要删除所有本地存储的源文件吗？\\n\\n此操作将清空 uploads 目录，但保留数据库记录，用于CDN回源验证。\\n\\n请确保您已启用CDN永久缓存模式，否则将导致文件无法访问！此操作不可撤销！');">
                            <button type="submit" class="btn btn-error">
                                <i class="fas fa-bomb mr-2"></i>
                                删除所有本地文件
                            </button>
                            <label class="label">
                                <span class="label-text-alt text-error">高危操作：仅在CDN永久缓存模式下使用</span>
                            </label>
                        </form>
                    </div>
                    
                    <div class="alert alert-warning mt-4">
                        <div>
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>警告：删除操作无法撤销，请确认后执行</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 系统设置卡片 -->
            <div class="card bg-base-100 shadow-xl animate__animated animate__fadeIn animate__delay-1s col-span-1 md:col-span-2">
                <div class="card-body">
                    <h2 class="card-title flex items-center">
                        <i class="fas fa-cogs text-primary mr-2"></i>
                        系统设置
                    </h2>

                    <form method="post" action="/admin/settings" class="mt-4 space-y-4">
                        <div class="form-control">
                            <label class="label cursor-pointer">
                                <span class="label-text flex flex-col">
                                    <span class="font-medium">CDN友好模式</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">开启后，文件将设置长期缓存，但会禁用访问次数限制。</span>
                                </span>
                                <input type="checkbox" class="toggle toggle-primary" name="cdn_friendly_mode" value="permanent" 
                                    <?php echo ($config['expiration']['expiration_policy'] ?? 'views') === 'permanent' ? 'checked' : ''; ?> />
                            </label>
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">最大访问次数</span>
                            </label>
                            <input type="number" name="max_views" class="input input-bordered w-full" 
                                   value="<?php echo htmlspecialchars($config['expiration']['max_views']); ?>" min="1" required />
                            <label class="label">
                                <span class="label-text-alt">文件被访问这么多次后将被删除（CDN模式下无效）。</span>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">最长保存天数</span>
                            </label>
                            <input type="number" name="max_days" class="input input-bordered w-full" 
                                   value="<?php echo htmlspecialchars($config['expiration']['max_days']); ?>" min="1" required />
                            <label class="label">
                                <span class="label-text-alt">文件上传超过这么多天后将被删除。</span>
                            </label>
                        </div>

                        <div class="form-control mt-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>
                                保存设置
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- 系统信息卡片 -->
            <div class="card bg-base-100 shadow-xl animate__animated animate__fadeIn animate__delay-2s">
                <div class="card-body">
                    <h2 class="card-title flex items-center">
                        <i class="fas fa-server text-info mr-2"></i> 
                        系统信息
                    </h2>
                    
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <tbody>
                                <tr>
                                    <td class="font-medium">PHP版本</td>
                                    <td><?php echo phpversion(); ?></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">服务器时间</td>
                                    <td><?php echo date('Y-m-d H:i:s'); ?></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">上传目录</td>
                                    <td>
                                        <span class="<?php echo is_writable($config['upload']['storage_path']) ? 'text-success' : 'text-error'; ?>">
                                            <?php echo $config['upload']['storage_path']; ?>
                                            <?php echo is_writable($config['upload']['storage_path']) ? '(可写)' : '(不可写)'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-medium">会话过期时间</td>
                                    <td><?php echo $config['admin']['session_expire']; ?> 秒</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- 快速链接卡片 -->
            <div class="card bg-base-100 shadow-xl animate__animated animate__fadeIn animate__delay-2s">
                <div class="card-body">
                    <h2 class="card-title flex items-center">
                        <i class="fas fa-link text-info mr-2"></i> 
                        快速链接
                    </h2>
                    
                    <div class="grid grid-cols-1 gap-3">
                        <a href="/" class="btn btn-outline btn-info gap-2">
                            <i class="fas fa-home"></i>
                            返回首页
                        </a>
                        
                        <a href="/about" class="btn btn-outline btn-info gap-2">
                            <i class="fas fa-info-circle"></i>
                            关于页面
                        </a>
                        
                        <button class="btn btn-outline btn-info gap-2" onclick="window.location.reload();">
                            <i class="fas fa-sync"></i>
                            刷新统计
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- 密码切换可见性的脚本 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const togglePasswordBtn = document.querySelector('.toggle-password');
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function() {
            const passwordInput = this.parentElement.querySelector('input');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // 切换图标
            const icon = this.querySelector('i');
            if (type === 'password') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?> 