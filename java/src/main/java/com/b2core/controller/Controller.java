package com.b2core.controller;

import java.io.*;
import java.nio.file.*;
import java.util.*;

/**
 * 控制器基类
 * 所有控制器都应继承此类
 */
public class Controller {
    // 视图路径
    private static final String VIEW_PATH = "/views";
    
    // 控制器基类可以包含一些通用的方法
    // 例如视图渲染、请求处理等
    
    /**
     * 显示视图
     * @param viewName 视图名称
     * @param model 数据模型
     */
    protected String display(String viewName, Map<String, Object> model) {
        try {
            // 读取视图文件
            String viewFilePath = VIEW_PATH + "/" + viewName + ".html";
            InputStream viewStream = getClass().getResourceAsStream(viewFilePath);
            if (viewStream == null) {
                return "View not found: " + viewName;
            }
            
            // 读取视图内容
            String viewContent = new String(viewStream.readAllBytes());
            
            // 处理模板变量替换
            // 这是一个简化的实现，实际应用中可能需要更复杂的模板引擎
            for (Map.Entry<String, Object> entry : model.entrySet()) {
                String placeholder = "{{" + entry.getKey() + "}}";
                String value = entry.getValue() != null ? entry.getValue().toString() : "";
                viewContent = viewContent.replace(placeholder, value);
            }
            
            return viewContent;
        } catch (IOException e) {
            return "Error rendering view: " + e.getMessage();
        }
    }
    
    /**
     * 重定向到指定URL
     * @param url 目标URL
     */
    protected String redirect(String url) {
        // 在实际的Web应用中，这里应该设置HTTP重定向响应
        // 这里只是返回一个标识符
        return "REDIRECT:" + url;
    }
}