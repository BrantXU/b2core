<?php
/**
 * B2Core框架核心文件
 * 作者: Brant (brantx@gmail.com)
 * 版本: 3.0
 * 
 * 核心功能:
 * 1. MVC架构实现
 * 2. URL路由处理
 * 3. 数据库操作封装
 * 4. 视图渲染
 */

// 定义框架版本号
define('B2CORE_VERSION','3.0');

// 载入配置文件：数据库、url路由等配置
require(APP.'config.php');

// 初始化数据库连接(如果配置了数据库)
if(isset($db_config)){
  $db = new db($db_config);
}

/**
 * 获取请求的URL路径
 * 兼容多种服务器环境(包括SAE)的URL解析
 */
$uri = '';
if(isset($_SERVER['PATH_INFO'])) $uri = $_SERVER['PATH_INFO'];
elseif(isset($_SERVER['ORIG_PATH_INFO'])) $uri = $_SERVER['ORIG_PATH_INFO'];
elseif(isset($_SERVER['QUERY_STRING'])){ 
  $ss = explode('&',$_SERVER['QUERY_STRING']);
  $uri = $ss[0];
}

/**
 * URL重写处理函数
 * 将 abc/def 重定向到 abc/def/ 以优化SEO
 */
function render_url(){ 
  global $uri;
  // 以下情况不进行重定向:
  if(strpos($uri,'.'))return;      // URL包含文件扩展名
  if($_SERVER['QUERY_STRING'])return; // 存在查询字符串
  if(substr($uri,-1)=='/')return;    // URL已以/结尾
  if($uri =='')return;               // 空URL
  
  // 执行301永久重定向
  header("HTTP/1.1 301 Moved Permanently");
  header ('Location:'.$_SERVER['REQUEST_URI'].'/');
  exit(0);
}

// 处理魔术引号(Magic Quotes)
if(get_magic_quotes_gpc()) {
  /**
   * 递归去除转义字符
   * @param mixed $value 需要处理的值
   * @return mixed 处理后的值
   */
  function stripslashes_deep($value) {
    $value = is_array($value) ? 
      array_map('stripslashes_deep', $value) : 
      (isset($value) ? stripslashes($value) : null);
    return $value;
  }
  
  // 去除POST、GET、COOKIE中的转义字符
  $_POST = stripslashes_deep($_POST);
  $_GET = stripslashes_deep($_GET);
  $_COOKIE = stripslashes_deep($_COOKIE);
}

/**
 * 路由处理
 * 根据config.php中的路由配置进行URL重写
 */
foreach ($route_config as $key => $val) { 
  $key = str_replace(':any', '([^\/.]+)', str_replace(':num', '([0-9]+)', $key));
  if (preg_match('#^'.$key.'#', $uri)) {
    $uri = preg_replace('#^'.$key.'#', $val, $uri);
  }
}

// 解析URL段落
$uri = rtrim($uri,'/');
$seg = explode('/',$uri);
$des_dir = $dir = '';

/**
 * 载入控制器目录结构
 * 依次载入控制器上级所有目录的__construct.php文件
 */
foreach($seg as $cur_dir) {
  $des_dir.=$cur_dir."/";
  if(is_file(APP.'c'.$des_dir.'__construct.php')) {
    require(APP.'c'.$des_dir.'__construct.php'); 
    $dir .=array_shift($seg).'/';
  }
  else {
    break;
  }
}

/**
 * 根据URL调用对应的控制器方法
 * 默认调用 home 控制器的 index 方法
 */
$dir = $dir ? $dir:'/';
array_unshift($seg,NULL);
$class  = isset($seg[1])?$seg[1]:'home';    // 控制器名
$method = isset($seg[2])?$seg[2]:'index';   // 方法名

// 检查控制器文件是否存在
if(!is_file(APP.'c'.$dir.$class.'.php')) {
  show_404('file:'.APP.'c'.$dir.$class.'.php');
}

// 载入控制器文件并检查类和方法是否存在
require(APP.'c'.$dir.$class.'.php');
if(!class_exists($class)) {
  show_404('class_not_exists:'.$class);
}
if(!method_exists($class,$method)) {
  show_404('method_not_exists:'.$class.$method);
}

// 实例化控制器并调用方法
$B2 = new $class();
call_user_func_array(array(&$B2, $method), array_slice($seg, 3));

/**
 * 动态加载类文件
 * @param string $path 类文件相对路径
 * @param mixed $instantiate 是否实例化
 * @return mixed 类实例或TRUE
 */
function &load($path, $instantiate = TRUE) {
  $param = FALSE;
  if(is_array($instantiate)) {
    $param = $instantiate;
    $instantiate = TRUE;
  }
  
  $file = explode('/',$path);
  $class_name = array_pop($file);
  $object_name = md5($path);
  
  // 静态缓存已加载的对象
  static $objects = array();
  if (isset($objects[$object_name])) {
    return $objects[$object_name];
  }
  
  // 加载类文件
  require(APP.$path.'.php');
  
  // 根据参数决定是否实例化
  if ($instantiate == FALSE) {
    $objects[$object_name] = TRUE;
  }
  elseif ($param) {
    $objects[$object_name] = new $class_name($param);
  }
  else {
    $objects[$object_name] = new $class_name();
  }
  return $objects[$object_name];
}

/**
 * 获取URL段落值
 * @param int $i 段落索引
 * @return mixed 段落值或FALSE
 */
function seg($i) {
  global $seg;
  return isset($seg[$i])?$seg[$i]:false;
}

/**
 * 视图渲染函数
 * @param string $view 视图文件路径
 * @param array $param 传递给视图的参数
 * @param bool $cache 是否缓存输出
 * @return mixed 缓存时返回输出内容
 */
function view($view,$param = array(),$cache = FALSE) {
  if(!empty($param)) {
    extract($param);
  }
  ob_start();
  if(is_file(APP.$view.'.php')) {
    require APP.$view.'.php';
  }
  else {
    echo 'view '.$view.' desn\'t exsit';
    return false;
  }
  if ($cache === TRUE) {
    $buffer = ob_get_contents();
    @ob_end_clean();
    return $buffer;
  }
}

/**
 * 写入日志
 * @param int $level 日志级别
 * @param string $content 日志内容
 */
function write_log($level = 0 ,$content = 'none') {
  file_put_contents(
    APP.'log/'.$level.'-'.date('Y-m-d').'.log', 
    $content, 
    FILE_APPEND
  );
}

/**
 * 显示404错误页面
 * @param string $msg 错误信息
 */
function show_404($msg = '') {
  header("HTTP/1.1 404 Not Found");
  echo '404:'.$msg;
  exit(1);
}

/**
 * 基础控制器类
 */
class c { 
  function index() {
    echo "基于 B2 v".VERSION." 创建";
  }
}

/**
 * 数据库操作类
 */
class db {
  var $link;
  var $last_query;
  
  /**
   * 构造函数 - 建立数据库连接
   * @param array $conf 数据库配置
   */
  function __construct($conf) {
    $this->link = mysqli_connect($conf['host'], $conf['user'], $conf['password'], $conf['default_db']);
    if (!$this->link) {
      __msg('无法连接: ' . mysqli_connect_error() .' <br /> 如果是初次使用b2core 请配置 config.php 文件，并导入 db.sql ');
      return FALSE;
    }

    mysqli_query($this->link, 'set names utf8');
  }

  /**
   * 执行SQL查询
   * @param string $query SQL语句
   * @return mixed 查询结果
   */
  function query($query) {
    $ret = array();
    $this->last_query = $query;
    $result = mysqli_query($this->link, $query);
    if (!$result) {
      echo "DB Error, could not query the database\n";
      echo 'MySQL Error: ' . mysqli_error($this->link);
      echo 'Error Query: ' . $query;
      exit;
    }
    if($result === TRUE) return TRUE;
    while($record = mysqli_fetch_assoc($result)) {
      $ret[] = $record;
    }
    return $ret;
  }

  // 获取最后插入的ID
  function insert_id() {
    return mysqli_insert_id($this->link);
  }
  
  /**
   * 执行多条SQL语句
   * @param string $query 多条SQL语句
   */
  function muti_query($query) {
    $sq = explode(";\n",$query);
    foreach($sq as $s){
      if(trim($s)!= '') {
        $this->query($s);
      }
    }
  }
  
  /**
   * 转义字符串
   * @param string $str 需要转义的字符串
   * @return string 转义后的字符串
   */
  function escape($str){
    return mysqli_real_escape_string($this->link, $str);
  }
}

/**
 * 基础模型类
 * 封装了通用的CRUD操作
 */
class m { 
  var $db;
  var $table;
  var $filter = 1;
  var $fields;
  var $key;
  
  function __construct($table) {
    global $db;
    $this->db = $db;
    $this->table = $table;
    $this->key = 'id';
  }

  public function __call($name, $arg) {
    return call_user_func_array(array($this, $name), $arg);
  }

  // 以下是CRUD相关方法...
  // 此处省略已有代码
}

/**
 * 显示系统消息
 * @param string $str 消息内容
 */
function __msg($str){
  header("Content-type: text/html; charset=utf-8");
  echo '<!DOCTYPE html><head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link href="http://lib.sinaapp.com/js/bootstrap/latest/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    </head><body><div class="hero-unit" >';
  echo $str;
  echo '</div></body></html>';
  exit(1);
}
