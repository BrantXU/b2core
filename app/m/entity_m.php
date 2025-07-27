<?php
class entity_m extends m {
  public $table;
  public $fields;
  
  public function __construct() {
    parent::__construct('tb_entity');
    $this->fields = array('id', 'tenant_id', 'name', 'type', 'data', 'description', 'created_at', 'updated_at');
  }

  /**
   * 获取实体列表
   * @param int $page 页码
   * @param int $limit 每页记录数
   * @return array
   */
  public function entitylist($page = 1, $limit = 20) {
    return $this->getPage($page, $limit);
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
}