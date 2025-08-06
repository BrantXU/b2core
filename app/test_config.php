<?php
// 测试配置文件生成
// 定义APP常量
define('APP', __DIR__ . '/');

// 引入必要的文件
require_once APP . 'config.php';
require_once APP . 'lib/db.php';
require_once APP . 'lib/m.php';
require_once APP . 'lib/b2core.php';

// 模拟POST数据
$data = array(
  'key' => 'test_config',
  'value' => '{
    "name": "测试配置",
    "description": "这是一个测试配置"
  }',
  'config_type' => 'test',
  'description' => '测试配置',
  'tenant_id' => 'default'
);

// 输出调试信息
echo '开始创建配置...\n';

// 创建配置模型实例
$config_m = load('m/config_m');

// 创建配置
$result = $config_m->createConfig($data);

if ($result) {
  echo '配置创建成功，ID: ' . $data['id'] . '\n';
  // 手动调用updateConfigFile方法
  echo '手动更新配置文件...\n';
  // 使用反射调用私有方法
  $reflection = new ReflectionClass('config_m');
  $method = $reflection->getMethod('updateConfigFile');
  $method->setAccessible(true);
  $method->invoke($config_m, 'default');
  echo '配置文件更新完成\n';
} else {
  echo '配置创建失败\n';
  // 检查是否存在相同key的配置
  $existingConfig = $config_m->getConfigByKey('test_config');
  if ($existingConfig) {
    echo '存在相同key的配置，ID: ' . $existingConfig['id'] . '\n';
    // 手动调用updateConfigFile方法
    echo '手动更新配置文件...\n';
    // 使用反射调用私有方法
    $reflection = new ReflectionClass('config_m');
    $method = $reflection->getMethod('updateConfigFile');
    $method->setAccessible(true);
    $method->invoke($config_m, 'default');
    echo '配置文件更新完成\n';
  }
}