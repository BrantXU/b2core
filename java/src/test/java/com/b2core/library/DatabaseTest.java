package com.b2core.library;

import org.junit.Test;
import static org.junit.Assert.*;

public class DatabaseTest {
    
    @Test
    public void testEscape() {
        // 创建Database实例
        Database db = new Database();
        
        // 测试字符串转义
        String original = "It's a test";
        String escaped = db.escape(original);
        
        // 验证结果
        assertEquals("It''s a test", escaped);
    }
}