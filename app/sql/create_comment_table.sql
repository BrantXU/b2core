CREATE TABLE IF NOT EXISTS tb_comment (
  id VARCHAR(32) PRIMARY KEY,
  content TEXT NOT NULL,
  user_id VARCHAR(32) NOT NULL,
  page_id VARCHAR(32) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status TINYINT DEFAULT 1
);

-- 添加索引以提高查询性能
CREATE INDEX idx_page_id ON tb_comment(page_id);
CREATE INDEX idx_user_id ON tb_comment(user_id);