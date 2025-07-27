<?php
class config_m extends m {
  public $table;
  public $fields;
  
  public function __construct() {
    parent::__construct('tb_config');
    $this->fields = array('key', 'value', 'description');
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
    $sql = "SELECT * FROM {$this->table} WHERE key = ?";
    return $this->db->getRow($sql, [$key]);
  }

  /**
   * 创建配置
   * @param array $data 配置数据
   * @return bool
   */
  public function createConfig($data) {
    return $this->add($data);
  }

  /**
   * 更新配置
   * @param string $id 配置ID
   * @param array $data 配置数据
   * @return bool
   */
  public function updateConfig($id, $data) {
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