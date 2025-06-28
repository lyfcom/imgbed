<?php
namespace ImgBed;

class Autoloader {
    /**
     * 注册自动加载器
     */
    public static function register() {
        spl_autoload_register([self::class, 'loadClass']);
    }
    
    /**
     * 加载类文件
     * @param string $class 完整类名
     * @return bool 是否成功加载
     */
    public static function loadClass($class) {
        // 只处理ImgBed命名空间下的类
        $prefix = 'ImgBed\\';
        
        // 不是我们的命名空间，跳过
        if (strpos($class, $prefix) !== 0) {
            return false;
        }
        
        // 获取相对类名
        $relativeClass = substr($class, strlen($prefix));
        
        // 将命名空间替换为目录分隔符，添加.php后缀
        $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';
        
        // 如果文件存在，引入它
        if (file_exists($file)) {
            require $file;
            return true;
        }
        
        return false;
    }
} 