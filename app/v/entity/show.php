
<?php if(isset($err['general'])): ?>
    <div class="uk-alert uk-alert-danger"><?=$err['general']?></div>
<?php endif; ?>
  <div class="uk-padding uk-form uk-form-stacked">
    <div class="uk-grid uk-child-width-1-1" uk-grid>
      <?php echo FormRenderer::renderFormFields($item, $entityData, [], [], true); ?>
      <div class="uk-width-1-1 uk-padding-small uk-grid-margin uk-first-column">
        <a href="<?=tenant_url($entity['type'].'/view/'.$action.'/'.$entity['id'].'/edit')?>" class="uk-button uk-button-primary">编辑</a>
        <a href="<?=tenant_url($entity['type'].'/view/'.$action.'/')?>" class="uk-button">返回列表</a>
        <a href="<?=tenant_url($entity['type'].'/view/'.$action.'/'.$entity['id'].'/hist')?>" class="uk-button">修订记录</a>
      </div>
    </div>
  </div>
