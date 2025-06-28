-- 创建媒体文件表
CREATE TABLE IF NOT EXISTS `{prefix}media` (
  `id` varchar(32) NOT NULL COMMENT '文件唯一ID',
  `filename` varchar(255) NOT NULL COMMENT '原始文件名',
  `filepath` varchar(255) NOT NULL COMMENT '存储路径',
  `filetype` varchar(50) NOT NULL COMMENT '文件MIME类型',
  `filesize` int(11) NOT NULL COMMENT '文件大小(字节)',
  `filehash` varchar(64) NOT NULL COMMENT '文件哈希值',
  `type` enum('image','video','other') NOT NULL COMMENT '文件类型',
  `extension` varchar(10) NOT NULL COMMENT '文件扩展名',
  `views` int(11) NOT NULL DEFAULT '0' COMMENT '访问次数',
  `created_at` datetime NOT NULL COMMENT '上传时间',
  `last_access` datetime DEFAULT NULL COMMENT '最后访问时间',
  `deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已删除',
  PRIMARY KEY (`id`),
  KEY `views` (`views`),
  KEY `created_at` (`created_at`),
  KEY `deleted` (`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 