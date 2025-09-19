<?php
require_once APP . 'lib/yaml.php';
class config_m extends m {
  public $table;
  public $fields;
  
  public function __construct() {    
    global $tdb;
    $this->db = $tdb; 
    $this->table = 'tb_config';
    $this->conditions = [];
    $this->key = 'id';    
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
    $sql = "SELECT * FROM {$this->table} WHERE (del IS NULL OR del = 0) ORDER BY created_at DESC";
    
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
    
    // 如果插入成功，更新配置文件并保存历史版本
    if ($result) {
      $this->updateConfigFile($data['tenant_id']);
      $this->saveConfigHistory($data, 'create');
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
    $data['id'] = $id;
    $result = $this->update($id, $data);
    // 如果更新成功，更新配置文件并保存历史版本
    if ($result) {
      $this->updateConfigFile($data['tenant_id']);
      $this->saveConfigHistory($data, 'update');
    }
    
    return $result;
  }

  /**
   * 删除配置
   * @param string|array $ids 配置ID（单个ID或逗号分隔的多个ID）
   * @return bool
   */
  public function deleteConfig($ids) {
    // 处理多个ID的情况（字符串逗号分隔或数组）
    if (is_array($ids) || (is_string($ids) && strpos($ids, ',') !== false)) {
      // 如果是字符串，转换为数组
      if (is_string($ids)) {
        $idArray = explode(',', $ids);
      } else {
        $idArray = $ids;
      }
      
      $result = true;
      $tenantIds = [];
      
      foreach ($idArray as $id) {
        $id = trim($id);
        if (empty($id)) continue;
        
        // 获取配置信息用于更新配置文件
        $config = $this->getConfig($id);
        
        $deleteResult = $this->del($id);
        
        // 添加调试日志
        if ($deleteResult) {
          error_log('成功删除配置 ID: ' . $id);
          // 记录删除操作日志
          $this->saveConfigLog($config, 'delete');
          
          // 记录租户ID
          if ($config && isset($config['tenant_id'])) {
            $tenantIds[$config['tenant_id']] = true;
          }
        } else {
          error_log('删除配置失败 ID: ' . $id);
          $result = false;
        }
      }
      
      // 更新受影响的租户的配置文件
      foreach (array_keys($tenantIds) as $tenantId) {
        $this->updateConfigFile($tenantId);
      }
      
      return $result;
    } else {
      // 单个ID的情况
      $id = $ids;
      // 获取配置信息用于更新配置文件
      $config = $this->getConfig($id);
      
      $result = $this->del($id);
      
      // 添加调试日志
      if ($result) {
        error_log('成功删除配置 ID: ' . $id);
        // 记录删除操作日志
        $this->saveConfigLog($config, 'delete');
      } else {
        error_log('删除配置失败 ID: ' . $id);
      }
      
      // 如果删除成功，更新配置文件
      if ($result && $config && isset($config['tenant_id'])) {
        $this->updateConfigFile($config['tenant_id']);
      }
      return $result;
    }
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
      // 跳过YAML文件生成，避免YAML依赖问题
      error_log('Skipping YAML file generation to avoid dependency issues');
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

  /**
   * 保存配置历史版本
   * @param array $configData 配置数据
   * @param string $action 操作类型
   * @param string $user 操作用户
   * @return bool 是否保存成功
   */
  private function saveConfigHistory($configData, $action = 'update', $user = '系统') {
    // 确定租户ID和配置ID
    if (!isset($configData['tenant_id']) || !isset($configData['id'])) {
      return false;
    }
    $tenantId = $configData['tenant_id'];
    $configId = $configData['id'];
    $histId = time();
    
    // 创建历史版本目录
    $histDir = APP . '../data/' . $tenantId . '/conf_hist/' . $configId . '/';
    if (!is_dir($histDir)) {
      mkdir($histDir, 0755, true);
    }
    
    $histFilePath = $histDir . $histId . '.json';
    
    // 添加操作信息到配置数据
    $historyData = $configData;
    $historyData['action'] = $action;
    $historyData['user'] = $user;
    $historyData['hist_timestamp'] = $histId;
    
    // 保存配置数据到历史版本文件
    return file_put_contents($histFilePath, json_encode($historyData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false;
  }

  /**
   * 保存配置操作日志
   * @param array $configData 配置数据
   * @param string $action 操作类型
   * @return bool 是否保存成功
   */
  private function saveConfigLog($configData, $action) {
    // 确定租户ID
    if (!isset($configData['tenant_id']) || !isset($configData['id']) || !isset($configData['key'])) {
      return false;
    }
    $tenantId = $configData['tenant_id'];
    
    // 创建日志目录
    $logDir = APP . '../data/' . $tenantId . '/log/' . date('Y-m-d') . '/';
    if (!is_dir($logDir)) {
      mkdir($logDir, 0755, true);
    }
    
    $logFilePath = $logDir . time() . '.json';
    
    // 构建日志数据
    $logData = [
      'timestamp' => time(),
      'action' => $action,
      'config_id' => $configData['id'],
      'config_key' => $configData['key'],
      'data' => $configData
    ];
    
    // 保存日志数据
    return file_put_contents($logFilePath, json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false;
  }

  /**
   * 获取配置历史版本列表
   * @param string $id 配置ID
   * @return array 历史版本列表
   */
  public function hist($id) {
    $config = $this->getConfig($id);
    if (!$config) {
      return [];
    }
    
    $tenantId = $config['tenant_id'];
    $dir = APP . '../data/' . $tenantId . '/conf_hist/' . $id . '/';
    
   // 检查目录是否存在
    if (!is_dir($dir)) {
      return [];
    }

    // 读取目录内容并过滤文件
    $files = scandir($dir);
    $files = array_diff($files, ['.', '..']); // 去掉当前目录和上级目录

    $histList = [];

    foreach ($files as $file) {
      $filePath = $dir . '/' . $file;
      if (is_file($filePath)) {
        // 读取历史文件内容
        $histData = json_decode(file_get_contents($filePath), true);
        if ($histData) {
          // 从文件名中提取历史版本ID（去掉.json扩展名）
          $histId = pathinfo($file, PATHINFO_FILENAME);
          
          $histList[] = [
            'id' => $histId,
            'time' => date('Y-m-d H:i:s', filemtime($filePath)),
            'action' => $histData['action'] ?? 'update',
            'user' => $histData['user'] ?? '系统'
          ];
        }
      }
    }

    // 按照时间倒序排序
    usort($histList, function($a, $b) {
      return strtotime($b['time']) - strtotime($a['time']);
    });

    return $histList;
  }

  /**
   * 查看特定历史版本内容
   * @param string $id 历史版本ID
   * @param string $config_id 配置ID
   * @return array 历史版本数据
   */
  public function vhist($id, $config_id) {
    $config = $this->getConfig($config_id);
    if (!$config) {
      return [];
    }
    
    $tenantId = $config['tenant_id'];
    $histFile = APP . '../data/' . $tenantId . '/conf_hist/' . $config_id . '/' . $id . '.json';
    
    if (file_exists($histFile)) {
      $histData = json_decode(file_get_contents($histFile), true);
      if ($histData) {
        // 确保包含所有必要的字段
        $histData['time'] = $histData['hist_timestamp'] ?? filemtime($histFile);
        $histData['action'] = $histData['action'] ?? 'update';
        $histData['user'] = $histData['user'] ?? '系统';
        return $histData;
      }
    }
    
    return [];
  }

  /**
   * 恢复历史版本到当前配置
   * @param string $hist_id 历史版本ID
   * @param string $config_id 配置ID
   * @return bool 是否恢复成功
   */
  public function restoreConfig($hist_id, $config_id) {
    // 获取历史版本数据
    $histData = $this->vhist($hist_id, $config_id);
    if (empty($histData)) {
      error_log('恢复失败：找不到历史版本数据，hist_id: ' . $hist_id . ', config_id: ' . $config_id);
      return false;
    }

    // 移除历史版本特有的字段
    $restoreData = $histData;
    unset($restoreData['action'], $restoreData['user'], $restoreData['hist_timestamp'], $restoreData['time']);

    // 更新当前配置
    $result = $this->updateConfig($config_id, $restoreData);
    
    if ($result) {
      error_log('成功恢复配置版本，hist_id: ' . $hist_id . ', config_id: ' . $config_id);
      // 记录恢复操作日志
      $this->saveConfigLog($restoreData, 'restore');
      return true;
    } else {
      error_log('恢复配置版本失败，hist_id: ' . $hist_id . ', config_id: ' . $config_id);
      return false;
    }
  }
}