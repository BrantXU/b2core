<?php
class config_m extends m {
  public $table;
  public $fields;
  
  public function __construct() {
    parent::__construct('tb_config');
    $this->fields = array('id', 'key', 'value', 'description', 'tenant_id', 'created_at', 'updated_at');
  }

  /**
   * 获取配置列表
   * @param int $page 页码
   * @param int $limit 每页记录数
   * @return array
   */
  public function configlist($page = 1, $limit = 20) {
    return $this->getPage($page, $limit);
  }

  /**
   * 根据ID获取配置信息
   * @param string $id 配置ID
   * @return array|null
   */
  public function getConfig($id) {
    return $this->getOne($id);
  }

  /**
   * 根据键名获取配置信息
   * @param string $key 配置键名
   * @return array|null
   */
  public function getConfigByKey($key) {
    $sql = "SELECT * FROM {$this->table} WHERE key = '" . $this->db->escape($key) . "'";
    $result = $this->db->query($sql);
    return isset($result[0]) ? $result[0] : null;
  }

  /**
   * 创建配置
   * @param array $data 配置数据
   * @return bool
   */
  public function createConfig($data) {
    // 检查是否已存在相同key的配置
    if (isset($data['key']) && $this->getConfigByKey($data['key'])) {
      // 如果已存在，可以选择更新现有配置或返回false
      // 这里我们选择返回false表示创建失败
      return false;
    }
    // 确保tenant_id始终有默认值
    if (!isset($data['tenant_id'])) {
      $data['tenant_id'] = 'default';
    }
    return $this->add($data);
  }

  /**
   * 更新配置
   * @param string $id 配置ID
   * @param array $data 配置数据
   * @return bool
   */
  public function updateConfig($id, $data) {
    // 获取现有配置以保留tenant_id（如果未提供）
    if (!isset($data['tenant_id'])) {
      $existingConfig = $this->getConfig($id);
      if ($existingConfig && isset($existingConfig['tenant_id'])) {
        $data['tenant_id'] = $existingConfig['tenant_id'];
      } else {
        $data['tenant_id'] = 'default';
      }
    }
    return $this->update($id, $data);
  }

  /**
   * 删除配置
   * @param string $id 配置ID
   * @return bool
   */
  public function deleteConfig($id) {
    return $this->del($id);
  }
}