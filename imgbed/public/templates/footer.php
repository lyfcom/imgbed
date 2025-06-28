    </div><?php // 内容区域结束 ?>
    
    <footer class="footer footer-center p-10 bg-base-200 text-base-content mt-12">
        <div class="grid grid-flow-col gap-4">
            <a href="/" class="link link-hover flex items-center gap-1">
                <i class="fas fa-home text-xs"></i> 首页
            </a>
            <a href="/about" class="link link-hover flex items-center gap-1">
                <i class="fas fa-info-circle text-xs"></i> 关于
            </a>
            <a href="javascript:void(0)" onclick="document.getElementById('privacy-modal').showModal()" class="link link-hover flex items-center gap-1">
                <i class="fas fa-shield-alt text-xs"></i> 隐私政策
            </a>
        </div> 
        <div>
            <div class="grid grid-flow-col gap-4">
                <a class="hover:text-primary transition-colors"><i class="fab fa-weixin text-xl"></i></a>
                <a class="hover:text-primary transition-colors"><i class="fab fa-qq text-xl"></i></a>
                <a class="hover:text-primary transition-colors"><i class="fab fa-weibo text-xl"></i></a>
                <a class="hover:text-primary transition-colors"><i class="fab fa-github text-xl"></i></a>
            </div>
        </div> 
        <div>
            <p>简易图床 &copy; <?php echo date('Y'); ?> - 文件自动过期 · 简单快捷</p>
            <p class="text-xs text-gray-500 mt-1">免责声明：请勿上传违法、侵权或敏感内容</p>
        </div>
    </footer>
    
    <!-- 隐私政策弹窗 -->
    <dialog id="privacy-modal" class="modal">
        <form method="dialog" class="modal-box">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i class="fas fa-shield-alt text-primary"></i> 隐私政策
            </h3>
            <div class="py-4 text-sm space-y-2">
                <p>我们尊重您的隐私，并且致力于保护您的个人信息。</p>
                <div class="w-full md:w-1/2 lg:w-1/4 mb-6 md:mb-0">
                    <h3 class="font-bold text-lg mb-2">政策</h3>
                    <p class="font-medium mt-4">文件存储政策</p>
                    <p>所有上传的文件将在被访问 <?php echo $config['expiration']['max_views']; ?> 次或存储 <?php echo $config['expiration']['max_days']; ?> 天后自动删除，不会永久保存。</p>
                    <p class="font-medium mt-4">无跟踪政策</p>
                    <p>我们尊重您的隐私，不会记录您的IP地址或其他个人身份信息。</p>
                </div>
                <p class="font-medium mt-4">法律责任</p>
                <p>用户需对上传的内容负责，请勿上传违法或侵权内容。</p>
            </div>
            <div class="modal-action">
                <button class="btn">关闭</button>
            </div>
        </form>
        <form method="dialog" class="modal-backdrop">
            <button>关闭</button>
        </form>
    </dialog>
    
    <!-- 回到顶部按钮 -->
    <button id="back-to-top" class="fixed bottom-6 right-6 btn btn-circle btn-primary opacity-0 invisible transition-all duration-300">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <script>
    // 回到顶部功能
    document.addEventListener('DOMContentLoaded', function() {
        const backToTopBtn = document.getElementById('back-to-top');
        
        // 滚动事件监听
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.replace('opacity-0', 'opacity-100');
                backToTopBtn.classList.replace('invisible', 'visible');
            } else {
                backToTopBtn.classList.replace('opacity-100', 'opacity-0');
                setTimeout(() => {
                    backToTopBtn.classList.replace('visible', 'invisible');
                }, 300);
            }
        });
        
        // 点击事件
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
    </script>
</body>
</html> 