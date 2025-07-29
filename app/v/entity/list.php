<div>
  <h2>实体列表</h2>
  
  <div style="margin-bottom: 20px;">
    <a href="<?=tenant_url($entity_type.'/create/')?>" class="pure-button pure-button-primary">创建实体</a>
  </div>
  
  <?php if(isset($entities) && !empty($entities)): ?>
    <table class="pure-table pure-table-horizontal" style="width: 100%;">
      <?php
      // 定义表头翻译映射
      $headerMap = [
        'id' => 'ID',
        'tenant_id' => '租户ID',
        'name' => '名称',
        'type' => '类型',
        'description' => '描述',
        'created_at' => '创建时间',
        'updated_at' => '更新时间'
      ];
      // 从$this->item获取表头字段
      $fields = [];
      if (!empty($item) && is_array($item)) {
        $fields = [];
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
        <?php foreach ($entities as $entity): ?>
          <?php 
          $entityData = [];
          if (!empty($entity['data'])) {
            $entityData = json_decode($entity['data'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
              $entityData = [];
            }
          }
          ?>
          <tr>
            <?php foreach ($fields as $fieldName => $fieldConfig):              ?>
                <td><?php
                  if (is_array($entityData) && is_array($fieldConfig)) {
                    // 使用配置中的field属性作为数据键，缺失时回退到字段名
                    $key = isset($fieldConfig['field']) && is_string($fieldConfig['field']) ? $fieldConfig['field'] : $fieldName;
                    if (isset($entityData[$key])) {
                      $value = $entityData[$key];
                      if (is_scalar($value)) {
                        echo htmlspecialchars((string)$value);
                      } else {
                        echo '[复杂数据]';
                      }
                    }
                  }
                ?></td>
            <?php endforeach; ?>
            <td>
              <a href="<?=tenant_url($entity_type.'/edit')?>?id=<?=$entity['id']?>" class="pure-button pure-button-small">编辑</a>
              <a href="<?=tenant_url($entity_type.'/delete')?>?id=<?=$entity['id']?>" class="pure-button pure-button-small button-error" onclick="return confirm('确定要删除此实体吗？')">删除</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="pure-alert">暂无实体数据</div>
  <?php endif; ?>
</div>