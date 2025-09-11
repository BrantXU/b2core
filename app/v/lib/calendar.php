<?php
// FormRenderer类在app/lib/render_form.php中定义，没有使用命名空间

// 获取实体数据（通过view函数传递的参数）
$entities = $entities ?? [];

// 获取表头字段（通过view函数传递的参数）
$item = $item ?? [];

// 获取配置（通过view函数传递的参数）
$config = $config ?? [];

// 获取租户ID（通过view函数传递的参数）
$tenantId = $tenantId ?? 'default';

// 获取实体类型（通过view函数传递的参数）
$entityType = $entity_type ?? '';

// 获取日期字段信息
$dateFields = $date_fields ?? [];
$selectedDateField = $selected_date_field ?? '';

// 创建日历容器
$calendarId = 'calendar_' . rand(1000, 9999);

// 生成JS数组数据
$jsData = [];
$fields = [];

// 获取要显示的字段
foreach ($item as $fieldName => $fieldConfig) {
    if (isset($fieldConfig['listed']) && $fieldConfig['listed']) {
        $fields[$fieldName] = $fieldConfig;
    }
}

// 生成数据数组
foreach ($entities as $entity) {
    $data = json_decode($entity['data'],true);
    $rowData = ['id' => $entity['id']];
    $cells = [];
    foreach ($fields as $fieldName => $fieldConfig) {
        $label =  isset($data[$fieldName.'_label'])?$data[$fieldName.'_label']:''; 
        $cellValue = FormRenderer::item($data[$fieldName] ?? '', $fieldConfig,$label, $data);
        $rowData[$fieldName] = $cellValue;
        $cells[] = $cellValue;
    }
    // 添加cells属性供CalendarRender使用
    $rowData['cells'] = $cells;
    $jsData[] = $rowData;
}

?>

<?php if(isset($entities) && !empty($entities)): ?>
    <div id="<?= $calendarId ?>-container">
        <?php if (!empty($dateFields) && count($dateFields) > 1): ?>
            <div class="uk-margin">
                <label class="uk-form-label">选择日期字段：</label>
                <select class="uk-select uk-form-width-medium" id="<?= $calendarId ?>-date-field">
                    <?php foreach ($dateFields as $fieldName => $fieldConfig): ?>
                        <option value="<?= $fieldName ?>" <?= $fieldName === $selectedDateField ? 'selected' : '' ?>>
                            <?= $fieldConfig['label'] ?? $fieldName ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        
        <div id="<?= $calendarId ?>" class="calendar-container"></div>
    </div>
    
    <script>
    // 定义日历数据和配置
    if (typeof window.calendarConfigs === 'undefined') {
        window.calendarConfigs = [];
    }
    
    window.calendarConfigs.push({
        containerId: '<?= $calendarId ?>',
        data: <?= json_encode($jsData, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG) ?>,
        fields: <?= json_encode($fields, JSON_UNESCAPED_UNICODE) ?>,
        dateFields: <?= json_encode($dateFields, JSON_UNESCAPED_UNICODE) ?>,
        selectedDateField: '<?= $selectedDateField ?>',
        baseUrl: '<?= tenant_url($entityType.'/') ?>',
        pageSize: <?= $config['pageSize'] ?? 10 ?>
    });
    </script>
<?php else: ?>
    <div class="uk-alert uk-alert-warning">暂无实体数据</div>
<?php endif; ?>