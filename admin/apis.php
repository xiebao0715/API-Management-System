<?php
require_once '../config.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: ../index.php');
    exit();
}

$user = get_current_session_user();
$apis = get_apis();

// 搜索功能
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
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

// 处理API添加/编辑
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_api'])) {
        // 添加API
        $new_api = [
            'id' => generate_id(),
            'name' => trim($_POST['name']),
            'url' => trim($_POST['url']),
            'description' => trim($_POST['description']),
            'parameters' => [],
            'request_example' => trim($_POST['request_example']),
            'response_example' => trim($_POST['response_example']),
            'status' => $_POST['status'],
            'call_count' => 0
        ];
        
        // 处理参数
        if (isset($_POST['param_name']) && is_array($_POST['param_name'])) {
            foreach ($_POST['param_name'] as $index => $name) {
                if (!empty($name)) {
                    $new_api['parameters'][] = [
                        'name' => $name,
                        'type' => $_POST['param_type'][$index],
                        'required' => isset($_POST['param_required'][$index]),
                        'description' => $_POST['param_description'][$index]
                    ];
                }
            }
        }
        
        $apis[] = $new_api;
        save_apis($apis);
        $message = 'API添加成功';
    } elseif (isset($_POST['edit_api'])) {
        // 编辑API
        $api_id = $_POST['api_id'];
        foreach ($apis as &$api) {
            if ($api['id'] === $api_id) {
                $api['name'] = trim($_POST['name']);
                $api['url'] = trim($_POST['url']);
                $api['description'] = trim($_POST['description']);
                $api['request_example'] = trim($_POST['request_example']);
                $api['response_example'] = trim($_POST['response_example']);
                $api['status'] = $_POST['status'];
                
                // 处理参数
                $api['parameters'] = [];
                if (isset($_POST['param_name']) && is_array($_POST['param_name'])) {
                    foreach ($_POST['param_name'] as $index => $name) {
                        if (!empty($name)) {
                            $api['parameters'][] = [
                                'name' => $name,
                                'type' => $_POST['param_type'][$index],
                                'required' => isset($_POST['param_required'][$index]),
                                'description' => $_POST['param_description'][$index]
                            ];
                        }
                    }
                }
                break;
            }
        }
        save_apis($apis);
        $message = 'API更新成功';
    } elseif (isset($_POST['delete_api'])) {
        // 删除API
        $api_id = $_POST['api_id'];
        $apis = array_filter($apis, function($api) use ($api_id) {
            return $api['id'] !== $api_id;
        });
        save_apis(array_values($apis));
        $message = 'API删除成功';
    }
}

// 获取要编辑的API信息
$edit_api = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    foreach ($apis as $api) {
        if ($api['id'] === $edit_id) {
            $edit_api = $api;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API管理 - <?php echo get_settings()['site_name']; ?></title>
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
        
        .param-row {
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
        <a href="apis.php" class="active"><i class="fas fa-plug me-2"></i>API管理</a>
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
                <a class="navbar-brand" href="#">API管理</a>
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
                            <h5 class="mb-0"><?php echo $edit_api ? '编辑API' : '添加API'; ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="<?php echo $edit_api ? 'edit_api' : 'add_api'; ?>" value="1">
                                <?php if ($edit_api): ?>
                                <input type="hidden" name="api_id" value="<?php echo $edit_api['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">接口名称</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $edit_api ? htmlspecialchars($edit_api['name']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="url" class="form-label">接口地址</label>
                                    <input type="text" class="form-control" id="url" name="url" value="<?php echo $edit_api ? htmlspecialchars($edit_api['url']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">接口描述</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $edit_api ? htmlspecialchars($edit_api['description']) : ''; ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">请求参数</label>
                                    <div id="parameters-container">
                                        <?php if ($edit_api && !empty($edit_api['parameters'])): ?>
                                            <?php foreach ($edit_api['parameters'] as $index => $param): ?>
                                            <div class="param-row row">
                                                <div class="col-md-2">
                                                    <input type="text" class="form-control" name="param_name[]" placeholder="参数名" value="<?php echo htmlspecialchars($param['name']); ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <select class="form-control" name="param_type[]">
                                                        <option value="string" <?php echo $param['type'] === 'string' ? 'selected' : ''; ?>>string</option>
                                                        <option value="integer" <?php echo $param['type'] === 'integer' ? 'selected' : ''; ?>>integer</option>
                                                        <option value="boolean" <?php echo $param['type'] === 'boolean' ? 'selected' : ''; ?>>boolean</option>
                                                        <option value="array" <?php echo $param['type'] === 'array' ? 'selected' : ''; ?>>array</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="param_required[<?php echo $index; ?>]" <?php echo $param['required'] ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">必填</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control" name="param_description[]" placeholder="描述" value="<?php echo htmlspecialchars($param['description']); ?>">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-danger remove-param">-</button>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="param-row row">
                                                <div class="col-md-2">
                                                    <input type="text" class="form-control" name="param_name[]" placeholder="参数名">
                                                </div>
                                                <div class="col-md-2">
                                                    <select class="form-control" name="param_type[]">
                                                        <option value="string">string</option>
                                                        <option value="integer">integer</option>
                                                        <option value="boolean">boolean</option>
                                                        <option value="array">array</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="param_required[]">
                                                        <label class="form-check-label">必填</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control" name="param_description[]" placeholder="描述">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-danger remove-param">-</button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="btn btn-secondary mt-2" id="add-param">添加参数</button>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="request_example" class="form-label">请求示例</label>
                                    <textarea class="form-control" id="request_example" name="request_example" rows="3"><?php echo $edit_api ? htmlspecialchars($edit_api['request_example']) : ''; ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="response_example" class="form-label">响应示例</label>
                                    <textarea class="form-control" id="response_example" name="response_example" rows="3"><?php echo $edit_api ? htmlspecialchars($edit_api['response_example']) : ''; ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">接口状态</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="active" <?php echo ($edit_api && $edit_api['status'] === 'active') ? 'selected' : ''; ?>>启用</option>
                                        <option value="inactive" <?php echo ($edit_api && $edit_api['status'] === 'inactive') ? 'selected' : ''; ?>>停用</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary"><?php echo $edit_api ? '更新API' : '添加API'; ?></button>
                                <?php if ($edit_api): ?>
                                <a href="apis.php" class="btn btn-secondary">取消</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">API列表</h5>
                            <form class="d-flex" method="GET">
                                <input class="form-control me-2" type="search" name="search" placeholder="搜索API..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-primary" type="submit">搜索</button>
                            </form>
                        </div>
                        <div class="card-body">
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
                                                    <span class="badge bg-success">启用</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">停用</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="?edit=<?php echo $api['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> 编辑
                                                </a>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $api['id']; ?>">
                                                    <i class="fas fa-trash"></i> 删除
                                                </button>
                                                
                                                <!-- 删除确认模态框 -->
                                                <div class="modal fade" id="deleteModal<?php echo $api['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">确认删除</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                确定要删除API "<?php echo htmlspecialchars($api['name']); ?>" 吗？
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="api_id" value="<?php echo $api['id']; ?>">
                                                                    <button type="submit" name="delete_api" class="btn btn-danger">删除</button>
                                                                </form>
                                                            </div>
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
        
        // 添加参数行
        document.getElementById('add-param').addEventListener('click', function() {
            const container = document.getElementById('parameters-container');
            const row = document.createElement('div');
            row.className = 'param-row row';
            row.innerHTML = `
                <div class="col-md-2">
                    <input type="text" class="form-control" name="param_name[]" placeholder="参数名">
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="param_type[]">
                        <option value="string">string</option>
                        <option value="integer">integer</option>
                        <option value="boolean">boolean</option>
                        <option value="array">array</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="param_required[]">
                        <label class="form-check-label">必填</label>
                    </div>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" name="param_description[]" placeholder="描述">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-param">-</button>
                </div>
            `;
            container.appendChild(row);
            
            // 为新添加的删除按钮绑定事件
            row.querySelector('.remove-param').addEventListener('click', function() {
                container.removeChild(row);
            });
        });
        
        // 为现有的删除按钮绑定事件
        document.querySelectorAll('.remove-param').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('.param-row');
                row.parentNode.removeChild(row);
            });
        });
    </script>
</body>
</html>