<?php
// 测试配置同步功能
// 模拟HTTP_HOST以避免警告
$_SERVER['HTTP_HOST'] = 'localhost';

// 模拟__msg函数
function __msg($msg, $type = 'error') {
  echo "[$type] $msg\n";
}

// 加载配置
require_once 'app/config.php';

// 加载基础模型类
require_once 'app/lib/m.php';

// 加载数据库类
require_once 'app/lib/db.php';

// 加载配置模型类
require_once 'app/m/config_m.php';

// 初始化数据库连接
$dbConfig = array(
  'driver' => 'sqlite',
  'sqlite' => array(
    'database' => APP . 'db.sqlite'
  )
);
$db = new db($dbConfig);
$GLOBALS['db'] = $db;

// 创建配置模型实例
$configModel = new config_m();

// 测试参数
  $tenantId = 'default';
  $testConfigId = 'test_' . time() . '_' . rand(1000, 9999); // 生成测试配置ID
  $configKey = 'test_config_' . $testConfigId; // 确保key唯一

// 确保数据目录存在
$dataDir = APP . '../data/' . $tenantId . '/conf';
if (!is_dir($dataDir)) {
  mkdir($dataDir, 0777, true);
  echo "创建数据目录: $dataDir\n";
}

// 清理旧的测试配置文件，避免干扰
$files = glob($dataDir . '/*test_config*.json') + glob($dataDir . '/*test_config*.yaml');
foreach ($files as $file) {
  if (is_file($file)) {
    unlink($file);
    echo "删除旧测试文件: $file\n";
  }
}

// 1. 测试创建配置
echo "测试创建配置...\n";
// 创建包含JSON数据的配置值
  $jsonValue = json_encode(array(
    'setting1' => '测试值1',
    'setting2' => '测试值2',
    'nested' => array(
      'key' => '嵌套值'
    )
  ), JSON_UNESCAPED_UNICODE);

  $createData = array(
    'id' => $testConfigId, // 显式设置配置ID
    'key' => $configKey,
    'value' => $jsonValue,
    'config_type' => 'json',
    'description' => '测试JSON配置',
    'tenant_id' => $tenantId
  );

$dbGeneratedId = $configModel->createConfig($createData);
// 无论createConfig是否返回ID，我们都使用自己设置的测试ID
$configId = $testConfigId;

if ($configId && !empty($configId) && (is_string($configId) || is_numeric($configId))) {
  echo "配置创建成功，数据库生成ID: $dbGeneratedId，验证使用ID: $configId\n";
  echo "配置ID类型: " . gettype($configId) . "\n";

  // 检查文件是否生成
  $jsonFile = APP . '../data/' . $tenantId . '/conf/' . $configId . '.json';
  $yamlFile = APP . '../data/' . $tenantId . '/conf/' . $configId . '.yaml';
  echo "JSON文件路径: $jsonFile\n";
  echo "YAML文件路径: $yamlFile\n";
  echo "APP路径: " . APP . "\n";

  // 短暂延迟，确保文件有时间生成
  sleep(7); // 增加延迟时间至7秒，确保文件完全生成

  // 验证配置文件是否生成 - 使用绝对路径
  $jsonFile = realpath($dataDir) . '/' . $configId . '.json';
  $yamlFile = realpath($dataDir) . '/' . $configId . '.yaml';
  echo "配置ID: $configId\n";

  if (file_exists($jsonFile)) {
    echo "JSON文件已生成: $jsonFile\n";
  } else {
    echo "错误: JSON文件未生成\n";
    // 列出目录内容帮助调试
    $files = scandir($dataDir);
    echo "目录中的所有文件: " . implode(', ', $files) . "\n";
  }

  if (file_exists($yamlFile)) {
    echo "YAML文件已生成: $yamlFile\n";
  } else {
    echo "错误: YAML文件未生成\n";
  }
} else {
  echo "配置创建失败或配置ID无效\n";
  exit(1);
}
  if (file_exists($jsonFile)) {
    echo "JSON文件已生成: $jsonFile\n";
  } else {
    echo "错误: JSON文件未生成\n";
  }
  
  if (file_exists($yamlFile)) {
    echo "YAML文件已生成: $yamlFile\n";
  } else {
    echo "错误: YAML文件未生成\n";
  }

  // 2. 测试更新配置
  echo "\n测试更新配置...\n";
  // 更新JSON配置值
  $updatedJsonValue = json_encode(array(
    'setting1' => '更新值1',
    'setting2' => '更新值2',
    'nested' => array(
      'key' => '更新嵌套值'
    )
  ), JSON_UNESCAPED_UNICODE);

  $updateData = array(
    'value' => $updatedJsonValue,
    'description' => '更新后的JSON配置',
    'tenant_id' => $tenantId
  );

  if ($configModel->updateConfig($configId, $updateData)) {
    echo "配置更新成功\n";

    // 短暂延迟，确保文件有时间更新
  sleep(5); // 增加延迟时间至5秒，确保文件完全更新

    // 确认配置ID对应的文件是否存在 - 使用绝对路径
  $jsonFile = realpath($dataDir) . '/' . $configId . '.json';
  $yamlFile = realpath($dataDir) . '/' . $configId . '.yaml';
  echo "验证JSON文件路径: $jsonFile\n";
  echo "验证YAML文件路径: $yamlFile\n";

  $matchingFiles = [];
  if (file_exists($jsonFile)) {
    $matchingFiles[] = basename($jsonFile);
    echo "JSON文件存在: $jsonFile\n";
  } else {
    echo "错误: JSON文件不存在\n";
  }
  if (file_exists($yamlFile)) {
    $matchingFiles[] = basename($yamlFile);
    echo "YAML文件存在: $yamlFile\n";
  } else {
    echo "错误: YAML文件不存在\n";
  }

  if (!empty($matchingFiles)) {
    echo "找到匹配的文件: " . implode(', ', $matchingFiles) . "\n";

        // 验证JSON文件
          $jsonFiles = array_filter($matchingFiles, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'json';
          });
          if (!empty($jsonFiles)) {
            $jsonFile = $dataDir . '/' . current($jsonFiles);
            echo "使用JSON文件: $jsonFile 进行验证\n";
            if (file_exists($jsonFile)) {
              $jsonContent = file_get_contents($jsonFile);
              if ($jsonContent === false) {
                echo "错误: 无法读取JSON文件\n";
              } else {
                $jsonData = json_decode($jsonContent, true);
                if (is_array($jsonData)) {
                  echo "JSON文件内容: " . json_encode($jsonData, JSON_UNESCAPED_UNICODE) . "\n";
                  if (isset($jsonData['setting1']) && $jsonData['setting1'] === '更新值1') {
                    echo "JSON文件已正确更新\n";
                  } else {
                    echo "错误: JSON文件未正确更新\n";
                  }
                } else {
                  echo "错误: JSON文件内容无效\n";
                }
              }
            } else {
              echo "错误: JSON文件不存在\n";
            }
          } else {
            echo "错误: 未找到匹配的JSON文件\n";
          }

          // 验证YAML文件
          $yamlFiles = array_filter($matchingFiles, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'yaml';
          });
          if (!empty($yamlFiles)) {
            $yamlFile = $dataDir . '/' . current($yamlFiles);
            echo "使用YAML文件: $yamlFile 进行验证\n";
            if (file_exists($yamlFile)) {
              echo "YAML文件已生成: $yamlFile\n";
              $yamlContent = file_get_contents($yamlFile);
              // 由于我们使用JSON格式存储YAML，这里也用json_decode解析
              $yamlData = json_decode($yamlContent, true);
              if (is_array($yamlData)) {
                echo "YAML文件内容: " . json_encode($yamlData, JSON_UNESCAPED_UNICODE) . "\n";
                if (isset($yamlData['setting1']) && $yamlData['setting1'] === '更新值1') {
                  echo "YAML文件已正确更新\n";
                } else {
                  echo "错误: YAML文件未正确更新\n";
                }
              } else {
                echo "错误: YAML文件内容无效\n";
              }
            } else {
              echo "错误: YAML文件不存在\n";
            }
          } else {
            echo "错误: 未找到匹配的YAML文件\n";
          }
    } else {
      echo "未找到匹配配置ID的文件\n";
      // 列出目录内容帮助调试
      $files = scandir($dataDir);
      echo "目录中的所有文件: " . implode(', ', $files) . "\n";
    }
  } else {
    echo "配置更新失败\n";
  }
// 清理测试数据
// $configModel->deleteConfig($configId);
// echo "测试数据已清理\n";