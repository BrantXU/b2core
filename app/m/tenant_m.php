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
   * 获取总记录数
   * @return int
   */
  public function getTotal() {
    $result = $this->db->query("SELECT COUNT(*) as total FROM $this->table");
    return isset($result[0]['total']) ? (int)$result[0]['total'] : 0;
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
    $result = $this->add($data);
    
    // 如果租户创建成功，创建以租户ID命名的文件夹
    if ($result && isset($data['id'])) {
      $tenantDir = APP . '../data/' . $data['id'];
      if (!is_dir($tenantDir)) {
        mkdir($tenantDir, 0777, true);
      }
    }
    
    return $result;
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
  
  /**
   * 添加用户到租户
   * @param string $userId 用户ID
   * @param string $tenantId 租户ID
   * @return bool
   */
  public function addUserToTenant($userId, $tenantId) {
    $userId = $this->db->escape($userId);
    $tenantId = $this->db->escape($tenantId);
    $query = "INSERT INTO tb_user_tenant (user_id, tenant_id) VALUES ('$userId', '$tenantId')";
    return $this->db->query($query);
  }
  
  /**
   * 从租户移除用户
   * @param string $userId 用户ID
   * @param string $tenantId 租户ID
   * @return bool
   */
  public function removeUserFromTenant($userId, $tenantId) {
    $query = "DELETE FROM tb_user_tenant WHERE user_id = ? AND tenant_id = ?";
    return $this->db->query($query, array($userId, $tenantId));
  }
  
  /**
   * 获取租户下的所有用户
   * @param string $tenantId 租户ID
   * @return array
   */
  public function getTenantUsers($tenantId) {
    $query = "SELECT u.* FROM tb_user u 
              JOIN tb_user_tenant ut ON u.id = ut.user_id 
              WHERE ut.tenant_id = ?";
    return $this->db->query($query, array($tenantId));
  }
  
  /**
   * 获取用户所属的所有租户
   * @param string $userId 用户ID
   * @return array
   */
  public function getUserTenants($userId) {
    $userId = $this->db->escape($userId);
    $query = "SELECT t.* FROM tb_tenant t 
              JOIN tb_user_tenant ut ON t.id = ut.tenant_id 
              WHERE ut.user_id = '$userId'";
    return $this->db->query($query);
  }
  
  /**
   * 进入租户
   * @param string $tenantId 租户ID
   * @return bool
   */
  public function enter($tenantId) {
    // 将租户ID存储到session中
    $_SESSION['current_tenant'] = $tenantId;
    return true;
  }
}