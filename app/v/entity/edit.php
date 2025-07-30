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
  ?>
  <form method="post" class="uk-form-stacked" enctype="multipart/form-data">
     <div class="uk-container uk-padding uk-border-rounded uk-form uk-form-stacked"><div class="uk-grid uk-child-width-1-1" uk-grid>
      <input type="hidden" name="name" value="<?= isset($entityData['name']) ? htmlspecialchars($entityData['name']) : (isset($val['data']['name']) ? htmlspecialchars($val['data']['name']) : '') ?>">
    <input type="hidden" name="type" value="<?= htmlspecialchars($entity_type) ?>">
    <?php echo FormRenderer::renderFormFields($item, $entityData, $val, $err); ?>
  </div>
    <input type="hidden" name="tenant_id" value="<?=isset($entity['tenant_id']) ? $entity['tenant_id'] : $_SESSION['route_tenant_id']?>" />
        <input type="hidden" name="id" value="<?=isset($entity['id']) ? $entity['id'] : ''?>" />
    <div>
      <button type="submit" class="uk-button uk-button-primary uk-margin-right"><?=isset($entity['id']) ? '更新' : '创建'?></button>
      <a href="<?=BASE?>/entity/" class="uk-button">返回</a>
    </div>
    </div></div>
  </form>