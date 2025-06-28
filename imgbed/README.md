# 简易图床

一个简单的PHP图床应用，支持图片和视频上传，无需登录注册。文件访问3次或保存3天后自动删除。

## 特性

- 支持PHP 8.4，不需要额外PHP插件
- 支持多种图片和视频格式上传
- 匿名上传，无需登录
- 文件被访问3次或保存3天后自动删除
- 支持拖拽上传
- 提供多种链接格式（原始链接、Markdown、HTML）
- 美观的界面（使用Tailwind CSS和DaisyUI）
- 支持自定义数据库表前缀

## 安装要求

- PHP 8.0+ (兼容PHP 8.4)
- MySQL 5.7+
- 支持PDO扩展

## 安装步骤

1. 将所有文件上传到网站根目录或子目录
2. 访问 `http://your-domain.com/install`（或子目录安装则访问 `http://your-domain.com/subdirectory/install`）
3. 填写数据库信息并完成安装

## 目录结构

```
imgbed/
  ├── config/         # 配置文件
  ├── database/       # 数据库文件
  ├── public/         # 公共访问目录
  │    ├── templates/ # 模板文件
  │    └── index.php  # 入口文件
  ├── src/            # 源代码
  └── uploads/        # 上传文件存储目录
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