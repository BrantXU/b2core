<div>
  <h2>实体列表</h2>
  
  <div style="margin-bottom: 20px;">
    <a href="<?=BASE?>/entity/create/" class="pure-button pure-button-primary">创建实体</a>
  </div>
  
  <?php if(isset($entities) && !empty($entities)): ?>
    <table class="pure-table pure-table-horizontal" style="width: 100%;">
      <thead>
        <tr>
          <th>ID</th>
          <th>租户ID</th>
          <th>名称</th>
          <th>类型</th>
          <th>描述</th>
          <th>创建时间</th>
          <th>更新时间</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($entities as $entity): ?>
          <tr>
            <td><?=$entity['id']?></td>
            <td><?=$entity['tenant_id']?></td>
            <td><?=$entity['name']?></td>
            <td><?=$entity['type']?></td>
            <td><?=isset($entity['description']) ? htmlspecialchars($entity['description']) : ''?></td>
            <td><?=$entity['created_at']?></td>
            <td><?=$entity['updated_at']?></td>
            <td>
              <a href="<?=BASE?>/entity/edit?id=<?=$entity['id']?>" class="pure-button pure-button-small">编辑</a>
              <a href="<?=BASE?>/entity/delete?id=<?=$entity['id']?>" class="pure-button pure-button-small button-error" onclick="return confirm('确定要删除此实体吗？')">删除</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="pure-alert">暂无实体数据</div>
  <?php endif; ?>
</div>