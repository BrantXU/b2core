<?php
// 所有配置内容都可以在这个文件维护
error_reporting(E_ERROR);
define('BASE', 'http://'.$_SERVER['SERVER_NAME'].'/b2core');
define('HOST', 'http://'.$_SERVER['SERVER_NAME']);
// 配置url路由
$route_config = array(
  '/login/'=>'/user/login/',
  '/reg/'=>'/user/reg/',
  '/logout/'=>'/user/logout/',
);


$db_config = array(
  'host'=>'localhost',
  'user'=>'root',
  'password'=>'root',
  'default_db'=>'b2core'
);      
 

