-- 为所有表添加del字段（逻辑删除标志）

-- 为用户表添加del字段
ALTER TABLE tb_user ADD COLUMN del INTEGER DEFAULT 0;

-- 为配置表添加del字段
ALTER TABLE tb_config ADD COLUMN del INTEGER DEFAULT 0;

-- 为租户表添加del字段
ALTER TABLE tb_tenant ADD COLUMN del INTEGER DEFAULT 0;

-- 为实体表添加del字段
ALTER TABLE tb_entity ADD COLUMN del INTEGER DEFAULT 0;

-- 为用户租户关联表添加del字段
ALTER TABLE tb_user_tenant ADD COLUMN del INTEGER DEFAULT 0;