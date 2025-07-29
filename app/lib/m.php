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
  protected function getPage($page = 1, $limit = 20, $conditions = []) {
    $offset = ($page - 1) * $limit;
    $where = $this->filter;
    
    if (!empty($conditions)) {
      $conditionParts = [];
      foreach ($conditions as $column => $value) {
        $escapedValue = $this->db->escape($value);
        $conditionParts[] = "`{$column}` = '{$escapedValue}'";
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
      $id = "'".$this->db->escape($id)."'";
      $query = "DELETE FROM {$this->table} WHERE {$this->key}={$id}";
    }
    return $this->db->query($query);
  }

  public function __call($name, $arg) {
    return call_user_func_array(array($this, $name), $arg);
  }
}