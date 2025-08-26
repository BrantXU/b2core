<?php
/* 
 * B2Core框架入口文件
 * 1. 定义应用目录常量 APP
 * 2. 引入框架核心文件 b2core.php
 */
define('APP','../app/');

// 引入Composer自动加载器
require_once('../vendor/autoload.php');
require_once(APP.'config.php');
require_once APP.'lib/db.php';
require_once(APP.'/lib/m.php');
require_once(APP.'/lib/b2core.php');
