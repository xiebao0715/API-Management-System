<?php
require_once 'config.php';

$settings = get_settings();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e90ff;
            --secondary-color: #4682b4;
            --accent-color: #00bfff;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Microsoft YaHei', sans-serif;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color) !important;
        }
        
        .sidebar {
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
            height: 100vh;
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            z-index: 1000;
            transition: left 0.3s ease;
            padding-top: 60px;
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .sidebar a {
            color: white;
            padding: 15px 20px;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .main-content {
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }
        
        .sidebar.active ~ .main-content {
            margin-left: 250px;
        }
        
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
            border: none;
            border-radius: 10px;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .footer {
            background-color: #343a40;
            color: white;
            padding: 30px 0;
            margin-top: 40px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .notification-bar {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .notification-bar i {
            font-size: 1.3rem;
            margin-right: 10px;
        }
        
        /* 弹窗样式 */
        .modal-content {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            border: none;
        }
        
        .modal-footer {
            border-top: 1px solid #eee;
        }
        
        .section-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background: var(--primary-color);
        }
        
        /* 为系统介绍和公告内容添加样式，支持换行 */
        .card-text {
            white-space: pre-line;
            margin-top: 25px;
        }
        
        /* 为弹窗内容添加换行支持 */
        .modal-body {
            white-space: pre-line;
        }
        
        /* 为首页主要内容区域添加边距 */
        .main-container {
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar.active ~ .main-content {
                margin-left: 0;
            }
            
            .notification-bar {
                font-size: 1rem;
                padding: 15px;
            }
            
            /* 移动端适配 */
            .main-container {
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- 音频元素 -->
    <?php if (!empty($settings['audio_url'])): ?>
    <audio id="welcomeAudio" preload="auto"></audio>
    <?php endif; ?>
    
    <!-- 弹窗提醒 -->
    <?php if (!empty($settings['popup_content'])): ?>
    <div class="modal fade" id="popupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">站点提醒</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo htmlspecialchars($settings['popup_content']); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">我知道了</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- 侧边栏 -->
    <div class="sidebar" id="sidebar">
        <a href="index.php"><i class="fas fa-home me-2"></i>首页</a>
        <a href="api_list.php"><i class="fas fa-list me-2"></i>API列表</a>
        <?php if (!is_logged_in()): ?>
        <a href="login.php"><i class="fas fa-sign-in-alt me-2"></i>登录</a>
        <a href="register.php"><i class="fas fa-user-plus me-2"></i>注册</a>
        <?php else: ?>
        <a href="profile.php"><i class="fas fa-user me-2"></i>个人中心</a>
        <?php if (is_admin()): ?>
        <a href="admin/index.php"><i class="fas fa-cog me-2"></i>后台管理</a>
        <?php endif; ?>
        <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>退出登录</a>
        <?php endif; ?>
    </div>
    
    <!-- 主内容区 -->
    <div class="main-content">
        <!-- 顶部导航 -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <button class="btn" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="navbar-brand mx-auto" href="index.php"><?php echo htmlspecialchars($settings['site_name']); ?></a>
            </div>
        </nav>
        
        <!-- 主体内容 -->
        <div class="container main-container">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="section-title card-title">站点介绍</h5>
                            <!-- 修改为支持HTML内容和换行 -->
                            <div class="card-text"><?php echo $settings['site_description']; ?></div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="section-title card-title">最新公告</h5>
                            <!-- 修改为支持HTML内容和换行 -->
                            <div class="card-text"><?php echo $settings['site_notice']; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="section-title card-title">友情链接</h5>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($settings['friend_links'] as $link): ?>
                                <li class="list-group-item">
                                    <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($link['name']); ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 底部 -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <p>本站总访问次数: <?php echo count(get_visit_log()); ?> 次</p>
                        <p>站点已运行时间: 
                            <?php 
                            $start_time = filemtime(DATA_DIR);
                            $uptime = time() - $start_time;
                            $days = floor($uptime / (60 * 60 * 24));
                            echo $days . " 天";
                            ?>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p>
                            <a href="https://beian.miit.gov.cn/" target="_blank" class="text-white">
                                <?php echo htmlspecialchars($settings['icp_number']); ?>
                            </a>
                        </p>
                        <p><?php echo htmlspecialchars($settings['copyright']); ?></p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 侧边栏切换
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // 页面加载完成后显示弹窗（如果存在）
        window.addEventListener('load', function() {
            <?php if (!empty($settings['popup_content'])): ?>
            var popupModal = new bootstrap.Modal(document.getElementById('popupModal'));
            popupModal.show();
            
            // 监听弹窗关闭事件，关闭后播放音频
            document.getElementById('popupModal').addEventListener('hidden.bs.modal', function () {
                <?php if (!empty($settings['audio_url'])): ?>
                var audio = document.getElementById('welcomeAudio');
                if (audio) {
                    audio.src = "<?php echo htmlspecialchars($settings['audio_url']); ?>";
                    var playPromise = audio.play();
                    
                    if (playPromise !== undefined) {
                        playPromise.catch(function(error) {
                            console.log("音频播放被浏览器阻止:", error);
                        });
                    }
                }
                <?php endif; ?>
            });
            <?php else: ?>
            // 如果没有弹窗内容，直接尝试播放音频
            <?php if (!empty($settings['audio_url'])): ?>
            var audio = document.getElementById('welcomeAudio');
            if (audio) {
                audio.src = "<?php echo htmlspecialchars($settings['audio_url']); ?>";
                var playPromise = audio.play();
                
                if (playPromise !== undefined) {
                    playPromise.catch(function(error) {
                        console.log("音频播放被浏览器阻止:", error);
                    });
                }
            }
            <?php endif; ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>