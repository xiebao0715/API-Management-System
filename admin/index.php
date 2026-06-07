<?php
require_once '../config.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../index.php');
    exit();
}

$user = get_current_session_user();

// 获取统计数据
$users = get_users();
$user_count = count($users);

$apis = get_apis();
$api_count = count($apis);

// 计算总API调用次数
$total_api_calls = 0;
foreach ($apis as $api) {
    if (isset($api['call_count'])) {
        $total_api_calls += $api['call_count'];
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - <?php echo get_settings()['site_name']; ?></title>
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
        
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
        <a href="index.php" class="active"><i class="fas fa-tachometer-alt me-2"></i>数据面板</a>
        <a href="users.php"><i class="fas fa-users me-2"></i>用户管理</a>
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
                <a class="navbar-brand" href="#">后台管理系统</a>
                <div class="d-flex align-items-center">
                    <span class="me-3">欢迎您，<?php echo htmlspecialchars($user['nickname']); ?></span>
                    <a href="../logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> 退出
                    </a>
                </div>
            </div>
        </nav>
        
        <div class="container-fluid mt-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>用户总数</h5>
                                    <h2><?php echo $user_count; ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>总访问次数</h5>
                                    <h2><?php echo count(get_visit_log()); ?></h2>
                                </div>
                                <i class="fas fa-user-clock fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>API数量</h5>
                                    <h2><?php echo $api_count; ?></h2>
                                </div>
                                <i class="fas fa-plug fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>总调用次数</h5>
                                    <h2><?php echo $total_api_calls; ?></h2>
                                </div>
                                <i class="fas fa-chart-line fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">系统信息</h5>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <td>操作系统：</td>
                                    <td><?php echo php_uname('s'); ?></td>
                                </tr>
                                <tr>
                                    <td>PHP版本：</td>
                                    <td><?php echo PHP_VERSION; ?></td>
                                </tr>
                                <tr>
                                    <td>服务器软件：</td>
                                    <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? '未知'; ?></td>
                                </tr>
                                <tr>
                                    <td>系统安装时间：</td>
                                    <td><?php echo date('Y-m-d H:i:s', filemtime('../data')); ?></td>
                                </tr>
                            </table>
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