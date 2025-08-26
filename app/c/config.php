<?php
class config extends base {
  protected $m;
  
  public function __construct() {
    parent::__construct();
    $this->m = load('m/config_m');
  }

  /**
   * 配置列表页面 - 前端分页版本
   */
  public function index(): void {
    // 获取所有配置数据
    $configs = $this->m->getAll();
    // 设置默认每页显示数量
    $limit = 10;
    // 计算总记录数
    $total = count($configs);
    // 计算总页数
    $totalPages = max(1, ceil($total / $limit));
    
    // 准备分页数据
    $pagination = [
      'total_items' => $total,
      'limit' => $limit,
      'total_pages' => $totalPages
    ];
    
    $param['configs'] = $configs;
    $param['pagination'] = $pagination;
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '配置列表';
    $this->addBreadcrumb('配置管理', tenant_url('config/'));
    $this->addBreadcrumb('配置列表', '', true);
    $this->display('v/config/list', $param);
  }

  /**
   * 创建配置页面
   */
  public function create(): void {
    // 载入YAML处理类
    require_once(APP . 'lib/yaml.php');
    
    $conf = array('key' => 'required', 'value' => 'required', 'config_type' => 'required');
    $err = validate($conf);
    
    if (!empty($_POST) && $err === TRUE) {
      // 将YAML转换为JSON格式保存
      if (isset($_POST['value'])) {
        // 尝试解析YAML
        $yamlData = YAML::decode($_POST['value']);
        if ($yamlData !== null) {
          // 如果是有效的YAML，转换为JSON保存
          $_POST['value'] = json_encode($yamlData, JSON_UNESCAPED_UNICODE);
        }
      }
      
      // 在控制器中生成ID并添加到数据中
      $_POST['id'] = randstr(8);
      $_POST['config_type'] = $_POST['config_type'] ?? '';
      // 设置租户ID（这里假设为固定值，实际应用中应从会话或上下文中获取）
      $_POST['tenant_id'] = 'default';
      // 设置时间戳
      $_POST['created_at'] = date('Y-m-d H:i:s');
      $_POST['updated_at'] = date('Y-m-d H:i:s');
      // 检查是否已存在相同key的配置
      $existingConfig = $this->m->getConfigByKey($_POST['key']);
      if ($existingConfig) {
        $err = array('key' => '该键名已存在，请使用不同的键名');
      } else {
        $result = $this->m->createConfig($_POST);
        if ($result) {
          redirect(tenant_url('config/'), '配置创建成功。');
        } else {
          $err = array('general' => '创建配置失败');
        }
      }
    }
    
    $param['val'] = $_POST;
    $param['err'] = is_array($err) ? $err : array();
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = '创建配置';
    $this->addBreadcrumb('配置管理', tenant_url('config/'));
    $this->addBreadcrumb('创建配置', '', true);
    // 获取租户列表用于显示
    $tenant_m = load('m/tenant_m');
    $param['tenants'] = $tenant_m->tenantlist();
    $this->display('v/config/edit', $param);
  }

  /**
   * 编辑配置页面
   */
  public function edit(): void {
    // 检查是否是创建操作
    $isCreate = isset($_GET['action']) && $_GET['action'] == 'create';
    
    if (!$isCreate) {
      $id = $_GET['id'];
      $config = $this->m->getConfig($id);
      
      if (!$config) {
        show_404('配置不存在');
      }
      
      // 载入YAML处理类
      require_once(APP . 'lib/yaml.php');
      
      // 将JSON值转换为YAML格式用于显示
      if (isset($config['value'])) {
        // 尝试解析JSON
        $jsonData = json_decode($config['value'], true);
        if ($jsonData !== null) {
          // 如果是有效的JSON，转换为YAML显示
          $config['value'] = YAML::encode($jsonData);
        }
      }
    } else {
      // 创建操作时，初始化空配置
      $config = array();
      // 载入YAML处理类
      require_once(APP . 'lib/yaml.php');
    }
    
    $conf = array('key' => 'required', 'value' => 'required');
    $err = validate($conf);
    
    if (!empty($_POST) && $err === TRUE) {
      // 将YAML转换回JSON格式保存
      if (isset($_POST['value'])) {
        // 尝试解析YAML
        $yamlData = YAML::decode($_POST['value']);
        if ($yamlData !== null) {
          // 如果是有效的YAML，转换为JSON保存
          $_POST['value'] = json_encode($yamlData, JSON_UNESCAPED_UNICODE);
        }
      }
      
      if ($isCreate) {
        // 创建配置逻辑
        // 在控制器中生成ID并添加到数据中
        $_POST['id'] = randstr(8);
        $_POST['config_type'] = $_POST['config_type'] ?? '';
        // 设置租户ID（这里假设为固定值，实际应用中应从会话或上下文中获取）
        $_POST['tenant_id'] = 'default';
        // 设置时间戳
        $_POST['created_at'] = date('Y-m-d H:i:s');
        $_POST['updated_at'] = date('Y-m-d H:i:s');
        // 检查是否已存在相同key的配置
        $existingConfig = $this->m->getConfigByKey($_POST['key']);
        if ($existingConfig) {
          $err = array('key' => '该键名已存在，请使用不同的键名');
        } else {
          $result = $this->m->createConfig($_POST);
          if ($result) {
            redirect(tenant_url('config/'), '配置创建成功。');
          } else {
            $err = array('general' => '创建配置失败');
          }
        }
      } else {
        // 更新时间戳
        $_POST['updated_at'] = date('Y-m-d H:i:s');
        $result = $this->m->updateConfig($id, $_POST);
        if ($result) {
          redirect(tenant_url('config/'), '配置更新成功。');
        } else {
          $err = array('general' => '更新配置失败');
        }
      }
    }
    
    $param['config'] = $config;
    $param['val'] = $_POST;
    $param['err'] = is_array($err) ? $err : array();
    $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = $isCreate ? '创建配置' : '编辑配置';
    $this->addBreadcrumb('配置管理', tenant_url('config/'));
    $this->addBreadcrumb($isCreate ? '创建配置' : '编辑：'.$config['key'], '', true);
    // 获取租户列表用于显示
    $tenant_m = load('m/tenant_m');
    $param['tenants'] = $tenant_m->tenantlist();
    $this->display('v/config/edit', $param);
  }

  /**
   * 删除配置
   */
  public function delete(): void {
    $id = $_GET['id'];
    $result = $this->m->deleteConfig($id);
    
    if ($result) {
      redirect(tenant_url('config/'), '配置删除成功。');
    } else {
      redirect(tenant_url('config/'), '删除配置失败。');
    }
  }

  /**
   * 批量删除配置
   */
  public function batch_delete(): void {
    if (!empty($_POST['ids']) && is_array($_POST['ids'])) {
      $ids = $_POST['ids'];
      $this->log('收到批量删除请求，ID数量: ' . count($ids));
      
      $result = $this->m->batchDeleteConfig($ids);
      
      $successCount = count($result['success']);
      $failCount = count($result['fail']);
      $this->log('批量删除结果: 成功 ' . $successCount . ' 条，失败 ' . $failCount . ' 条');
      
      $message = '成功删除 ' . $successCount . ' 条配置';
      if ($failCount > 0) {
        $message .= '，失败 ' . $failCount . ' 条配置';
      }
      
      redirect(tenant_url('config/'), $message);
    } else {
      $this->log('批量删除请求参数无效');
      redirect(tenant_url('config/'), '请选择要删除的配置。');
    }
  }

  /**
   * lpp配置导入
   * 支持单条配置或JSON数组格式的多条配置导入
   */
  public function import(): void {
    // 载入YAML处理类
    require_once(APP . 'lib/yaml.php');
    
    $err = array();
    $successCount = 0;
    $failCount = 0;
    $failMessages = array();
    // 添加日志记录
    $this->log($_FILES);
    if (isset($_FILES['config_file'])) {
      $file = $_FILES['config_file'];
      $this->log('收到文件上传请求: ' . print_r($file, true));
      // 检查文件是否上传成功
      if ($file['error'] !== UPLOAD_ERR_OK) {
        $err['general'] = '文件上传失败，请重试。错误码: ' . $file['error'];
        $this->log('文件上传失败，错误码: ' . $file['error']);
      } else {
        // 获取文件扩展名
      $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      $this->log('文件扩展名: ' . $fileExt);
      
      // 获取文件内容
      $fileContent = file_get_contents($file['tmp_name']);
      
      // 如果是cfg格式文件，需要先解压
      if ($fileExt === 'cfg') {
        $this->log('检测到cfg格式文件，尝试解压');
        if (function_exists('gcompress_decompress')) {
          $uncompressedContent = gcompress_decompress($fileContent);
          if ($uncompressedContent !== false) {
            $fileContent = $uncompressedContent;
            $this->log('cfg文件解压成功，解压后大小: ' . strlen($fileContent) . ' 字节');
          } else {
            $err['general'] = 'cfg文件解压失败，请确保文件已正确压缩。';
            $this->log('cfg文件解压失败');
          }
        } else {
          $err['general'] = '系统不支持gcompress解压功能。';
          $this->log('系统不支持gcompress解压功能');
        }
      }
      
      if (empty($err)) {
        $this->log('文件处理成功，大小: ' . strlen($fileContent) . ' 字节');
        
        // 尝试解析文件内容（先尝试YAML，再尝试JSON）
        $configData = YAML::decode($fileContent);
        if ($configData === null) {
          $this->log('尝试以YAML解析失败，转为JSON解析');
          $configData = json_decode($fileContent, true);
        } else {
          $this->log('成功以YAML解析文件内容');
        }
        
        if ($configData === null) {
          $err['general'] = '无法解析配置文件，请确保文件格式为YAML或JSON。';
          $this->log('无法解析配置文件，JSON错误: ' . json_last_error_msg());
        } else {
          $this->log('成功解析配置文件，数据类型: ' . (is_array($configData) ? '数组' : '对象'));
          
          // 确定配置数据是单条还是多条
          $configsToProcess = array();
          
          // 检查是否是JSON数组格式（多条配置）
          if (is_array($configData) && isset($configData[0]) && is_array($configData[0])) {
            $configsToProcess = $configData;
            $this->log('检测到多条配置，数量: ' . count($configsToProcess));
          } elseif (is_array($configData) && isset($configData['id']) && isset($configData['key'])) {
            // 单条配置
            $configsToProcess[] = $configData;
            $this->log('检测到单条配置');
          } else {
            $err['general'] = '配置文件格式不正确，应为JSON数组或包含id和key字段的对象。';
            $this->log('配置文件格式不正确，缺少必要字段');
          }
          
          // 处理配置数据
            if (!empty($configsToProcess) && empty($err)) {
              $this->log('开始处理配置数据，总数: ' . count($configsToProcess));
              foreach ($configsToProcess as $index => $item) {
                //$this->log('处理第 ' . ($index + 1) . ' 条配置');
                // 检查单条配置格式
                if (!is_array($item) || !isset($item['id']) || !isset($item['key'])) {
                  $failCount++;
               //   $failMessages[] = '第' . ($index + 1) . '条配置缺少必要字段(id或key)';
                 // $this->log('第 ' . ($index + 1) . ' 条配置缺少必要字段(id或key): ' . print_r($item, true));
                  continue;
                }
                
                $this->log('第 ' . ($index + 1) . ' 条配置格式正确，id: ' . $item['id'] . ', key: ' . $item['key']);
                
                // 准备配置数据
                $data = array(
                  'id' => $item['id'],
                  'key' => $item['key'],
                  'value' =>$item['data'],
                  'config_type' => isset($item['type']) ? $item['type'] : 'mod',
                  'description' => isset($item['label']) ? $item['label'] : '',
                  'tenant_id' => 'default', // 根据实际需求设置租户ID
                  'created_at' => date('Y-m-d H:i:s'),
                  'updated_at' => date('Y-m-d H:i:s')
                );
                
                // 移除id和key，将剩余部分作为value
                $itemCopy = $item;
                unset($itemCopy['id']);
                unset($itemCopy['key']);
                unset($itemCopy['config_type']);
                unset($itemCopy['description']);
                
               // $data['value'] = json_encode($itemCopy, JSON_UNESCAPED_UNICODE);
                $this->log('第 ' . ($index + 1) . ' 条配置值: ' . $data['value']);
                
                // 检查是否已存在相同id或key的配置
                $existingConfigById = $this->m->getConfig($data['id']);
                $existingConfigByKey = $this->m->getConfigByKey($data['key']);
                
                if ($existingConfigById) {
                  $this->log('第 ' . ($index + 1) . ' 条配置id已存在: ' . $data['id'] . '，尝试更新');
                  // 更新现有配置
                  $result = $this->m->updateConfig($data['id'], $data);
                  if ($result) {
                    $successCount++;
                    $this->log('第 ' . ($index + 1) . ' 条配置更新成功');
                  } else {
                    $failCount++;
                    $failMessages[] = '第' . ($index + 1) . '条配置(id: ' . $data['id'] . ')更新失败';
                    $this->log('第 ' . ($index + 1) . ' 条配置更新失败');
                  }
              } elseif ($existingConfigByKey) {
                $failCount++;
                $failMessages[] = '第' . ($index + 1) . '条配置(key: ' . $data['key'] . ')的键名已存在';
              } else {
                // 创建新配置
                $result = $this->m->createConfig($data);
                if ($result) {
                  $successCount++;
                } else {
                  $failCount++;
                  $failMessages[] = '第' . ($index + 1) . '条配置(id: ' . $data['id'] . ')创建失败';
                }
              }
            }
            
            // 导入完成，显示结果
            $this->log('配置处理完成，成功: ' . $successCount . ' 条，失败: ' . $failCount . ' 条');
            if ($successCount > 0) {
              $message = '成功导入 ' . $successCount . ' 条配置';
              if ($failCount > 0) {
                $message .= '，失败 ' . $failCount . ' 条。失败信息：' . implode('; ', $failMessages);
              }
              $this->log('导入结果: ' . $message);
              redirect(tenant_url('config/'), $message);
            } else {
              $err['general'] = '所有配置导入失败：' . implode('; ', $failMessages);
              $this->log('导入失败: ' . $err['general']);
            }
          }
        }
      }
    }
    
  }

  $param['err'] = $err;
  $param['page_title'] = $param['meta_keywords'] = $param['meta_description'] = 'lpp配置导入';
  $this->display('v/config/import', $param);
}
  function convert() : void {
    $configs = $this->m->getPage(1,20,['config_type'=>'mod']);
    //print_r($configs);
    foreach($configs as $c){
      $d = json_decode($c['value'],true);
      print_r($d);
    } 
  }
}