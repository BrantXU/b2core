<div id="calendar-container"></div>
<script>
// 初始化日历渲染器
document.addEventListener('DOMContentLoaded', function() {
    const calendarOptions = {
        data: <?= json_encode($entities ?? []) ?>,
        fields: <?= json_encode($item['fields'] ?? []) ?>,
        dateFields: <?= json_encode($dateFields ?? []) ?>,
        selectedDateField: '<?= $selectedDateField ?? "" ?>',
        onEventClick: function(event, item) {
            UIkit.modal.alert(`事件详情：${item.Name}\n日期：${item.StartDate}\n状态：${item.Status}`);
        },
        onDateClick: function(date) {
            UIkit.modal.alert(`点击日期：${date.toLocaleDateString()}`);
        }
    };
    const calendar = new CalendarRender('calendar-container', calendarOptions, '<?= tenant_url("") ?>');
    calendar.init();
});
</script>