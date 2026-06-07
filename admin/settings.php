<?php
require_once '../config.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../index.php');
    exit();
}

$user = get_current_session_user();
$settings = get_settings();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings['site_name'] = trim($_POST['site_name']);
    $settings['site_description'] = trim($_POST['site_description']);
    $settings['site_notice'] = trim($_POST['site_notice']);
    $settings['popup_content'] = trim($_POST['popup_content']);
    $settings['audio_url'] = trim($_POST['audio_url']);
    $settings['icp_number'] = trim($_POST['icp_number']);
    $settings['copyright'] = trim($_POST['copyright']);
    
    // 处理友情链接
    $friend_links = [];
    if (isset($_POST['friend_link_name']) && is_array($_POST['friend_link_name'])) {
        foreach ($_POST['friend_link_name'] as $index => $name) {
            if (!empty($name) && !empty($_POST['friend_link_url'][$index])) {
                $friend_links[] = [
                    'name' => $name,
                    'url' => $_POST['friend_link_url'][$index]
                ];
            }
        }
    }
    $settings['friend_links'] = $friend_links;
    
    save_settings($settings);
    $message = '设置已保存';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>站点设置 - <?php echo get_settings()['site_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e90ff;
            --secondary-color: #4682b4;
        }
        
        body {
            background-color: #f8f9fa;
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
        
        .sidebar a:hover, .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .main-content {
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }
        
        .sidebar.active ~ .main-content {
            margin-left: 250px;
        }
        
        .friend-link-row {
            margin-bottom: 10px;
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
        }
    </style>
</head>
<body>
    <!-- 侧边栏 -->
    <div class="sidebar" id="sidebar">
        <a href="index.php"><i class="fas fa-tachometer-alt me-2"></i>数据面板</a>
        <a href="users.php"><i class="fas fa-users me-2"></i>用户管理</a>
        <a href="apis.php"><i class="fas fa-plug me-2"></i>API管理</a>
        <a href="settings.php" class="active"><i class="fas fa-cog me-2"></i>站点设置</a>
        <a href="../index.php"><i class="fas fa-home me-2"></i>返回首页</a>
    </div>
    
    <!-- 主内容区 -->
    <div class="main-content">
        <!-- 顶部导航 -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <button class="btn" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="navbar-brand" href="#">站点设置</a>
                <div class="d-flex align-items-center">
                    <span class="me-3">欢迎您，<?php echo htmlspecialchars($user['nickname']); ?></span>
                    <a href="../logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> 退出
                    </a>
                </div>
            </div>
        </nav>
        
        <div class="container-fluid mt-4">
            <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">站点设置</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">站点名称</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_description" class="form-label">站点介绍</label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="3" required><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_notice" class="form-label">站点公告</label>
                                    <textarea class="form-control" id="site_notice" name="site_notice" rows="3" required><?php echo htmlspecialchars($settings['site_notice']); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="popup_content" class="form-label">弹窗提醒内容</label>
                                    <textarea class="form-control" id="popup_content" name="popup_content" rows="3" required><?php echo htmlspecialchars($settings['popup_content']); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="audio_url" class="form-label">音频链接</label>
                                    <input type="text" class="form-control" id="audio_url" name="audio_url" value="<?php echo htmlspecialchars($settings['audio_url']); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">友情链接</label>
                                    <div id="friend-links-container">
                                        <?php if (!empty($settings['friend_links'])): ?>
                                            <?php foreach ($settings['friend_links'] as $index => $link): ?>
                                            <div class="friend-link-row row">
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control" name="friend_link_name[]" placeholder="链接名称" value="<?php echo htmlspecialchars($link['name']); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control" name="friend_link_url[]" placeholder="链接地址" value="<?php echo htmlspecialchars($link['url']); ?>">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-danger remove-friend-link">-</button>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="friend-link-row row">
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control" name="friend_link_name[]" placeholder="链接名称">
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control" name="friend_link_url[]" placeholder="链接地址">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-danger remove-friend-link">-</button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="btn btn-secondary mt-2" id="add-friend-link">添加链接</button>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="icp_number" class="form-label">备案号</label>
                                    <input type="text" class="form-control" id="icp_number" name="icp_number" value="<?php echo htmlspecialchars($settings['icp_number']); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="copyright" class="form-label">版权所有者</label>
                                    <input type="text" class="form-control" id="copyright" name="copyright" value="<?php echo htmlspecialchars($settings['copyright']); ?>" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">保存设置</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 侧边栏切换
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // 添加友情链接
        document.getElementById('add-friend-link').addEventListener('click', function() {
            const container = document.getElementById('friend-links-container');
            const row = document.createElement('div');
            row.className = 'friend-link-row row';
            row.innerHTML = `
                <div class="col-md-5">
                    <input type="text" class="form-control" name="friend_link_name[]" placeholder="链接名称">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="friend_link_url[]" placeholder="链接地址">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-friend-link">-</button>
                </div>
            `;
            container.appendChild(row);
            
            // 为新添加的删除按钮绑定事件
            row.querySelector('.remove-friend-link').addEventListener('click', function() {
                container.removeChild(row);
            });
        });
        
        // 为现有的删除按钮绑定事件
        document.querySelectorAll('.remove-friend-link').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('.friend-link-row');
                row.parentNode.removeChild(row);
            });
        });
    </script>
</body>
</html>