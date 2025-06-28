<?php require_once __DIR__ . '/header.php'; ?>

<div class="flex flex-col items-center">
    <div class="text-center mb-8 animate__animated animate__fadeIn">
        <h1 class="text-4xl font-bold gradient-heading mb-2">关于我们的图床</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300">简单、快速、安全的文件分享服务</p>
    </div>
    
    <div class="card bg-base-100 shadow-xl w-full max-w-3xl mb-8 animate__animated animate__fadeInUp">
        <div class="card-body">
            <h2 class="text-2xl font-bold mb-4 flex items-center">
                <i class="fas fa-info-circle text-primary mr-2"></i> 服务介绍
            </h2>
            
            <p class="mb-4">我们提供一个简单易用的临时文件存储服务，让您能够快速分享图片、视频和音频等文件，无需注册即可使用。</p>
            
            <div class="divider"></div>
            
            <div class="prose dark:prose-invert">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-bold mb-2">简单高效</h3>
                        <p class="text-sm">无需注册，即刻上传。支持拖拽、复制粘贴等多种方式，提供极致的用户体验。</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold mb-2">安全可靠</h3>
                        <p class="text-sm">所有文件访问 <?php echo $config['expiration']['max_views']; ?> 次或保存 <?php echo $config['expiration']['max_days']; ?> 天后自动删除，不永久存储敏感资料。</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold mb-2">响应式设计</h3>
                        <p class="text-sm">完美适配各种设备，从手机到电脑，随时随地使用。</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold mb-2">多种分享格式</h3>
                        <p class="text-sm">支持原始链接、Markdown和HTML多种分享格式。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow-xl w-full max-w-3xl mb-8 animate__animated animate__fadeInUp animate__delay-1s">
        <div class="card-body">
            <h2 class="text-2xl font-bold mb-4 flex items-center">
                <i class="fas fa-question-circle text-primary mr-2"></i> 常见问题解答
            </h2>
            
            <div class="space-y-4">
                <div class="collapse collapse-arrow bg-base-200 rounded-lg">
                    <input type="checkbox" /> 
                    <div class="collapse-title font-medium">
                        文件会存储多久？
                    </div>
                    <div class="collapse-content"> 
                        <p>文件将在以下两种情况下被自动删除：</p>
                        <ul class="list-disc list-inside mt-2 ml-4">
                            <li>当文件被访问超过 <?php echo $config['expiration']['max_views']; ?> 次</li>
                            <li>当文件上传后超过 <?php echo $config['expiration']['max_days']; ?> 天</li>
                        </ul>
                        <p class="mt-2">这是为了确保我们的服务不被用于长期文件存储，并保护用户隐私。</p>
                    </div>
                </div>
                
                <div class="collapse collapse-arrow bg-base-200 rounded-lg">
                    <input type="checkbox" /> 
                    <div class="collapse-title font-medium">
                        支持哪些文件类型？
                    </div>
                    <div class="collapse-content"> 
                        <p>我们支持大多数常见的图片、视频和音频格式，包括但不限于：</p>
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            <div>
                                <p class="font-medium">图片格式：</p>
                                <ul class="list-disc list-inside ml-4">
                                    <li>JPG/JPEG</li>
                                    <li>PNG</li>
                                    <li>GIF</li>
                                    <li>WEBP</li>
                                    <li>BMP</li>
                                    <li>SVG</li>
                                </ul>
                            </div>
                            <div>
                                <p class="font-medium">视频格式：</p>
                                <ul class="list-disc list-inside ml-4">
                                    <li>MP4</li>
                                    <li>WEBM</li>
                                    <li>MOV</li>
                                    <li>AVI</li>
                                    <li>MKV</li>
                                </ul>
                            </div>
                            <div>
                                <p class="font-medium">音频格式：</p>
                                <ul class="list-disc list-inside ml-4">
                                    <li>MP3</li>
                                    <li>WAV</li>
                                    <li>FLAC</li>
                                    <li>OGG</li>
                                    <li>AAC</li>
                                    <li>M4A</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="collapse collapse-arrow bg-base-200 rounded-lg">
                    <input type="checkbox" /> 
                    <div class="collapse-title font-medium">
                        文件大小限制是多少？
                    </div>
                    <div class="collapse-content"> 
                        <p>单个文件最大上传大小为 <?php echo floor($config['upload']['max_size'] / 1024 / 1024); ?>MB。</p>
                    </div>
                </div>
                
                <div class="collapse collapse-arrow bg-base-200 rounded-lg">
                    <input type="checkbox" /> 
                    <div class="collapse-title font-medium">
                        我的文件安全吗？
                    </div>
                    <div class="collapse-content"> 
                        <p>我们非常重视您的隐私和文件安全：</p>
                        <ul class="list-disc list-inside mt-2 ml-4">
                            <li>所有文件都会在短时间内自动删除，不会长期保存</li>
                            <li>我们不会查看、分析或共享您上传的文件内容</li>
                            <li>每个文件都有唯一的随机ID，无法被轻易猜测</li>
                        </ul>
                        <p class="mt-2 text-warning">但请注意：互联网并非绝对安全的环境，请勿上传敏感或机密文件。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow-xl w-full max-w-3xl mb-8 animate__animated animate__fadeInUp animate__delay-2s">
        <div class="card-body text-center">
            <h2 class="text-2xl font-bold mb-4">
                <i class="fas fa-heart text-error mr-2"></i> 支持我们
            </h2>
            
            <p class="mb-6">如果您喜欢我们的服务，可以通过以下方式支持我们：</p>
            
            <div class="flex flex-wrap justify-center gap-4">
                <a href="javascript:void(0)" class="btn btn-primary">
                    <i class="fas fa-star mr-2"></i> 收藏网站
                </a>
                <a href="javascript:void(0)" class="btn btn-outline">
                    <i class="fas fa-share-alt mr-2"></i> 分享给朋友
                </a>
            </div>
        </div>
    </div>
    
    <div class="flex justify-center w-full max-w-3xl">
        <a href="/" class="btn btn-primary btn-lg gap-2">
            <i class="fas fa-cloud-upload-alt"></i>
            开始上传
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?> 