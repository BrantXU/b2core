<?php
class CalendarRenderer{
    public $item = [];
    public $data = [];
    public $entity_type = '';
    public $opt;
 
    public function render()
    {
        // 过滤字段（如果存在filter）
        if(isset($this->opt['filter'])){
            foreach($this->opt['filter'] as $key=>$value){
                $this->opt['filter'][$key] = $value == 'eid'?$this->opt['eid']:$value; 
                unset($this->item[$key]);
            }
        }
        
        // 自动检测日期字段
        $dateFields = $this->_detectDateFields();
        
        $param['entity_type'] = $this->entity_type;
        $param['item'] = $this->item;
        $param['opt'] = isset($this->opt['filter'])? base64_encode(json_encode($this->opt['filter'])):'';
        $param['entities'] = $this->data;
        $param['date_fields'] = $dateFields;
        $param['selected_date_field'] = $this->_selectDateField($dateFields);
        
        return view('v/lib/calendar', $param, true);
    }
    
    /**
     * 检测数据中的日期字段
     */
    private function _detectDateFields()
    {
        $dateFields = [];
        
        foreach ($this->item as $fieldName => $fieldConfig) {
            // 检查字段类型是否为日期相关
            $fieldType = $fieldConfig['type'] ?? '';
            if (in_array($fieldType, ['date', 'datetime', 'time'])) {
                $dateFields[$fieldName] = $fieldConfig;
            }
            
            // 检查字段名称是否包含日期关键词
            $fieldNameLower = strtolower($fieldName);
            $dateKeywords = ['date', 'time', 'day', 'month', 'year', 'created', 'updated', 'start', 'end'];
            foreach ($dateKeywords as $keyword) {
                if (strpos($fieldNameLower, $keyword) !== false) {
                    $dateFields[$fieldName] = $fieldConfig;
                    break;
                }
            }
        }
        
        return $dateFields;
    }
    
    /**
     * 选择默认的日期字段
     */
    private function _selectDateField($dateFields)
    {
        if (empty($dateFields)) {
            return '';
        }
        
        // 优先选择包含"date"的字段
        foreach ($dateFields as $fieldName => $fieldConfig) {
            if (stripos($fieldName, 'date') !== false) {
                return $fieldName;
            }
        }
        
        // 其次选择包含"time"的字段
        foreach ($dateFields as $fieldName => $fieldConfig) {
            if (stripos($fieldName, 'time') !== false) {
                return $fieldName;
            }
        }
        
        // 返回第一个日期字段
        return array_key_first($dateFields);
    }
}