<div>
  <h2>租户列表</h2>
  
  <div style="margin-bottom: 20px;">
    <a href="<?=tenant_url('tenant/create/')?>" class="pure-button pure-button-primary">创建租户</a>
  </div>
  
  <?php if(isset($tenants) && !empty($tenants)): ?>
    <table class="pure-table pure-table-horizontal" style="width: 100%;">
      <thead>
        <tr>
          <th>ID</th>
          <th>名称</th>
          <th>状态</th>
          <th>创建时间</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tenants as $tenant): ?>
          <tr>
            <td><?=$tenant['id']?></td>
            <td><?=$tenant['name']?></td>
            <td><?=($tenant['status'] == 1) ? '启用' : '禁用'?></td>
            <td><?=$tenant['created_at']?></td>
            <td>
              <a href="<?=tenant_url('tenant/enter')?>?id=<?=$tenant['id']?>" class="pure-button pure-button-small">进入</a>
              <a href="<?=tenant_url('tenant/edit')?>?id=<?=$tenant['id']?>" class="pure-button pure-button-small">编辑</a>
              <a href="<?=tenant_url('tenant/delete')?>?id=<?=$tenant['id']?>" class="pure-button pure-button-small button-error" onclick="return confirm('确定要删除此租户吗？')">删除</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="pure-alert">暂无租户数据</div>
  <?php endif; ?>

  <?php if(isset($pagination)): ?>
    <div class="pagination" style="margin-top: 20px; text-align: center;">
      <span>共 <?=$pagination['totalItems']?> 条记录，每页显示 <?=$pagination['limit']?> 条，共 <?=$pagination['total']?> 页</span>
      <div style="margin-top: 10px;">
        <?php if($pagination['current'] > 1): ?>
          <a href="<?=tenant_url('tenant/')?>?page=<?=$pagination['current']-1?>&" class="pure-button pure-button-small">上一页</a>
        <?php endif; ?>

        <?php
        // 显示页码链接，最多显示10个页码
        $startPage = max(1, $pagination['current'] - 4);
        $endPage = min($pagination['total'], $startPage + 9);
        
        for($i = $startPage; $i <= $endPage; $i++):
        ?>
          <a href="<?=tenant_url('tenant/')?>?page=<?=$i?>&" class="pure-button pure-button-small <?=($i == $pagination['current']) ? 'pure-button-active' : ''?>">
            <?=$i?>
          </a>
        <?php endfor; ?>

        <?php if($pagination['current'] < $pagination['total']): ?>
          <a href="<?=tenant_url('tenant/')?>?page=<?=$pagination['current']+1?>&" class="pure-button pure-button-small">下一页</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>