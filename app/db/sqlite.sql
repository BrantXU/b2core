CREATE TABLE IF NOT EXISTS tb_user (
  id TEXT PRIMARY KEY,
  username TEXT NOT NULL,
  password TEXT NOT NULL,
  email TEXT NOT NULL,
  level INTEGER DEFAULT 0
);

INSERT INTO tb_user (username, password, email, level) 
VALUES ('admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@b24.cn', 1);

-- 创建配置表
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
INSERT OR IGNORE INTO tb_config (id, key, value, description, tenant_id) 
VALUES ('12345678', 'site_name', 'B2Core系统', '网站名称', 'default');

-- 创建租户表
CREATE TABLE IF NOT EXISTS tb_tenant (
  id TEXT PRIMARY KEY,
  name TEXT NOT NULL,
  status INTEGER DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 插入默认租户
INSERT OR IGNORE INTO tb_tenant (id, name, status) 
VALUES ('default', '默认租户', 1);

-- 创建实体表
CREATE TABLE IF NOT EXISTS tb_entity (
  id TEXT PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  name TEXT NOT NULL,
  type TEXT NOT NULL,
  data TEXT,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);