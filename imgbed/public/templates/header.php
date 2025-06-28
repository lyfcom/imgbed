<?php
$config = require __DIR__ . '/../../config/config.php';
$siteTitle = $config['site']['title'] ?? '简易图床';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $siteTitle; ?></title>
    <!-- 引入第三方托管的 Tailwind CSS 和 DaisyUI -->
    <link href="https://jsdelivr.holoknot.com/npm/daisyui@latest/dist/full.css" rel="stylesheet" type="text/css" />
    <script src="https://tailwindcss.holoknot.com"></script>
    <!-- 自定义样式 -->
    <style>
        .upload-area {
            border: 2px dashed #ccc;
            transition: all 0.3s ease;
        }
        .upload-area:hover, .upload-area.active {
            border-color: #4F46E5;
        }
        .max-h-80vh {
            max-height: 80vh;
        }
        .animate-pulse-once {
            animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) 1;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="navbar bg-base-100 shadow-md">
        <div class="flex-1">
            <a href="/" class="btn btn-ghost normal-case text-xl"><?php echo $siteTitle; ?></a>
        </div>
        <div class="flex-none">
            <ul class="menu menu-horizontal px-1">
                <li><a href="/" class="font-medium">上传</a></li>
            </ul>
        </div>
    </div>
    
    <div class="container mx-auto px-4 py-8"><?php // 内容区域开始 ?> 