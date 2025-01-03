CREATE TABLE IF NOT EXISTS tb_user (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT NOT NULL,
  password TEXT NOT NULL,
  email TEXT NOT NULL,
  level INTEGER DEFAULT 0
);

INSERT INTO tb_user (username, password, email, level) 
VALUES ('admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@b24.cn', 1); 