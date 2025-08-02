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
      //print_r($data);
      $this->item = $data['item'] ?? []; // 确保item是数组
    } else {
      $this->item = []; // 默认设为空数组
    }
  }

  /**
   * 实体列表页面
   */
  public function index(): void {
    $entities = $this->m->entitylist();
    
    // 加工处理entities数据
    $processedEntities = [];
    foreach ($entities as $entity) {
      // 解码data字段中的JSON数据
      $entityData = [];
      if (!empty($entity['data'])) {
        $entityData = json_decode($entity['data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
          $entityData = [];
        }
      }
      
      // 合并基础字段和data字段
      $fullEntityData = array_merge($entity, $entityData);
      
      // 使用FormRenderer渲染实体字段
      $renderedFields = [];
      if (!empty($this->item)) {
        foreach ($this->item as $fieldName => $fieldConfig) {
          if (isset($fieldConfig['listed']) && $fieldConfig['listed'] == 1) {
            // 准备renderControl所需参数
            $type = $fieldConfig['type'] ?? 'text';
            $value = isset($fullEntityData[$fieldName]) ? htmlspecialchars($fullEntityData[$fieldName]) : '';
            $readonly = isset($fieldConfig['readonly']) && $fieldConfig['readonly'] ? 'readonly' : '';
            $readonlyClass = $readonly ? ' uk-background-muted' : '';
            $required = isset($fieldConfig['required']) && $fieldConfig['required'] ? 'required' : '';
            $tips = isset($fieldConfig['tips']) ? '<small class="help-text">'.htmlspecialchars($fieldConfig['tips']).'</small>' : '';
            $props = $fieldConfig['props'] ?? [];

            $renderedFields[$fieldName] = FormRenderer::renderControl(
              $type, 
              $fieldName, 
              $value, 
              $readonly, 
              $readonlyClass, 
              $required, 
              $tips, 
              true, // view模式
              $props, 
              $fullEntityData, 
              [] 
            );
          }
        }
      }
      
      $processedEntity = $entity;
      $processedEntity['rendered_fields'] = $renderedFields;
      $processedEntities[] = $processedEntity;
    }
    
    $param['entities'] = $processedEntities;
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '实体列表';
    $param['item'] = $this->item;
    $param['entity_type'] = $this->type;
    $this->display('v/entity/list', $param);
  }

  /**
   * 创建实体页面
   */
  public function add(): void {
    $this->create();
  }
  
  public function create(): void {
    // 详细记录表单提交数据
    if (!empty($_POST)) {
      error_log('表单提交数据: ' . print_r($_POST, true));
    }
    
    // $conf = array('name' => 'required');
    // $err = validate($conf,isset($_POST['data'])?$_POST['data']:$_POST);
    $err = [];
    if (!empty($_POST)) {
      error_log('表单验证结果: ' . ($err === TRUE ? '通过' : print_r($err, true)));
    }
    
   
    if (!empty($_POST) ) {
      // 在控制器中生成ID并添加到数据中
      $_POST['id'] = randstr(8);
      $_POST['tenant_id'] = $this->tenant_id;
      $_POST['type'] = $this->type;
      $_POST['name'] = $_POST['data']['name'];
      // 租户ID通过表单隐藏字段设置
      // 设置创建和更新时间
      $_POST['created_at'] = date('Y-m-d H:i:s');
      $_POST['updated_at'] = date('Y-m-d H:i:s');
      $_POST['data'] = json_encode($_POST['data'], JSON_UNESCAPED_UNICODE);

      error_log('准备保存的数据: ' . print_r($_POST, true));
      
      $result = $this->m->createEntity($_POST);
      
      error_log('createEntity 结果: ' . ($result ? '成功' : '失败'));
      
      if ($result) {
        redirect(tenant_url($this->type.'/'), '实体创建成功。');
      } else {
        $err = array('general' => '创建实体失败，请联系管理员');
        error_log('创建实体失败，错误信息: ' . print_r($err, true));
      }
    }
    
    $param['val'] = $_POST;
    $param['err'] = is_array($err) ? $err : array();
    $param['entity_type'] = $this->type;
    $param['item'] = $this->item;
    $param['entity'] = array();
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '创建实体';
    $this->display('v/entity/edit', $param);
  }

  /**
   * 编辑实体页面
   */
  public function edit($id =''): void {
 //   $id = $_GET['id'];
    $entity = $this->m->getEntity($id);
    
    if (!$entity) {
      show_404('实体不存在');
    }
    
    $conf = array('name' => 'required');
    $err = [];//validate($conf,isset($_POST['data'])?$_POST['data']:$_POST);

    if (!empty($_POST)) 
    {
      // 设置更新时间
      if(isset($_POST['data']['name']))$_POST['name'] = $_POST['data']['name'];
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
  
  public function view($id ='') {
   // $id = $_GET['id'] ?? '';
    if (empty($id)) {
      redirect(tenant_url('entity/'), '实体ID不存在');
      return;
    }
    $entity = $this->m->getEntity($id);
    if (!$entity) {
      redirect(tenant_url('entity/'), '实体不存在');
      return;
    }
    // 获取实体配置项
    $item = $this->item;
    $param = [
      'page_title' => '实体展示',
      'entity' => $entity,
      'entity_type' => $entity['type'],
      'item' => $item,
      // 设置对象菜单所需变量
      'object_menu_key' => $this->type,
      'object_id' => $id
    ];
    $this->display('v/entity/show', $param);
  }
  
  /**
   * 导入实体数据
   */
  public function import(): void {
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
          $file = $_FILES['import_file'];
          
          // 验证文件类型
          if ($file['type'] !== 'text/csv' && !in_array(pathinfo($file['name'], PATHINFO_EXTENSION), ['csv'])) {
              redirect(tenant_url('entity/'), '请上传CSV格式文件', 'error');
              return;
          }
          
          // 读取CSV文件
          $handle = fopen($file['tmp_name'], 'r');
          $header = fgetcsv($handle);
          $success = 0;
          $error = 0;
          
          while (($row = fgetcsv($handle)) !== false) {
              $data = array_combine($header, $row);
              
              // 验证必要字段
              if (empty($data['name'])) {
                  $error++;
                  continue;
              }
              
              // 准备实体数据
              $entity_data = [
                  'name' => $data['name'],
                  'type' => $this->type,
                  'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                  'created_at' => date('Y-m-d H:i:s'),
                  'updated_at' => date('Y-m-d H:i:s'),
                  'id' => randstr(8)
              ];
              
              // 保存实体
              if ($this->m->createEntity($entity_data)) {
                  $success++;
              } else {
                  $error++;
              }
          }
          
          fclose($handle);
          redirect(tenant_url('entity/'), "导入完成：成功{$success}条，失败{$error}条");
      }
      
      $this->display('v/entity/import', ['page_title' => '导入实体']);
  }

  /**
   * 导出实体数据，导出的数据应该是 数据表中 data 字段中的 json 数据，表头的内容在 item 配置中。
   */
  public function export(): void {
      // 获取所有实体数据
      $entities = $this->m->getAllEntities($this->type);
      
      // 设置CSV响应头
      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename="entities_' . date('YmdHis') . '.csv"');
      
      $output = fopen('php://output', 'w');
      
      // 写入CSV头部 - 从item配置中获取
      $header = [];
      if (is_array($this->item) && !empty($this->item)) {
          // 使用item配置中的字段名称作为表头
          $header = array_map(function($field) {
              return $field['name'] ?? '';
          }, array_values($this->item));
      } else {
          // 如果没有有效的item配置，使用默认表头
          $header = ['ID', 'Name', 'Type', 'Created At', 'Updated At'];
      }
      fputcsv($output, $header, ',', '"', '\\');
      
      // 写入数据行
      foreach ($entities as $entity) {
          // 解码data字段中的JSON数据
          $data = json_decode($entity['data'], true) ?? [];

          $row = [];
          if (is_array($this->item) && !empty($this->item)) {
              // 根据item配置中的字段导出对应的数据
              foreach ($this->item as $fieldId => $field) {
                  // 从data中获取对应字段的值，如果不存在则为空字符串
                  $row[] = $data[$fieldId] ?? '';
              }
          } else {
              // 使用默认字段
              $row = [
                  $entity['id'] ?? '',
                  $data['name'] ?? '',
                  $entity['type'] ?? '',
                  $entity['created_at'] ?? '',
                  $entity['updated_at'] ?? ''
              ];
          }
          fputcsv($output, $row, ',', '"', '\\');
      }
      
      fclose($output);
      exit;
  }
}
?>