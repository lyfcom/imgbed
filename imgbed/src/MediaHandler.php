<?php
namespace ImgBed;

class MediaHandler {
    private $db;
    private $config;
    
    public function __construct($config) {
        $this->db = Database::getInstance();
        $this->config = $config;
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
            'filetype' => $actualMimeType,
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
            return ['error' => '保存文件信息失败: ' . $e->getMessage()];
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

        // 如果不是永久缓存策略，才处理访问计数逻辑
        if (($this->config['expiration']['expiration_policy'] ?? 'views') !== 'permanent') {
            // 从配置中获取最大访问次数
            $max_views = $this->config['expiration']['max_views'];

            // 检查访问次数是否已达到或超过上限
            if ($file['views'] >= $max_views) {
                // 如果达到上限，立即删除文件和记录，然后返回false
                $this->deleteFileAndRecord($file['id'], $file['filepath']);
                return false;
            }

            // 如果未达到上限，则更新访问计数
            $this->updateFileAccess($id);
        }

        // 返回文件信息，供本次查看
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
     * 彻底删除文件（物理删除和数据库记录删除）
     * @param string $id 文件ID
     * @param string $filepath 文件相对路径
     * @return bool 删除是否成功
     */
    public function deleteFileAndRecord($id, $filepath) {
        // 物理删除文件
        $fullPath = $this->config['upload']['storage_path'] . $filepath;
        $unlinked = false;

        if (file_exists($fullPath)) {
            if (@unlink($fullPath)) {
                $unlinked = true;
            }
        } else {
            // 如果文件本就不存在，也视为成功，以便删除数据库记录
            $unlinked = true;
        }
        
        // 仅在物理文件删除成功或文件本就不存在时，才删除数据库记录
        if ($unlinked) {
            $sql = "DELETE FROM {prefix}media WHERE id = :id";
            try {
                $this->db->query($sql, ['id' => $id]);
                return true;
            } catch (\Exception $e) {
                // 可以记录日志
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * 清理并删除所有过期文件
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
        $deletedCount = 0;
        foreach ($files as $file) {
            if ($this->deleteFileAndRecord($file['id'], $file['filepath'])) {
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * 获取文件类型
     * @param string $extension 文件扩展名
     * @param string $actualMimeType 真实的MIME类型
     * @return string|false 文件类型或false
     */
    private function getFileType($extension, $actualMimeType) {
        $isImageExtensionAllowed = in_array($extension, $this->config['upload']['allowed_image_types']);
        $isVideoExtensionAllowed = in_array($extension, $this->config['upload']['allowed_video_types']);
        $isAudioExtensionAllowed = in_array($extension, $this->config['upload']['allowed_audio_types']);

        if ($isImageExtensionAllowed && str_starts_with($actualMimeType, 'image/')) {
            return 'image';
        }
        
        if ($isVideoExtensionAllowed && str_starts_with($actualMimeType, 'video/')) {
            return 'video';
        }
        
        if ($isAudioExtensionAllowed && (str_starts_with($actualMimeType, 'audio/') || $actualMimeType === 'application/ogg')) {
            return 'audio';
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
        if ($size < 1024) {
            return $size . ' B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($size, 1024));
        return @round($size / pow(1024, $i), 2) . ' ' . $units[$i];
    }
    
    /**
     * 获取上传错误消息
     * @param int $errorCode 错误代码
     * @return string 错误消息
     */
    private function getUploadErrorMessage($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => '文件大小超过php.ini中的upload_max_filesize限制',
            UPLOAD_ERR_FORM_SIZE => '文件大小超过表单中的MAX_FILE_SIZE限制',
            UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
            UPLOAD_ERR_NO_FILE => '没有文件被上传',
            UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
            UPLOAD_ERR_CANT_WRITE => '文件写入失败',
            UPLOAD_ERR_EXTENSION => '文件上传被PHP扩展停止'
        ];
        
        return $errors[$errorCode] ?? '未知上传错误';
    }
    
    /**
     * 获取文件URL
     * @param string $fileId 文件ID
     * @return string 文件URL
     */
    public function getFileUrl($fileId) {
        return $this->getBaseUrl() . '/file/' . $fileId;
    }
    
    /**
     * 获取基础URL
     * @return string 基础URL
     */
    private function getBaseUrl() {
        return $this->config['site']['base_url'];
    }

    /**
     * 删除所有本地存储的源文件，但保留数据库记录
     * @return int 返回成功删除的文件数量
     */
    public function deleteAllLocalFiles() {
        // 查找所有未被标记为删除的文件记录
        $sql = "SELECT id, filepath FROM {prefix}media WHERE deleted = 0";
        $files = $this->db->fetchAll($sql);

        $deletedCount = 0;
        foreach ($files as $file) {
            $fullPath = $this->config['upload']['storage_path'] . $file['filepath'];
            if (file_exists($fullPath)) {
                if (@unlink($fullPath)) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }
} 