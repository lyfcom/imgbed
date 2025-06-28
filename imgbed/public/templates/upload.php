<?php require_once __DIR__ . '/header.php'; ?>

<div class="flex flex-col items-center">
    <!-- 页面标题 -->
    <div class="text-center mb-8 animate__animated animate__fadeIn">
        <h1 class="text-4xl font-bold gradient-heading mb-2">快速图床</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300">简单、快速、安全地分享您的图片、视频和音频</p>
    </div>
    
    <div class="card bg-base-100 shadow-xl w-full max-w-2xl animate__animated animate__fadeInUp">
        <div class="card-body">
            <div class="flex items-center mb-4">
                <i class="fas fa-cloud-upload-alt text-2xl text-primary mr-3"></i>
                <div>
                    <h2 class="card-title m-0">上传文件</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">支持拖拽上传，访问 <?php echo $config['expiration']['max_views']; ?> 次或 <?php echo $config['expiration']['max_days']; ?> 天后自动删除</p>
                </div>
            </div>
            
            <div id="upload-area" class="upload-area rounded-xl p-10 text-center cursor-pointer bg-base-200 hover:bg-base-300 transition-all">
                <div id="upload-ui" class="py-4">
                    <div class="mb-4 relative">
                        <div class="w-20 h-20 mx-auto rounded-full bg-primary/10 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-4 text-lg font-medium">点击或拖拽文件到这里上传</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">支持图片、视频等各种格式</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">最大文件大小: <?php echo floor($config['upload']['max_size'] / 1024 / 1024); ?>MB</p>
                    
                    <div class="mt-6 flex flex-wrap justify-center gap-2">
                        <span class="badge badge-primary">JPG</span>
                        <span class="badge badge-primary">PNG</span>
                        <span class="badge badge-primary">GIF</span>
                        <span class="badge badge-primary">WEBP</span>
                        <span class="badge badge-secondary">MP4</span>
                        <span class="badge badge-secondary">WEBM</span>
                        <span class="badge badge-accent">MP3</span>
                        <span class="badge badge-accent">WAV</span>
                        <span class="badge badge-accent">FLAC</span>
                        <span class="badge badge-ghost">更多格式</span>
                    </div>
                </div>
                
                <div id="upload-progress" class="hidden py-6">
                    <div class="text-center mb-6">
                        <div class="loading loading-spinner loading-lg text-primary"></div>
                    </div>
                    <p id="upload-status" class="text-lg font-medium">正在上传...</p>
                    <div class="mt-4 px-4">
                        <progress id="progress-bar" class="progress progress-primary w-full" value="0" max="100"></progress>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-right"><span id="progress-percent">0%</span></p>
                    </div>
                </div>
            </div>
            
            <input type="file" id="file-input" class="hidden" />
            
            <!-- 上传成功后显示的结果 -->
            <div id="upload-result" class="hidden mt-6 animate__animated animate__fadeIn">
                <div class="border border-base-300 rounded-xl p-6 bg-base-200">
                    <div class="flex flex-col md:flex-row items-center gap-6 mb-6">
                        <div id="preview-container" class="w-32 h-32 rounded-lg overflow-hidden bg-base-300 flex items-center justify-center shadow-md relative">
                            <!-- 预览将在这里显示 -->
                        </div>
                        <div class="text-center md:text-left flex-1">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check-circle text-success"></i>
                                <h3 class="font-bold text-lg">上传成功!</h3>
                            </div>
                            <p id="result-filename" class="font-medium text-base mt-2 break-all"></p>
                            <p id="result-filesize" class="text-sm text-gray-600 dark:text-gray-400 mt-1"></p>
                            <div class="mt-3 flex items-center gap-1">
                                <i class="fas fa-exclamation-triangle text-warning text-sm"></i>
                                <p class="text-xs text-warning">文件访问 <?php echo $config['expiration']['max_views']; ?> 次或 <?php echo $config['expiration']['max_days']; ?> 天后将自动删除</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="divider text-sm">分享链接</div>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="label">
                                <span class="label-text font-medium flex items-center gap-2">
                                    <i class="fas fa-link text-primary"></i> 原始链接
                                </span>
                            </label>
                            <div class="join w-full">
                                <input id="original-link" type="text" class="input input-bordered join-item w-full" readonly />
                                <button class="btn join-item btn-primary copy-btn" data-target="original-link">
                                    <i class="fas fa-copy mr-1"></i> 复制
                                </button>
                            </div>
                        </div>
                        
                        <div>
                            <label class="label">
                                <span class="label-text font-medium flex items-center gap-2">
                                    <i class="fas fa-code text-primary"></i> Markdown格式
                                </span>
                            </label>
                            <div class="join w-full">
                                <input id="markdown-link" type="text" class="input input-bordered join-item w-full" readonly />
                                <button class="btn join-item btn-primary copy-btn" data-target="markdown-link">
                                    <i class="fas fa-copy mr-1"></i> 复制
                                </button>
                            </div>
                        </div>
                        
                        <div>
                            <label class="label">
                                <span class="label-text font-medium flex items-center gap-2">
                                    <i class="fas fa-code text-primary"></i> HTML格式
                                </span>
                            </label>
                            <div class="join w-full">
                                <input id="html-link" type="text" class="input input-bordered join-item w-full" readonly />
                                <button class="btn join-item btn-primary copy-btn" data-target="html-link">
                                    <i class="fas fa-copy mr-1"></i> 复制
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-actions justify-end mt-6">
                        <button id="upload-new" class="btn btn-primary">
                            <i class="fas fa-cloud-upload-alt mr-2"></i> 继续上传
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="text-center text-xs text-gray-400 dark:text-gray-500 mt-4">
                <p>所有文件上传后均匿名保存，请勿上传违法或敏感内容</p>
            </div>
        </div>
    </div>
    
    <!-- 使用说明 -->
    <div class="mt-12 w-full max-w-2xl">
        <div class="collapse collapse-arrow bg-base-100 shadow-lg rounded-box mb-4">
            <input type="checkbox" /> 
            <div class="collapse-title font-medium flex items-center">
                <i class="fas fa-info-circle mr-2 text-primary"></i> 图床使用说明
            </div>
            <div class="collapse-content"> 
                <div class="space-y-2 text-sm">
                    <p><i class="fas fa-check-circle text-success mr-2"></i> 支持大多数图片、视频和音频格式</p>
                    <p><i class="fas fa-check-circle text-success mr-2"></i> 无需注册即可上传使用</p>
                    <p><i class="fas fa-exclamation-circle text-warning mr-2"></i> 文件访问 <?php echo $config['expiration']['max_views']; ?> 次或存储 <?php echo $config['expiration']['max_days']; ?> 天后会自动删除</p>
                    <p><i class="fas fa-exclamation-circle text-warning mr-2"></i> 请勿上传违规内容，我们有权删除</p>
                </div>
            </div>
        </div>
        
        <div class="collapse collapse-arrow bg-base-100 shadow-lg rounded-box">
            <input type="checkbox" /> 
            <div class="collapse-title font-medium flex items-center">
                <i class="fas fa-question-circle mr-2 text-primary"></i> 常见问题
            </div>
            <div class="collapse-content"> 
                <div class="space-y-4 text-sm">
                    <div>
                        <h4 class="font-bold">如何使用链接？</h4>
                        <p>上传完成后，系统会提供三种链接形式：原始链接、Markdown格式和HTML格式。</p>
                    </div>
                    <div>
                        <h4 class="font-bold">为什么文件会被删除？</h4>
                        <p>为了节省服务器资源，当文件被访问超过 <?php echo $config['expiration']['max_views']; ?> 次或存储超过 <?php echo $config['expiration']['max_days']; ?> 天时会被自动删除。</p>
                    </div>
                    <div>
                        <h4 class="font-bold">是否支持大文件上传？</h4>
                        <p>支持最大 <?php echo floor($config['upload']['max_size'] / 1024 / 1024); ?>MB 的文件上传，超过限制的文件将被拒绝。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // HTML转义函数，防止XSS
    function escapeHtml(unsafe) {
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('file-input');
    const uploadUI = document.getElementById('upload-ui');
    const uploadProgress = document.getElementById('upload-progress');
    const progressBar = document.getElementById('progress-bar');
    const progressPercent = document.getElementById('progress-percent');
    const uploadStatus = document.getElementById('upload-status');
    const uploadResult = document.getElementById('upload-result');
    const previewContainer = document.getElementById('preview-container');
    const resultFilename = document.getElementById('result-filename');
    const resultFilesize = document.getElementById('result-filesize');
    const originalLink = document.getElementById('original-link');
    const markdownLink = document.getElementById('markdown-link');
    const htmlLink = document.getElementById('html-link');
    const uploadNewBtn = document.getElementById('upload-new');

    // 点击上传区域触发文件选择
    uploadArea.addEventListener('click', () => {
        fileInput.click();
    });

    // 拖拽事件处理
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.add('active');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.remove('active');
        }, false);
    });

    uploadArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            handleFiles(files[0]);
        }
    }

    // 文件选择处理
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFiles(e.target.files[0]);
        }
    });

    // 处理选择的文件
    function handleFiles(file) {
        uploadUI.classList.add('hidden');
        uploadProgress.classList.remove('hidden');
        
        const formData = new FormData();
        formData.append('file', file);
        
        const xhr = new XMLHttpRequest();
        
        xhr.open('POST', '/upload', true);
        
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                progressBar.value = percentComplete;
                progressPercent.textContent = `${percentComplete}%`;
                uploadStatus.textContent = `上传中... ${percentComplete}%`;
            }
        });
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        const fileInfo = response.file;
                        showUploadResult(fileInfo);
                    } else {
                        showError(response.error || '上传失败');
                    }
                } catch (e) {
                    showError('解析响应失败');
                }
            } else {
                showError('上传失败，服务器错误');
            }
        };
        
        xhr.onerror = function() {
            showError('网络错误，上传失败');
        };
        
        xhr.send(formData);
    }
    
    // 显示错误
    function showError(message) {
        uploadProgress.classList.add('hidden');
        uploadUI.classList.remove('hidden');
        
        // 显示错误提示
        const toast = document.createElement('div');
        toast.className = 'toast toast-top toast-center z-50';
        
        const alert = document.createElement('div');
        alert.className = 'alert alert-error';
        alert.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <span>${message}</span>
        `;
        
        toast.appendChild(alert);
        document.body.appendChild(toast);
        
        // 3秒后移除提示
        setTimeout(() => {
            toast.classList.add('animate__animated', 'animate__fadeOut');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 500);
        }, 3000);
    }
    
    // 显示上传结果
    function showUploadResult(fileInfo) {
        uploadProgress.classList.add('hidden');
        uploadResult.classList.remove('hidden');
        
        const safeFilename = escapeHtml(fileInfo.filename);
        resultFilename.textContent = fileInfo.filename;
        resultFilesize.textContent = fileInfo.size;
        
        // 设置各种链接
        const fileUrl = fileInfo.url;
        originalLink.value = fileUrl;
        
        // 根据文件类型设置Markdown和HTML链接
        if (fileInfo.type === 'image') {
            markdownLink.value = `![${safeFilename}](${fileUrl})`;
            htmlLink.value = `<img src="${fileUrl}" alt="${safeFilename}" />`;
            
            // 设置图片预览
            previewContainer.innerHTML = `<img src="${fileUrl}" class="w-full h-full object-cover" />`;
        } else if (fileInfo.type === 'video') {
            markdownLink.value = `[${safeFilename}](${fileUrl})`;
            htmlLink.value = `<video src="${fileUrl}" controls title="${safeFilename}"></video>`;
            
            // 设置视频预览 - 修复覆盖问题
            previewContainer.innerHTML = `
                <div class="relative w-full h-full">
                    <video src="${fileUrl}" class="w-full h-full object-cover" muted></video>
                    <a href="${fileUrl}" target="_blank" class="play-button absolute inset-0 flex items-center justify-center bg-black/30 z-10">
                        <i class="fas fa-play text-white text-2xl"></i>
                    </a>
                </div>
            `;
        } else if (fileInfo.type === 'audio') {
            markdownLink.value = `[${safeFilename}](${fileUrl})`;
            htmlLink.value = `<audio src="${fileUrl}" controls title="${safeFilename}"></audio>`;
            
            // 设置音频预览
            previewContainer.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full w-full">
                    <i class="fas fa-music text-4xl text-accent mb-2"></i>
                    <p class="text-xs text-gray-500 mb-2">${fileInfo.extension.toUpperCase()}</p>
                    <audio src="${fileUrl}" controls class="w-full max-w-full"></audio>
                </div>
            `;
        } else {
            markdownLink.value = `[${safeFilename}](${fileUrl})`;
            htmlLink.value = `<a href="${fileUrl}">${safeFilename}</a>`;
            
            // 设置通用文件图标
            previewContainer.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full">
                    <i class="fas fa-file-alt text-4xl text-gray-400 mb-2"></i>
                    <p class="text-xs text-gray-500">${fileInfo.extension.toUpperCase()}</p>
                </div>
            `;
        }
        
        // 成功提示
        const toast = document.createElement('div');
        toast.className = 'toast toast-top toast-center z-50';
        
        const alert = document.createElement('div');
        alert.className = 'alert alert-success';
        alert.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <span>上传成功!</span>
        `;
        
        toast.appendChild(alert);
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('animate__animated', 'animate__fadeOut');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 500);
        }, 2000);
    }
    
    // 继续上传按钮
    uploadNewBtn.addEventListener('click', () => {
        uploadResult.classList.add('hidden');
        uploadUI.classList.remove('hidden');
        fileInput.value = '';
    });
    
    // 复制链接按钮
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-target');
            const input = document.getElementById(targetId);
            input.select();
            document.execCommand('copy');
            
            // 显示复制成功提示
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check mr-1"></i> 已复制';
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.classList.remove('btn-success');
            }, 1500);
        });
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?> 