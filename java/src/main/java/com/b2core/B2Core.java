package com.b2core;

import com.sun.net.httpserver.HttpServer;
import com.sun.net.httpserver.HttpHandler;
import com.sun.net.httpserver.HttpExchange;
import java.io.IOException;
import java.io.OutputStream;
import java.net.InetSocketAddress;
import java.util.Properties;
import java.io.InputStream;

/**
 * B2Core框架核心类
 * 实现MVC架构和路由处理
 */
public class B2Core {
    // 框架版本
    public static final String VERSION = "1.0.0";
    
    // HTTP服务器实例
    private HttpServer server;
    
    // 应用配置
    private Properties appConfig;
    
    // 数据库配置
    private Properties dbConfig;
    
    // 路由配置
    private Properties routeConfig;
    
    /**
     * 构造函数
     */
    public B2Core() {
        // 加载配置
        loadConfig();
    }
    
    /**
     * 加载配置文件
     */
    private void loadConfig() {
        appConfig = new Properties();
        dbConfig = new Properties();
        routeConfig = new Properties();
        
        try {
            // 加载应用配置
            InputStream appConfigStream = getClass().getClassLoader().getResourceAsStream("config/application.properties");
            if (appConfigStream != null) {
                appConfig.load(appConfigStream);
            }
            
            // 加载数据库配置
            InputStream dbConfigStream = getClass().getClassLoader().getResourceAsStream("config/database.properties");
            if (dbConfigStream != null) {
                dbConfig.load(dbConfigStream);
            }
            
            // 加载路由配置
            InputStream routeConfigStream = getClass().getClassLoader().getResourceAsStream("config/routes.properties");
            if (routeConfigStream != null) {
                routeConfig.load(routeConfigStream);
            }
        } catch (IOException e) {
            System.err.println("Failed to load configuration: " + e.getMessage());
        }
    }
    
    /**
     * 启动Web服务器
     */
    public void start() throws IOException {
        // 获取服务器端口
        int port = Integer.parseInt(appConfig.getProperty("server.port", "8080"));
        
        // 创建HTTP服务器
        server = HttpServer.create(new InetSocketAddress(port), 0);
        
        // 设置路由处理器
        setupRoutes();
        
        // 启动服务器
        server.start();
        
        System.out.println("B2Core Framework v" + VERSION + " started on port " + port);
    }
    
    /**
     * 设置路由处理器
     */
    private void setupRoutes() {
        // 为每个路由配置创建处理器
        for (String route : routeConfig.stringPropertyNames()) {
            String handler = routeConfig.getProperty(route);
            server.createContext(route, new RouteHandler(handler));
        }
    }
    
    /**
     * 路由处理器
     */
    private static class RouteHandler implements HttpHandler {
        private String handler;
        
        public RouteHandler(String handler) {
            this.handler = handler;
        }
        
        @Override
        public void handle(HttpExchange exchange) throws IOException {
            // 解析处理器信息
            String[] parts = handler.split("\\.");
            String controllerName = parts[0];
            String methodName = parts.length > 1 ? parts[1] : "index";
            
            // 根据控制器名称创建实例
            // 这里需要实现控制器的动态加载和方法调用
            
            // 简单响应
            String response = "Controller: " + controllerName + ", Method: " + methodName;
            exchange.sendResponseHeaders(200, response.getBytes().length);
            OutputStream os = exchange.getResponseBody();
            os.write(response.getBytes());
            os.close();
        }
    }
    
    /**
     * 主方法，启动应用
     */
    public static void main(String[] args) {
        System.out.println("Welcome to B2Core!");
        
        // 启动HTTP服务器
        try {
            B2Core app = new B2Core();
            app.start();
        } catch (IOException e) {
            System.err.println("Failed to start server: " + e.getMessage());
            e.printStackTrace();
        }
    }
}