<?php if(isset($err['general'])): ?>
    <div class="uk-alert uk-alert-danger"><?=$err['general']?></div>
  <?php endif; ?>
  <?php 
  require_once APP . 'lib/form_render.php';
  $entityData = [];
  if (isset($entity['data'])) {
    $entityData = json_decode($entity['data'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      $entityData = [];
    }
  }
  // 确保name和type字段在表单提交时能正确传递
  $nameValue = isset($entityData['name']) ? htmlspecialchars($entityData['name']) : (isset($val['data']['name']) ? htmlspecialchars($val['data']['name']) : '');
  $typeValue = htmlspecialchars($entity_type);
  ?>
  <form method="post" class="uk-padding uk-form uk-form-stacked" enctype="multipart/form-data">
    <div class="uk-grid uk-child-width-1-1" uk-grid>
      <?php echo FormRenderer::renderFormFields($item, $entityData, $val, $err); ?>
      <div class="uk-width-1-1 uk-padding-small uk-grid-margin uk-first-column" >
        <input type="hidden" name="redirect_url" value="<?=isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : ''?>">
        <button type="submit" class="uk-button uk-button-primary uk-margin-right"><?=isset($entity['id']) ? '更新' : '创建'?></button>
        <a href="<?=BASE?>/entity/" class="uk-button">返回</a>
      </div>
    </div>
  </form>