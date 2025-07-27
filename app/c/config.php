<?php
class config extends base {
  protected $m;
  
  public function __construct() {
    parent::__construct();
    $this->m = load('m/config_m');
  }

  /**
   * 配置列表页面
   */
  public function index(): void {
    $configs = $this->m->configlist();
    $param['configs'] = $configs;
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '配置列表';
    $this->display('v/config/list', $param);
  }

  /**
   * 创建配置页面
   */
  public function create(): void {
    $conf = array('key' => 'required', 'value' => 'required');
    $err = validate($conf);
    
    if (!empty($_POST) && $err === TRUE) {
      // 在控制器中生成ID并添加到数据中
      $_POST['id'] = randstr(8);
      $result = $this->m->createConfig($_POST);
      if ($result) {
        redirect(BASE . '/config/', '配置创建成功。');
      } else {
        $err = array('general' => '创建配置失败');
      }
    }
    
    $param['val'] = $_POST;
    $param['err'] = is_array($err) ? $err : array();
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '创建配置';
    $this->display('v/config/create', $param);
  }

  /**
   * 编辑配置页面
   */
  public function edit(): void {
    $id = $_GET['id'];
    $config = $this->m->getConfig($id);
    
    if (!$config) {
      show_404('配置不存在');
    }
    
    $conf = array('key' => 'required', 'value' => 'required');
    $err = validate($conf);
    
    if (!empty($_POST) && $err === TRUE) {
      $result = $this->m->updateConfig($id, $_POST);
      if ($result) {
        redirect(BASE . '/config/', '配置更新成功。');
      } else {
        $err = array('general' => '更新配置失败');
      }
    }
    
    $param['config'] = $config;
    $param['val'] = $_POST;
    $param['err'] = is_array($err) ? $err : array();
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '编辑配置';
    $this->display('v/config/edit', $param);
  }

  /**
   * 删除配置
   */
  public function delete(): void {
    $id = $_GET['id'];
    $result = $this->m->deleteConfig($id);
    
    if ($result) {
      redirect(BASE . '/config/', '配置删除成功。');
    } else {
      redirect(BASE . '/config/', '删除配置失败。');
    }
  }
}