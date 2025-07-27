<?php
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
  public function __construct($conf) {
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