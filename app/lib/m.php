<?php 
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
  public $conditions;
  
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
   * @param array $conditions 条件
   * @return array
   */
  public function getPage($page = 1, $limit = 20, $conditions = []) {
    $offset = ($page - 1) * $limit;
    $where = $this->filter;
    
    if (!empty($conditions)) {
      $conditionParts = [];
      //print_r($conditions);
      foreach ($conditions as $column => $value) {
        $escapedValue = $this->db->escape($value);
        //TODO ： json 查询分别处理
        $conditionParts[] = "{$column} = '{$escapedValue}'";
      }
      $where .= " AND " . implode(" AND ", $conditionParts);
    }
    
    $query = "SELECT * FROM {$this->table} WHERE {$where} LIMIT {$limit} OFFSET {$offset}";
    return $this->db->query($query);
  }

  /**
   * 获取单条记录
   * @param string|int $id
   * @return array|null
   */
  protected function getOne($id) {
    // 根据ID类型进行不同的处理
    if (is_numeric($id)) {
      $id = (int)$id;
      $query = "SELECT * FROM {$this->table} WHERE {$this->key}={$id} LIMIT 1";
    } else {
      $id = "'".$this->db->escape($id)."'";
      $query = "SELECT * FROM {$this->table} WHERE {$this->key}={$id} LIMIT 1";
    }
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
    $lastId = null;
    $fields = [];
    $values = [];
    foreach($data as $key => $val) {
        if(in_array($key, $this->fields)) {
            $fields[] = $key;
            $values[] = "'".$this->db->escape($val)."'";
        }
    }
    
    if(!empty($fields)) {
        $query = "INSERT INTO {$this->table} (".implode(',', $fields).") VALUES (".implode(',', $values).")";
        if($this->db->query($query)) {
            $lastId = $this->db->insert_id();
            // 及时释放内存
            unset($fields, $values, $chunk);
            gc_collect_cycles();
        }
    }
    return $lastId;
}

  /**
   * 更新记录
   * @param string|int $id
   * @param array $data
   * @return bool
   */
  protected function update($id, $data) {
    if(empty($data)) return false;
    
    // 根据ID类型进行不同的处理
    if (is_numeric($id)) {
      $id = (int)$id;
      $where = "{$this->key}={$id}";
    } else {
      $id = "'".$this->db->escape($id)."'";
      $where = "{$this->key}={$id}";
    }
    
    $sets = array();
    foreach($data as $key => $val) {
      if(in_array($key, $this->fields)) {
        $sets[] = $key."='".$this->db->escape($val)."'";
      }
    }
    
    if(empty($sets)) return false;
    
    $query = "UPDATE {$this->table} SET ".implode(',', $sets)." WHERE {$where}";
    return $this->db->query($query);
  }

  /**
   * 删除记录
   * @param string|int $id
   * @return bool
   */
  protected function del($id) {
    // 根据ID类型进行不同的处理
    if (is_numeric($id)) {
      $id = (int)$id;
      $query = "DELETE FROM {$this->table} WHERE {$this->key}={$id}";
    } else {
      $id = "'" . $this->db->escape($id) . "'";
      $query = "DELETE FROM {$this->table} WHERE {$this->key}={$id}";
    }
    
    // 添加调试日志
    error_log('执行删除SQL: ' . $query);
    $result = $this->db->query($query);
    
    if (!$result) {
      // 数据库操作失败，错误信息已由db类记录
      error_log('SQL执行失败: ' . $this->db->last_query);
    }
    
    return $result;
  }

  /**
   * 处理不存在的方法调用
   * @param string $name 方法名
   * @param array $arg 参数数组
   * @return bool|mixed 返回false表示方法不存在
   */
  public function __call($name, $arg) {
    error_log("尝试调用不存在的方法: {$name}");
    return false;
  }
}