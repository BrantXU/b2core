<?php
class entity_m extends m {
  public $table;
  public $fields;
  public $type;
  public $conditions = [];

  public function __construct($table = null) 
  {
    global $db_tenant;
    $this->db = $db_tenant; 
    $this->table = 'tb_entity';
    $this->conditions = [];
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
    if(!empty($this->conditions)){
      $conditions = array_merge($conditions,$this->conditions);
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
    // 创建实体
    $entityId = $this->add($data);
    
    if ($entityId) {
      // 获取创建的完整实体数据
      $entityData = $this->getEntity($entityId);
      
      if ($entityData) {
        // 保存实体缓存
        $this->saveEntityCache($entityData);
      }
    }
    //die($entityId);
    return $entityId;
  }

  /**
   * 更新实体
   * @param string $id 实体ID
   * @param array $data 实体数据
   * @return bool
   */
  public function updateEntity($id, $data) {
    // 更新数据库
    $result = $this->update($id, $data);
    
    if ($result) {
      // 获取更新后的完整实体数据
      $entityData = $this->getEntity($id);
      
      if ($entityData) {
        // 保存实体缓存
        $this->saveEntityCache($entityData);
      }
    }
    
    return $result;
  }

  /**
   * 保存实体缓存到文件
   * @param array $entityData 实体完整数据
   * @return bool 是否保存成功
   */
  private function saveEntityCache($entityData) {
    // 确定租户ID和实体ID
    $tenantId = $entityData['tenant_id'];
    $entityId = $entityData['id'];
    
    // 创建缓存文件路径
    $cacheDir = APP . '../data/' . $tenantId . '/entity/';
    if (!is_dir($cacheDir)) {
      mkdir($cacheDir, 0755, true);
    }
    
    $cacheFilePath = $cacheDir . $entityId . '.json';
    
    // 保存实体数据到缓存文件
    return file_put_contents($cacheFilePath, json_encode($entityData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false;
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