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
  var $driver;
  
  /**
   * 构造函数 - 建立数据库连接
   * @param array $conf 数据库配置
   */
  function __construct($conf) {
    try {
      // 检查数据库驱动类型
      if (!isset($conf['driver'])) {
        throw new Exception("Missing database driver configuration");
      }
      
      $this->driver = strtolower($conf['driver']);
      
      switch ($this->driver) {
        case 'sqlite':
          $this->connect_sqlite($conf['sqlite']);
          break;
        case 'mysql':
          $this->connect_mysql($conf['mysql']);
          break;
        default:
          throw new Exception("Unsupported database driver: {$this->driver}");
      }
    } catch (Exception $e) {
      __msg($e->getMessage() . '<br><br>如果是初次使用b2core:<br>1. 请检查并配置 config.php 文件<br>2. 导入数据库结构');
      exit(1);
    }
  }

  /**
   * 连接SQLite数据库
   */
  private function connect_sqlite($conf) {
    if (!isset($conf['database'])) {
      throw new Exception("Missing SQLite database path configuration");
    }
    
    // 确保数据库目录存在
    $db_dir = dirname($conf['database']);
    if (!is_dir($db_dir)) {
      if (!mkdir($db_dir, 0777, true)) {
        throw new Exception("Failed to create database directory: {$db_dir}");
      }
    }
    
    try {
      $this->link = new SQLite3($conf['database']);
    } catch (Exception $e) {
      throw new Exception("SQLite connection failed: " . $e->getMessage());
    }
  }

  /**
   * 连接MySQL数据库
   */
  private function connect_mysql($conf) {
    $required = ['host', 'user', 'password', 'database'];
    foreach($required as $param) {
      if(!isset($conf[$param])) {
        throw new Exception("Missing required MySQL config: {$param}");
      }
    }
    
    // 设置默认值
    $conf['port'] = isset($conf['port']) ? $conf['port'] : 3306;
    $conf['charset'] = isset($conf['charset']) ? $conf['charset'] : 'utf8mb4';
    
    $this->link = mysqli_connect(
      $conf['host'],
      $conf['user'],
      $conf['password'],
      $conf['database'],
      $conf['port']
    );

    if (!$this->link) {
      throw new Exception("MySQL connection failed: " . mysqli_connect_error());
    }

    if (!mysqli_set_charset($this->link, $conf['charset'])) {
      throw new Exception('Failed to set charset: ' . mysqli_error($this->link));
    }
  }

  /**
   * 执行SQL查询
   */
  function query($query) {
    $ret = array();
    $this->last_query = $query;
    
    if ($this->driver == 'sqlite') {
      $result = $this->link->query($query);
      if (!$result) {
        throw new Exception("SQLite query failed: " . $this->link->lastErrorMsg());
      }
      if ($result->numColumns() == 0) return true;
      $count = 0;
      while($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if($count++ > 1000) {
          break;
        }
        $ret[] = $row;
      }
    } else {
      $result = mysqli_query($this->link, $query);
      if (!$result) {
        throw new Exception("MySQL query failed: " . mysqli_error($this->link));
      }
      if ($result === TRUE) return TRUE;
      $count = 0;
      while($record = mysqli_fetch_assoc($result)) {
        if($count++ > 1000) {
          break;
        }
        $ret[] = $record;
      }
      mysqli_free_result($result);
    }
    return $ret;
  }

  /**
   * 获取最后插入的ID
   */
  function insert_id() {
    return $this->driver == 'sqlite' ? 
      $this->link->lastInsertRowID() : 
      mysqli_insert_id($this->link);
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
   */
  function escape($str) {
    return $this->driver == 'sqlite' ?
      SQLite3::escapeString($str) :
      mysqli_real_escape_string($this->link, $str);
  }
}

/**
 * 基础模型类
 * 封装了通用的CRUD操作
 */
class m { 
  protected $db;
  protected $filter = 1;
  protected $key;
  public $table;
  public $fields;
  
  function __construct($table) {
    global $db;
    $this->db = $db;
    $this->table = $table;
    $this->key = 'id';
  }

  /**
   * 分页获取数据
   * @param int $page 页码
   * @param int $limit 每页记录数
   * @return array
   */
  protected function getPage($page = 1, $limit = 20) {
    $offset = ($page - 1) * $limit;
    $query = "SELECT * FROM {$this->table} LIMIT {$limit} OFFSET {$offset}";
    return $this->db->query($query);
  }

  /**
   * 获取单条记录
   * @param int $id
   * @return array|null
   */
  protected function getOne($id) {
    $id = (int)$id;
    $query = "SELECT * FROM {$this->table} WHERE {$this->key}={$id} LIMIT 1";
    $result = $this->db->query($query);
    return isset($result[0]) ? $result[0] : null;
  }

  /**
   * 添加记录
   * @param array $data
   * @return int|bool
   */
  protected function add($data) {
    if(empty($data)) return false;
    
    $fields = array();
    $values = array();
    foreach($data as $key => $val) {
      if(in_array($key, $this->fields)) {
        $fields[] = $key;
        $values[] = "'".$this->db->escape($val)."'";
      }
    }
    
    if(empty($fields)) return false;
    
    $query = "INSERT INTO {$this->table} (".implode(',', $fields).") VALUES (".implode(',', $values).")";
    if($this->db->query($query)) {
      return $this->db->insert_id();
    }
    return false;
  }

  /**
   * 更新记录
   * @param int $id
   * @param array $data
   * @return bool
   */
  protected function update($id, $data) {
    if(empty($data)) return false;
    $id = (int)$id;
    
    $sets = array();
    foreach($data as $key => $val) {
      if(in_array($key, $this->fields)) {
        $sets[] = $key."='".$this->db->escape($val)."'";
      }
    }
    
    if(empty($sets)) return false;
    
    $query = "UPDATE {$this->table} SET ".implode(',', $sets)." WHERE {$this->key}={$id}";
    return $this->db->query($query);
  }

  /**
   * 删除记录
   * @param int $id
   * @return bool
   */
  protected function del($id) {
    $id = (int)$id;
    $query = "DELETE FROM {$this->table} WHERE {$this->key}={$id}";
    return $this->db->query($query);
  }

  public function __call($name, $arg) {
    return call_user_func_array(array($this, $name), $arg);
  }
}

/**
 * 显示系统消息
 * @param string $str 消息内容
 */
function __msg($str){
  header("Content-type: text/html; charset=utf-8");
  echo '<!DOCTYPE html><head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link href="pure-min.css" rel="stylesheet" type="text/css">
    </head><body><div style="padding: 20px;background: #f9f9ff;line-height: 1.5;margin: 20px;border-radius: 10px;border: 1px solid #ddd;margin: 100px auto;text-align: center;" >';
  echo $str;
  echo '</div></body></html>';
  exit(1);
}
