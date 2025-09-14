<div class="uk-grid uk-grid-divider" uk-grid>
   <!-- 左侧控件列表 -->
   <div class="uk-width-2-3@m">
    <div class="uk-margin uk-flex uk-flex-between uk-flex-middle">
      <div>
        <button class="uk-button uk-button-primary" onclick="updateConfig()">更新配置</button>
        <button class="uk-button uk-button-success" onclick="addWidget()" >模块属性</button>
        <button class="uk-button uk-button-success" onclick="addWidget()" >添加控件</button>
        <button type="button" class="uk-button uk-button-default" onclick="deleteWidget()">删除控件</button>
         <a href="<?=tenant_url('config/yaml/'.$config['id']) ?>" class="uk-button  uk-button-success"  >专家模式</a> 
         <a href="<?=tenant_url('config/hist/'.$config['id']) ?>" class="uk-button  uk-button-success"  >历史版本</a>
      </div>
    </div>
    <h3>控件列表</h3>
    <div id="widgetList" class="uk-grid uk-grid-small uk-child-width-1-1" uk-grid>
      <div class="uk-width-1-1">
        <div class="uk-alert uk-alert-warning">点击上方添加控件</div>
      </div>
    </div>
  </div>
  <!-- 右侧属性菜单 -->
   <div class="uk-width-1-3@m">
    <div id="propertyPanel">
      <div class="uk-alert uk-alert-primary">
        <p>请从左侧选择一个控件来编辑其属性</p>
      </div>
    </div>
  </div>
</div>

<!-- 属性编辑表单模板 -->
<template id="propertyTemplate">
  <div class="uk-card uk-card-default uk-card-body">
    <h3 class="uk-card-title">{widgetName} 属性</h3>
    <form id="propertyForm" class="uk-form-stacked">
      <div class="uk-margin">
        <label class="uk-form-label">控件ID</label>
        <div class="uk-form-controls">
          <input class="uk-input" type="text" name="id" value="{widgetId}" readonly>
        </div>
      </div>
      <div class="uk-margin">
        <label class="uk-form-label">控件名称</label>
        <div class="uk-form-controls">
          <input class="uk-input" type="text" name="name" value="{name}" required>
        </div>
      </div>
      <div class="uk-margin">
        <label class="uk-form-label">控件类型</label>
        <div class="uk-form-controls">
          <select class="uk-select" name="type" onchange="updatePropertyFields()">
            <option value="text">文本输入</option>
            <option value="number">数字输入</option>
            <option value="datepicker">日期选择</option>
            <option value="select">下拉选择</option>
            <option value="textarea">文本区域</option>
            <option value="section">分区标题</option>
            <option value="tab">标签页</option>
          </select>
        </div>
      </div>
      <div class="uk-margin">
        <label class="uk-form-label">列宽</label>
        <div class="uk-form-controls">
          <select class="uk-select" name="width">
            <option value="1">1列</option>
            <option value="2">2列</option>
            <option value="3">3列</option>
            <option value="4">4列</option>
          </select>
        </div>
      </div>
      <div class="uk-margin">
        <label class="uk-form-label">是否在列表中显示</label>
        <div class="uk-form-controls">
          <input type="checkbox" name="listed" value="1" {listedChecked}>
        </div>
      </div>
      <div class="uk-margin">
        <label class="uk-form-label">是否必填</label>
        <div class="uk-form-controls">
          <input type="checkbox" name="required" value="1" {requiredChecked}>
        </div>
      </div>
      <!-- 动态属性区域 -->
      <div id="dynamicProperties">
        <!-- 不同类型控件的特定属性将在这里动态生成 -->
      </div>
      <div class="uk-margin">
        <button type="submit" class="uk-button uk-button-primary">保存属性</button>
      </div>
    </form>
  </div>
</template>
<!-- 引入控件编辑器JS -->
<script src="/static/js/widgetEditor.js"></script>
<script>
  config = <?=$config['value']?>;
  // 初始化控件编辑器
  document.addEventListener('DOMContentLoaded', function() {
    widgetEditor = new WidgetEditor({
        configId: '<?=$config['id']?>',
        tenantId: '<?=$tenant_id ?? "default"?>'
    });
  });
</script>
<style>
.uk-input {
  width: 90%;
}
.widget-item {
  cursor: pointer;
  transition: all 0.2s;
}
.widget-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.uk-sortable-handle {
  cursor: move;
}
#propertyPanel {
  position: sticky;
  top: 20px;
}
</style>