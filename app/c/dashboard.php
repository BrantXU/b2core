<?php
class dashboard extends base {
  
  public function __construct() {
    parent::__construct();
  }

  /**
   * 租户仪表板页面
   */
  public function index(): void {
    // 获取当前租户ID
    $tenant_id = $_GET['tenant_id'] ?? $_SESSION['current_tenant'] ?? null;
    
    if (!$tenant_id) {
      redirect(BASE . '/tenant/', '未指定租户。');
      return;
    }
    
    // 检查用户是否有权访问该租户
    $user = $this->check();
    if ($user['id'] <= 0) {
      redirect(BASE . '/user/login/', '请先登录。');
      return;
    }
    
    // 对于default租户，允许所有用户访问
    if ($tenant_id == 'default') {
      $hasAccess = true;
    } else {
      // 检查用户是否属于该租户
      $tenant_m = load('m/tenant_m');
      $userTenants = $tenant_m->getUserTenants($user['id']);
      $hasAccess = false;
      
      foreach ($userTenants as $tenant) {
        if (isset($tenant['id']) && $tenant['id'] == $tenant_id) {
          $hasAccess = true;
          break;
        }
      }
    }
    
    if (!$hasAccess) {
      redirect(BASE . '/tenant/', '您无权访问该租户。');
      return;
    }
    
    // 设置页面参数
    $param['tenant_id'] = $tenant_id;
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '租户仪表板';
    
    // 显示仪表板视图
    $this->display('v/dashboard/index', $param);
  }
}