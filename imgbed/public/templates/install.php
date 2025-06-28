<?php require_once __DIR__ . '/header.php'; ?>

<div class="flex flex-col items-center">
    <div class="text-center mb-8 animate__animated animate__fadeIn">
        <h1 class="text-4xl font-bold gradient-heading mb-2">安装向导</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300">只需几个简单步骤即可完成图床配置</p>
    </div>
    
    <div class="card bg-base-100 shadow-xl w-full max-w-2xl animate__animated animate__fadeInUp">
        <div class="card-body">
            <div class="flex items-center mb-6">
                <div class="relative">
                    <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white font-bold">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-success rounded-full border-2 border-white"></div>
                </div>
                <div class="ml-4">
                    <h2 class="card-title">数据库配置</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">请填写数据库连接信息</p>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <ul class="steps steps-vertical lg:steps-horizontal w-full mb-8">
                <li class="step step-primary">准备安装</li>
                <li class="step step-primary">数据库配置</li>
                <li class="step">创建表</li>
                <li class="step">完成</li>
            </ul>
            
            <form id="install-form" class="mt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text flex items-center gap-2">
                                <i class="fas fa-server text-primary"></i> 数据库主机
                            </span>
                            <span class="label-text-alt text-gray-500">通常为 localhost</span>
                        </label>
                        <input type="text" id="db_host" name="db_host" class="input input-bordered w-full" value="localhost" required />
                    </div>
                    
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text flex items-center gap-2">
                                <i class="fas fa-database text-primary"></i> 数据库名称
                            </span>
                        </label>
                        <input type="text" id="db_name" name="db_name" class="input input-bordered w-full" placeholder="您的数据库名" required />
                    </div>
                    
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text flex items-center gap-2">
                                <i class="fas fa-user text-primary"></i> 数据库用户名
                            </span>
                        </label>
                        <input type="text" id="db_user" name="db_user" class="input input-bordered w-full" required />
                    </div>
                    
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text flex items-center gap-2">
                                <i class="fas fa-key text-primary"></i> 数据库密码
                            </span>
                        </label>
                        <input type="password" id="db_pass" name="db_pass" class="input input-bordered w-full" />
                    </div>
                </div>
                
                <div class="form-control w-full mt-4">
                    <label class="label">
                        <span class="label-text flex items-center gap-2">
                            <i class="fas fa-table text-primary"></i> 数据表前缀
                        </span>
                        <span class="label-text-alt text-gray-500">多个项目共用一个数据库时避免冲突</span>
                    </label>
                    <input type="text" id="db_prefix" name="db_prefix" class="input input-bordered w-full" value="imgbed_" required />
                </div>
                
                <div class="divider">管理员设置</div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">管理员密码</span>
                    </label>
                    <div class="relative">
                        <input type="password" id="admin_password" name="admin_password" class="input input-bordered w-full" required />
                        <button type="button" class="toggle-password absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <label class="label">
                        <span class="label-text-alt">设置一个强密码以保护管理后台</span>
                    </label>
                </div>
                
                <div class="alert alert-info mt-6 flex items-start">
                    <div>
                        <i class="fas fa-info-circle mt-1"></i>
                    </div>
                    <div>
                        <h3 class="font-bold">安装提示</h3>
                        <p class="text-sm">请确保您已经创建了数据库，并且用户拥有足够的权限。安装完成后，出于安全考虑，将自动创建安装锁以防止重复安装。</p>
                    </div>
                </div>
                
                <div class="form-control w-full mt-6">
                    <button type="submit" class="btn btn-primary" id="install-btn">
                        <i class="fas fa-magic mr-2"></i> 开始安装
                    </button>
                </div>
            </form>
            
            <div id="install-result" class="hidden mt-6 animate__animated animate__fadeIn">
                <div class="alert alert-success shadow-lg">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div>
                            <h3 class="font-bold">安装成功！</h3>
                            <div class="text-sm">图床已准备就绪，您可以开始上传使用。</div>
                        </div>
                    </div>
                </div>
                
                <div class="card bg-base-200 p-4 mt-4">
                    <h4 class="font-bold text-lg mb-2 flex items-center gap-2">
                        <i class="fas fa-check-circle text-success"></i> 安装已完成
                    </h4>
                    <ul class="list-disc list-inside space-y-2 text-sm ml-2">
                        <li>数据库连接成功</li>
                        <li>数据表已创建</li>
                        <li>配置文件已更新</li>
                    </ul>
                </div>
                
                <div class="mt-8 text-center">
                    <a href="/" class="btn btn-primary btn-lg">
                        <i class="fas fa-home mr-2"></i> 开始使用图床
                    </a>
                </div>
            </div>
            
            <div id="install-error" class="hidden mt-6 animate__animated animate__shakeX">
                <div class="alert alert-error shadow-lg">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div>
                            <h3 class="font-bold">安装失败</h3>
                            <div id="error-message" class="text-sm">安装过程中发生错误，请检查数据库信息。</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button id="retry-btn" class="btn btn-outline">
                        <i class="fas fa-redo mr-2"></i> 重试安装
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-10 w-full max-w-2xl">
        <div class="collapse collapse-arrow bg-base-100 shadow-lg rounded-box">
            <input type="checkbox" /> 
            <div class="collapse-title font-medium flex items-center">
                <i class="fas fa-question-circle mr-2 text-primary"></i> 安装帮助
            </div>
            <div class="collapse-content"> 
                <div class="space-y-4 text-sm">
                    <div>
                        <h4 class="font-bold">数据库要求</h4>
                        <p>需要MySQL 5.7+数据库，并确保您拥有创建表的权限。</p>
                    </div>
                    <div>
                        <h4 class="font-bold">表前缀的作用</h4>
                        <p>如果您的数据库中已有其他项目，使用表前缀可以避免表名冲突。</p>
                    </div>
                    <div>
                        <h4 class="font-bold">安装失败怎么办？</h4>
                        <p>请检查数据库连接信息是否正确，并确保数据库已创建且用户有足够权限。</p>
                    </div>
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
    const retryBtn = document.getElementById('retry-btn');
    
    // 密码可见性切换
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const passwordInput = this.parentElement.querySelector('input');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            if (type === 'password') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    });

    installForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 显示加载状态
        installBtn.disabled = true;
        installBtn.innerHTML = '<span class="loading loading-spinner loading-sm mr-2"></span> 正在安装...';
        
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
                        
                        // 更新步骤显示
                        const steps = document.querySelectorAll('.step');
                        steps.forEach(step => step.classList.add('step-primary'));
                        
                        // 显示成功提示
                        const toast = document.createElement('div');
                        toast.className = 'toast toast-top toast-center z-50';
                        
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success';
                        alert.innerHTML = `
                            <i class="fas fa-check-circle"></i>
                            <span>安装成功！</span>
                        `;
                        
                        toast.appendChild(alert);
                        document.body.appendChild(toast);
                        
                        setTimeout(() => {
                            toast.classList.add('animate__animated', 'animate__fadeOut');
                            setTimeout(() => {
                                document.body.removeChild(toast);
                            }, 500);
                        }, 2000);
                    } else {
                        errorMessage.textContent = response.error || '安装失败，请检查数据库信息。';
                        installError.classList.remove('hidden');
                        installBtn.disabled = false;
                        installBtn.innerHTML = '<i class="fas fa-magic mr-2"></i> 开始安装';
                    }
                } catch (e) {
                    errorMessage.textContent = '解析响应失败';
                    installError.classList.remove('hidden');
                    installBtn.disabled = false;
                    installBtn.innerHTML = '<i class="fas fa-magic mr-2"></i> 开始安装';
                }
            } else {
                errorMessage.textContent = '服务器错误';
                installError.classList.remove('hidden');
                installBtn.disabled = false;
                installBtn.innerHTML = '<i class="fas fa-magic mr-2"></i> 开始安装';
            }
        };
        
        xhr.onerror = function() {
            errorMessage.textContent = '网络错误';
            installError.classList.remove('hidden');
            installBtn.disabled = false;
            installBtn.innerHTML = '<i class="fas fa-magic mr-2"></i> 开始安装';
        };
        
        xhr.send(formData);
    });
    
    // 重试按钮
    retryBtn.addEventListener('click', function() {
        installError.classList.add('hidden');
        installBtn.disabled = false;
        installBtn.innerHTML = '<i class="fas fa-magic mr-2"></i> 开始安装';
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?> 