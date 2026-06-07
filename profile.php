<?php
require_once 'config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$user = get_current_session_user();
$message = '';

// 处理修改昵称和密码
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $nickname = trim($_POST['nickname']);
        
        if (!empty($nickname)) {
            $users = get_users();
            foreach ($users as &$u) {
                if ($u['id'] === $user['id']) {
                    $u['nickname'] = $nickname;
                    break;
                }
            }
            save_users($users);
            
            // 更新会话中的用户信息
            $_SESSION['user']['nickname'] = $nickname;
            $user = $_SESSION['user'];
            
            $message = '昵称更新成功';
        }
    } elseif (isset($_POST['update_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (md5($old_password) !== $user['password']) {
            $message = '原密码错误';
        } elseif ($new_password !== $confirm_password) {
            $message = '新密码与确认密码不一致';
        } elseif (strlen($new_password) < 6) {
            $message = '新密码长度不能少于6位';
        } else {
            $users = get_users();
            foreach ($users as &$u) {
                if ($u['id'] === $user['id']) {
                    $u['password'] = md5($new_password);
                    break;
                }
            }
            save_users($users);
            
            // 更新会话中的用户信息
            $_SESSION['user']['password'] = md5($new_password);
            $user = $_SESSION['user'];
            
            $message = '密码更新成功';
        }
    } elseif (isset($_POST['regenerate_key'])) {
        $new_api_key = md5($user['account'] . time());
        
        $users = get_users();
        foreach ($users as &$u) {
            if ($u['id'] === $user['id']) {
                $u['api_key'] = $new_api_key;
                break;
            }
        }
        save_users($users);
        
        // 更新会话中的用户信息
        $_SESSION['user']['api_key'] = $new_api_key;
        $user = $_SESSION['user'];
        
        $message = 'API密钥已重新生成';
    }
}

// 统计调用的API数量
$api_call_count = 0;
if (isset($user['api_calls']) && is_array($user['api_calls'])) {
    $api_call_count = count($user['api_calls']);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人中心 - <?php echo get_settings()['site_name']; ?></title>
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
            margin-bottom: 20px;
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
        <a href="index.php"><i class="fas fa-home me-2"></i>首页</a>
        <a href="api_list.php"><i class="fas fa-list me-2"></i>API列表</a>
        <a href="profile.php" class="active"><i class="fas fa-user me-2"></i>个人中心</a>
        <?php if (is_admin()): ?>
        <a href="admin/index.php"><i class="fas fa-cog me-2"></i>后台管理</a>
        <?php endif; ?>
        <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>退出登录</a>
    </div>
    
    <!-- 主内容区 -->
    <div class="main-content">
        <!-- 顶部导航 -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <button class="btn" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="navbar-brand mx-auto" href="#">个人中心</a>
            </div>
        </nav>
        
        <div class="container mt-4">
            <?php if ($message): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">个人信息</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-circle fa-5x text-primary"></i>
                            </div>
                            <h5><?php echo htmlspecialchars($user['nickname']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars($user['account']); ?></p>
                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'success' : 'secondary'; ?>">
                                <?php echo $user['role'] === 'admin' ? '管理员' : '普通用户'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">API统计</h5>
                        </div>
                        <div class="card-body">
                            <p>调用接口数量: <strong><?php echo $api_call_count; ?></strong></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">修改昵称</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="nickname" class="form-label">昵称</label>
                                    <input type="text" class="form-control" id="nickname" name="nickname" value="<?php echo htmlspecialchars($user['nickname']); ?>" required>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">更新昵称</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">修改密码</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="old_password" class="form-label">原密码</label>
                                    <input type="password" class="form-control" id="old_password" name="old_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">新密码</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">确认新密码</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" name="update_password" class="btn btn-primary">更新密码</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">API密钥</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="api_key" class="form-label">您的API密钥</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="api_key" value="<?php echo htmlspecialchars($user['api_key']); ?>" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()">复制</button>
                                </div>
                            </div>
                            <form method="POST">
                                <button type="submit" name="regenerate_key" class="btn btn-warning" onclick="return confirm('确定要重新生成API密钥吗？旧密钥将失效。')">重新生成密钥</button>
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
        
        // 复制到剪贴板
        function copyToClipboard() {
            var copyText = document.getElementById("api_key");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            
            // 显示提示
            var originalText = document.querySelector('.input-group .btn').innerText;
            document.querySelector('.input-group .btn').innerText = '已复制';
            setTimeout(function() {
                document.querySelector('.input-group .btn').innerText = originalText;
            }, 2000);
        }
    </script>
</body>
</html>