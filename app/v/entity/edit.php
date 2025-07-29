<div>
  <h2><?=$page_title?></h2>
  <?php if(isset($err['general'])): ?>
    <div class="pure-alert pure-alert-error"><?=$err['general']?></div>
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
  <form method="post" class="pure-form pure-form-stacked" enctype="multipart/form-data">
    <input type="hidden" name="name" value="<?= isset($entityData['name']) ? htmlspecialchars($entityData['name']) : (isset($val['data']['name']) ? htmlspecialchars($val['data']['name']) : '') ?>">
    <input type="hidden" name="type" value="<?= htmlspecialchars($entity_type) ?>">
    <?php echo FormRenderer::renderFormFields($item, $entityData, $val, $err); ?>
    
    <div>
      <label>租户</label>
      <select name="tenant_id">
        <?php if(isset($tenants) && !empty($tenants)): ?>
          <?php foreach ($tenants as $tenant): ?>
            <option value="<?=$tenant['id']?>" <?=isset($val['tenant_id']) && $val['tenant_id'] == $tenant['id'] ? 'selected' : (isset($entity['tenant_id']) && $entity['tenant_id'] == $tenant['id'] ? 'selected' : '')?>><?=$tenant['name']?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </div>
    
    <input type="hidden" name="id" value="<?=isset($entity['id']) ? $entity['id'] : ''?>" />

    <div>
      <button type="submit" class="pure-button pure-button-primary"><?=isset($entity['id']) ? '更新' : '创建'?></button>
      <a href="<?=BASE?>/entity/" class="pure-button">返回</a>
    </div>
  </form>
</div>