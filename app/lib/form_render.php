<?php
class FormRenderer {
    public static function renderFormField($field, $config, $entityData, $val, $err) {
        // 计算宽度，section和tab类型强制3列
        if ($config['type'] === 'section' || $config['type'] === 'tab') {
            $width = 3;
        } else {
            $width = isset($config['width']) ? intval($config['width']) : 1;
            $width = max(1, min(3, $width)); // 限制在1-3之间
        }
        $html = '<div style="grid-column: span '.$width.'; padding: 0.5rem;">';
        
        if ($config['type'] === 'section' || $config['type'] === 'tab') {
            $html .= '<h3>'.htmlspecialchars($config['name']).'</h3>';
        } else {
            $requiredMark = isset($config['required']) && $config['required'] ? '<span style="color: red;">*</span>' : '';
            $html .= '<label>'.htmlspecialchars($config['name']).$requiredMark.'</label>';
            
            $value = isset($entityData[$field]) ? htmlspecialchars($entityData[$field]) : (isset($val['data'][$field]) ? htmlspecialchars($val['data'][$field]) : '');
            $readonly = isset($config['readonly']) && $config['readonly'] ? 'readonly' : '';
            $required = isset($config['required']) && $config['required'] ? 'required' : '';
            $tips = isset($config['tips']) ? '<small class="help-text">'.htmlspecialchars($config['tips']).'</small>' : '';
            
            switch($config['type']) {
                case 'datepicker':
                    $html .= '<input type="date" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' style="width: 90%;">';
                    break;
                case 'select_new':
                    $html .= '<select name="data['.$field.']" '.$required.' style="width: 90%;">';
                    $html .= $tips;
                    $dataSource = $config['props']['data_source'] ?? '';
                    $options = explode("\n", $dataSource);
                    foreach($options as $opt) {
                        $opt = trim($opt);
                        if($opt === '') continue;
                        $selected = (isset($entityData[$field]) && $entityData[$field] === $opt) || (isset($val['data'][$field]) && $val['data'][$field] === $opt) ? 'selected' : '';
                        $html .= '<option value="'.htmlspecialchars($opt).'" '.$selected.'>'.htmlspecialchars($opt).'</option>';
                    }
                    $html .= '</select>';
                    break;
                case 'percent':
                    $html .= '<div style="display: inline-flex; align-items: center; width: 100%;"><input type="number" step="0.01" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' style="width: 90%;"><span style="margin-left: 5px;">%</span></div>';
                    break;
                case 'yuan':
                    $html .= '<div style="display: inline-flex; align-items: center; width: 100%;"><input type="number" step="0.01" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' style="width: 90%;"><span style="margin-left: 5px;">¥</span></div>';
                    break;
                case 'amount':
                    $html .= '<input type="number" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' style="width: 90%;">';
                    break;
                case 'upload':
                    $html .= '<input type="file" name="data['.$field.']" '.$readonly.' style="width: 90%;">';
                    break;
                case 'muti':
                    $html .= '<textarea name="data['.$field.']" rows="5" '.$readonly.' '.$required.' style="width: 90%;">'.$value.'</textarea>';
                    $html .= $tips;
                    break;
                default:
                    $html .= '<input type="text" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' style="width: 90%;">';
                    $html .= $tips;
            }
            
            $html .= '<span class="help-inline">'.(isset($err['data'][$field]) ? $err['data'][$field] : '').'</span>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    public static function renderFormFields($item, $entityData, $val, $err) {
        $html = '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">';
        foreach ($item as $field => $config) {
            $html .= self::renderFormField($field, $config, $entityData, $val, $err);
        }
        $html .= '</div>';
        return $html;
    }
}
?>