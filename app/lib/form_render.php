<?php
class FormRenderer {

    /* 参数 view 有三种类型
    form view list 
    list 类型不显示 label 
    */
    public static function renderFormField($field, $config, $entityData, $val, $err, $view = 'form' ) {
        // 计算宽度，section和tab类型强制3列
        if ($config['type'] === 'section' || $config['type'] === 'tab') {
            $width = 3;
        } else {
            $width = isset($config['width']) ? intval($config['width']) : 1;
            $width = max(1, min(3, $width)); // 限制在1-3之间
        }
        $html = '';
        if ($config['type'] === 'section' || $config['type'] === 'tab') {
            $html .= '<h3 class="uk-width-1-1">'.htmlspecialchars($config['name']).'</h3>';
        } else {
            $html.='<div class="'.($width == 3 ? 'uk-width-1-1 ' : ($width == 2 ? 'uk-width-2-3@m ' : 'uk-width-1-3@m ')).'uk-padding-small">';
            $requiredMark = isset($config['required']) && $config['required'] ? '<span style="color: red;">*</span>' : '';
            $html .= '<label class="uk-form-label">'.htmlspecialchars($config['name']).$requiredMark.'</label>
            <div class="uk-form-controls">';
            $value = isset($entityData[$field]) ? htmlspecialchars($entityData[$field]) : (isset($val['data'][$field]) ? htmlspecialchars($val['data'][$field]) : '');
            $readonly = isset($config['readonly']) && $config['readonly'] ? 'readonly' : '';
            $readonlyClass = $readonly ? ' uk-background-muted' : ''; // 添加只读样式类
            $required = isset($config['required']) && $config['required'] ? 'required' : '';
            $tips = isset($config['tips']) ? '<small class="help-text">'.htmlspecialchars($config['tips']).'</small>' : '';

            // 调用新方法渲染控件
            $html .= self::renderControl($config['type'], $field, $value, $readonly, $readonlyClass, $required, $tips, $view, $config['props'] ?? [], $entityData, $val);

            $html .= '<span class="help-inline">'.(isset($err['data'][$field]) ? $err['data'][$field] : '').'</span>';
            $html .= '</div></div>';
        }
        return $html;
    }

    /**
     * 仅负责渲染控件部分
     */
    public static function renderControl($type, $field, $value, $readonly, $readonlyClass, $required, $tips, $view, $props, $entityData, $val) {
        $html = '';

        switch($type) {
            case 'datepicker':
                if ($view) {
                    $html .= '<div class="uk-text-muted">' . htmlspecialchars($value) . '</div>';
                } else {
                    $html .= '<input class="uk-input uk-width-1-1' . $readonlyClass . '" type="date" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' >';
                }
                break;
            case 'select_new':
            case 'select':
                if ($view) {
                    $value_data = explode(':::', $value);
                    // 检查数组是否有至少2个元素，并且第二个元素不为null
                    if (count($value_data) >= 2 && !is_null($value_data[1])) {
                        $html .= '<div class="uk-text-muted">' . htmlspecialchars($value_data[1]) . '</div>';
                    } else {
                        // 如果格式不正确，直接显示原始值
                        $html .= '<div class="uk-text-muted">' . htmlspecialchars(trim($value) ?: '') . '</div>';
                    }
                } else {
                    $html .= '<select class="uk-select uk-width-1-1' . $readonlyClass . '" name="data['.$field.']" '.$required.' >';
                    $html .= $tips;
                    $dataSource = $props['data_source'] ?? '';
                    $type = $props['type'] ?? '';

                    // 当type为mod时，从entity表获取数据
                    if ($type === 'mod' && !empty($dataSource)) {
                        // 引入entity模型
                        $entityModel = new entity_m();
                        // 获取指定类型的实体数据
                        $entities = $entityModel->getAllEntities($dataSource);
                        echo $dataSource;

                        foreach($entities as $entity) {
                            $entityId = htmlspecialchars($entity['id']);
                            $entityName = htmlspecialchars($entity['name']);
                            $selected = (isset($entityData[$field]) && $entityData[$field] === $entityId) || (isset($val['data'][$field]) && $val['data'][$field] === $entityId) ? 'selected' : '';
                            $html .= '<option value="'.$entityId.':::'.$entityName.'" '.$selected.'>'.$entityName.'</option>';
                        }
                    } else {
                        // 原有逻辑：从配置的data_source中分割选项
                        $options = explode("
", $dataSource);
                        foreach($options as $opt) {
                            $opt = trim($opt);
                            if($opt === '') continue;
                            $selected = (isset($entityData[$field]) && $entityData[$field] === $opt) || (isset($val['data'][$field]) && $val['data'][$field] === $opt) ? 'selected' : '';
                            $html .= '<option value="'.htmlspecialchars($opt).'" '.$selected.'>'.htmlspecialchars($opt).'</option>';
                        }
                    }

                    $html .= '</select>';
                }
                break;
            case 'percent':
                if ($view) {
                    $html .= '<div class="uk-text-muted">' . htmlspecialchars($value) . '%</div>';
                } else {
                    $html .= '<div style="display: inline-flex; align-items: center; width: 100%;"><input class="uk-input' . $readonlyClass . '" type="number" step="0.01" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' ><span style="margin-left: 5px;">%</span></div>';
                }
                break;
            case 'yuan':
                if ($view) {
                    $html .= '<div class="uk-text-muted">¥' . htmlspecialchars($value) . '</div>';
                } else {
                    $html .= '<div style="display: inline-flex; align-items: center; width: 100%;"><input  class="uk-input' . $readonlyClass . '" type="number" step="0.01" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' style="width: 90%;"><span style="margin-left: 5px;">¥</span></div>';
                }
                break;
            case 'amount':
                if ($view) {
                    $html .= '<div class="uk-text-muted">' . htmlspecialchars($value) . '</div>';
                } else {
                    $html .= '<input type="number" class="uk-input' . $readonlyClass . '"  name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' style="width: 90%;">';
                }
                break;
            case 'upload':
                if ($view) {
                    $html .= '<div class="uk-text-muted">' . htmlspecialchars($value) . '</div>';
                } else {
                    $html .= '<input type="file" name="data['.$field.']" '.$readonly.' style="width: 90%;"'.($readonly ? ' disabled' : '').'>';
                }
                break;
            case 'muti':
                if ($view) {
                    $html .= '<div class="uk-text-muted">' . nl2br(htmlspecialchars($value)) . '</div>';
                } else {
                    $html .= '<textarea class="uk-textarea uk-width-1-1' . $readonlyClass . '" name="data['.$field.']" rows="5" '.$readonly.' '.$required.' >'.$value.'</textarea>';
                    $html .= $tips;
                }
                break;
            default:
                $html .=$view?'<div class="uk-text-muted">'.$value.'</div>':'<input type="text" class="uk-input' . $readonlyClass . '" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' >';
                $html .= $tips;
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