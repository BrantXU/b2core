
<?php if(isset($err['general'])): ?>
    <div class="uk-alert uk-alert-danger"><?=$err['general']?></div>
<?php endif; ?>
  <div class="uk-padding uk-form uk-form-stacked">
    <div class="uk-grid uk-grid-small" uk-grid id="entity-show-container">
      <!-- 表单字段将由前端JavaScript渲染 -->
      <div class="uk-grid" id="form-fields-container"></div>
      <div class="uk-width-1-1 uk-padding-small uk-grid-margin uk-first-column">
        <a href="<?=tenant_url($entity['type'].'/view/'.$action.'/'.$entity['id'].'/edit')?>" class="uk-button uk-button-primary">编辑</a>
        <a href="<?=tenant_url($entity['type'].'/view/'.$action.'/')?>" class="uk-button">返回列表</a>
        <a href="<?=tenant_url($entity['type'].'/view/'.$action.'/'.$entity['id'].'/hist')?>" class="uk-button">修订记录</a>
      </div>
    </div>
  </div>

  <script>
    // 传递数据到前端
    window.entityData = <?=json_encode($entityData, JSON_UNESCAPED_UNICODE)?>;
    window.formConfig = <?=json_encode($item, JSON_UNESCAPED_UNICODE)?>;
    window.entityInfo = <?=json_encode([
        'id' => $entity['id'],
        'type' => $entity['type'],
        'action' => $action
    ], JSON_UNESCAPED_UNICODE)?>;
    document.addEventListener('DOMContentLoaded', function() {
      // 使用WidgetRenderer渲染表单字段
      const formFieldsHtml = WidgetRenderer.renderFormFields(
        window.formConfig, 
        window.entityData, 
        {}, 
        {}, 
        true // view模式
      );
      
      document.getElementById('form-fields-container').innerHTML = formFieldsHtml;
    });
  </script>
