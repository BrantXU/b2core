<?php
class FormRenderer {

    /* 参数 view 有三种类型
    form view list 
    list 类型不显示 label 
    */
    public static function renderFormField($field, $config, $entityData, $val, $err, $view = 'form', $item = [] ) {
        // 计算宽度，section和tab类型强制3列
        if ($config['type'] === 'section' || $config['type'] === 'tab'|| $config['type'] === 'data') {
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
            if ($config['type'] !== 'data') {
                $html .= '<label class="uk-form-label">'.htmlspecialchars($config['name']).$requiredMark.'</label>';
            }
            $html .= '<div class="uk-form-controls">';
            $value = isset($entityData[$field]) ? htmlspecialchars($entityData[$field]) : (isset($val['data'][$field]) ? htmlspecialchars($val['data'][$field]) : '');
            $readonly = isset($config['readonly']) && $config['readonly'] ? 'readonly' : '';
            $readonlyClass = $readonly ? ' uk-background-muted' : ''; // 添加只读样式类
            $required = isset($config['required']) && $config['required'] ? 'required' : '';
            $tips = isset($config['tips']) ? '<small class="help-text">'.htmlspecialchars($config['tips']).'</small>' : '';

            // 调用新方法渲染控件
            $html .= self::renderControl($config['type'], $field, $value, $readonly, $readonlyClass, $required, $tips, $view, $config['props'] ?? [], $entityData, $val, $item);

            $html .= '<span class="help-inline">'.(isset($err['data'][$field]) ? $err['data'][$field] : '').'</span>';
            $html .= '</div></div>';
        }
        return $html;
    }

    /**
     * 仅负责渲染控件部分
     */
    public static function renderControl($type, $field, $value, $readonly, $readonlyClass, $required, $tips, $view, $props, $entityData, $val, $item = []) {
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
                $hiddenName = 'data['.$field.'_label]';
                if ($view) {
                    $displayValue = isset($entityData[$field.'_label']) ? htmlspecialchars($entityData[$field.'_label']) : htmlspecialchars($value);
                    $html .= '<div class="uk-text-muted">'.$displayValue.'</div>';
                } else {
                    // 初始化hiddenValue为空字符串
                    $hiddenValue = '';
                    
                    // 当type为mod时，从entity表获取数据
                    $dataSource = $props['data_source'] ?? '';
                    $type = $props['type'] ?? '';
                    
                    // 查找当前选中的值
                    $selectedValue = isset($entityData[$field]) ? $entityData[$field] : (isset($val['data'][$field]) ? $val['data'][$field] : '');
                    
                    if ($type === 'mod' && !empty($dataSource)) {
                        // 引入entity模型
                        $entityModel = new entity_m();
                        // 获取指定类型的实体数据
                        $entities = $entityModel->getAllEntities($dataSource);
                        
                        // 查找当前选中的实体
                        $selectedEntityId = $selectedValue;
                        
                        // 如果有选中的实体ID，查找对应的标签
                        if (!empty($selectedEntityId)) {
                            foreach($entities as $entity) {
                                if ($entity['id'] === $selectedEntityId) {
                                    $hiddenValue = htmlspecialchars($entity['name']);
                                    break;
                                }
                            }
                        }
                        
                        // 如果没有找到对应的标签，但有选中的值，使用选中的值作为标签
                        if (empty($hiddenValue) && !empty($selectedEntityId)) {
                            $hiddenValue = htmlspecialchars($selectedEntityId);
                        }
                        
                        // 如果仍然没有hiddenValue，但有entityData中的_label值，则使用它
                        if (empty($hiddenValue) && isset($entityData[$field.'_label'])) {
                            $hiddenValue = htmlspecialchars($entityData[$field.'_label']);
                        }
                        
                        // 如果仍然没有hiddenValue，且没有选中的值，则使用第一个选项的标签作为默认值
                        if (empty($hiddenValue) && empty($selectedValue)) {
                            if ($type === 'mod' && !empty($dataSource) && !empty($entities)) {
                                $hiddenValue = htmlspecialchars($entities[0]['name']);
                            } else if (!empty($options)) {
                                $firstOption = trim($options[0]);
                                if (!empty($firstOption)) {
                                    if (strpos($firstOption, ':') !== false) {
                                        list(, $firstOptionLabel) = explode(':', $firstOption, 2);
                                        $hiddenValue = htmlspecialchars(trim($firstOptionLabel));
                                    } else {
                                        $hiddenValue = htmlspecialchars($firstOption);
                                    }
                                }
                            }
                        }
                    } else {
                        // 原有逻辑：从配置的data_source中分割选项
                        $options = explode("
", $dataSource);
                        
                        // 如果有选中的值，查找对应的标签
                        if (!empty($selectedValue)) {
                            foreach($options as $opt) {
                                $opt = trim($opt);
                                if($opt === '') continue;
                                
                                // 检查是否为 value:label 格式
                                if (strpos($opt, ':') !== false) {
                                    list($optValue, $optLabel) = explode(':', $opt, 2);
                                    $optValue = trim($optValue);
                                    $optLabel = trim($optLabel);
                                } else {
                                    $optValue = $opt;
                                    $optLabel = $opt;
                                }
                                
                                // 如果找到匹配的值，使用对应的标签
                                if ($optValue === $selectedValue) {
                                    $hiddenValue = htmlspecialchars($optLabel);
                                    break;
                                }
                            }
                        }
                        
                        // 如果没有找到对应的标签，但有选中的值，使用选中的值作为标签
                    if (empty($hiddenValue) && !empty($selectedValue)) {
                        $hiddenValue = htmlspecialchars($selectedValue);
                    }
                    
                    // 如果仍然没有hiddenValue，但有entityData中的_label值，则使用它
                    if (empty($hiddenValue) && isset($entityData[$field.'_label'])) {
                        $hiddenValue = htmlspecialchars($entityData[$field.'_label']);
                    }
                    
                    // 如果仍然没有hiddenValue，且没有选中的值，则使用第一个选项的标签作为默认值
                    if (empty($hiddenValue) && empty($selectedValue)) {
                        if ($type === 'mod' && !empty($dataSource) && !empty($entities)) {
                            $hiddenValue = htmlspecialchars($entities[0]['name']);
                        } else if (!empty($options)) {
                            $firstOption = trim($options[0]);
                            if (!empty($firstOption)) {
                                if (strpos($firstOption, ':') !== false) {
                                    list(, $firstOptionLabel) = explode(':', $firstOption, 2);
                                    $hiddenValue = htmlspecialchars(trim($firstOptionLabel));
                                } else {
                                    $hiddenValue = htmlspecialchars($firstOption);
                                }
                            }
                        }
                    }
                    }
                    
                    $html .= '<input type="hidden" name="'.$hiddenName.'" value="'.$hiddenValue.'">';
                    $html .= '<select class="uk-select uk-width-1-1' . $readonlyClass . '" name="data['.$field.']" '.$required
                        .' onchange="document.getElementsByName(\''.$hiddenName.'\')[0].value = this.options[this.selectedIndex].text">';
                    $html .= $tips;
                    
                    // 重新处理选项以设置selected属性
                    if ($type === 'mod' && !empty($dataSource)) {
                        foreach($entities as $entity) {
                            $entityId = htmlspecialchars($entity['id']);
                            $entityName = htmlspecialchars($entity['name']);
                            $selected = (isset($entityData[$field]) && $entityData[$field] === $entityId) || (isset($val['data'][$field]) && $val['data'][$field] === $entityId) ? 'selected' : '';
                            $html .= '<option value="'.$entityId.'" '.$selected.'>'.$entityName.'</option>';
                        }
                    } else {
                        foreach($options as $opt) {
                            $opt = trim($opt);
                            if($opt === '') continue;
                            
                            // 检查是否为 value:label 格式
                            if (strpos($opt, ':') !== false) {
                                list($optValue, $optLabel) = explode(':', $opt, 2);
                                $optValue = trim($optValue);
                                $optLabel = trim($optLabel);
                            } else {
                                $optValue = $opt;
                                $optLabel = $opt;
                            }
                            
                            $selected = (isset($entityData[$field]) && $entityData[$field] === $optValue) || (isset($val['data'][$field]) && $val['data'][$field] === $optValue) ? 'selected' : '';
                            $html .= '<option value="'.htmlspecialchars($optValue).'" '.$selected.'>'.htmlspecialchars($optLabel).'</option>';
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
            case 'data':
                // data类型控件需要渲染成占据一整行的表格
                // 获取数据源
                $dataSource = $props['data_source'] ?? '';
                if ($view && !empty($dataSource)) {
                    // 在view模式下，渲染成表格
                    $html .= '<div class="uk-width-1-1">';
                    
                    // 获取数据
                    $entityModel = new entity_m();

                    // 获取配置
                    $item = $entityModel->getItem($dataSource);
                    $entities = $entityModel->getAllEntities($dataSource);
                    
                    
                    if (!empty($entities)) {
                        $html .= '<table class="uk-table uk-table-striped uk-table-hover">';
                        
                        // 生成表头
                        $html .= '<thead><tr>';
                        
                        // 如果有传入item配置，则根据配置生成表头
                        if (!empty($item) && is_array($item)) {
                            // 添加ID列
                            $html .= '<th>ID</th>';
                            
                            // 根据item配置添加其他列
                            foreach ($item as $fieldName => $fieldConfig) {
                                // 只显示设置了listed=1的字段
                                if (isset($fieldConfig['listed']) && $fieldConfig['listed'] == 1) {
                                    $html .= '<th>' . htmlspecialchars($fieldConfig['name'] ?? $fieldName) . '</th>';
                                }
                            }
                            
                            // 添加创建时间列
                            $html .= '<th>创建时间</th>';
                        } else {
                            // 没有item配置时使用简化版本
                            // 动态生成表头，基于第一个实体的配置
                            // 这里假设所有实体有相同的配置结构
                            $sampleEntity = $entities[0];
                            $sampleData = json_decode($sampleEntity['data'], true);
                            
                            // 添加ID列
                            $html .= '<th>ID</th>';
                            
                            // 添加其他字段列
                            if (is_array($sampleData)) {
                                foreach ($sampleData as $key => $val) {
                                    // 跳过一些内部字段
                                    if (!in_array($key, ['id', 'tenant_id', 'type', 'data', 'created_at', 'updated_at'])) {
                                        $html .= '<th>' . htmlspecialchars($key) . '</th>';
                                    }
                                }
                            }
                            
                            // 添加创建时间列
                            $html .= '<th>创建时间</th>';
                        }
                        $html .= '</tr></thead>';
                        
                        // 生成表格内容
                        $html .= '<tbody>';
                        foreach ($entities as $entity) {
                            $entityData = json_decode($entity['data'], true);
                            $html .= '<tr>';
                            
                            // 显示ID
                            $html .= '<td>' . htmlspecialchars($entity['id'] ?? '') . '</td>';
                            
                            // 如果有传入item配置，则根据配置显示字段
                            if (!empty($item) && is_array($item)) {
                                foreach ($item as $fieldName => $fieldConfig) {
                                    // 只显示设置了listed=1的字段
                                    if (isset($fieldConfig['listed']) && $fieldConfig['listed'] == 1) {
                                        // 获取字段值
                                        $fieldValue = '';
                                        if (isset($entityData[$fieldName])) {
                                            $fieldValue = $entityData[$fieldName];
                                        } elseif (isset($entity[$fieldName])) {
                                            $fieldValue = $entity[$fieldName];
                                        }
                                        $html .= '<td>' . htmlspecialchars($fieldValue) . '</td>';
                                    }
                                }
                            } else {
                                // 没有item配置时使用简化版本
                                if (is_array($entityData)) {
                                    foreach ($entityData as $key => $val) {
                                        // 跳过一些内部字段
                                        if (!in_array($key, ['id', 'tenant_id', 'type', 'data', 'created_at', 'updated_at'])) {
                                            $html .= '<td>' . htmlspecialchars($val ?? '') . '</td>';
                                        }
                                    }
                                }
                            }
                            
                            // 显示创建时间
                            $html .= '<td>' . htmlspecialchars($entity['created_at'] ?? '') . '</td>';
                            $html .= '</tr>';
                        }
                        $html .= '</tbody>';
                        
                        $html .= '</table>';
                    } else {
                        $html .= '<div class="uk-alert uk-alert-warning">暂无数据</div>';
                    }
                    
                    $html .= '</div>';
                } else {
                    // 非view模式或没有数据源时，显示简单的文本输入框
                    $html .= '<input type="text" class="uk-input' . $readonlyClass . '" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' >';
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