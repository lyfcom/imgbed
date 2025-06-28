<?php
/**
 * 图床配置文件
 * 支持PHP 8.4
 */

return [
    // 数据库配置
    'db' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'dbname' => 'imgbed',
        'charset' => 'utf8mb4',
        'prefix' => 'imgbed_', // 表前缀，可自定义
    ],
    
    // 上传配置
    'upload' => [
        'max_size' => 52428800, // 最大上传大小 (50MB)
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'],
        'allowed_video_types' => ['mp4', 'webm', 'mov', 'avi', 'mkv'],
        'storage_path' => __DIR__ . '/../uploads/', // 存储路径
    ],
    
    // 文件过期设置
    'expiration' => [
        'max_views' => 3,       // 访问超过3次自动删除
        'max_days' => 3,        // 保存超过3天自动删除
        'check_interval' => 3600, // 检查间隔(秒)
    ],
    
    // 网站设置
    'site' => [
        'title' => '简易图床',
        'base_url' => '', // 网站基础URL，留空则自动检测
    ],
]; 