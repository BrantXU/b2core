<?php
/**
 * FormRenderer类 - 表单渲染工具
 * 用于根据配置和数据渲染不同类型的表单字段
 */
require_once(APP.'lib/render_select.php');

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
            $label = isset($entityData[$field.'_label']) ? $entityData[$field.'_label'] : null;
            $compare = isset($entityData[$field.'_compare']) ? $entityData[$field.'_compare'] : null;

            // 调用渲染控件的方法
            $controlConfig = [
                'type' => $config['type'],
                'id' => $field,
                'readonly' => !empty($readonly),
                'required' => !empty($required),
                'tips' => $tips,
                'view' => $view,
                'props' => $config['props'] ?? []
            ];
            $html .= self::renderControl($value, $controlConfig, $label , $compare);

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
        $config['view'] = true;
        return self::renderControl($value, $config, $label);
    }

    /**
     * 仅负责渲染控件部分
     * 
     * @param mixed $value 字段值
     * @param array $config 控件配置
     * @param string $label 标签值
     * @return string 渲染后的HTML
     */
    public static function renderControl($value, $config, $label,$compare = null) {
        $type = $config['type'];
        $field = $config['id'] ?? '';
        $readonly = isset($config['readonly']) && $config['readonly'] ? 'readonly' : '';
        $required = isset($config['required']) && $config['required'] ? 'required' : '';
        $tips = isset($config['tips']) ? '<small class="help-text">'.htmlspecialchars($config['tips']).'</small>' : '';
        $view = $config['view'] ?? false;
        $props = $config['props'] ?? [];
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
                $html .= render_select($value, $config, $label, $view);
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
            case 'puretext':
                $html .= '<div class="uk-text-muted">' .$props['tpl'] . '</div>';
                break;
            case 'data':
                //print_r($config['props']);
                $tr = new TableRenderer();
                $tr->item = load('m/entity_m')->getItem($config['props']['data_source']);
                $conditions = [];
                if(isset($config['filter'])) {
                    $filter = explode(':',$config['filter']);
                    $tr_filter = array($filter[0] => $filter[1]);
                    foreach($tr_filter as $key => $value) {
                        // Store the key and value separately for proper escaping in the model
                        $value = $value=='eid'?$opt['eid']:$value;
                        $conditions["json_filter_{$key}"] = $value;
                    }
                }
                
                $tr->data =  load('m/entity_m')->getPage(1,200,$conditions);
                $tr->entity_type = $config['props']['data_source'];
                $html.= $tr->render();
                break;

            default:
                if ($view) {
                    $html .= '<div class="uk-text-muted">' . $value . '</div>';
                } else {
                    $html .= '<input type="text" class="uk-input' . $readonlyClass . '" name="data['.$field.']" value="'.$value.'" '.$readonly.' '.$required.'>';
                    $html .= $tips;
                }
        }
        if($compare) $html .= ' <div class="uk-text-muted"><del>'.$compare.'</del></div>';
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