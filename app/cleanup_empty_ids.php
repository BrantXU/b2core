<?php
require_once 'lib/db.php';

// 数据库配置
$db_config = array(
    'driver' => 'sqlite',
    'sqlite' => array(
        'database' => __DIR__ . '/../app/db.sqlite',
    )
);

try {
    // 连接数据库
    $db = new db($db_config);
    
    // 要检查的表列表
    $tables = ['tb_user', 'tb_config', 'tb_tenant', 'tb_entity'];
    
    foreach ($tables as $table) {
        echo "清理表 {$table} 中没有ID的数据...\n";
        
        // 删除ID为NULL或空字符串的记录
        $query = "DELETE FROM {$table} WHERE id IS NULL OR id = ''";
        $result = $db->query($query);
        
        echo "完成清理表 {$table}\n\n";
    }
    
    echo "所有表的清理工作已完成。\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
?>