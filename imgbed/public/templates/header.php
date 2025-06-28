<?php
$config = require __DIR__ . '/../../config/config.php';
$siteTitle = $config['site']['title'] ?? '简易图床';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $siteTitle; ?></title>
    <!-- 引入 Tailwind CSS 和 DaisyUI -->
    <link href="https://jsdelivr.holoknot.com/npm/daisyui@latest/dist/full.css" rel="stylesheet" type="text/css" />
    <script src="https://tailwindcss.holoknot.com"></script>
    <link rel="stylesheet" href="https://cfjs.holoknot.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <!-- 引入字体图标 -->
    <link rel="stylesheet" href="https://jsdelivr.holoknot.com/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    
    <!-- 主题配置 -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5',
                        secondary: '#0EA5E9',
                    },
                }
            }
        }
    </script>
    
    <!-- 自定义样式 -->
    <style>
        body {
            background: linear-gradient(to bottom, #f9fafb, #f3f4f6);
            min-height: 100vh;
        }
        
        .upload-area {
            border: 2px dashed #ccc;
            transition: all 0.3s ease;
            box-shadow: inset 0 0 6px rgba(0,0,0,0.05);
        }
        
        .upload-area:hover, .upload-area.active {
            border-color: #4F46E5;
            box-shadow: inset 0 0 12px rgba(79, 70, 229, 0.1);
        }
        
        .max-h-80vh {
            max-height: 80vh;
        }
        
        .animate-pulse-once {
            animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) 1;
        }
        
        /* 确保视频预览正确显示 */
        #preview-container {
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        #preview-container video {
            object-fit: cover;
            width: 100%;
            height: 100%;
            pointer-events: none; /* 禁止视频自身的事件，让点击穿透到链接 */
        }
        
        #preview-container .play-button {
            cursor: pointer;
            z-index: 2;
        }
        
        .card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .card:hover {
            box-shadow: 0 10px 15px rgba(0,0,0,0.1), 0 4px 6px rgba(0,0,0,0.05);
            transform: translateY(-2px);
        }
        
        .gradient-heading {
            background-image: linear-gradient(135deg, #4F46E5, #0EA5E9);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        @media (prefers-color-scheme: dark) {
            [data-theme="dark"] body {
                background: linear-gradient(to bottom, #1e293b, #0f172a);
            }
            [data-theme="dark"] .navbar {
                background: rgba(15, 23, 42, 0.8);
                border-bottom: 1px solid rgba(255,255,255,0.05);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
        
        /* 平滑滚动 */
        html {
            scroll-behavior: smooth;
        }
    </style>
    
    <!-- 主题切换脚本 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 检查本地存储中的主题设置
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            } else {
                // 根据系统设置自动选择主题
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
            }
        });
        
        // 切换主题函数
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }
    </script>
</head>
<body class="min-h-screen">
    <div class="navbar bg-base-100 shadow-md sticky top-0 z-50">
        <div class="navbar-start">
            <div class="dropdown lg:hidden">
                <label tabindex="0" class="btn btn-ghost btn-circle">
                    <i class="fas fa-bars"></i>
                </label>
                <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                    <li><a href="/" class="<?php echo $currentPath === '/' || $currentPath === '' ? 'active' : ''; ?>">
                        <i class="fas fa-cloud-upload-alt mr-2"></i> 上传文件
                    </a></li>
                    <li><a href="/about">
                        <i class="fas fa-info-circle mr-2"></i> 关于
                    </a></li>
                    <!-- 管理页面链接，使用小文本减少可见性 -->
                    <li class="text-xs opacity-50 hover:opacity-100">
                        <a href="/admin">
                            <i class="fas fa-cog mr-2"></i> 系统管理
                        </a>
                    </li>
                </ul>
            </div>
            <a href="/" class="btn btn-ghost normal-case text-xl">
                <i class="fas fa-images mr-2 text-primary"></i>
                <span class="gradient-heading font-bold"><?php echo $siteTitle; ?></span>
            </a>
        </div>
        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1 font-medium">
                <li><a href="/" class="<?php echo $currentPath === '/' || $currentPath === '' ? 'active' : ''; ?>">
                    <i class="fas fa-cloud-upload-alt mr-2"></i> 上传文件
                </a></li>
                <li><a href="/about">
                    <i class="fas fa-info-circle mr-2"></i> 关于
                </a></li>
                <!-- 桌面版管理页面链接，使用小文本减少可见性 -->
                <li class="text-xs opacity-50 hover:opacity-100">
                    <a href="/admin">
                        <i class="fas fa-cog mr-2"></i> 系统管理
                    </a>
                </li>
            </ul>
        </div>
        <div class="navbar-end">
            <button class="btn btn-circle btn-ghost" onclick="toggleTheme()">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:flex"></i>
            </button>
        </div>
    </div>
    
    <div class="container mx-auto px-4 py-8 min-h-[calc(100vh-14rem)]"><?php // 内容区域开始 ?> 