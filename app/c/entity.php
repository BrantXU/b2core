<?php
class entity extends base {
  protected $m;
  protected $type;
  public $tenant_id;
  public $item;

  public function __construct(string $entity_type = '' )
  {
    parent::__construct();
    // 声明并初始化 $type 属性
    $this->type = $entity_type;
    $this->m = load('m/entity_m');
    $this->m->type = $entity_type;
    $this->tenant_id = $_SESSION['route_tenant_id'];
    //读取 data/tenantid/mod/$entity_type.json
    $file = APP.'../data/'.$this->tenant_id.'/mod/'.$entity_type.'.json';
    if(file_exists($file)){
      $data = json_decode(file_get_contents($file),true);
      $this->item = $data['item'];
    }
  }

  /**
   * 实体列表页面
   */
  public function index(): void {
    $entities = $this->m->entitylist();
    $param['entities'] = $entities;
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '实体列表';
    $param['item'] = $this->item;
    $param['entity_type'] = $this->type;
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
      $_POST['data'] = json_encode($_POST['data'], JSON_UNESCAPED_UNICODE);
      $result = $this->m->createEntity($_POST);
      if ($result) {
        redirect(tenant_url($this->type.'/'), '实体创建成功。');
      } else {
        $err = array('general' => '创建实体失败');
      }
    }
    
    $param['val'] = $_POST;
      $param['err'] = is_array($err) ? $err : array();
      $param['entity_type'] = $this->type;
      $param['item'] = $this->item;
      $param['entity'] = array();
      $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '创建实体';
    // 获取租户列表用于显示
    $tenant_m = load('m/tenant_m');
    $param['tenants'] = $tenant_m->tenantlist();
    $this->display('v/entity/edit', $param);
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
      $_POST['data'] = json_encode($_POST['data'],JSON_UNESCAPED_UNICODE);
      $result = $this->m->updateEntity($id, $_POST);
      if ($result) {
        redirect(tenant_url($this->type.'/'), '实体更新成功。');
      } else {
        $err = array('general' => '更新实体失败');
      }
    }
    
    $param['entity'] = $entity;    
    $param['item'] = $this->item;
    $param['entity_type'] = $this->type;
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
      redirect(tenant_url('entity/'), '实体删除成功。');
    } else {
      redirect(tenant_url('entity/'), '删除实体失败。');
    }
  }
}