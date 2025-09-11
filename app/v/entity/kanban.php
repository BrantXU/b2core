<?php if (!empty($statusFields)): ?>
<div class="uk-margin">
    <label class="uk-form-label" for="statusFieldSelect">状态字段:</label>
    <select class="uk-select uk-form-width-small" id="statusFieldSelect">
        <?php foreach ($statusFields as $fieldName => $fieldConfig): ?>
            <option value="<?= $fieldName ?>" <?= $fieldName === $selectedStatusField ? 'selected' : '' ?>>
                <?= $fieldConfig['label'] ?? $fieldName ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<?php endif; ?>

<div id="kanbanContainer" class="kanban-view"></div>
<script src="/static/js/kanbanRender.js"></script>

<script>
// 等待DOM加载完成
document.addEventListener('DOMContentLoaded', function() {
    // 准备看板数据
    const kanbanData = <?= json_encode($entities, JSON_UNESCAPED_UNICODE) ?>;
    
    // 配置选项
    const kanbanOptions = {
        data: kanbanData,
        categoryField: '<?= $selectedStatusField ?>',
        defaultCategories: ['待处理', '进行中', '已完成'],
        showSearch: true,
        showCreate: true,
        searchPlaceholder: '搜索看板内容...',
        enableDrag: true,
        enableCache: true,
        cacheKey: 'kanban_<?= $entity_type ?>_state',
        fields: <?= json_encode($item['fields'] ?? [], JSON_UNESCAPED_UNICODE) ?>
    };
    
    // 初始化看板渲染器
    try {
        const kanban = new KanbanRender('kanbanContainer', kanbanOptions, '<?= $base_url ?>');
        
        // 绑定状态字段切换事件
        const statusFieldSelect = document.getElementById('statusFieldSelect');
        if (statusFieldSelect) {
            statusFieldSelect.addEventListener('change', function() {
                kanban.setCategoryField(this.value);
            });
        }
        
        // 保存看板实例到全局变量，便于调试
        window.kanbanInstance = kanban;
        
    } catch (error) {
        console.error('看板初始化失败:', error);
        
        // 显示错误信息
        const container = document.getElementById('kanbanContainer');
        container.innerHTML = `
            <div class="uk-alert uk-alert-danger">
                <h4>看板加载失败</h4>
                <p>${error.message}</p>
                <p>请检查浏览器控制台获取更多信息。</p>
            </div>
        `;
    }
});
</script>