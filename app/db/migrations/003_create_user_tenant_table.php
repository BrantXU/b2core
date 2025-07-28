<?php
/**
 * 数据库迁移脚本 - 创建用户租户关联表
 */
class Migration_003_Create_User_Tenant_Table {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function up() {
        // SQLite数据库更新
        if ($this->db->driver == 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS tb_user_tenant (
                      user_id TEXT NOT NULL,
                      tenant_id TEXT NOT NULL
                    )";
            $this->db->query($sql);
        }
        
        // MySQL数据库更新
        if ($this->db->driver == 'mysql') {
            $sql = "CREATE TABLE IF NOT EXISTS tb_user_tenant (
                      user_id VARCHAR(8) NOT NULL,
                      tenant_id VARCHAR(8) NOT NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $this->db->query($sql);
        }
        
        return true;
    }
    
    public function down() {
        // 删除用户租户关联表
        if ($this->db->driver == 'sqlite') {
            $this->db->query("DROP TABLE IF EXISTS tb_user_tenant");
        }
        
        if ($this->db->driver == 'mysql') {
            $this->db->query("DROP TABLE IF EXISTS tb_user_tenant");
        }
        
        return true;
    }
}
?>