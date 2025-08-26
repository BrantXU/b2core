<?php
/**
 * 渲染下拉选择框
 *
 * @param string $value 当前选中的值
 * @param array $config 配置数组，包含数据源、类型、必填等信息
 * @param string $label 选择框的标签
 * @param bool $view 是否为查看模式
 * @return string 渲染后的HTML字符串
 */

function render_select($value,$config,$label,$view) {
    $hiddenName = $config['id'].'_label';// ?? '';
    $html = '';

    $tips = $config['tips'] ?? '';
    $required = $config['required'] ?? '';
    $readonly = $config['readonly'] ?? '';
    $readonlyClass = $readonly ? ' uk-form-readonly' : '';
    $field = $config['id'] ?? '';
    $props = $config['props'] ?? [];
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
        $html .= '<select class="uk-select uk-width-1-1' . $readonlyClass . '" name="data['.$field.']" '.$required
            .' onchange="document.getElementsByName(\''.$hiddenName.'\')[0].value = this.options[this.selectedIndex].text">';
        $html .= $tips;

        if ($sourceType === 'mod' && !empty($dataSource)) {
            // 引入entity模型
            $entityModel = new entity_m();
            // 获取指定类型的实体数据
            $entities = $entityModel->getAllEntities($dataSource);
            // 查找选中实体的标签
            $hiddenValue = getHiddenValueFromEntities($selectedValue, $entities, $label);
            
            foreach ($entities as $entity) {
                $entityId = htmlspecialchars($entity['id']);
                $entityName = htmlspecialchars($entity['name']);
                $isSelected = (isset($value) && $value === $entityId) ? 'selected' : '';
                $html .= '<option value="'.$entityId.'" '.$isSelected.'>'.$entityName.'</option>';
            }
        } else {
            // 处理普通选项
            $options = !empty($dataSource) ? explode("\n", $dataSource) : [];
            // 查找选中选项的标签
            $hiddenValue = getHiddenValueFromOptions($selectedValue, $options, $label);

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

        // 添加隐藏字段
        $html .= '<input type="hidden" name="data['.$hiddenName.']" value="'.$hiddenValue.'">';

        // 添加选择框
        $html .= '</select>';
    }
    return $html;
}

    /**
     * 从实体列表中获取隐藏字段的值
     * 
     * @param string $selectedValue 选中的值
     * @param array $entities 实体列表
     * @param string $label 标签值
     * @return string 隐藏字段的值
     */
function getHiddenValueFromEntities($selectedValue, $entities, $label) {
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
function getHiddenValueFromOptions($selectedValue, $options, $label) {
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