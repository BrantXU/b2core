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
