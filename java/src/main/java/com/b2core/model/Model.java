package com.b2core.model;

import com.b2core.library.Database;
import java.util.*;

/**
 * 模型基类
 * 所有数据模型都应继承此类
 */
public class Model {
    protected String tableName;   // 表名
    protected String primaryKey;  // 主键
    protected Database db;        // 数据库实例
    
    /**
     * 构造函数
     * 初始化数据库实例
     */
    public Model() {
        this.db = new Database();
        // 子类应该在构造函数中设置tableName和primaryKey
    }
    
    /**
     * 保存数据
     * @param data 要保存的数据
     * @return 保存的记录ID
     */
    public long save(Map<String, Object> data) {
        // 如果主键在数据中且不为0，则更新记录
        if (data.containsKey(primaryKey) && (long) data.get(primaryKey) > 0) {
            return update(data);
        } else {
            // 否则插入新记录
            return insert(data);
        }
    }
    
    /**
     * 插入新记录
     * @param data 要插入的数据
     * @return 插入的记录ID
     */
    protected long insert(Map<String, Object> data) {
        // 移除主键字段，因为它是自增的
        data.remove(primaryKey);
        
        // 构建SQL语句
        StringBuilder sql = new StringBuilder();
        sql.append("INSERT INTO ").append(tableName).append(" (");
        
        // 添加字段名
        StringJoiner fieldNames = new StringJoiner(", ");
        StringJoiner placeholders = new StringJoiner(", ");
        
        for (String field : data.keySet()) {
            fieldNames.add(field);
            placeholders.add("?");
        }
        
        sql.append(fieldNames.toString()).append(") VALUES (").append(placeholders.toString()).append(")");
        
        // 执行插入操作
        return db.insertId(sql.toString(), data.values().toArray());
    }
    
    /**
     * 更新记录
     * @param data 要更新的数据
     * @return 更新的记录数
     */
    protected int update(Map<String, Object> data) {
        // 构建SQL语句
        StringBuilder sql = new StringBuilder();
        sql.append("UPDATE ").append(tableName).append(" SET ");
        
        // 添加字段名
        StringJoiner fieldNames = new StringJoiner(", ");
        for (String field : data.keySet()) {
            if (!field.equals(primaryKey)) {  // 主键不参与更新
                fieldNames.add(field + " = ?");
            }
        }
        
        sql.append(fieldNames.toString()).append(" WHERE ").append(primaryKey).append(" = ?");
        
        // 准备参数
        List<Object> params = new ArrayList<>();
        for (Map.Entry<String, Object> entry : data.entrySet()) {
            if (!entry.getKey().equals(primaryKey)) {  // 主键不参与更新
                params.add(entry.getValue());
            }
        }
        params.add(data.get(primaryKey));  // 添加主键值作为WHERE条件
        
        // 执行更新操作
        return db.execute(sql.toString(), params.toArray());
    }
    
    /**
     * 删除记录
     * @param id 记录ID
     * @return 删除的记录数
     */
    public int delete(long id) {
        String sql = "DELETE FROM " + tableName + " WHERE " + primaryKey + " = ?";
        return db.execute(sql, id);
    }
    
    /**
     * 根据ID查找记录
     * @param id 记录ID
     * @return 记录数据
     */
    public Map<String, Object> find(long id) {
        String sql = "SELECT * FROM " + tableName + " WHERE " + primaryKey + " = ?";
        List<Map<String, Object>> results = db.query(sql, id);
        return results.isEmpty() ? null : results.get(0);
    }
    
    /**
     * 查找所有记录
     * @return 记录列表
     */
    public List<Map<String, Object>> findAll() {
        String sql = "SELECT * FROM " + tableName;
        return db.query(sql);
    }
    
    /**
     * 根据条件查找记录
     * @param conditions 查询条件
     * @return 记录列表
     */
    public List<Map<String, Object>> findBy(Map<String, Object> conditions) {
        // 构建SQL语句
        StringBuilder sql = new StringBuilder();
        sql.append("SELECT * FROM ").append(tableName).append(" WHERE ");
        
        // 添加条件
        StringJoiner conditionStrings = new StringJoiner(" AND ");
        List<Object> params = new ArrayList<>();
        
        for (Map.Entry<String, Object> entry : conditions.entrySet()) {
            conditionStrings.add(entry.getKey() + " = ?");
            params.add(entry.getValue());
        }
        
        sql.append(conditionStrings.toString());
        
        // 执行查询
        return db.query(sql.toString(), params.toArray());
    }
}