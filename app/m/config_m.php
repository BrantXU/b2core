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
    
    // 如果插入成功，生成JSON文件
    if ($result && isset($data['id']) && isset($data['tenant_id'])) {
      $this->generateConfigFile($data);
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
    
    // 如果更新成功，重新生成JSON文件
    if ($result) {
      // 获取完整的配置数据
      $configData = $this->getConfig($id);
      if ($configData) {
        $this->generateConfigFile($configData);
      }
    }
    
    return $result;
  }

  /**
   * 删除配置
   * @param string $id 配置ID
   * @return bool
   */
  public function deleteConfig($id) {
    // 获取配置信息用于删除对应的JSON文件
    $config = $this->getConfig($id);
    
    $result = $this->del($id);
    
    // 如果删除成功，也删除对应的JSON文件
    if ($result && $config) {
      $this->deleteConfigFile($config);
    }
    
    return $result;
  }
  
  /**
   * 生成配置的JSON文件
   * @param array $config 配置数据
   */
  private function generateConfigFile($config) {
    // 确保租户和配置类型目录存在
    $tenantDir = APP . '../data/' . $config['tenant_id'];
    $configTypeDir = $tenantDir . '/' . $config['config_type'];
    if (!is_dir($configTypeDir)) {
      mkdir($configTypeDir, 0777, true);
    }
    
    // 生成JSON文件路径
    $jsonFile = $configTypeDir . '/' . $config['id'] . '.json';
    
    // 准备要写入的数据
    $dataToWrite = $config['value'];
    // 写入JSON文件
    file_put_contents($jsonFile, $dataToWrite);

    // 解析JSON数据为数组
    $dataArray = json_decode($dataToWrite, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        // 生成YAML文件路径
        $yamlFile = $configTypeDir . '/' . $config['id'] . '.yaml';
        // 将数组编码为YAML
        $yamlContent = YAML::encode($dataArray);
        // 写入YAML文件
        file_put_contents($yamlFile, $yamlContent);
    }
}
  
  /**
   * 删除配置的JSON文件
   * @param array $config 配置数据
   */
  private function deleteConfigFile($config) {
    $basePath = APP . '../data/' . $config['tenant_id'] . '/' . $config['config_type'] . '/' . $config['id'];
    $jsonFile = $basePath . '.json';
    $yamlFile = $basePath . '.yaml';
    
    if (file_exists($jsonFile)) {
      unlink($jsonFile);
    }
    if (file_exists($yamlFile)) {
      unlink($yamlFile);
    }
  }
}