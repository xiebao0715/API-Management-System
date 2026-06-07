<?php
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickname']);
    $account = trim($_POST['account']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($nickname) || empty($account) || empty($password) || empty($confirm_password)) {
        $message = '请填写完整信息';
    } elseif ($password !== $confirm_password) {
        $message = '两次输入的密码不一致';
    } elseif (strlen($password) < 6) {
        $message = '密码长度不能少于6位';
    } else {
        $users = get_users();
        
        // 检查账号是否已存在
        foreach ($users as $user) {
            if ($user['account'] === $account) {
                $message = '该账号已被注册';
                break;
            }
        }
        
        if (!$message) {
            $new_user = [
                'id' => generate_id(),
                'nickname' => $nickname,
                'account' => $account,
                'password' => md5($password),
                'role' => empty($users) ? 'admin' : 'user', // 第一个注册的为管理员
                'api_key' => md5($account . time()),
                'created_at' => date('Y-m-d H:i:s'),
                'api_calls' => []
            ];
            
            $users[] = $new_user;
            save_users($users);
            
            $_SESSION['user'] = $new_user;
            header('Location: index.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户注册 - <?php echo get_settings()['site_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e90ff;
            --secondary-color: #4682b4;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        
        .register-card {
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card register-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold">用户注册</h2>
                            <p class="text-muted">请填写以下信息完成注册</p>
                        </div>
                        
                        <?php if ($message): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nickname" class="form-label">昵称</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-signature"></i></span>
                                    <input type="text" class="form-control" id="nickname" name="nickname" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="account" class="form-label">账号</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="account" name="account" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">密码</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">确认密码</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">注册</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p class="mb-0">已有账号？<a href="login.php">立即登录</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>