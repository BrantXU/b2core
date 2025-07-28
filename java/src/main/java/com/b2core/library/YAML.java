package com.b2core.library;

import java.util.*;
import java.util.regex.*;

/**
 * YAML处理工具类
 */
public class YAML {
    /**
     * 将Map转换为YAML格式字符串
     * @param data 数据Map
     * @return YAML格式字符串
     */
    public static String encode(Map<String, Object> data) {
        return mapToYaml(data, 0);
    }
    
    /**
     * 将YAML格式字符串转换为Map
     * @param yaml YAML格式字符串
     * @return 数据Map
     */
    public static Map<String, Object> decode(String yaml) {
        // 简单的YAML解析实现
        return parseYamlStructure(yaml);
    }
    
    /**
     * 解析YAML结构
     * @param yaml YAML字符串
     * @return 解析结果
     */
    private static Map<String, Object> parseYamlStructure(String yaml) {
        // 使用正则表达式解析YAML
        // 这是一个简化的实现，适用于基本的YAML结构
        
        // 将YAML转换为Map结构
        String[] lines = yaml.split("\n");
        Map<String, Object> result = new HashMap<>();
        Stack<Map<String, Object>> context = new Stack<>();
        context.push(result);
        
        for (String line : lines) {
            // 跳过空行和注释
            if (line.trim().isEmpty() || line.matches("\\s*#.*")) {
                continue;
            }
            
            // 计算缩进级别
            int indent = (int)(line.indexOf(line.trim()) / 2);
            line = line.trim();
            
            // 调整上下文栈
            while (context.size() > 1 && indent <= getIndentLevel(context)) {
                context.pop();
            }
            
            // 获取当前数据引用
            Map<String, Object> current = context.peek();
            
            // 解析键值对
            Pattern pattern = Pattern.compile("^([\\w\\-\\.]+):\\s*(.*)$");
            Matcher matcher = pattern.matcher(line);
            if (matcher.find()) {
                String key = matcher.group(1);
                String value = matcher.group(2).trim();
                
                if (value.isEmpty()) {
                    // 嵌套对象
                    Map<String, Object> nested = new HashMap<>();
                    current.put(key, nested);
                    context.push(nested);
                } else {
                    // 简单值
                    current.put(key, parseScalar(value));
                }
            } else {
                pattern = Pattern.compile("^-\\s*(.*)$");
                matcher = pattern.matcher(line);
                if (matcher.find()) {
                    // 数组项
                    String value = matcher.group(1).trim();
                    
                    // TODO: 实现数组项处理逻辑
                    System.out.println("Array item: " + value);
                }
            }
        }
        
        return result;
    }
    
    /**
     * 获取上下文栈的缩进级别
     * @param context 上下文栈
     * @return 缩进级别
     */
    private static int getIndentLevel(Stack<Map<String, Object>> context) {
        // 这是一个简化的实现，实际应用中可能需要更复杂的逻辑
        return context.size() - 1;
    }
    
    /**
     * 解析标量值
     * @param value 值
     * @return 解析后的值
     */
    private static Object parseScalar(String value) {
        // null值
        if ("null".equals(value)) {
            return null;
        }
        
        // 布尔值
        if ("true".equals(value)) {
            return true;
        }
        
        if ("false".equals(value)) {
            return false;
        }
        
        // 数字
        if (value.matches("\\d+")) {
            return Integer.parseInt(value);
        }
        
        if (value.matches("\\d+\\.\\d+")) {
            return Double.parseDouble(value);
        }
        
        // 字符串（带引号）
        if (value.matches("\".*\"")) {
            return value.substring(1, value.length() - 1).replace("\\\"", "\"");
        }
        
        if (value.matches("'.*'")) {
            return value.substring(1, value.length() - 1).replace("''", "'");
        }
        
        // 普通字符串
        return value;
    }
    
    /**
     * 将Map转换为YAML格式字符串
     * @param data 数据Map
     * @param indent 缩进级别
     * @return YAML格式字符串
     */
    private static String mapToYaml(Map<String, Object> data, int indent) {
        StringBuilder yaml = new StringBuilder();
        String spaces = "  ".repeat(indent);
        
        for (Map.Entry<String, Object> entry : data.entrySet()) {
            String key = entry.getKey();
            Object value = entry.getValue();
            
            if (value instanceof Map) {
                // 嵌套对象
                yaml.append(spaces).append(key).append(":\n");
                yaml.append(mapToYaml((Map<String, Object>) value, indent + 1));
            } else {
                yaml.append(spaces).append(key).append(": ").append(formatScalar(value)).append("\n");
            }
        }
        
        return yaml.toString();
    }
    
    /**
     * 格式化标量值
     * @param value 值
     * @return 格式化后的值
     */
    private static String formatScalar(Object value) {
        if (value == null) {
            return "null";
        } else if (value instanceof Boolean) {
            return (Boolean) value ? "true" : "false";
        } else if (value instanceof Number) {
            return value.toString();
        } else if (value instanceof String) {
            String str = (String) value;
            // 检查是否需要引号
            if (str.matches(".*[\\s:\\\[\\\]{}\"\\",&*|\\-<>=!].*") || str.isEmpty()) {
                return "\"" + str.replace("\"", "\\\"") + "\"";
            }
            return str;
        } else {
            return "\"" + value.toString().replace("\"", "\\\"") + "\"";
        }
    }
}