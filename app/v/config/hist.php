<?php
/**
 * 配置历史版本列表视图
 */
?>
<div class="uk-container uk-container-expand">
  <h1 class="uk-heading-divider">配置历史版本</h1>
  
  <div class="uk-card uk-card-default uk-card-body">
    <div class="uk-grid-small" uk-grid>
      <div class="uk-width-auto">
        <span class="uk-label uk-label-primary">配置ID</span>
      </div>
      <div class="uk-width-expand">
        <code><?= $config['id'] ?></code>
      </div>
    </div>
    
    <div class="uk-grid-small" uk-grid>
      <div class="uk-width-auto">
        <span class="uk-label uk-label-primary">配置键名</span>
      </div>
      <div class="uk-width-expand">
        <code><?= $config['key'] ?></code>
      </div>
    </div>
  </div>
  
  <?php if (!empty($hist)): ?>
    <div class="uk-margin-top">
      <h3>历史版本列表</h3>
      <div class="uk-overflow-auto">
        <table class="uk-table uk-table-divider uk-table-hover uk-table-small">
          <thead>
            <tr>
              <th>版本ID</th>
              <th>操作时间</th>
              <th>操作类型</th>
              <th>操作人</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($hist as $item): ?>
              <tr>
                <td><code><?= $item['id'] ?></code></td>
                <td><?= date('Y-m-d H:i:s', strtotime($item['time'])) ?></td>
                <td>
                  <span class="uk-label uk-label-<?= 
                    $item['action'] === 'create' ? 'success' : 
                    ($item['action'] === 'update' ? 'warning' : 'danger')
                  ?>">
                    <?= $item['action'] === 'create' ? '创建' : 
                         ($item['action'] === 'update' ? '更新' : '删除') ?>
                  </span>
                </td>
                <td><?= !empty($item['user']) ? $item['user'] : '系统' ?></td>
                <td>
                  <a href="<?= tenant_url('config/vhist/' . $item['id'] . '/' . $config['id']) ?>" 
                     class="uk-button uk-button-small uk-button-primary">
                    <span uk-icon="icon: eye"></span> 查看
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php else: ?>
    <div class="uk-margin-top uk-text-center">
      <div class="uk-alert uk-alert-warning">
        <p>暂无历史版本记录</p>
      </div>
    </div>
  <?php endif; ?>
  
  <div class="uk-margin-top">
    <a href="<?= tenant_url('config/') ?>" class="uk-button uk-button-default">
      <span uk-icon="icon: arrow-left"></span> 返回配置列表
    </a>
  </div>
</div>