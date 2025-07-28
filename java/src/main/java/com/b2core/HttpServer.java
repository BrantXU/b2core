package com.b2core;

import com.b2core.controller.ConfigController;
import com.sun.net.httpserver.HttpServer;
import com.sun.net.httpserver.HttpHandler;
import com.sun.net.httpserver.HttpExchange;

import java.io.IOException;
import java.io.OutputStream;
import java.net.InetSocketAddress;
import java.util.HashMap;
import java.util.Map;

/**
 * 简单的HTTP服务器实现
 * 用于测试B2Core应用
 */
public class HttpServer {
    private static final int PORT = 8080;
    private com.sun.net.httpserver.HttpServer server;
    
    /**
     * 启动HTTP服务器
     */
    public void start() throws IOException {
        server = HttpServer.create(new InetSocketAddress(PORT), 0);
        
        // 注册路由处理器
        server.createContext("/", new RootHandler());
        server.createContext("/config/index", new ConfigIndexHandler());
        server.createContext("/config/create", new ConfigCreateHandler());
        
        server.setExecutor(null); // 使用默认执行器
        server.start();
        
        System.out.println("Server started on port " + PORT);
    }
    
    /**
     * 根路径处理器
     */
    static class RootHandler implements HttpHandler {
        @Override
        public void handle(HttpExchange exchange) throws IOException {
            String response = "Welcome to B2Core!";
            exchange.sendResponseHeaders(200, response.getBytes().length);
            OutputStream os = exchange.getResponseBody();
            os.write(response.getBytes());
            os.close();
        }
    }
    
    /**
     * 配置列表页面处理器
     */
    static class ConfigIndexHandler implements HttpHandler {
        @Override
        public void handle(HttpExchange exchange) throws IOException {
            ConfigController controller = new ConfigController();
            String response = controller.index();
            
            exchange.sendResponseHeaders(200, response.getBytes().length);
            OutputStream os = exchange.getResponseBody();
            os.write(response.getBytes());
            os.close();
        }
    }
    
    /**
     * 创建配置页面处理器
     */
    static class ConfigCreateHandler implements HttpHandler {
        @Override
        public void handle(HttpExchange exchange) throws IOException {
            ConfigController controller = new ConfigController();
            String response = controller.create();
            
            exchange.sendResponseHeaders(200, response.getBytes().length);
            OutputStream os = exchange.getResponseBody();
            os.write(response.getBytes());
            os.close();
        }
    }
}