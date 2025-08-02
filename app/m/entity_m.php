<?php
class entity_m extends m {
  public $table;
  public $fields;
  public $type;

  public function __construct($table = null) 
  {
    global $db_tenant;
    $this->db = $db_tenant; 
    $this->table = 'tb_entity';
    $this->key = 'id';    
    $this->fields = array('id', 'tenant_id', 'name', 'type', 'data', 'description', 'created_at', 'updated_at');
  }

  /**
   * 获取实体列表
   * @param int $page 页码
   * @param int $limit 每页记录数
   * @return array
   */
  public function entitylist($page = 1, $limit = 20) {
    $conditions = [];
    if (!empty($this->type)) {
      $conditions['type'] = $this->type;
    }
    return $this->getPage($page, $limit, $conditions);
  }

  /**
   * 根据ID获取实体信息
   * @param string $id 实体ID
   * @return array|null
   */
  public function getEntity($id) {
    return $this->getOne($id);
  }

  /**
   * 创建实体
   * @param array $data 实体数据
   * @return int|bool
   */
  public function createEntity($data) {
    return $this->add($data);
  }

  /**
   * 更新实体
   * @param string $id 实体ID
   * @param array $data 实体数据
   * @return bool
   */
  public function updateEntity($id, $data) {
    return $this->update($id, $data);
  }

  /**
   * 删除实体
   * @param string $id 实体ID
   * @return bool
   */
  public function deleteEntity($id) {
    return $this->del($id);
  }

  /**
   * 获取所有实体数据
   * @param string $type 实体类型
   * @return array
   */
  public function getAllEntities($type = null) {
      $where = '1';
      if (!empty($type)) {
          $type = $this->db->escape($type);
          $where = "type = '{$type}'";
      }
      $query = "SELECT * FROM {$this->table} WHERE {$where}";
      return $this->db->query($query);
  }
}