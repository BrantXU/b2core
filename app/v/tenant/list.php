<div>
  <h2>租户列表</h2>
  
  <div style="margin-bottom: 20px;">
    <a href="<?=tenant_url('tenant/create/')?>" class="pure-button pure-button-primary">创建租户</a>
  </div>
  
  <?php if(isset($tenants) && !empty($tenants)): ?>
    <table class="uk-table uk-table-striped uk-table-hover">
      <thead>
        <tr>
          <th>名称</th>
          <th>状态</th>
          <th>创建时间</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tenants as $tenant): ?>
          <tr>
            <td><?=$tenant['name']?></td>
            <td><?=($tenant['status'] == 1) ? '启用' : '禁用'?></td>
            <td><?=$tenant['created_at']?></td>
            <td>
              <a href="<?=tenant_url('tenant/enter')?>/<?=$tenant['id']?>" class="uk-button uk-button-small">进入</a>
              <a href="<?=tenant_url('tenant/edit')?>/<?=$tenant['id']?>" class="uk-button uk-button-small">编辑</a>
              <a href="<?=tenant_url('tenant/delete')?>/<?=$tenant['id']?>" class="uk-button uk-button-small button-error" onclick="return confirm('确定要删除此租户吗？')">删除</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="pure-alert">暂无租户数据</div>
  <?php endif; ?>
</div>