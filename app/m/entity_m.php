<?php
class entity_m extends m {
  public $table;
  public $fields;
  public $type;
  public $conditions = [];
  public $tenant_id;

  public function __construct($table = null) 
  {
    global $tdb;
    $this->db = $tdb; 
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
   * 分页获取数据，重写父类方法以支持JSON查询
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
      foreach ($conditions as $column => $value) {
        // 处理JSON查询条件
        if (strpos($column, 'json_filter_') === 0) {
          // 提取JSON字段名
          $jsonKey = substr($column, 12); // 去掉 'json_filter_' 前缀
          $escapedValue = $this->db->escape($value);
          $conditionParts[] = "data->>'\$.{$jsonKey}' = '{$escapedValue}'";
        } else {
          $escapedValue = $this->db->escape($value);
          $conditionParts[] = "{$column} = '{$escapedValue}'";
        }
      }
      $where .= " AND " . implode(" AND ", $conditionParts);
    }
    
    $query = "SELECT * FROM {$this->table} WHERE {$where} LIMIT {$limit} OFFSET {$offset}";
    return $this->db->query($query);
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
    //$entityId = 
    $this->add($data);
    $this->saveEntityCache($data);
    $this->saveEntityHistory($data);
    return $data['id'];
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
        $this->saveEntityHistory($entityData);
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


  private function saveEntityHistory($entityData) {
    // 确定租户ID和实体ID
    $tenantId = $entityData['tenant_id'];
    $entityId = $entityData['id'];
    $histId = time();
    // 创建缓存文件路径
    $cacheDir = APP . '../data/' . $tenantId . '/hist/'.$entityId.'/';
    if (!is_dir($cacheDir)) {
      mkdir($cacheDir, 0755, true);
    }
    
    $cacheFilePath = $cacheDir . $histId . '.json';
    $logoFilePath = APP . '../data/' . $tenantId . '/log/'.date('Y-m-d').'.log';
    $wdata = json_encode($entityData, JSON_UNESCAPED_UNICODE);
    // 保存到日志文件
    file_put_contents($logoFilePath, date('H:i:s').'  '.$entityId.'  '.$wdata."\n", FILE_APPEND);
    // 保存实体数据到缓存文件
    return file_put_contents($cacheFilePath, $wdata) !== false;
  }

  public function hist($id) {
    $dir = APP . '../data/' . $this->tenant_id . '/hist/'.$id.'/';
    // 检查目录是否存在
    if (!is_dir($dir)) {
        return [];
    }

    // 读取目录内容并过滤文件
    $files = scandir($dir);
    $files = array_diff($files, ['.', '..']); // 去掉当前目录和上级目录

    $fileList = [];

    foreach ($files as $file) {
        $filePath = $dir . '/' . $file;
        if (is_file($filePath)) {
            $fileList[] = [
                'name' => $file,
                'time' => filemtime($filePath) // 获取文件最后修改时间
            ];
        }
    }

    // 按照修改时间倒序排序
    usort($fileList, function($a, $b) {
        return $b['time'] - $a['time'];
    });

    return $fileList;
  }

  /* view history edititon */

  public function vhist($id,$entity_id) {
    if (preg_match_all('/[\d]+/', $id, $matches)) {
      $id = $matches[0][0];// . "<br>";
    }
    $hist = APP . '../data/' . $this->tenant_id . '/hist/'.$entity_id.'/'.$id.'.json';
    $entity = json_decode(file_get_contents($hist),true);
    $entity['data'] = json_decode($entity['data'],true);
    if(isset($matches[0][1])){
      $id = $matches[0][1];
      $hist = APP . '../data/' . $this->tenant_id . '/hist/'.$entity_id.'/'.$id.'.json';
      $entity1 = json_decode(file_get_contents($hist),true);
      $entityData = json_decode($entity1['data'],true);
      foreach($entityData as $k=>$v){
        if( $entity['data'][$k]!=$v)$entity['data'][$k.'_compare'] = $v;
      }
    }
    return $entity;
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

  /**
   * 获取实体配置
   * @param string $mod 配置ID或值
   * @param array $conf 配置映射数组
   * @return array 配置项数组
   */
  public function getItem($mod) {
    global $conf;
    $configId = $mod;
    // 1. 检查$conf是否存在且包含id_map
    if (!empty($conf) && isset($conf['id_key_map'])) {
      // 2. 检查id是否存在于id_map中
      if (!isset($conf['id_key_map'][$mod])) {
        // 3. 如果id不存在，尝试根据value查找id
        $reverseMap = array_flip($conf['id_key_map']);
        if (isset($reverseMap[$mod])) {
          $configId = $reverseMap[$mod];
        }
      }
    }
    
    // 4. 根据找到的configId载入配置文件
    $file = APP.'../data/'.$this->tenant_id.'/conf/'.$configId.'.json';
    $item = [];
    if(file_exists($file)){
      $data = json_decode(file_get_contents($file),true);
      $item = $data['item'] ?? []; // 确保item是数组
    }
    
    return $item;
  }
}