CREATE TABLE IF NOT EXISTS tb_config (
  id TEXT PRIMARY KEY,
  key TEXT NOT NULL UNIQUE,
  value TEXT NOT NULL,
  description TEXT,
  tenant_id TEXT DEFAULT 'default',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 插入默认配置
INSERT INTO tb_config (key, value, description, tenant_id) 
VALUES ('site_name', 'B2Core系统', '网站名称', 'default');