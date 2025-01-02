<?php
// 错误报告级别设置
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 定义基础URL常量
define('BASE', 'http://'.$_SERVER['SERVER_NAME'].'/b2core');
define('HOST', 'http://'.$_SERVER['SERVER_NAME']);

// URL路由配置
$route_config = array(
    '/login/'=>'/user/login/',    // 登录页面路由
    '/reg/'=>'/user/reg/',        // 注册页面路由
    '/logout/'=>'/user/logout/',   // 登出路由
);

// 数据库配置信息
$db_config = array(
    'driver' => 'sqlite',        // 数据库类型：mysql 或 sqlite
    'sqlite' => array(
        'database' => APP.'db/b2core.db',  // SQLite数据库文件路径
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
 

