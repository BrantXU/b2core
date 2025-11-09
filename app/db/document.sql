-- 文档表 (tb_document) 创建语句
-- 用于存储与实体相关的文档文件信息

-- SQLite 版本
CREATE TABLE IF NOT EXISTS tb_document (
    id TEXT PRIMARY KEY,
    del INTEGER DEFAULT 0,
    tenant_id TEXT NOT NULL,
    entity_id TEXT NOT NULL,
    name TEXT NOT NULL,
    filename TEXT NOT NULL,
    filepath TEXT NOT NULL,
    filetype TEXT NOT NULL,
    filesize INTEGER NOT NULL,
    original_size INTEGER,
    description TEXT,
    created_by TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- MySQL 版本
-- CREATE TABLE IF NOT EXISTS tb_document (
--     id VARCHAR(8) PRIMARY KEY,
--     del TINYINT DEFAULT 0,
--     tenant_id VARCHAR(8) NOT NULL,
--     entity_id VARCHAR(8) NOT NULL,
--     name VARCHAR(255) NOT NULL,
--     filename VARCHAR(255) NOT NULL,
--     filepath VARCHAR(500) NOT NULL,
--     filetype VARCHAR(100) NOT NULL,
--     filesize INT NOT NULL,
--     original_size INT,
--     description VARCHAR(500),
--     created_by VARCHAR(8) NOT NULL,
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
-- );