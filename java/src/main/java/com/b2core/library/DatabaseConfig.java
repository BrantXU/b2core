package com.b2core.library;

/**
 * 数据库配置类
 */
public class DatabaseConfig {
    private String driver;
    private SQLiteConfig sqliteConfig;
    private MySQLConfig mysqlConfig;
    
    public String getDriver() {
        return driver;
    }
    
    public void setDriver(String driver) {
        this.driver = driver;
    }
    
    public SQLiteConfig getSqliteConfig() {
        return sqliteConfig;
    }
    
    public void setSqliteConfig(SQLiteConfig sqliteConfig) {
        this.sqliteConfig = sqliteConfig;
    }
    
    public MySQLConfig getMysqlConfig() {
        return mysqlConfig;
    }
    
    public void setMysqlConfig(MySQLConfig mysqlConfig) {
        this.mysqlConfig = mysqlConfig;
    }
}