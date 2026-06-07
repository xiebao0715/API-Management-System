<?php
require_once '../config.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../index.php');
    exit();
}

$user = get_current_session_user();
$users = get_users();

// 搜索功能
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search) {
    $filtered_users = [];
    foreach ($users as $u) {
        if (stripos($u['nickname'], $search) !== false || 
            stripos($u['account'], $search) !== false) {
            $filtered_users[] = $u;
        }
    }
    $users = $filtered_users;
}

// 处理密码修改
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $message = '新密码与确认密码不一致';
    } elseif (strlen($new_password) < 6) {
        $message = '新密码长度不能少于6位';
    } else {
        foreach ($users as &$u) {
            if ($u['id'] === $user_id) {
                $u['password'] = md5($new_password);
                break;
            }
        }
        save_users($users);
        $message = '密码更新成功';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理 - <?php echo get_settings()['site_name']; ?></title>
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
        <a href="users.php" class="active"><i class="fas fa-users me-2"></i>用户管理</a>
        <a href="apis.php"><i class="fas fa-plug me-2"></i>API管理</a>
        <a href="settings.php"><i class="fas fa-cog me-2"></i>站点设置</a>
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
                <a class="navbar-brand" href="#">用户管理</a>
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
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">用户列表</h5>
                            <form class="d-flex" method="GET">
                                <input class="form-control me-2" type="search" name="search" placeholder="搜索用户..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-primary" type="submit">搜索</button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>昵称</th>
                                            <th>账号</th>
                                            <th>角色</th>
                                            <th>API调用次数</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $u): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($u['nickname']); ?></td>
                                            <td><?php echo htmlspecialchars($u['account']); ?></td>
                                            <td>
                                                <?php if ($u['role'] === 'admin'): ?>
                                                    <span class="badge bg-success">管理员</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">普通用户</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo isset($u['api_calls']) ? count($u['api_calls']) : 0; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#passwordModal<?php echo $u['id']; ?>">
                                                    <i class="fas fa-key"></i> 修改密码
                                                </button>
                                                
                                                <!-- 密码修改模态框 -->
                                                <div class="modal fade" id="passwordModal<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">修改密码 - <?php echo htmlspecialchars($u['nickname']); ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                                    <div class="mb-3">
                                                                        <label for="new_password" class="form-label">新密码</label>
                                                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="confirm_password" class="form-label">确认新密码</label>
                                                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                                                                    <button type="submit" name="update_password" class="btn btn-primary">更新密码</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
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
    </script>
</body>
</html>