
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

  <div class="uk-padding uk-form uk-form-stacked">
    <div class="uk-grid uk-child-width-1-1" uk-grid>
      <?php echo FormRenderer::renderFormFields($item, $entityData, [], [], true); ?>
      <div class="uk-margin-top">
        <a href="<?=BASE?>/entity/" class="uk-button">返回列表</a>
      </div>
    </div>
  </div>
