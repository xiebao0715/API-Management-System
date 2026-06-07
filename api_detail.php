<?php
require_once 'config.php';

// 移除登录检查，允许未登录用户访问
// if (!is_logged_in()) {
//     header('Location: login.php');
//     exit();
// }

$id = isset($_GET['id']) ? $_GET['id'] : '';
$apis = get_apis();
$api = null;

foreach ($apis as $item) {
    if ($item['id'] == $id) {
        $api = $item;
        break;
    }
}

if (!$api) {
    header('Location: api_list.php');
    exit();
}

// 增加调用次数（仅在用户登录时增加）
if (is_logged_in()) {
    if (!isset($api['call_count'])) {
        $api['call_count'] = 0;
    }
    $api['call_count']++;

    // 更新API数据
    foreach ($apis as &$item) {
        if ($item['id'] == $id) {
            $item = $api;
            break;
        }
    }
    save_apis($apis);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($api['name']); ?> - <?php echo get_settings()['site_name']; ?></title>
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
            font-size: 0.9em;
        }
        
        .status-active {
            background-color: #198754;
        }
        
        .status-inactive {
            background-color: #dc3545;
        }
        
        pre {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            overflow-x: auto;
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
                <a class="navbar-brand mx-auto" href="api_list.php">API详情</a>
            </div>
        </nav>
        
        <div class="container mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="mb-0"><?php echo htmlspecialchars($api['name']); ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p><strong>接口地址:</strong> <?php echo htmlspecialchars($api['url']); ?></p>
                                    <p><strong>接口状态:</strong> 
                                        <?php if ($api['status'] === 'active'): ?>
                                            <span class="badge status-badge status-active">启用</span>
                                        <?php else: ?>
                                            <span class="badge status-badge status-inactive">停用</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>接口描述:</strong></p>
                                    <p><?php echo htmlspecialchars($api['description']); ?></p>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5>请求参数</h5>
                                    <?php if (!empty($api['parameters'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>参数名</th>
                                                    <th>类型</th>
                                                    <th>是否必填</th>
                                                    <th>描述</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($api['parameters'] as $param): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($param['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($param['type']); ?></td>
                                                    <td><?php echo $param['required'] ? '是' : '否'; ?></td>
                                                    <td><?php echo htmlspecialchars($param['description']); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                    <p class="text-muted">该接口无请求参数</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5>请求示例</h5>
                                    <?php if (!empty($api['request_example'])): ?>
                                    <pre><?php echo htmlspecialchars($api['request_example']); ?></pre>
                                    <?php else: ?>
                                    <p class="text-muted">暂无请求示例</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <h5>响应示例</h5>
                                    <?php if (!empty($api['response_example'])): ?>
                                    <pre><?php echo htmlspecialchars($api['response_example']); ?></pre>
                                    <?php else: ?>
                                    <p class="text-muted">暂无响应示例</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <a href="api_list.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> 返回列表
                                </a>
                                <?php if (!is_logged_in()): ?>
                                    <a href="login.php" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt"></i> 登录后调用API
                                    </a>
                                <?php endif; ?>
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