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

// 创建表格
$tableId = 'entityTable_' . rand(1000, 9999);

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
        // 添加cells属性供TableRender使用
        $rowData['cells'] = $cells;
        $jsData[] = $rowData;
    }

?>

<?php if(isset($entities) && !empty($entities)): ?>
    <div id="<?= $tableId ?>-container"></div>
    <script>
    // 定义表格数据和配置，但不直接渲染
    // 这些数据将在页面加载完成后由template.php中的统一脚本处理
    if (typeof window.tableConfigs === 'undefined') {
        window.tableConfigs = [];
    }
    
    window.tableConfigs.push({
        containerId: '<?= $tableId ?>-container',
        data: <?= json_encode($jsData, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG) ?>,
        fields: <?= json_encode($fields, JSON_UNESCAPED_UNICODE) ?>,
        baseUrl: '<?= tenant_url($entityType.'/') ?>',
        pageSize: <?= $config['pageSize'] ?? 10 ?>,
        searchable: <?= $config['searchable'] ?? 'true' ?>
    });
    </script>
<?php else: ?>
    <div class="uk-alert uk-alert-warning">暂无实体数据</div>
<?php endif; ?>
