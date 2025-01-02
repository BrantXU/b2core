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
    'host'=>'localhost',          // 数据库主机
    'user'=>'root',              // 数据库用户名
    'password'=>'root',          // 数据库密码
    'default_db'=>'b2core'       // 默认数据库名
);      
 

