<?php
require_once APP . 'lib/yaml.php';

class config_m extends m {
  public $table;
  public $fields;
  
  public function __construct() {
    parent::__construct('tb_config');
   $this->fields = array('id', 'key', 'value', 'config_type', 'description', 'tenant_id', 'created_at', 'updated_at');
  }

  /**
   * 获取配置列表
   * @param int $page 页码
   * @param int $limit 每页记录数
   * @return array
   */

  public function configlist($page = 1, $limit = 20) {
    return $this->getPage($page, $limit);
  }

  /**
   * 获取配置总记录数
   * @return int
   */
  public function getTotal() {
    $sql = "SELECT COUNT(*) as total FROM {$this->table}";
    
    try {
      $result = $this->db->query($sql);
      return isset($result[0]['total']) ? (int)$result[0]['total'] : 0;
    } catch (Exception $e) {
      $this->log('获取配置总数失败: ' . $e->getMessage());
      return 0;
    }
  }

  /**
   * 获取所有配置数据
   * @return array
   */
  public function getAll() {
    $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
    
    try {
      return $this->db->query($sql);
    } catch (Exception $e) {
      $this->log('获取所有配置失败: ' . $e->getMessage());
      return [];
    }
  }

  /**
   * 根据ID获取配置信息
   * @param string $id 配置ID
   * @return array|null
   */
  
  public function getConfig($id) {
    return $this->getOne($id);
  }

  /**
   * 根据键名获取配置信息
   * @param string $key 配置键名
   * @return array|null
   */

  public function getConfigByKey($key) {
    $sql = "SELECT * FROM {$this->table} WHERE key = '" . $this->db->escape($key) . "'";
    $result = $this->db->query($sql);
    return isset($result[0]) ? $result[0] : null;
  }

  /**
   * 创建配置
   * @param array $data 配置数据
   * @return bool
   */
  public function createConfig($data) {
    // 检查是否已存在相同key的配置
    if (isset($data['key']) && $this->getConfigByKey($data['key'])) {
      // 如果已存在，可以选择更新现有配置或返回false
      // 这里我们选择返回false表示创建失败
      return false;
    }
    // 确保tenant_id始终有默认值
    if (!isset($data['tenant_id'])) {
      $data['tenant_id'] = 'default';
    }
    
    // 调用父类的add方法插入数据
    $result = $this->add($data);
    
    // 如果插入成功，更新配置文件
    if ($result) {
      $this->updateConfigFile($data['tenant_id']);
    }
    
    return $result;
  }

  /**
   * 更新配置
   * @param string $id 配置ID
   * @param array $data 配置数据
   * @return bool
   */
  public function updateConfig($id, $data) {
    // 获取现有配置以保留tenant_id（如果未提供）
    if (!isset($data['tenant_id'])) {
      $existingConfig = $this->getConfig($id);
      if ($existingConfig && isset($existingConfig['tenant_id'])) {
        $data['tenant_id'] = $existingConfig['tenant_id'];
      } else {
        $data['tenant_id'] = 'default';
      }
    }
    
    // 调用父类的update方法更新数据
    $result = $this->update($id, $data);
    // 如果更新成功，更新配置文件
    if ($result) {
      $this->updateConfigFile($data['tenant_id']);
    }
    
    return $result;
  }

  /**
   * 删除配置
   * @param string $id 配置ID
   * @return bool
   */
  public function deleteConfig($id) {
    // 获取配置信息用于更新配置文件
    $config = $this->getConfig($id);
    
    $result = $this->del($id);
    
    // 添加调试日志
    if ($result) {
      error_log('成功删除配置 ID: ' . $id);
    } else {
      error_log('删除配置失败 ID: ' . $id);
    }
    
    // 如果删除成功，更新配置文件
    if ($result && $config && isset($config['tenant_id'])) {
      $this->updateConfigFile($config['tenant_id']);
    }
    return $result;
  }

  /**
   * 批量删除配置
   * @param array $ids 配置ID数组
   * @return array 包含成功和失败的ID数组
   */
  public function batchDeleteConfig($ids) {
    $successIds = array();
    $failIds = array();
    $tenantIds = array();

    foreach ($ids as $id) {
      // 获取配置信息
      $config = $this->getConfig($id);
      
      if (!$config) {
        $failIds[] = $id;
        continue;
      }
      
      // 记录租户ID，用于后续更新配置文件
      $tenantIds[$config['tenant_id']] = true;
      
      // 删除配置
      $result = $this->del($id);
      
     // echo $id;
      // 添加调试日志
      if ($result) {
        $successIds[] = $id;
        error_log('成功删除配置 ID: ' . $id);
      } else {
        $failIds[] = $id;
        error_log('删除配置失败 ID: ' . $id);
      }
    }
    
    // 更新受影响的租户的配置文件
    foreach (array_keys($tenantIds) as $tenantId) {
      $this->updateConfigFile($tenantId);
    }
    
    return array(
      'success' => $successIds,
      'fail' => $failIds
    );
  }
  
  /**
   * 更新租户的配置文件
   * @param string $tenantId 租户ID
   */
  private function updateConfigFile($tenantId) {
    // 确保租户目录存在
    $tenantDir = APP . '../data/' . $tenantId;
    error_log('Tenant directory: ' . $tenantDir);
    if (!is_dir($tenantDir)) {
      mkdir($tenantDir, 0777, true);
      error_log('Created tenant directory: ' . $tenantDir);
    }

    // 确保配置目录存在
    $confDir = $tenantDir . '/conf';
    error_log('Config directory: ' . $confDir);
    if (!is_dir($confDir)) {
      mkdir($confDir, 0777, true);
      error_log('Created config directory: ' . $confDir);
    }

    // 获取该租户的所有配置
    $sql = "SELECT * FROM {$this->table} WHERE tenant_id = '" . $this->db->escape($tenantId) . "'";
    error_log('Executing SQL: ' . $sql);
    $configs = $this->db->query($sql);
    error_log('Found ' . count($configs) . ' configs for tenant: ' . $tenantId);

    // 为每个配置创建单独的JSON和YAML文件
    foreach ($configs as $config) {
      $configId = $config['id'];
      
      // 确保配置ID是有效的
      if (empty($configId) || !is_string($configId) && !is_numeric($configId)) {
        error_log('Warning: Invalid config ID: ' . var_export($configId, true));
        continue;
      }
      
      // 根据项目规则，文件内容应该是数据表的data字段的json字符串中的数据
      // 由于配置表中没有data字段，我们使用value字段代替
      $configData = json_decode($config['value'], true);
      if (!is_array($configData)) {
        $configData = array('value' => $config['value']);
        error_log('Config value is not valid JSON for ID: ' . $configId);
      } else {
        error_log('Successfully parsed config value for ID: ' . $configId);
      }
      // 添加基本信息
      $configData['id'] = $config['id'];
      $configData['key'] = $config['key'];
      
      // 记录正在处理的配置ID
      error_log('Processing config ID: ' . $configId);

      // 写入JSON文件 - 使用绝对路径
      $jsonFile = realpath($confDir) . '/' . $configId . '.json';
      error_log('Writing JSON file: ' . $jsonFile);
      if (file_put_contents($jsonFile, json_encode($configData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false) {
        error_log('Created JSON file: ' . $jsonFile);
      } else {
        error_log('Failed to create JSON file: ' . $jsonFile);
      }

      // 写入YAML文件
      /* Yaml 是为了调试方便，正式环境不建议使用 */
      $yamlFile = realpath($confDir) . '/' . $configId . '.yaml';
      error_log('Writing YAML file: ' . $yamlFile);
      
      // 使用已加载的YAML类进行处理
      if (class_exists('YAML')) {
        try {
          $yamlContent = YAML::encode($configData);
          if (file_put_contents($yamlFile, $yamlContent) !== false) {
            error_log('Created YAML file: ' . $yamlFile);
          } else {
            error_log('Failed to create YAML file: ' . $yamlFile);
          }
        } catch (Exception $e) {
          error_log('Error encoding YAML: ' . $e->getMessage());
          // 失败时尝试使用JSON格式
          if (file_put_contents($yamlFile, json_encode($configData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false) {
            error_log('Created YAML file (JSON format): ' . $yamlFile);
          } else {
            error_log('Failed to create YAML file (JSON format): ' . $yamlFile);
          }
        }
      } else {
        // 如果YAML类不可用，使用JSON格式
        error_log('Warning: YAML class not available. Using JSON format for YAML file: ' . $yamlFile);
        if (file_put_contents($yamlFile, json_encode($configData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false) {
          error_log('Created YAML file (JSON format): ' . $yamlFile);
        } else {
          error_log('Failed to create YAML file (JSON format): ' . $yamlFile);
        }
      }
    }

    // 生成配置清单文件，包含两类数据
    $confJsonFile = $tenantDir . '/conf.json';  // 按照项目规则，文件路径应为tenantDir/conf.json
    error_log('Writing config manifest file: ' . $confJsonFile);

    // 第一类数据：Id和key的对应清单
    $idKeyMap = array();
    // 第二类数据：按照type汇总后的Id和key的对应清单
    $typeMap = array();

    foreach ($configs as $config) {
      $configId = $config['id'];
      $configKey = $config['key'];
      $configType = isset($config['config_type']) ? $config['config_type'] : 'default';

      // 确保配置ID是有效的
      if (!empty($configId) && (is_string($configId) || is_numeric($configId))) {
        // 更新Id和key的对应清单
        $idKeyMap[$configId] = $configKey;

        // 更新按照type汇总的清单
        if (!isset($typeMap[$configType])) {
          $typeMap[$configType] = array();
        }
        $typeMap[$configType][$configId] = $configKey;
      }
    }

    // 构建完整的配置清单数据
    $confJsonData = array(
      'id_key_map' => $idKeyMap,
      'type_map' => $typeMap
    );

    // 写入配置清单文件
    if (file_put_contents($confJsonFile, json_encode($confJsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false) {
      error_log('Created config manifest file: ' . $confJsonFile);
    } else {
      error_log('Failed to create config manifest file: ' . $confJsonFile);
    }
  }
}