# ImgBed - 高性能PHP图床

ImgBed 是一个现代化、轻量级、功能丰富的PHP图床应用。它专为需要临时文件分享和存储的场景设计，提供匿名上传、自动过期和强大的管理功能。项目基于原生PHP开发，兼容最新的PHP 8.4标准，并注重代码质量、安全性和用户体验。

## ✨ 项目特性

-   **现代化技术栈**: 兼容 PHP 8.4，无需额外扩展，可在最常见的虚拟主机环境中运行。
-   **多种格式支持**: 支持常见图片格式（JPG, PNG, GIF, WebP等）、视频格式（MP4, WebM, MOV等）和音频格式（MP3, WAV, FLAC等）。
-   **易于部署**: 只需一个支持PHP（8.0+）和MySQL/MariaDB的Web服务器即可快速运行。
-   **文件自动过期**: 文件在满足设定的"最大访问次数"或"最长保存天数"任一条件后，会自动从服务器和数据库中彻底删除（可在后台管理面板调整）。
-   **安全保障**:
    -   后台管理有密码保护和会话过期机制。
    -   文件上传时会验证真实MIME类型，防止伪造。
-   **安全上传**: 通过MIME类型和文件扩展名双重验证，防止上传恶意文件。
-   **响应式与美观界面**: 使用 Tailwind CSS 和 DaisyUI 构建，提供美观、响应式的用户界面和流畅的动画效果，支持明暗主题切换。
-   **拖拽上传**: 支持文件拖拽上传，提供便捷的用户体验。
-   **多种链接格式**: 上传成功后自动生成**原始链接**、**Markdown**和**HTML**格式，方便在不同场景下使用。
-   **管理后台**:
    -   使用自定义密码登录，保证管理安全。
    -   提供存储统计，实时查看活跃文件、过期文件等信息。
    -   支持手动触发过期文件清理任务。
    -   显示服务器环境信息，便于排查问题。
-   **安装向导**: 提供Web安装界面，简化数据库配置和初始化过程。
-   **高安全性**: 内置安装锁定、XSS防护、会话安全配置等多重安全机制。
-   **CDN友好**: 可选的"永久缓存"模式，禁用访问次数限制，利用浏览器和CDN进行长期缓存，提升访问速度。

## 🛠️ 技术栈

-   **后端**: PHP 8.0+ (兼容 PHP 8.4)
-   **数据库**: MySQL 5.7+ (或 MariaDB 10.2+)
-   **前端**: Tailwind CSS, DaisyUI, Animate.css, Font Awesome

## 🚀 安装与部署

### 环境要求

-   Web服务器 (Nginx, Apache, or Caddy)
-   PHP 8.0 或更高版本
-   MySQL 5.7 或更高版本
-   已启用 `pdo_mysql` PHP扩展
-   已启用 `fileinfo` PHP扩展

### 安装步骤

1.  **上传代码**: 将项目所有文件上传到您的网站根目录或任意子目录。
2.  **设置权限**: 确保PHP进程对 `uploads/` 和 `tmp/` 目录有写入权限。如果这两个目录不存在，请手动创建。
    ```bash
    mkdir uploads tmp
    chmod -R 755 uploads tmp
    chown -R www-data:www-data uploads tmp # 'www-data' 可能是您的Web服务器用户
    ```
3.  **配置Web服务器**:
    -   将网站的运行目录（Document Root）指向 `public` 目录。
    -   设置URL重写规则，将所有请求都指向 `public/index.php`。
    -   **Nginx 示例配置**:
        ```nginx
        server {
            listen 80;
            server_name your-domain.com;
            root /path/to/imgbed/public;
            index index.php;

            location / {
                try_files $uri $uri/ /index.php?$query_string;
            }

            location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/var/run/php/php8.x-fpm.sock; # 根据您的PHP版本修改
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            }

            location ~ /\.ht {
                deny all;
            }
        }
        ```
4.  **运行安装向导**: 在浏览器中访问您的域名 `http://your-domain.com/install`。根据页面提示填写您的MySQL数据库信息，然后点击"安装"。
5.  **安装完成**: 安装成功后，系统会自动创建 `config/install.lock` 文件以锁定安装程序。您将被重定向到首页，可以开始使用了。

## ⚙️ 配置说明

所有配置都在 `config/config.php` 文件中。

-   `db`: 数据库连接信息。
-   `upload`: 上传相关配置。
    -   `max_size`: 最大上传文件大小（单位：字节）。
    -   `allowed_image_types`/`allowed_video_types`: 允许上传的文件扩展名列表。
-   `expiration`: 文件过期规则。
    -   `max_views`: 最大访问次数。
    -   `max_days`: 最长存储天数。
    -   `check_interval`: 自动清理任务的触发间隔（秒）。
-   `site`: 网站基本信息。
    -   `title`: 网站标题。
    -   `base_url`: 网站URL，留空会自动检测。
-   `admin`: 管理后台配置。
    -   `password`: 管理员登录密码（**强烈建议修改**）。
    -   `session_expire`: 管理员会话过期时间（秒）。

## 🔐 管理后台

-   **访问地址**: `http://your-domain.com/admin`
-   **登录**: 使用您在 `config.php` 中设置的 `admin.password` 进行登录。
-   **功能**:
    -   **存储统计**: 查看文件数量和状态。
    -   **清理操作**: 手动执行过期文件清理。
    -   **系统信息**: 查看PHP版本、服务器时间等环境信息。
    -   **安全退出**: 销毁会话，安全退出登录。

## ⚠️ 安全注意事项

-   **修改默认密码**: 首次部署后，请立即修改 `config/config.php` 中的管理员密码。
-   **关闭错误显示**: 在生产环境中，建议修改 `public/index.php`，将 `ini_set('display_errors', 1);` 改为 `ini_set('display_errors', 0);`。
-   **保护配置文件**: 确保Web服务器配置正确，禁止通过URL直接访问 `config/` 目录。
-   **备份**: 定期备份 `uploads/` 目录和MySQL数据库。

## 📂 目录结构

```
imgbed/
├── config/             # 配置文件目录
│   └── config.php      # 应用主配置文件
├── database/           # 数据库结构
│   └── schema.sql      # 表结构定义文件
├── public/             # Web可访问的公共目录 (Document Root)
│   ├── assets/         # CSS, JS, 字体等静态资源
│   ├── templates/      # PHP模板文件
│   └── index.php       # 应用统一入口文件
├── src/                # 应用核心PHP源代码
│   ├── Autoloader.php  # 自动加载类
│   ├── Database.php    # 数据库操作类
│   └── MediaHandler.php# 媒体文件处理核心类
├── tmp/                # 会话文件存储目录
└── uploads/            # 上传文件存储根目录
```

## 使用方法

1. 访问网站首页
2. 点击或拖拽文件到上传区域
3. 等待上传完成
4. 复制所需的链接格式（原始、Markdown或HTML）

## 注意事项

- 默认限制上传文件大小为50MB，可在配置文件中修改
- 确保uploads目录有写入权限
- 生产环境请修改 public/index.php 文件，关闭错误显示 