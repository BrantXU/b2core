<?php
class tenant_m extends m {
  public $table;
  public $fields;
  
  public function __construct() {
    parent::__construct('tb_tenant');
    $this->fields = array('id', 'name', 'status');
  }

  /**
   * 获取租户列表
   * @param int $page 页码
   * @param int $limit 每页记录数
   * @return array
   */
  public function tenantlist($page = 1, $limit = 20) {
    return $this->getPage($page, $limit);
  }

  /**
   * 根据ID获取租户信息
   * @param int $id 租户ID
   * @return array|null
   */
  public function getTenant($id) {
    return $this->getOne($id);
  }

  /**
   * 根据子域名获取租户信息
   * @param string $subdomain 子域名
   * @return array|null
   */
  public function getTenantBySubdomain($subdomain) {
    return null;
  }

  /**
   * 创建租户
   * @param array $data 租户数据
   * @return int|bool
   */
  public function createTenant($data) {
    return $this->add($data);
  }

  /**
   * 更新租户
   * @param int $id 租户ID
   * @param array $data 租户数据
   * @return bool
   */
  public function updateTenant($id, $data) {
    return $this->update($id, $data);
  }

  /**
   * 删除租户
   * @param int $id 租户ID
   * @return bool
   */
  public function deleteTenant($id) {
    return $this->del($id);
  }

  /**
   * 检查子域名是否已存在
   * @param string $subdomain 子域名
   * @return string|bool
   */
  public function isSubdomainExist($subdomain) {
    return false;
  }
}