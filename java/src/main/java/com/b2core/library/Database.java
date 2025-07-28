package com.b2core.library;

import java.sql.*;
import java.util.*;

/**
 * 数据库操作类
 * 支持SQLite和MySQL数据库
 */
public class Database {
    private Connection connection;
    
    /**
     * 构造函数
     * 根据配置建立数据库连接
     */
    public Database() {
        try {
            // 从配置文件读取数据库配置
            // 这里简化处理，实际应用中应该从配置文件或环境变量中读取
            String driver = "sqlite"; // 或 "mysql"
            
            if ("sqlite".equals(driver)) {
                // SQLite连接
                String url = "jdbc:sqlite:database.db";
                connection = DriverManager.getConnection(url);
            } else if ("mysql".equals(driver)) {
                // MySQL连接
                String url = "jdbc:mysql://localhost:3306/mydb";
                String user = "root";
                String password = "password";
                connection = DriverManager.getConnection(url, user, password);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
    
    /**
     * 执行查询
     * @param sql SQL查询语句
     * @param params 查询参数
     * @return 查询结果
     */
    public List<Map<String, Object>> query(String sql, Object... params) {
        List<Map<String, Object>> results = new ArrayList<>();
        
        try (PreparedStatement stmt = connection.prepareStatement(sql)) {
            // 设置参数
            for (int i = 0; i < params.length; i++) {
                stmt.setObject(i + 1, params[i]);
            }
            
            // 执行查询
            try (ResultSet rs = stmt.executeQuery()) {
                // 获取结果集元数据
                ResultSetMetaData metaData = rs.getMetaData();
                int columnCount = metaData.getColumnCount();
                
                // 处理结果集
                while (rs.next()) {
                    Map<String, Object> row = new HashMap<>();
                    for (int i = 1; i <= columnCount; i++) {
                        String columnName = metaData.getColumnName(i);
                        Object value = rs.getObject(i);
                        row.put(columnName, value);
                    }
                    results.add(row);
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        
        return results;
    }
    
    /**
     * 执行更新操作（INSERT, UPDATE, DELETE）
     * @param sql SQL语句
     * @param params 参数
     * @return 影响的行数
     */
    public int execute(String sql, Object... params) {
        try (PreparedStatement stmt = connection.prepareStatement(sql)) {
            // 设置参数
            for (int i = 0; i < params.length; i++) {
                stmt.setObject(i + 1, params[i]);
            }
            
            // 执行更新
            return stmt.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
            return 0;
        }
    }
    
    /**
     * 执行插入并返回自增ID
     * @param sql SQL插入语句
     * @param params 插入参数
     * @return 插入记录的ID
     */
    public long insertId(String sql, Object... params) {
        try (PreparedStatement stmt = connection.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            // 设置参数
            for (int i = 0; i < params.length; i++) {
                stmt.setObject(i + 1, params[i]);
            }
            
            // 执行插入
            stmt.executeUpdate();
            
            // 获取生成的ID
            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if (rs.next()) {
                    return rs.getLong(1);
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        
        return 0;
    }
    
    /**
     * 转义字符串
     * @param str 待转义的字符串
     * @return 转义后的字符串
     */
    public String escape(String str) {
        if (str == null) {
            return null;
        }
        
        // 转义单引号
        return str.replace("'", "''");
    }
}