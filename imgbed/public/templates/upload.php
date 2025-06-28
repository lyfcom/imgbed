<?php require_once __DIR__ . '/header.php'; ?>

<div class="flex flex-col items-center">
    <div class="card bg-base-100 shadow-xl w-full max-w-2xl">
        <div class="card-body">
            <h2 class="card-title">上传图片/视频</h2>
            <p class="text-sm text-gray-500 mb-4">支持拖拽上传，访问3次或3天后自动删除</p>
            
            <div id="upload-area" class="upload-area rounded-lg p-10 text-center cursor-pointer">
                <div id="upload-ui">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="mt-4 text-lg">点击或拖拽文件到这里上传</p>
                    <p class="text-sm text-gray-500">最大文件大小: <?php echo floor($config['upload']['max_size'] / 1024 / 1024); ?>MB</p>
                </div>
                
                <div id="upload-progress" class="hidden">
                    <div class="text-center mb-4">
                        <div class="loading loading-spinner loading-lg"></div>
                    </div>
                    <p id="upload-status">正在上传...</p>
                    <progress id="progress-bar" class="progress progress-primary w-full mt-2" value="0" max="100"></progress>
                </div>
            </div>
            
            <input type="file" id="file-input" class="hidden" />
            
            <!-- 上传成功后显示的结果 -->
            <div id="upload-result" class="hidden mt-4">
                <div class="border rounded-lg p-4 bg-base-200">
                    <div class="flex items-center mb-4">
                        <div id="preview-container" class="w-20 h-20 rounded overflow-hidden bg-gray-200 mr-4 flex items-center justify-center">
                            <!-- 预览将在这里显示 -->
                        </div>
                        <div>
                            <h3 id="result-filename" class="font-bold"></h3>
                            <p id="result-filesize" class="text-sm text-gray-600"></p>
                            <p class="text-xs text-orange-500 mt-1">文件访问3次或3天后将自动删除</p>
                        </div>
                    </div>
                    
                    <div class="divider">链接格式</div>
                    
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="label">
                                <span class="label-text font-medium">原始链接</span>
                            </label>
                            <div class="join w-full">
                                <input id="original-link" type="text" class="input input-bordered join-item w-full" readonly />
                                <button class="btn join-item copy-btn" data-target="original-link">复制</button>
                            </div>
                        </div>
                        
                        <div>
                            <label class="label">
                                <span class="label-text font-medium">Markdown格式</span>
                            </label>
                            <div class="join w-full">
                                <input id="markdown-link" type="text" class="input input-bordered join-item w-full" readonly />
                                <button class="btn join-item copy-btn" data-target="markdown-link">复制</button>
                            </div>
                        </div>
                        
                        <div>
                            <label class="label">
                                <span class="label-text font-medium">HTML格式</span>
                            </label>
                            <div class="join w-full">
                                <input id="html-link" type="text" class="input input-bordered join-item w-full" readonly />
                                <button class="btn join-item copy-btn" data-target="html-link">复制</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-actions justify-end mt-4">
                        <button id="upload-new" class="btn btn-primary">继续上传</button>
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
        toast.className = 'toast toast-top toast-center';
        
        const alert = document.createElement('div');
        alert.className = 'alert alert-error';
        alert.innerHTML = `<span>${message}</span>`;
        
        toast.appendChild(alert);
        document.body.appendChild(toast);
        
        // 3秒后移除提示
        setTimeout(() => {
            document.body.removeChild(toast);
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
            
            // 设置视频预览
            previewContainer.innerHTML = `<video src="${fileUrl}" class="w-full h-full object-cover"></video>`;
        } else {
            markdownLink.value = `[${safeFilename}](${fileUrl})`;
            htmlLink.value = `<a href="${fileUrl}">${safeFilename}</a>`;
            
            // 设置通用文件图标
            previewContainer.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>`;
        }
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
            const originalText = button.textContent;
            button.textContent = '已复制';
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.textContent = originalText;
                button.classList.remove('btn-success');
            }, 1500);
        });
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?> 