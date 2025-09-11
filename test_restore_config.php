<?php
/**
 * 测试配置历史版本恢复功能
 */

// 定义必要的常量
define('APP', __DIR__ . '/app/');

// 包含必要的文件
require_once __DIR__ . '/app/lib/db.php';
require_once __DIR__ . '/app/lib/m.php';
require_once __DIR__ . '/app/m/config_m.php';
require_once __DIR__ . '/app/lib/utility.php';

// 初始化数据库连接
global $tdb;
$dbConfig = [
    'driver' => 'sqlite',
    'sqlite' => [
        'database' => APP . 'db.sqlite'
    ]
];
$tdb = new db($dbConfig);

// 创建配置模型实例
$configModel = new config_m();

// 创建新的测试配置（直接插入数据库避免YAML依赖）
echo "创建测试配置...\n";
$testKey = 'test_restore_key_' . time();
$configId = randstr();

// 直接插入数据库记录
$sql = "INSERT INTO tb_config (id, key, value, description, config_type, tenant_id, created_at, updated_at) 
        VALUES ('$configId', '$testKey', 'initial_value', '测试恢复功能的配置', 'test', 'default', datetime('now'), datetime('now'))";

$result = $tdb->query($sql);
if (!$result) {
    echo "创建测试配置失败\n";
    exit(1);
}
echo "测试配置创建成功，ID: $configId (Key: $testKey)\n";

// 2. 手动创建历史版本文件（避免触发YAML错误）
echo "手动创建历史版本...\n";
$histId = time();
$histDir = APP . '../data/default/conf_hist/' . $configId . '/';
if (!is_dir($histDir)) {
    mkdir($histDir, 0755, true);
}

$histData = [
    'id' => $configId,
    'key' => $testKey,
    'value' => 'restore_test_value',
    'config_type' => 'test',
    'description' => '测试恢复的历史版本',
    'tenant_id' => 'default',
    'action' => 'manual_test',
    'user' => 'test_user',
    'hist_timestamp' => $histId
];

$histFilePath = $histDir . $histId . '.json';
if (file_put_contents($histFilePath, json_encode($histData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false) {
    echo "创建历史版本文件失败\n";
    exit(1);
}
echo "历史版本创建成功，ID: $histId\n";

// 4. 查看历史版本详情
echo "\n查看历史版本详情...\n";
$histData = $configModel->vhist($histId, $configId);
if (empty($histData)) {
    echo "获取历史版本详情失败\n";
    exit(1);
}

echo "历史版本详情:\n";
echo "- 操作时间: " . ($histData['time'] ?? '未知') . "\n";
echo "- 操作类型: " . ($histData['action'] ?? '未知') . "\n";
echo "- 操作人: " . ($histData['user'] ?? '未知') . "\n";

// 5. 恢复历史版本
echo "\n恢复历史版本到当前配置...\n";
$result = $configModel->restoreConfig($histId, $configId);
if ($result) {
    echo "恢复成功！\n";
    
    // 验证恢复后的配置
    $restoredConfig = $configModel->getConfig($configId);
    echo "恢复后的配置值: " . ($restoredConfig['value'] ?? '未知') . "\n";
    echo "恢复后的描述: " . ($restoredConfig['description'] ?? '未知') . "\n";
    
} else {
    echo "恢复失败\n";
    exit(1);
}

// 6. 清理测试数据
echo "\n清理测试数据...\n";
$deleteResult = $configModel->deleteConfig($configId);
if ($deleteResult) {
    echo "测试配置已删除\n";
} else {
    echo "删除测试配置失败\n";
}

echo "\n测试完成！\n";
?>