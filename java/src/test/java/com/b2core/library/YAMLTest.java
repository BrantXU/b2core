package com.b2core.library;

import org.junit.Test;
import static org.junit.Assert.*;
import java.util.*;

public class YAMLTest {
    
    @Test
    public void testEncode() {
        // 创建测试数据
        Map<String, Object> data = new HashMap<>();
        data.put("name", "示例配置");
        data.put("debug", true);
        data.put("timeout", 30);
        
        // 调用encode方法
        String yaml = YAML.encode(data);
        
        // 验证结果
        assertNotNull(yaml);
        assertTrue(yaml.contains("name: \"示例配置\""));
        assertTrue(yaml.contains("debug: true"));
        assertTrue(yaml.contains("timeout: 30"));
    }
    
    @Test
    public void testDecode() {
        // 创建测试YAML字符串
        String yaml = "name: \"示例配置\"\ndebug: true\ntimeout: 30\n";
        
        // 调用decode方法
        Map<String, Object> data = YAML.decode(yaml);
        
        // 验证结果
        assertNotNull(data);
        assertEquals("示例配置", data.get("name"));
        assertEquals(true, data.get("debug"));
        assertEquals(30, data.get("timeout"));
    }
}