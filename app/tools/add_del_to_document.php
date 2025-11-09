<?php
/**
 * 为document表添加del字段的迁移脚本
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * 执行迁移
 */
function run_migration() {
    try {
        // 获取所有租户的数据库文件
        $data_dir = __DIR__ . '/../../data/';
        
        if (!is_dir($data_dir)) {
            echo "数据目录不存在: $data_dir\n";
            return;
        }
        
        $tenant_dirs = glob($data_dir . '*', GLOB_ONLYDIR);
        
        foreach ($tenant_dirs as $tenant_dir) {
            $tenant_id = basename($tenant_dir);
            $db_file = $tenant_dir . '/db.sqlite';
            
            if (file_exists($db_file)) {
                echo "处理租户: $tenant_id\n";
                
                try {
                // 直接使用SQLite3
                $db = new SQLite3($db_file);
                
                // 检查del字段是否已存在
                $result = $db->query("PRAGMA table_info(tb_document)");
                $has_del_field = false;
                
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    if ($row['name'] === 'del') {
                        $has_del_field = true;
                        break;
                    }
                }
                
                if (!$has_del_field) {
                    // 添加del字段
                    $db->exec("ALTER TABLE tb_document ADD COLUMN del INTEGER DEFAULT 0");
                    echo "  ✅ 已添加del字段\n";
                } else {
                    echo "  ⏩ del字段已存在，跳过\n";
                }
                
                $db->close();
                
            } catch (Exception $e) {
                echo "  ❌ 错误: " . $e->getMessage() . "\n";
            }
            } else {
                echo "  ⏩ 数据库文件不存在: $db_file\n";
            }
        }
        
        echo "\n迁移完成！\n";
        
    } catch (Exception $e) {
        echo "错误: " . $e->getMessage() . "\n";
    }
}

// 执行迁移
run_migration();