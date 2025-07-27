<?php
class entity extends base {
  protected $m;
  
  public function __construct() {
    parent::__construct();
    $this->m = load('m/entity_m');
  }

  /**
   * 实体列表页面
   */
  public function index(): void {
    $entities = $this->m->entitylist();
    $param['entities'] = $entities;
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '实体列表';
    $this->display('v/entity/list', $param);
  }

  /**
   * 创建实体页面
   */
  public function create(): void {
    $conf = array('name' => 'required', 'type' => 'required');
    $err = validate($conf);
    
    if (!empty($_POST) && $err === TRUE) {
      // 在控制器中生成ID并添加到数据中
      $_POST['id'] = randstr(8);
      // 设置租户ID（这里假设为固定值，实际应用中应从会话或上下文中获取）
      $_POST['tenant_id'] = 'default';
      // 设置创建和更新时间
      $_POST['created_at'] = date('Y-m-d H:i:s');
      $_POST['updated_at'] = date('Y-m-d H:i:s');
      $result = $this->m->createEntity($_POST);
      if ($result) {
        redirect(BASE . '/entity/', '实体创建成功。');
      } else {
        $err = array('general' => '创建实体失败');
      }
    }
    
    $param['val'] = $_POST;
    $param['err'] = is_array($err) ? $err : array();
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '创建实体';
    // 获取租户列表用于显示
    $tenant_m = load('m/tenant_m');
    $param['tenants'] = $tenant_m->tenantlist();
    $this->display('v/entity/create', $param);
  }

  /**
   * 编辑实体页面
   */
  public function edit(): void {
    $id = $_GET['id'];
    $entity = $this->m->getEntity($id);
    
    if (!$entity) {
      show_404('实体不存在');
    }
    
    $conf = array('name' => 'required', 'type' => 'required');
    $err = validate($conf);
    
    if (!empty($_POST) && $err === TRUE) {
      // 设置更新时间
      $_POST['updated_at'] = date('Y-m-d H:i:s');
      $result = $this->m->updateEntity($id, $_POST);
      if ($result) {
        redirect(BASE . '/entity/', '实体更新成功。');
      } else {
        $err = array('general' => '更新实体失败');
      }
    }
    
    $param['entity'] = $entity;
    $param['val'] = $_POST;
    $param['err'] = is_array($err) ? $err : array();
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '编辑实体';
    // 获取租户列表用于显示
    $tenant_m = load('m/tenant_m');
    $param['tenants'] = $tenant_m->tenantlist();
    $this->display('v/entity/edit', $param);
  }

  /**
   * 删除实体
   */
  public function delete(): void {
    $id = $_GET['id'];
    $result = $this->m->deleteEntity($id);
    
    if ($result) {
      redirect(BASE . '/entity/', '实体删除成功。');
    } else {
      redirect(BASE . '/entity/', '删除实体失败。');
    }
  }
}