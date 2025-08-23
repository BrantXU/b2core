<?php
/**
 * FormRenderer类 - 表单渲染工具
 * 用于根据配置和数据渲染不同类型的表单字段
 */
class FormRenderer {
    /**
     * 渲染单个表单字段
     * 
     * @param string $field 字段名
     * @param array $config 字段配置
     * @param array $entityData 实体数据
     * @param array $val 表单提交的值
     * @param array $err 错误信息
     * @param string $view 视图类型: form, view, list
     * @return string 渲染后的HTML
     */
    public static function renderFormField($field, $config, $entityData, $val, $err, $view = 'form') {
        // 计算宽度，section和tab类型强制3列
        if (in_array($config['type'], ['section', 'tab', 'data'])) {
            $width = 3;
        } else {
            $width = isset($config['width']) ? intval($config['width']) : 1;
            $width = max(1, min(3, $width)); // 限制在1-3之间
        }

        $html = '';
        if (in_array($config['type'], ['section', 'tab'])) {
            $html .= '<h3 class="uk-width-1-1">'.htmlspecialchars($config['name']).'</h3>';
        } else {
            // 确定宽度类
            $widthClass = $width == 3 ? 'uk-width-1-1 ' : ($width == 2 ? 'uk-width-2-3@m ' : 'uk-width-1-3@m ');
            $html .= '<div class="' . $widthClass . 'uk-padding-small">';

            // 添加必填标记
            $requiredMark = isset($config['required']) && $config['required'] ? '<span style="color: red;">*</span>' : '';

            // 添加标签
            if ($config['type'] !== 'data') {
                $html .= '<label class="uk-form-label">'.htmlspecialchars($config['name']).$requiredMark.'</label>';
            }

            $html .= '<div class="uk-form-controls">';

            // 获取字段值
            $value = isset($entityData[$field]) ? htmlspecialchars($entityData[$field]) : (isset($val['data'][$field]) ? htmlspecialchars($val['data'][$field]) : '');
            $readonly = isset($config['readonly']) && $config['readonly'] ? 'readonly' : '';
            $required = isset($config['required']) && $config['required'] ? 'required' : '';
            $tips = isset($config['tips']) ? '<small class="help-text">'.htmlspecialchars($config['tips']).'</small>' : '';

            // 获取标签值
            $label = isset($entityData[$field.'_label']) ? $entityData[$field.'_label'] : '';

            // 调用渲染控件的方法
            $html .= self::renderControl($config['type'], $field, $value, $label, $readonly, $required, $tips, $view, $config['props'] ?? []);

            // 添加错误信息
            $errorMsg = isset($err['data'][$field]) ? $err['data'][$field] : '';
            $html .= '<span class="help-inline">' . $errorMsg . '</span>';

            $html .= '</div></div>';
        }

        return $html;
    }

    /**
     * 渲染单个表单元素（用于非表单场景）
     * 
     * @param mixed $value 元素值
     * @param array $config 元素配置
     * @param string $label 标签值
     * @return string 渲染后的HTML
     */
    public static function item($value, $config = [], $label = '') {
        if (empty($config)) {
            return $value;
        }

        return self::renderControl(
            $config['type'],
            $config['id'],
            $value,
            $label,
            isset($config['readonly']) ? true : false,
            isset($config['required']) ? true : false,
            isset($config['tips']) ? $config['tips'] : '',
            true,
            isset($config['props']) ? $config['props'] : []
        );
    }

    /**
     * 仅负责渲染控件部分
     * 
     * @param string $type 控件类型
     * @param string $field 字段名
     * @param mixed $value 字段值
     * @param string $label 标签值
     * @param string $readonly 是否只读
     * @param string $required 是否必填
     * @param string $tips 提示信息
     * @param bool|string $view 视图类型
     * @param array $props 额外属性
     * @return string 渲染后的HTML
     */
    public static function renderControl($type, $field, $value, $label, $readonly, $required, $tips, $view, $props = []) {
        $html = '';
        $readonlyClass = $readonly ? ' uk-background-muted' : ''; // 添加只读样式类

        switch ($type) {
            case 'datepicker':
                if ($view) {
                    $html .= '<div class="uk-text-muted">' . htmlspecialchars($value) . '</div>';
                } else {
                    $html .= '<input class="uk-input uk-width-1-1' . $readonlyClass . '" type="date" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.'>';
                }
                break;

            case 'select_new':
            case 'select':
                $hiddenName = 'data['.$field.'_label]';

                if ($view) {
                    $displayValue = $label ?: htmlspecialchars($value);
                    $html .= '<div class="uk-text-muted">' . $displayValue . '</div>';
                } else {
                    // 初始化hiddenValue为空字符串
                    $hiddenValue = '';

                    // 获取数据源和类型
                    $dataSource = $props['data_source'] ?? '';
                    $sourceType = $props['type'] ?? '';

                    // 查找当前选中的值
                    $selectedValue = isset($value) ? $value : '';

                    // 处理mod类型的数据源
                    if ($sourceType === 'mod' && !empty($dataSource)) {
                        // 引入entity模型
                        $entityModel = new entity_m();
                        // 获取指定类型的实体数据
                        $entities = $entityModel->getAllEntities($dataSource);

                        // 查找选中实体的标签
                        $hiddenValue = self::getHiddenValueFromEntities($selectedValue, $entities, $label);
                    } else {
                        // 处理普通选项
                        $options = !empty($dataSource) ? explode("
", $dataSource) : [];
                        // 查找选中选项的标签
                        $hiddenValue = self::getHiddenValueFromOptions($selectedValue, $options, $label);
                    }

                    // 添加隐藏字段
                    $html .= '<input type="hidden" name="'.$hiddenName.'" value="'.$hiddenValue.'">';

                    // 添加选择框
                    $html .= '<select class="uk-select uk-width-1-1' . $readonlyClass . '" name="data['.$field.']" '.$required
                        .' onchange="document.getElementsByName(\''.$hiddenName.'\')[0].value = this.options[this.selectedIndex].text">';
                    $html .= $tips;

                    // 添加选项
                    if ($sourceType === 'mod' && !empty($dataSource) && !empty($entities)) {
                        foreach ($entities as $entity) {
                            $entityId = htmlspecialchars($entity['id']);
                            $entityName = htmlspecialchars($entity['name']);
                            $isSelected = (isset($value) && $value === $entityId) ? 'selected' : '';
                            $html .= '<option value="'.$entityId.'" '.$isSelected.'>'.$entityName.'</option>';
                        }
                    } else {
                        $options = !empty($dataSource) ? explode("
", $dataSource) : [];
                        foreach ($options as $opt) {
                            $opt = trim($opt);
                            if ($opt === '') continue;

                            // 解析选项值和标签
                            if (strpos($opt, ':') !== false) {
                                list($optValue, $optLabel) = explode(':', $opt, 2);
                                $optValue = trim($optValue);
                                $optLabel = trim($optLabel);
                            } else {
                                $optValue = $opt;
                                $optLabel = $opt;
                            }

                            $isSelected = (isset($value) && $value === $optValue) ? 'selected' : '';
                            $html .= '<option value="'.htmlspecialchars($optValue).'" '.$isSelected.'>'.htmlspecialchars($optLabel).'</option>';
                        }
                    }

                    $html .= '</select>';
                }
                break;

            case 'percent':
                if ($view) {
                    $html .= '<div class="uk-text-muted">' . htmlspecialchars($value) . '%</div>';
                } else {
                    $html .= '<div style="display: inline-flex; align-items: center; width: 100%;"><input class="uk-input' . $readonlyClass . '" type="number" step="0.01" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.'><span style="margin-left: 5px;">%</span></div>';
                }
                break;

            case 'yuan':
                if ($view) {
                    $html .= '<div class="uk-text-muted">¥' . htmlspecialchars($value) . '</div>';
                } else {
                    $html .= '<div style="display: inline-flex; align-items: center; width: 100%;"><input class="uk-input' . $readonlyClass . '" type="number" step="0.01" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' style="width: 90%;"><span style="margin-left: 5px;">¥</span></div>';
                }
                break;

            case 'amount':
                if ($view) {
                    $html .= '<div class="uk-text-muted">' . htmlspecialchars($value) . '</div>';
                } else {
                    $html .= '<input type="number" class="uk-input' . $readonlyClass . '" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.' style="width: 90%;">';
                }
                break;

            case 'upload':
                if ($view) {
                    $html .= '<div class="uk-text-muted">' . htmlspecialchars($value) . '</div>';
                } else {
                    $disabledAttr = $readonly ? ' disabled' : '';
                    $html .= '<input type="file" name="data['.$field.']" style="width: 90%;"' . $disabledAttr . '>';
                }
                break;

            case 'muti':
                if ($view) {
                    $html .= '<div class="uk-text-muted">' . nl2br(htmlspecialchars($value)) . '</div>';
                } else {
                    $html .= '<textarea class="uk-textarea uk-width-1-1' . $readonlyClass . '" name="data['.$field.']" rows="5" '.$readonly.' '.$required.'>'.$value.'</textarea>';
                    $html .= $tips;
                }
                break;

            case 'data':
                // data类型控件需要渲染成占据一整行的表格
                $html .= self::table_render();
                break;

            default:
                if ($view) {
                    $html .= '<div class="uk-text-muted">' . $value . '</div>';
                } else {
                    $html .= '<input type="text" class="uk-input' . $readonlyClass . '" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.'>';
                    $html .= $tips;
                }
        }

        return $html;
    }

    /**
     * 渲染数据表格
     * 
     * @return string 渲染后的表格HTML
     */
    private static function table_render() {
        // 这里添加表格渲染逻辑
        // 临时返回一个空表格作为占位符
        return '<div class="uk-overflow-auto"><table class="uk-table uk-table-divider"><thead><tr><th>暂无数据</th></tr></thead><tbody><tr><td>请配置数据源</td></tr></tbody></table></div>';
    }

    /**
     * 从实体列表中获取隐藏字段的值
     * 
     * @param string $selectedValue 选中的值
     * @param array $entities 实体列表
     * @param string $label 标签值
     * @return string 隐藏字段的值
     */
    private static function getHiddenValueFromEntities($selectedValue, $entities, $label) {
        $hiddenValue = '';

        // 如果有选中的实体ID，查找对应的标签
        if (!empty($selectedValue)) {
            foreach ($entities as $entity) {
                if ($entity['id'] === $selectedValue) {
                    $hiddenValue = htmlspecialchars($entity['name']);
                    break;
                }
            }
        }

        // 如果没有找到对应的标签，但有选中的值，使用选中的值作为标签
        if (empty($hiddenValue) && !empty($selectedValue)) {
            $hiddenValue = htmlspecialchars($selectedValue);
        }

        // 如果仍然没有hiddenValue，但有标签值，则使用它
        if (empty($hiddenValue) && !empty($label)) {
            $hiddenValue = htmlspecialchars($label);
        }

        // 如果仍然没有hiddenValue，且没有选中的值，则使用第一个选项的标签作为默认值
        if (empty($hiddenValue) && empty($selectedValue) && !empty($entities)) {
            $hiddenValue = htmlspecialchars($entities[0]['name']);
        }

        return $hiddenValue;
    }

    /**
     * 从选项列表中获取隐藏字段的值
     * 
     * @param string $selectedValue 选中的值
     * @param array $options 选项列表
     * @param string $label 标签值
     * @return string 隐藏字段的值
     */
    private static function getHiddenValueFromOptions($selectedValue, $options, $label) {
        $hiddenValue = '';

        // 如果有选中的值，查找对应的标签
        if (!empty($selectedValue)) {
            foreach ($options as $opt) {
                $opt = trim($opt);
                if ($opt === '') continue;

                // 解析选项值和标签
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

        // 如果仍然没有hiddenValue，但有标签值，则使用它
        if (empty($hiddenValue) && !empty($label)) {
            $hiddenValue = htmlspecialchars($label);
        }

        // 如果仍然没有hiddenValue，且没有选中的值，则使用第一个选项的标签作为默认值
        if (empty($hiddenValue) && empty($selectedValue) && !empty($options)) {
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

        return $hiddenValue;
    }

    /**
     * 渲染多个表单字段
     * 
     * @param array $config 表单配置
     * @param array $data 实体数据
     * @param array $val 表单提交的值
     * @param array $errors 错误信息
     * @param bool|string $view 视图类型
     * @return string 渲染后的HTML
     */
    public static function renderFormFields($config, $data, $val = [], $errors = [], $view = false) {
        $html = '';
        $fields = $config ?? [];

        foreach ($fields as $id => $field) {
            $value = $data[$id] ?? '';
            $error = $errors[$id] ?? '';
            $html .= self::renderFormField($id, $field, $data, $val, $error, $view);
        }

        return $html;
    }
}
?>