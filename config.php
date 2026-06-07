<?php
// 系统配置文件
define('SYSTEM_NAME', 'API管理系统');
define('SYSTEM_VERSION', '1.0.0');
define('DATA_DIR', __DIR__ . '/data/');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// 创建必要的目录
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0777, true);
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// 数据文件路径
define('USERS_FILE', DATA_DIR . 'users.json');
define('APIS_FILE', DATA_DIR . 'apis.json');
define('SETTINGS_FILE', DATA_DIR . 'settings.json');
define('VISIT_LOG_FILE', DATA_DIR . 'visit_log.json');

// 初始化默认设置
if (!file_exists(SETTINGS_FILE)) {
    $default_settings = [
        'site_name' => 'API管理系统',
        'site_description' => '一个功能强大的API管理系统',
        'site_notice' => '欢迎使用我们的API管理系统！',
        'friend_links' => [
            ['name' => '阿里云', 'url' => 'https://www.aliyun.com'],
            ['name' => '腾讯云', 'url' => 'https://cloud.tencent.com']
        ],
        'popup_content' => '欢迎访问我们的API管理系统！',
        'audio_url' => '',
        'icp_number' => '京ICP备00000000号',
        'copyright' => '© 2023 API管理系统 版权所有'
    ];
    file_put_contents(SETTINGS_FILE, json_encode($default_settings, JSON_PRETTY_PRINT));
}

// 初始化用户文件
if (!file_exists(USERS_FILE)) {
    file_put_contents(USERS_FILE, json_encode([]));
}

// 初始化API文件
if (!file_exists(APIS_FILE)) {
    file_put_contents(APIS_FILE, json_encode([]));
}

// 初始化访问日志文件
if (!file_exists(VISIT_LOG_FILE)) {
    file_put_contents(VISIT_LOG_FILE, json_encode([]));
}

// 加载设置
function get_settings() {
    return json_decode(file_get_contents(SETTINGS_FILE), true);
}

// 保存设置
function save_settings($settings) {
    file_put_contents(SETTINGS_FILE, json_encode($settings, JSON_PRETTY_PRINT));
}

// 获取用户数据
function get_users() {
    return json_decode(file_get_contents(USERS_FILE), true);
}

// 保存用户数据
function save_users($users) {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

// 获取API数据
function get_apis() {
    return json_decode(file_get_contents(APIS_FILE), true);
}

// 保存API数据
function save_apis($apis) {
    file_put_contents(APIS_FILE, json_encode($apis, JSON_PRETTY_PRINT));
}

// 获取访问日志
function get_visit_log() {
    return json_decode(file_get_contents(VISIT_LOG_FILE), true);
}

// 保存访问日志
function save_visit_log($log) {
    file_put_contents(VISIT_LOG_FILE, json_encode($log, JSON_PRETTY_PRINT));
}

// 记录访问
function log_visit() {
    $log = get_visit_log();
    $log[] = [
        'ip' => $_SERVER['REMOTE_ADDR'],
        'time' => time(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ];
    save_visit_log($log);
}

// 检查是否已登录
function is_logged_in() {
    return isset($_SESSION['user']);
}

// 获取当前用户
function get_current_session_user() {
    return $_SESSION['user'] ?? null;
}

// 检查是否为管理员
function is_admin() {
    $user = get_current_session_user();
    return $user && ($user['role'] === 'admin');
}

// 通过ID查找用户
function find_user_by_id($id) {
    $users = get_users();
    foreach ($users as $user) {
        if ($user['id'] == $id) {
            return $user;
        }
    }
    return null;
}

// 通过账号查找用户
function find_user_by_account($account) {
    $users = get_users();
    foreach ($users as $user) {
        if ($user['account'] == $account) {
            return $user;
        }
    }
    return null;
}

// 生成唯一ID
function generate_id() {
    return md5(uniqid(rand(), true));
}

// 初始化会话
session_start();
log_visit();
?>