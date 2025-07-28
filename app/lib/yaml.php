<?php
/**
 * YAML处理工具类
 * 使用Symfony YAML组件进行解析
 */
class YAML {
    /**
     * 将数组转换为YAML格式字符串
     * @param array $data 数据数组
     * @return string YAML格式字符串
     */
    public static function encode($data) {
        return self::arrayToYaml($data);
    }
    
    /**
     * 将YAML格式字符串转换为数组
     * @param string $yaml YAML格式字符串
     * @return array 数据数组
     */
    public static function decode($yaml) {
        // 简单的YAML解析实现
        return self::parseYamlStructure($yaml);
    }
    
    /**
     * 解析YAML结构
     * @param string $yaml YAML字符串
     * @return array 解析结果
     */
    private static function parseYamlStructure($yaml) {
        // 使用正则表达式解析YAML
        // 这是一个简化的实现，适用于基本的YAML结构
        
        // 将YAML转换为数组结构
        $lines = explode("\n", $yaml);
        $result = array();
        $context = array(array('data' => &$result, 'indent' => -1));
        
        foreach ($lines as $line) {
            // 跳过空行和注释
            if (trim($line) === '' || preg_match('/^\s*#/', $line)) {
                continue;
            }
            
            // 计算缩进级别
            $indent = (int)(strspn($line, ' ') / 2);
            $line = ltrim($line);
            
            // 调整上下文栈
            while (count($context) > 1 && $indent <= $context[count($context) - 1]['indent']) {
                array_pop($context);
            }
            
            // 获取当前数据引用
            $current = &$context[count($context) - 1]['data'];
            
            // 解析键值对
            if (preg_match('/^([\w\-\.]+):\s*(.*)$/', $line, $matches)) {
                $key = $matches[1];
                $value = trim($matches[2]);
                
                if ($value === '') {
                    // 嵌套对象
                    $current[$key] = array();
                    $context[] = array('data' => &$current[$key], 'indent' => $indent);
                } else {
                    // 简单值
                    $current[$key] = self::parseScalar($value);
                }
            } elseif (preg_match('/^-\s*(.*)$/', $line, $matches)) {
                // 数组项
                $value = trim($matches[1]);
                
                if (!isset($current) || !is_array($current)) {
                    $current = array();
                }
                
                if ($value === '') {
                    // 数组项是嵌套对象
                    $newItem = array();
                    $current[] = $newItem;
                    $context[] = array('data' => &$current[count($current) - 1], 'indent' => $indent);
                } else {
                    // 数组项是简单值
                    $current[] = self::parseScalar($value);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * 解析标量值
     * @param string $value 值
     * @return mixed 解析后的值
     */
    private static function parseScalar($value) {
        // null值
        if ($value === 'null') {
            return null;
        }
        
        // 布尔值
        if ($value === 'true') {
            return true;
        }
        
        if ($value === 'false') {
            return false;
        }
        
        // 数字
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }
        
        // 字符串（带引号）
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            return str_replace('\\"', '"', $matches[1]);
        }
        
        if (preg_match('/^\'(.*)\'$/', $value, $matches)) {
            return str_replace('\'', "'", $matches[1]);
        }
        
        // 普通字符串
        return $value;
    }
    
    /**
     * 将数组转换为YAML格式字符串
     * @param array $data 数据数组
     * @param int $indent 缩进级别
     * @return string YAML格式字符串
     */
    private static function arrayToYaml($data, $indent = 0) {
        $yaml = '';
        $spaces = str_repeat('  ', $indent);
        
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    // 检查是否是索引数组（列表）
                    if (array_keys($value) === range(0, count($value) - 1)) {
                        // 索引数组（列表）
                        $yaml .= "{$spaces}{$key}:\n";
                        foreach ($value as $item) {
                            if (is_array($item)) {
                                $yaml .= "{$spaces}  - " . trim(self::arrayToYaml($item, $indent + 2)) . "\n";
                            } else {
                                $yaml .= "{$spaces}  - " . self::formatScalar($item) . "\n";
                            }
                        }
                    } else {
                        // 关联数组（对象）
                        $yaml .= "{$spaces}{$key}:\n";
                        $yaml .= self::arrayToYaml($value, $indent + 1);
                    }
                } else {
                    $yaml .= "{$spaces}{$key}: " . self::formatScalar($value) . "\n";
                }
            }
        }
        
        return $yaml;
    }
    
    /**
     * 格式化标量值
     * @param mixed $value 值
     * @return string 格式化后的值
     */
    private static function formatScalar($value) {
        if (is_null($value)) {
            return 'null';
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_numeric($value)) {
            return $value;
        } elseif (is_string($value)) {
            // 检查是否需要引号
            if (preg_match('/[\s:\[\]{}\",&*#?|\-<>=!%@`]/', $value) || $value === '') {
                return '"' . str_replace('"', '\\"', $value) . '"';
            }
            return $value;
        } else {
            return '"' . str_replace('"', '\\"', (string)$value) . '"';
        }
    }
}
?>