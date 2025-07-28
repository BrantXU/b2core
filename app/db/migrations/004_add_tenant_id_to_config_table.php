<?php
/**
 * 数据库迁移脚本 - 为配置表添加租户ID字段
 */
class Migration_004_Add_Tenant_Id_To_Config_Table {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function up() {
        // SQLite数据库更新
        if ($this->db->driver == 'sqlite') {
            // 检查tenant_id列是否已存在
            $columns = $this->db->query("PRAGMA table_info(tb_config)");
            $hasTenantId = false;
            foreach ($columns as $column) {
                if ($column['name'] == 'tenant_id') {
                    $hasTenantId = true;
                    break;
                }
            }
            
            // 如果tenant_id列不存在，则添加它
            if (!$hasTenantId) {
                // 由于SQLite不支持直接添加列到现有表，我们需要重新创建表
                $this->db->query("ALTER TABLE tb_config RENAME TO tb_config_old");
                
                $sql = "CREATE TABLE tb_config (
                          id TEXT PRIMARY KEY,
                          key TEXT NOT NULL UNIQUE,
                          value TEXT NOT NULL,
                          description TEXT,
                          tenant_id TEXT DEFAULT 'default',
                          created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                          updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                        )";
                $this->db->query($sql);
                
                // 复制旧数据到新表，为现有记录设置默认tenant_id
                $this->db->query("INSERT INTO tb_config (id, key, value, description, tenant_id, created_at, updated_at) SELECT id, key, value, description, 'default', created_at, updated_at FROM tb_config_old");
                
                // 删除旧表
                $this->db->query("DROP TABLE tb_config_old");
            }
        }
        
        // MySQL数据库更新
        if ($this->db->driver == 'mysql') {
            // 检查tenant_id列是否已存在
            $columns = $this->db->query("SHOW COLUMNS FROM tb_config LIKE 'tenant_id'");
            if (empty($columns)) {
                // 如果tenant_id列不存在，则添加它
                $sql = "ALTER TABLE tb_config ADD COLUMN tenant_id VARCHAR(8) DEFAULT 'default'";
                $this->db->query($sql);
            }
        }
        
        return true;
    }
    
    public function down() {
        // SQLite数据库回滚
        if ($this->db->driver == 'sqlite') {
            // 由于SQLite的限制，我们需要重新创建表
            $this->db->query("ALTER TABLE tb_config RENAME TO tb_config_old");
            
            $sql = "CREATE TABLE tb_config (
                      id TEXT PRIMARY KEY,
                      key TEXT NOT NULL UNIQUE,
                      value TEXT NOT NULL,
                      description TEXT,
                      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )";
            $this->db->query($sql);
            
            // 复制旧数据到新表，但不包括tenant_id
            $this->db->query("INSERT INTO tb_config (id, key, value, description, created_at, updated_at) SELECT id, key, value, description, created_at, updated_at FROM tb_config_old");
            
            // 删除旧表
            $this->db->query("DROP TABLE tb_config_old");
        }
        
        // MySQL数据库回滚
        if ($this->db->driver == 'mysql') {
            // 检查tenant_id列是否存在
            $columns = $this->db->query("SHOW COLUMNS FROM tb_config LIKE 'tenant_id'");
            if (!empty($columns)) {
                // 如果tenant_id列存在，则删除它
                $sql = "ALTER TABLE tb_config DROP COLUMN tenant_id";
                $this->db->query($sql);
            }
        }
        
        return true;
    }
}
?>