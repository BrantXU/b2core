<div>
  <h2>配置列表</h2>
  
  <div class="uk-margin">
    <a href="<?=tenant_url('config/edit/') ?>?action=create" class="uk-button uk-button-primary">创建配置</a>
  </div>
  
  <?php if(isset($configs) && !empty($configs)): ?>
    <table class="uk-table uk-table-divider uk-table-striped" style="width: 100%;">
      <thead>
        <tr>
          <th>ID</th>
          <th>租户ID</th>
          <th>键名</th>
          <th>值</th>
          <th>类别</th>
          <th>描述</th>
          <th>创建时间</th>
          <th>更新时间</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($configs as $config): ?>
          <tr>
            <td><?=$config['id']?></td>
            <td><?=isset($config['tenant_id']) ? $config['tenant_id'] : 'default'?></td>
            <td><?=$config['key']?></td>
            <td><?=substr($config['value'], 0, 50)?><?=strlen($config['value']) > 50 ? '...' : ''?></td>
            <td><?=$config['config_type']?></td>
            <td><?=$config['description']?></td>
            <td><?=$config['created_at']?></td>
            <td><?=$config['updated_at']?></td>
            <td>
              <a href="<?=tenant_url('config/edit')?>?id=<?=$config['id']?>" class="uk-button uk-button-small uk-button-default">编辑</a>
              <a href="<?=tenant_url('config/delete')?>?id=<?=$config['id']?>" class="uk-button uk-button-small uk-button-danger" onclick="return confirm('确定要删除此配置吗？')">删除</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="uk-alert uk-alert-warning">暂无配置数据</div>
  <?php endif; ?>
</div>