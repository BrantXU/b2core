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
$tenant_id = '';
// 统一开启session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 载入配置文件：数据库、url路由等配置
// require(APP.'config.php');

/**
 * 获取请求的URL路径
 * 兼容多种服务器环境(包括SAE)的URL解析
 */
$uri = '';

if(isset($_SERVER['PATH_INFO'])) $uri = $_SERVER['PATH_INFO'];
elseif(isset($_SERVER['ORIG_PATH_INFO'])) $uri = $_SERVER['ORIG_PATH_INFO'];
// elseif(isset($_SERVER['QUERY_STRING'])){ 
//   $ss = explode('&',$_SERVER['QUERY_STRING']);
//   $uri = $ss[0];
// }

/**
 * URL重写处理函数
 * 将 abc/def 重定向到 abc/def/ 以优化SEO
 */
function render_url(): void { 
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

// echo $uri;

// 解析URL段落
$uri = rtrim($uri,'/'); 
$seg = explode('/',$uri);
$des_dir = $dir = '';

// 检查是否为明确指定的路由
$is_specific_route = false;
if (!empty($seg)) {
    $seg_tenant = isset($seg[1])?$seg[1]:'default';
    error_log("seg_tenant = " . $seg_tenant);
    foreach ($route_config as $key => $val) {
        $key_segments = explode('/', trim($key, '/'));
        error_log("key_segment[0] = " . $key_segments[0]);
        if (!empty($key_segments) && $key_segments[0] === $seg_tenant) {
            $is_specific_route = true;
            error_log("is_specific_route = TRUE");
            break;
        }
    }
}
error_log("is_specific_route = ".($is_specific_route?"TRUE":"FALSE"));

// 如果不是明确指定的路由，则按租户ID/模块名/方法名的格式处理

if (!$is_specific_route && count($seg) >= 2) {
  // 第一段作为租户ID
  $tenant_id = $seg_tenant;
  array_shift($seg); 
  array_shift($seg);
  array_unshift($seg,'');
  // 将租户ID存储到全局变量或session中
} 

  /**
   * 载入控制器目录结构
   * 依次载入控制器上级所有目录的__construct.php文件
   */
   
  foreach($seg as $cur_dir) {
    $des_dir.=$cur_dir."/";
    //echo APP.'c'.$des_dir.'__construct.php';
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

  $dir = $dir ? $dir:'';
  array_unshift($seg,NULL);
  $class  = isset($seg[1])?$seg[1]:'home';    // 控制器名
  $method = isset($seg[2])?$seg[2]:'index';   // 方法名
// 检查控制器文件是否存在
if(!is_file(APP.'c'.$dir.$class.'.php')) {
  //show_404('file:'.APP.'c'.$dir.$class.'.php');
  // 当控制器文件不存在时，默认使用entity控制器
  $entity_type = $class;
  $class='entity';
}

// 载入控制器文件并检查类和方法是否存在
require(APP.'/c'.$dir.$class.'.php');
if(!class_exists($class)) {
  show_404('class_not_exists:'.$class);
}
if(!method_exists($class,$method)) {
  show_404('method_not_exists:'.$class.$method);
}

// 实例化控制器并调用方法
if($class=='entity') {
    $entity_type = isset($seg[1]) ? $seg[1] : 'default';
    $B2 = new $class($entity_type);
} else {
    $B2 = new $class();
}
call_user_func_array(array(&$B2, $method), array_slice($seg, 3));

/**
 * 动态加载类文件
 * @param string $path 类文件相对路径
 * @param mixed $instantiate 是否实例化
 * @return mixed 类实例或TRUE
 */
function &load(string $path, mixed $instantiate = true) {
  $param = false;
  if(is_array($instantiate)) {
    $param = $instantiate;
    $instantiate = true;
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
  if ($instantiate == false) {
    $objects[$object_name] = true;
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
function seg(int $i): mixed {
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
function view(string $view, array $param = [], bool $cache = false): mixed {
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
  if ($cache === true) {
    $buffer = ob_get_contents();
    @ob_end_clean();
    return $buffer;
  }
  return null;
}

/**
 * 写入日志
 * @param int $level 日志级别
 * @param string $content 日志内容
 */
function write_log(int $level = 0, string $content = 'none'): void {
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
function show_404(string $msg = ''): void {
  header("HTTP/1.1 404 Not Found");
  echo '404:'.$msg;
  exit(1);
}

/**
 * 基础控制器类
 */
class c { 
  public function index(): void {
    echo "基于 B2 v".B2CORE_VERSION." 创建";
  }
}


/**
 * 显示系统消息
 * @param string $str 消息内容
 */
function __msg(string $str): void {
  header("Content-type: text/html; charset=utf-8");
  echo '<!DOCTYPE html><head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link href="/uikit.min.css" rel="stylesheet" type="text/css">
    </head><body><div class="uk-container" style="padding: 20px;background: #f9f9ff;line-height: 1.5;margin: 20px;border-radius: 10px;border: 1px solid #ddd;margin: 100px auto;text-align: center;" >';
  echo $str;
  echo '</div></body></html>';
  exit(1);
}