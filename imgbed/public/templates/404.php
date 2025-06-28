<?php require_once __DIR__ . '/header.php'; ?>

<div class="flex flex-col items-center justify-center py-12 animate__animated animate__fadeIn">
    <div class="max-w-md text-center p-6">
        <div class="mb-8">
            <i class="fas fa-exclamation-triangle text-8xl text-warning mb-4 animate__animated animate__headShake animate__delay-1s"></i>
            <h1 class="text-6xl font-bold gradient-heading">404</h1>
            <p class="mt-2 text-xl text-gray-600 dark:text-gray-300">找不到页面或资源</p>
        </div>
        
        <div class="card bg-base-100 shadow-xl p-6 mb-8">
            <p class="text-lg">您请求的页面或文件已被移除、重命名或可能已过期自动删除。</p>
        </div>
        
        <a href="/" class="btn btn-primary btn-lg gap-2">
            <i class="fas fa-home"></i>
            返回首页
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 页面自动倒计时返回首页
    let countdown = 15;
    const countdownInterval = setInterval(function() {
        countdown--;
        if (countdown <= 0) {
            clearInterval(countdownInterval);
            window.location.href = '/';
        }
    }, 1000);
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?> 