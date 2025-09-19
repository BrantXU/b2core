<script src="/static/js/Sortable.min.js"></script>
<script src="/static/js/kanbanRender.js"></script>
<div id="kanbanApp"></div>

<script>
// 等待DOM加载完成
document.addEventListener('DOMContentLoaded', function() {
    // 准备看板数据
    const kanbanData = <?= json_encode($entities, JSON_UNESCAPED_UNICODE) ?>;
    // 配置选项
    const kanbanConfig = {
        entities: kanbanData,
        item: <?= json_encode($item, JSON_UNESCAPED_UNICODE) ?>,
        statusFields: <?= json_encode($statusFields, JSON_UNESCAPED_UNICODE) ?>,
        selectedStatusField: '<?= $selectedStatusField ?>',
        entityType: '<?= $entity_type ?>',
        baseUrl: '<?= $base_url ?>',
        objectMenuKey: '<?= $object_menu_key ?>',
        objectId: '<?= $object_id ?>'
    };
    
    // 初始化看板渲染器
    try {
        const kanban = new KanbanRender('kanbanApp', kanbanConfig);
        
        // 保存看板实例到全局变量，便于调试
        window.kanbanInstance = kanban;
        
    } catch (error) {
        console.error('看板初始化失败:', error);
        
        // 显示错误信息
        const container = document.getElementById('kanbanApp');
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