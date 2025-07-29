<?php

use Symfony\Component\Yaml\Yaml as SymfonyYaml;

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
        return SymfonyYaml::dump($data, 256, 2, SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }
    
    /**
     * 将YAML格式字符串转换为数组
     * @param string $yaml YAML格式字符串
     * @return array 数据数组
     */
    public static function decode($yaml) {
        return SymfonyYaml::parse($yaml);
    }
}
?>