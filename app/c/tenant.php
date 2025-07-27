<?php
class tenant extends base {
  protected $m;
  
  public function __construct() {
    parent::__construct();
    $this->m = load('m/tenant_m');
  }

  /**
   * 租户列表页面
   */
  public function index(): void {
    $tenants = $this->m->tenantlist();
    $param['tenants'] = $tenants;
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '租户列表';
    $this->display('v/tenant/list', $param);
  }

  /**
   * 创建租户页面
   */
  public function create(): void {
    $conf = array('name' => 'required');
    $err = validate($conf);
    
    if (!empty($_POST) && $err === TRUE) {
      // 在控制器中生成ID并添加到数据中
      $_POST['id'] = randstr(8);
      $result = $this->m->createTenant($_POST);
      if ($result) {
        redirect(BASE . '/tenant/', '租户创建成功。');
      } else {
        $err = array('general' => '创建租户失败');
      }
    }
    
    $param['val'] = $_POST;
    $param['err'] = is_array($err) ? $err : array();
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '创建租户';
    $this->display('v/tenant/create', $param);
  }

  /**
   * 编辑租户页面
   */
  public function edit(): void {
    $id = $_GET['id'];
    $tenant = $this->m->getTenant($id);
    
    if (!$tenant) {
      show_404('租户不存在');
    }
    
    $conf = array('name' => 'required');
    $err = validate($conf);
    
    if (!empty($_POST) && $err === TRUE) {
      $result = $this->m->updateTenant($id, $_POST);
      if ($result) {
        redirect(BASE . '/tenant/', '租户更新成功。');
      } else {
        $err = array('general' => '更新租户失败');
      }
    }
    
    $param['tenant'] = $tenant;
    $param['val'] = $_POST;
    $param['err'] = is_array($err) ? $err : array();
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '编辑租户';
    $this->display('v/tenant/edit', $param);
  }

  /**
   * 删除租户
   */
  public function delete(): void {
    $id = $_GET['id'];
    $result = $this->m->deleteTenant($id);
    
    if ($result) {
      redirect(BASE . '/tenant/', '租户删除成功。');
    } else {
      redirect(BASE . '/tenant/', '删除租户失败。');
    }
  }
}