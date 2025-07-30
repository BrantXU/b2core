<?php
class FormRenderer {
    public static function renderFormField($field, $config, $entityData, $val, $err, $view = false) {
        // 计算宽度，section和tab类型强制3列
        if ($config['type'] === 'section' || $config['type'] === 'tab') {
            $width = 3;
        } else {
            $width = isset($config['width']) ? intval($config['width']) : 1;
            $width = max(1, min(3, $width)); // 限制在1-3之间
        }
        $html = '';
        if ($config['type'] === 'section' || $config['type'] === 'tab') {
            $html .= '<h3>'.htmlspecialchars($config['name']).'</h3>';
        } else {
            $html.='<div class="'.($width == 3 ? 'uk-width-1-1 ' : ($width == 2 ? 'uk-width-2-3@m ' : 'uk-width-1-3@m ')).'uk-padding-small">';
            $requiredMark = isset($config['required']) && $config['required'] ? '<span style="color: red;">*</span>' : '';
            $html .= '<label class="uk-form-label">'.htmlspecialchars($config['name']).$requiredMark.'</label>
            <div class="uk-form-controls">';
            $value = isset($entityData[$field]) ? htmlspecialchars($entityData[$field]) : (isset($val['data'][$field]) ? htmlspecialchars($val['data'][$field]) : '');
            $readonly = isset($config['readonly']) && $config['readonly'] ? 'readonly' : '';
            $required = isset($config['required']) && $config['required'] ? 'required' : '';
            $tips = isset($config['tips']) ? '<small class="help-text">'.htmlspecialchars($config['tips']).'</small>' : '';
            
            switch($config['type']) {
                case 'datepicker':
                    if ($view) {
                        $html .= '<div class="uk-text-muted">' . htmlspecialchars($value) . '</div>';
                    } else {
                        $html .= '<input class="uk-input uk-width-1-1" type="date" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' >';
                    }
                    break;
                case 'select_new':
                    if ($view) {
                        $html .= '<div class="uk-text-muted">' . htmlspecialchars($value) . '</div>';
                    } else {
                        $html .= '<select class="uk-select uk-width-1-1" name="data['.$field.']" '.$required.' >';
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
                    }
                    break;
                case 'percent':
                    if ($view) {
                        $html .= '<div class="uk-text-muted">' . htmlspecialchars($value) . '%</div>';
                    } else {
                        $html .= '<div style="display: inline-flex; align-items: center; width: 100%;"><input class="uk-input" type="number" step="0.01" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' ><span style="margin-left: 5px;">%</span></div>';
                    }
                    break;
                case 'yuan':
                    if ($view) {
                        $html .= '<div class="uk-text-muted">¥' . htmlspecialchars($value) . '</div>';
                    } else {
                        $html .= '<div style="display: inline-flex; align-items: center; width: 100%;"><input  class="uk-input" type="number" step="0.01" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' style="width: 90%;"><span style="margin-left: 5px;">¥</span></div>';
                    }
                    break;
                case 'amount':
                    if ($view) {
                        $html .= '<div class="uk-text-muted">' . htmlspecialchars($value) . '</div>';
                    } else {
                        $html .= '<input type="number" class="uk-input"  name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' style="width: 90%;">';
                    }
                    break;
                case 'upload':
                    if ($view) {
                        $html .= '<div class="uk-text-muted">' . htmlspecialchars($value) . '</div>';
                    } else {
                        $html .= '<input type="file" name="data['.$field.']" '.$readonly.' style="width: 90%;">';
                    }
                    break;
                case 'muti':
                    if ($view) {
                        $html .= '<div class="uk-text-muted">' . nl2br(htmlspecialchars($value)) . '</div>';
                    } else {
                        $html .= '<textarea class="uk-textarea uk-width-1-1" name="data['.$field.']" rows="5" '.$readonly.' '.$required.' >'.$value.'</textarea>';
                        $html .= $tips;
                    }
                    break;
                default:
                    $html .=$view?'<div class="uk-text-muted">'.$value.'</div>':'<input type="text" class="uk-input" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' style="width: 90%;">';
                    $html .= $tips;
            }
            
            $html .= '<span class="help-inline">'.(isset($err['data'][$field]) ? $err['data'][$field] : '').'</span>';
            $html .= '</div></div>';
        }
        return $html;
    }
    
    public static function renderFormFields($config, $data,$val=[], $errors = [], $view = false ) {
        $html = '';
        $html = '';// 确保fields数组存在
        $fields = $config ?? [];
        foreach ($fields as $id => $field) {
            $name = $field['name'];
            $value = $data[$id] ?? '';
            $error = $errors[$id] ?? '';
            $html .= self::renderFormField($id, $field, $data,$val, $error, $view );
        }
        //$html .= '</div></div>';
        return $html;
    }
}
?>