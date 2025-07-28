package com.b2core.model;

import java.util.*;

/**
 * 配置模型
 * 处理配置数据的数据库操作
 */
public class ConfigModel extends Model {
    /**
     * 构造函数
     * 设置表名和主键
     */
    public ConfigModel() {
        this.tableName = "tb_config";
        this.primaryKey = "id";
    }
    
    /**
     * 根据键名获取配置
     * @param key 配置键名
     * @return 配置值
     */
    public String getByKey(String key) {
        String sql = "SELECT value FROM " + tableName + " WHERE key = ?";
        List<Map<String, Object>> results = db.query(sql, key);
        
        if (!results.isEmpty()) {
            return (String) results.get(0).get("value");
        }
        
        return null;
    }
    
    /**
     * 获取配置列表
     * @param page 页码
     * @param limit 每页记录数
     * @return 配置列表
     */
    public List<Map<String, Object>> getPage(int page, int limit) {
        int offset = (page - 1) * limit;
        String sql = "SELECT * FROM " + tableName + " ORDER BY id DESC LIMIT ? OFFSET ?";
        return db.query(sql, limit, offset);
    }
    
    /**
     * 获取单条配置
     * @param id 配置ID
     * @return 配置数据
     */
    public Map<String, Object> getOne(long id) {
        return find(id);
    }
    
    /**
     * 创建配置
     * @param data 配置数据
     * @return 新配置ID
     */
    public long create(Map<String, Object> data) {
        return insert(data);
    }
    
    /**
     * 更新配置
     * @param data 配置数据
     * @return 是否成功
     */
    public boolean update(Map<String, Object> data) {
        long id = (long) data.get("id");
        int rowsAffected = super.update(data);
        return rowsAffected > 0;
    }
    
    /**
     * 删除配置
     * @param id 配置ID
     * @return 是否成功
     */
    public boolean delete(long id) {
        int rowsAffected = super.delete(id);
        return rowsAffected > 0;
    }
}