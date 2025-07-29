<?php
class Migration_005_Add_Config_Type_Column {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function up() {
        // Check if config_type column exists
        $columns = $this->db->query("PRAGMA table_info(tb_config)");
        $hasConfigType = false;
        foreach ($columns as $column) {
            if ($column['name'] == 'config_type') {
                $hasConfigType = true;
                break;
            }
        }
        
        if (!$hasConfigType) {
            $result = $this->db->query("ALTER TABLE tb_config ADD COLUMN config_type TEXT NOT NULL DEFAULT '';");
            if ($result) {
                echo "Migration 005_add_config_type_column.php completed successfully.\n";
            } else {
                throw new Exception('Failed to add config_type column: ' . $this->db->lastErrorMsg());
            }
        } else {
            echo "config_type column already exists, skipping migration.\n";
        }
    }
}