<?php require_once __DIR__ . '/header.php'; ?>

<div class="flex flex-col items-center">
    <div class="card bg-base-100 shadow-xl w-full max-w-2xl">
        <div class="card-body">
            <h2 class="card-title">安装图床</h2>
            <p class="text-sm text-gray-500 mb-4">请填写数据库信息以完成安装</p>
            
            <form id="install-form" class="mt-4">
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text">数据库主机</span>
                    </label>
                    <input type="text" id="db_host" name="db_host" class="input input-bordered w-full" value="localhost" required />
                </div>
                
                <div class="form-control w-full mt-2">
                    <label class="label">
                        <span class="label-text">数据库用户名</span>
                    </label>
                    <input type="text" id="db_user" name="db_user" class="input input-bordered w-full" required />
                </div>
                
                <div class="form-control w-full mt-2">
                    <label class="label">
                        <span class="label-text">数据库密码</span>
                    </label>
                    <input type="password" id="db_pass" name="db_pass" class="input input-bordered w-full" />
                </div>
                
                <div class="form-control w-full mt-2">
                    <label class="label">
                        <span class="label-text">数据库名</span>
                    </label>
                    <input type="text" id="db_name" name="db_name" class="input input-bordered w-full" required />
                </div>
                
                <div class="form-control w-full mt-2">
                    <label class="label">
                        <span class="label-text">表前缀</span>
                    </label>
                    <input type="text" id="db_prefix" name="db_prefix" class="input input-bordered w-full" value="imgbed_" required />
                </div>
                
                <div class="form-control w-full mt-4">
                    <button type="submit" class="btn btn-primary" id="install-btn">安装</button>
                </div>
            </form>
            
            <div id="install-result" class="hidden mt-4">
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>安装成功！</span>
                </div>
                <div class="mt-4 text-center">
                    <a href="/" class="btn btn-primary">开始使用</a>
                </div>
            </div>
            
            <div id="install-error" class="hidden mt-4">
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span id="error-message">安装失败，请检查数据库信息。</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const installForm = document.getElementById('install-form');
    const installBtn = document.getElementById('install-btn');
    const installResult = document.getElementById('install-result');
    const installError = document.getElementById('install-error');
    const errorMessage = document.getElementById('error-message');
    
    installForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 显示加载状态
        installBtn.disabled = true;
        installBtn.innerHTML = '<span class="loading loading-spinner"></span> 安装中...';
        
        // 隐藏之前的结果
        installResult.classList.add('hidden');
        installError.classList.add('hidden');
        
        // 获取表单数据
        const formData = new FormData(installForm);
        
        // 发送Ajax请求
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/do-install', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        installForm.classList.add('hidden');
                        installResult.classList.remove('hidden');
                    } else {
                        errorMessage.textContent = response.error || '安装失败，请检查数据库信息。';
                        installError.classList.remove('hidden');
                        installBtn.disabled = false;
                        installBtn.textContent = '重试安装';
                    }
                } catch (e) {
                    errorMessage.textContent = '解析响应失败';
                    installError.classList.remove('hidden');
                    installBtn.disabled = false;
                    installBtn.textContent = '重试安装';
                }
            } else {
                errorMessage.textContent = '服务器错误';
                installError.classList.remove('hidden');
                installBtn.disabled = false;
                installBtn.textContent = '重试安装';
            }
        };
        
        xhr.onerror = function() {
            errorMessage.textContent = '网络错误';
            installError.classList.remove('hidden');
            installBtn.disabled = false;
            installBtn.textContent = '重试安装';
        };
        
        xhr.send(formData);
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?> 