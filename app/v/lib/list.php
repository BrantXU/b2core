<div>
  <a href="<?= tenant_url($entity_type.'/export/') ?>" class="uk-button uk-button-secondary">导出数据</a>
  <a href="<?= tenant_url($entity_type.'/import/') ?>" class="uk-button uk-button-primary">导入数据</a>
  <a href="<?=tenant_url($entity_type.'/add/'.$opt)?>" class="uk-button uk-button-success">创建</a>
</div>

<?php if(isset($entities) && !empty($entities)): ?>
    <table class="uk-table uk-table-striped uk-table-hover" id="table" >
      <?php
      // 从item获取表头字段
      $fields = [];
      if (!empty($item) && is_array($item)) {
        foreach ($item as $key => $config) {
          if (isset($config['listed']) && $config['listed'] == 1) {
            $fields[$key] = $config;
          }
        }
      }
      ?>
      <thead>
        <tr>
          <?php foreach ($fields as $field): ?>
            <th><?= $field['name'] ?></th>
          <?php endforeach; ?>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($entities as $ent):
          $entity = json_decode($ent['data'],true);
          $entity['id'] = $ent['id'];
          ?>
          <tr class="clickable-row" data-id="<?= $entity['id'] ?>">
            <?php foreach ($fields as $fieldName => $fieldConfig): ?>
              <td>
              <?=isset($entity[$fieldName])?FormRenderer::item($entity[$fieldName],$fieldConfig,isset($entity[$fieldName.'_label'])?$entity[$fieldName.'_label']:''):''?>
              </td>
            <?php endforeach; ?>
            <td>
              <a href="<?=tenant_url($entity_type.'/edit')?>/<?=$entity['id']?>" class="uk-button uk-button-small">编辑</a>
              <a href="<?=tenant_url($entity_type.'/delete')?>/<?=$entity['id']?>" class="uk-button uk-button-small uk-button-danger" onclick="return confirm('确定要删除此实体吗？')">删除</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="uk-alert uk-alert-warning">暂无实体数据</div>
  <?php endif; ?>
  <div class="uk-flex uk-flex-between uk-margin">
    <div>
      <a href="<?= tenant_url($entity_type.'/export/') ?>" class="uk-button uk-button-secondary">导出数据</a>
      <a href="<?= tenant_url($entity_type.'/import/') ?>" class="uk-button uk-button-primary">导入数据</a>
      <a href="<?=tenant_url($entity_type.'/add/')?>" class="uk-button uk-button-success model-create">创建</a>
    </div>
  </div>

  <script>
    // 添加表格行点击事件
    document.addEventListener('DOMContentLoaded', function() {
      const rows = document.querySelectorAll('.clickable-row');
      rows.forEach(row => {
        row.addEventListener('click', function(e) {
          // 检查点击的是否是链接或按钮，如果是则不触发行点击事件
          if (e.target.closest('a, button')) {
            return;
          }
          const id = this.getAttribute('data-id');
          window.location.href = '<?=tenant_url( $entity_type.'/view/about/')?>' + id;
        });
        // 添加悬停效果
        row.style.cursor = 'pointer';
      });
    });
  </script>