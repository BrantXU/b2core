<?php
// 错误报告级别设置
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 获取基础URL配置
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$dirName = dirname($scriptName);
$baseUrl = $protocol . '://' . $host . ($dirName === '/' ? '' : $dirName);
$baseUrl = rtrim($baseUrl, '/');

// 定义基础URL常量
define('BASE', $baseUrl);
define('HOST', $protocol . '://' . $host);

// 安全相关配置
define('SEED', 'b2core_secret_key'); // 用于加密的种子字符串

// URL路由配置
$route_config = array(
    '/login/'=>'/user/login/',    // 登录页面路由
    '/reg/'=>'/user/reg/',        // 注册页面路由
    '/logout/'=>'/user/logout/',   // 登出路由
    '/tenants/'=>'/tenant/',       // 租户列表路由
    '/tenant/create/'=>'/tenant/create/', // 创建租户路由
    '/tenant/edit/'=>'/tenant/edit/',     // 编辑租户路由
    '/tenant/delete/'=>'/tenant/delete/', // 删除租户路由
);

// 数据库配置信息
$db_config = array(
    'driver' => 'sqlite',        // 数据库类型：mysql 或 sqlite
    'sqlite' => array(
        'database' => APP.'db.sqlite',  // SQLite数据库文件路径
    ),
    'mysql' => array(
        'host' => 'localhost',     // MySQL主机
        'user' => 'root',         // MySQL用户名
        'password' => 'root',     // MySQL密码
        'database' => 'b2core',   // MySQL数据库名
        'port' => 3306,          // MySQL端口号
        'charset' => 'utf8mb4'   // 字符集
    )
);
 

