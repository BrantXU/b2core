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
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 插入默认配置
INSERT INTO tb_config (id, key, value, description) 
VALUES ('12345678', 'site_name', 'B2Core系统', '网站名称');