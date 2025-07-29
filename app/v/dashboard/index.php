<div>
  <h2>租户仪表板</h2>
  
  <div class="pure-alert pure-alert-info">
    <p>当前租户ID: <strong><?= htmlspecialchars($tenant_id) ?></strong></p>
  </div>
  
  <div style="margin-top: 20px;">
    <p>欢迎来到租户仪表板页面！</p>
    <p>您可以在这里添加租户特定的功能和信息。</p>
  </div>
  
  <div style="margin-top: 20px;">
    <a href="<?= tenant_url('tenant/') ?>" class="pure-button">返回租户列表</a>
  </div>
</div>