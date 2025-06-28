<?php
namespace ImgBed;

class MediaHandler {
    private $db;
    private $config;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->config = require __DIR__ . '/../config/config.php';
    }
    
    /**
     * 处理文件上传
     * @param array $file $_FILES数组中的文件项
     * @return array|false 成功返回文件信息，失败返回false
     */
    public function uploadFile($file) {
        // 检查上传错误
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => $this->getUploadErrorMessage($file['error'])];
        }
        
        // 检查文件大小
        if ($file['size'] > $this->config['upload']['max_size']) {
            return ['error' => '文件大小超过限制'];
        }
        
        // 获取文件扩展名
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // 通过文件内容获取真实MIME类型，防止伪造
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $actualMimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        // 检查文件类型（同时验证扩展名和内容）
        $fileType = $this->getFileType($extension, $actualMimeType);
        if (!$fileType) {
            return ['error' => '不支持的文件类型或文件内容与扩展名不匹配'];
        }
        
        // 生成唯一文件ID和存储路径
        $fileId = $this->generateUniqueId();
        $storagePath = date('Y/m/d');
        $directory = $this->config['upload']['storage_path'] . $storagePath;
        
        // 创建目录（如果不存在）
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        
        // 构建文件存储路径
        $storageFilename = $fileId . '.' . $extension;
        $filePath = $directory . '/' . $storageFilename;
        $relativePath = $storagePath . '/' . $storageFilename;
        
        // 保存文件
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['error' => '文件上传失败'];
        }
        
        // 计算文件哈希值
        $fileHash = hash_file('sha256', $filePath);
        
        // 准备数据库记录
        $mediaData = [
            'id' => $fileId,
            'filename' => $file['name'],
            'filepath' => $relativePath,
            'filetype' => $file['type'],
            'filesize' => $file['size'],
            'filehash' => $fileHash,
            'type' => $fileType,
            'extension' => $extension,
            'views' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'deleted' => 0
        ];
        
        // 写入数据库
        try {
            $this->db->insert('media', $mediaData);
            
            // 返回文件信息
            return [
                'id' => $fileId,
                'filename' => $file['name'],
                'url' => $this->getFileUrl($fileId),
                'extension' => $extension,
                'size' => $this->formatFileSize($file['size']),
                'type' => $fileType,
                'created' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            // 如果数据库操作失败，删除已上传的文件
            @unlink($filePath);
            return ['error' => '保存文件信息失败'];
        }
    }
    
    /**
     * 通过ID获取文件
     * @param string $id 文件ID
     * @return array|false 成功返回文件信息，失败返回false
     */
    public function getFile($id) {
        $sql = "SELECT * FROM {prefix}media WHERE id = :id AND deleted = 0";
        $file = $this->db->fetch($sql, ['id' => $id]);
        
        if (!$file) {
            return false;
        }
        
        // 更新访问计数和最后访问时间
        $this->updateFileAccess($id);
        
        // 检查是否需要删除文件（访问次数达到上限）
        if ($file['views'] + 1 >= $this->config['expiration']['max_views']) {
            // 标记为删除状态，但此处不实际删除文件，由定时任务处理
            $this->markFileDeleted($id);
        }
        
        return $file;
    }
    
    /**
     * 更新文件访问信息
     * @param string $id 文件ID
     */
    private function updateFileAccess($id) {
        $sql = "UPDATE {prefix}media SET 
                views = views + 1, 
                last_access = :last_access 
                WHERE id = :id";
                
        $this->db->query($sql, [
            'id' => $id,
            'last_access' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * 标记文件为已删除状态
     * @param string $id 文件ID
     */
    private function markFileDeleted($id) {
        $sql = "UPDATE {prefix}media SET deleted = 1 WHERE id = :id";
        $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * 实际删除过期文件
     * 根据访问次数或上传日期清理文件
     */
    public function cleanupExpiredFiles() {
        // 查找需要删除的文件
        $expirationDate = date('Y-m-d H:i:s', strtotime("-{$this->config['expiration']['max_days']} days"));
        
        $sql = "SELECT id, filepath FROM {prefix}media 
                WHERE deleted = 0 AND (
                    views >= :max_views OR 
                    created_at < :expiration_date
                )";
                
        $files = $this->db->fetchAll($sql, [
            'max_views' => $this->config['expiration']['max_views'],
            'expiration_date' => $expirationDate
        ]);
        
        // 逐个删除文件
        foreach ($files as $file) {
            // 标记为删除状态
            $this->markFileDeleted($file['id']);
            
            // 物理删除文件
            $filePath = $this->config['upload']['storage_path'] . $file['filepath'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        
        return count($files);
    }
    
    /**
     * 删除已标记的文件
     */
    public function deleteMarkedFiles() {
        $sql = "SELECT id, filepath FROM {prefix}media WHERE deleted = 1";
        $files = $this->db->fetchAll($sql);
        
        foreach ($files as $file) {
            $filePath = $this->config['upload']['storage_path'] . $file['filepath'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        
        // 删除数据库记录
        $this->db->query("DELETE FROM {prefix}media WHERE deleted = 1");
        
        return count($files);
    }
    
    /**
     * 获取文件类型
     * @param string $extension 文件扩展名
     * @return string|false 文件类型或false
     */
    private function getFileType($extension, $actualMimeType) {
        $isImageExtensionAllowed = in_array($extension, $this->config['upload']['allowed_image_types']);
        $isVideoExtensionAllowed = in_array($extension, $this->config['upload']['allowed_video_types']);

        if ($isImageExtensionAllowed && str_starts_with($actualMimeType, 'image/')) {
            return 'image';
        }
        
        if ($isVideoExtensionAllowed && str_starts_with($actualMimeType, 'video/')) {
            return 'video';
        }
        
        return false;
    }
    
    /**
     * 生成唯一ID
     * @return string 32位唯一ID
     */
    private function generateUniqueId() {
        return md5(uniqid(mt_rand(), true));
    }
    
    /**
     * 格式化文件大小
     * @param int $size 字节数
     * @return string 格式化后的大小
     */
    private function formatFileSize($size) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
    
    /**
     * 获取上传错误消息
     * @param int $errorCode 错误代码
     * @return string 错误消息
     */
    private function getUploadErrorMessage($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => '文件大小超过php.ini中upload_max_filesize的限制',
            UPLOAD_ERR_FORM_SIZE => '文件大小超过表单中MAX_FILE_SIZE的限制',
            UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
            UPLOAD_ERR_NO_FILE => '没有文件被上传',
            UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
            UPLOAD_ERR_CANT_WRITE => '文件写入失败',
            UPLOAD_ERR_EXTENSION => '文件上传被PHP扩展停止',
        ];
        
        return isset($errors[$errorCode]) ? $errors[$errorCode] : '未知上传错误';
    }
    
    /**
     * 获取文件URL
     * @param string $fileId 文件ID
     * @return string 文件URL
     */
    public function getFileUrl($fileId) {
        $baseUrl = $this->getBaseUrl();
        return rtrim($baseUrl, '/') . "/file/{$fileId}";
    }
    
    /**
     * 获取基础URL
     * @return string 基础URL
     */
    private function getBaseUrl() {
        if (!empty($this->config['site']['base_url'])) {
            return $this->config['site']['base_url'];
        }
        
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $path = dirname($scriptName);
        
        // 如果在根目录，返回域名
        if ($path == '/' || $path == '\\') {
            $path = '';
        }
        
        return "{$protocol}://{$host}{$path}";
    }
} 