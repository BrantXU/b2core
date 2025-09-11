<?php
/**
 * 配置历史版本详情视图
 */
// 检查是否有成功或错误消息
$successMsg = $_GET['success'] ?? '';
$errorMsg = $_GET['error'] ?? '';
?>
<div class="uk-container uk-container-expand">
  <?php if (!empty($successMsg)): ?>
    <div class="uk-alert uk-alert-success" uk-alert>
      <a class="uk-alert-close" uk-close></a>
      <p><?= htmlspecialchars($successMsg) ?></p>
    </div>
  <?php elseif (!empty($errorMsg)): ?>
    <div class="uk-alert uk-alert-danger" uk-alert>
      <a class="uk-alert-close" uk-close></a>
      <p><?= htmlspecialchars($errorMsg) ?></p>
    </div>
  <?php endif; ?>
  
  <h1 class="uk-heading-divider">历史版本详情</h1>
  
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
    
    <div class="uk-grid-small" uk-grid>
      <div class="uk-width-auto">
        <span class="uk-label uk-label-primary">版本ID</span>
      </div>
      <div class="uk-width-expand">
        <code><?= $hist_id ?></code>
      </div>
    </div>
    
    <div class="uk-grid-small" uk-grid>
      <div class="uk-width-auto">
        <span class="uk-label uk-label-primary">操作时间</span>
      </div>
      <div class="uk-width-expand">
        <?= isset($hist['time']) ? date('Y-m-d H:i:s', strtotime($hist['time'])) : '未知时间' ?>
      </div>
    </div>
    
    <div class="uk-grid-small" uk-grid>
      <div class="uk-width-auto">
        <span class="uk-label uk-label-primary">操作类型</span>
      </div>
      <div class="uk-width-expand">
        <?php if (isset($hist['action'])): ?>
          <span class="uk-label uk-label-<?= 
            $hist['action'] === 'create' ? 'success' : 
            ($hist['action'] === 'update' ? 'warning' : 'danger')
          ?>">
            <?= $hist['action'] === 'create' ? '创建' : 
                 ($hist['action'] === 'update' ? '更新' : '删除') ?>
          </span>
        <?php else: ?>
          <span class="uk-label uk-label-secondary">未知操作</span>
        <?php endif; ?>
      </div>
    </div>
    
    <div class="uk-grid-small" uk-grid>
      <div class="uk-width-auto">
        <span class="uk-label uk-label-primary">操作人</span>
      </div>
      <div class="uk-width-expand">
        <?= !empty($hist['user']) ? $hist['user'] : '系统' ?>
      </div>
    </div>
  </div>
  
  <?php if (isset($hist['data']) && !empty($hist['data'])): ?>
    <div class="uk-margin-top">
      <h3>配置数据</h3>
      <div class="uk-card uk-card-default uk-card-body">
        <pre class="uk-background-muted uk-padding-small" style="max-height: 400px; overflow: auto;"><?= 
          htmlspecialchars(json_encode($hist['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
        ?></pre>
      </div>
    </div>
  <?php endif; ?>
  
  <div class="uk-margin-top">
    <a href="<?= tenant_url('config/hist/' . $config['id']) ?>" class="uk-button uk-button-default">
      <span uk-icon="icon: arrow-left"></span> 返回历史版本列表
    </a>
    <a href="<?= tenant_url('config/') ?>" class="uk-button uk-button-default uk-margin-left">
      <span uk-icon="icon: list"></span> 返回配置列表
    </a>
    <button onclick="confirmRestore()" class="uk-button uk-button-primary uk-margin-left">
      <span uk-icon="icon: refresh"></span> 恢复此版本
    </button>
  </div>

  <script>
  function confirmRestore() {
    UIkit.modal.confirm('确定要恢复到此历史版本吗？当前配置将被覆盖。').then(function() {
      window.location.href = '<?= tenant_url('config/restore/' . $hist_id . '/' . $config['id']) ?>';
    }, function() {
      // 用户取消操作
    });
  }
  </script>
</div>