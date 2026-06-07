<?php
require_once 'config.php';

// 移除登录检查，允许未登录用户访问
// if (!is_logged_in()) {
//     header('Location: login.php');
//     exit();
// }

$apis = get_apis();
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 搜索功能
if ($search) {
    $filtered_apis = [];
    foreach ($apis as $api) {
        if (stripos($api['name'], $search) !== false || 
            stripos($api['description'], $search) !== false) {
            $filtered_apis[] = $api;
        }
    }
    $apis = $filtered_apis;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API列表 - <?php echo get_settings()['site_name']; ?></title>
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
        
        .status-badge {
            font-size: 0.8em;
        }
        
        .status-active {
            background-color: #198754;
        }
        
        .status-inactive {
            background-color: #dc3545;
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
        <a href="api_list.php" class="active"><i class="fas fa-list me-2"></i>API列表</a>
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
                <a class="navbar-brand mx-auto" href="index.php">API列表</a>
            </div>
        </nav>
        
        <div class="container mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">API接口列表</h5>
                            <form class="d-flex" method="GET">
                                <input class="form-control me-2" type="search" name="search" placeholder="搜索API..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-primary" type="submit">搜索</button>
                            </form>
                        </div>
                        <div class="card-body">
                            <?php if (empty($apis)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">暂无API接口</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>接口名称</th>
                                                <th>接口描述</th>
                                                <th>请求次数</th>
                                                <th>状态</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($apis as $api): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($api['name']); ?></td>
                                                <td><?php echo htmlspecialchars($api['description']); ?></td>
                                                <td><?php echo isset($api['call_count']) ? $api['call_count'] : 0; ?></td>
                                                <td>
                                                    <?php if ($api['status'] === 'active'): ?>
                                                        <span class="badge status-badge status-active">启用</span>
                                                    <?php else: ?>
                                                        <span class="badge status-badge status-inactive">停用</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <!-- 未登录用户也可以查看API详情 -->
                                                    <a href="api_detail.php?id=<?php echo urlencode($api['id']); ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> 查看详情
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
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