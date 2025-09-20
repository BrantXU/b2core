<div class="uk-grid uk-grid-divider" uk-grid>
   <!-- 左侧控件列表 -->
   <div class="uk-width-3-4@m">
    <div class="uk-margin uk-flex uk-flex-between uk-flex-middle">
      <div>
        <button class="uk-button uk-button-primary" onclick="updateConfig()">更新配置</button>
        <button class="uk-button uk-button-success" onclick="addWidget()">添加控件</button>
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
   <div class="uk-width-1-4@m">
    <div id="propertyPanel">
      <div class="uk-alert uk-alert-primary">
        <p>请从左侧选择一个控件来编辑其属性</p>
      </div>
    </div>
  </div>
</div>

<!-- 引入控件编辑器JS -->
<script src="/static/js/layoutEditor.js"></script>
<script>
  config = <?=$config['value']?:'{"layout":[]}'; ?>;
  // 初始化控件编辑器
  document.addEventListener('DOMContentLoaded', function() {
    layoutEditor = new LayoutEditor({
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