package com.b2core.controller;

import com.b2core.model.ConfigModel;
import com.b2core.library.YAML;
import java.util.*;

/**
 * 配置控制器
 * 处理配置相关的HTTP请求
 */
public class ConfigController extends Controller {
    private ConfigModel configModel;
    
    /**
     * 构造函数
     * 初始化配置模型
     */
    public ConfigController() {
        this.configModel = new ConfigModel();
    }
    
    /**
     * 配置列表页面
     */
    public String index() {
        // 获取配置列表
        List<Map<String, Object>> configs = configModel.getPage(1, 10); // 默认获取第一页，每页10条
        
        // 准备视图数据
        Map<String, Object> model = new HashMap<>();
        model.put("configs", configs);
        
        // 渲染视图
        return display("config/list", model);
    }
    
    /**
     * 创建配置页面
     */
    public String create() {
        // 渲染视图
        return display("config/create", new HashMap<>());
    }
    
    /**
     * 处理创建配置请求
     * @param key 配置键
     * @param value 配置值
     * @param description 配置描述
     * @param tenantId 租户ID
     */
    public String doCreate(String key, String value, String description, long tenantId) {
        // 准备数据
        Map<String, Object> data = new HashMap<>();
        data.put("key", key);
        data.put("value", value);
        data.put("description", description);
        data.put("tenant_id", tenantId);
        
        // 创建配置
        long id = configModel.create(data);
        
        if (id > 0) {
            // 创建成功，重定向到列表页面
            return redirect("/config/index");
        } else {
            // 创建失败，返回错误信息
            Map<String, Object> model = new HashMap<>();
            model.put("error", "Failed to create config");
            return display("config/create", model);
        }
    }
    
    /**
     * 编辑配置页面
     * @param id 配置ID
     */
    public String edit(long id) {
        // 获取配置信息
        Map<String, Object> config = configModel.getOne(id);
        
        if (config == null) {
            // 配置不存在，重定向到列表页面
            return redirect("/config/index");
        }
        
        // 准备视图数据
        Map<String, Object> model = new HashMap<>();
        model.put("config", config);
        
        // 渲染视图
        return display("config/edit", model);
    }
    
    /**
     * 处理编辑配置请求
     * @param id 配置ID
     * @param key 配置键
     * @param value 配置值
     * @param description 配置描述
     * @param tenantId 租户ID
     */
    public String doEdit(long id, String key, String value, String description, long tenantId) {
        // 准备数据
        Map<String, Object> data = new HashMap<>();
        data.put("id", id);
        data.put("key", key);
        data.put("value", value);
        data.put("description", description);
        data.put("tenant_id", tenantId);
        
        // 更新配置
        boolean success = configModel.update(data);
        
        if (success) {
            // 更新成功，重定向到列表页面
            return redirect("/config/index");
        } else {
            // 更新失败，返回错误信息
            Map<String, Object> model = new HashMap<>();
            model.put("error", "Failed to update config");
            model.put("config", data);
            return display("config/edit", model);
        }
    }
    
    /**
     * 删除配置
     * @param id 配置ID
     */
    public String delete(long id) {
        // 删除配置
        boolean success = configModel.delete(id);
        
        if (success) {
            // 删除成功
            return "{\"success\": true}";
        } else {
            // 删除失败
            return "{\"success\": false, \"message\": \"Failed to delete config\"}";
        }
    }
}