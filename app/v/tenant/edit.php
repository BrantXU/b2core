<div>
  <h2>编辑租户</h2>
  
  <?php if(isset($err['general'])): ?>
    <div class="pure-alert pure-alert-error"><?=$err['general']?></div>
  <?php endif; ?>
  
  <form method="post" class="pure-form pure-form-stacked">
    <div>
      <label>租户名称</label>
      <input type="text" name="name" 
        value="<?=isset($val['name']) ? htmlspecialchars($val['name']) : (isset($tenant['name']) ? htmlspecialchars($tenant['name']) : '')?>" />
      <span class="help-inline"><?=isset($err['name']) ? $err['name'] : ''?></span>
    </div>

    <div>
      <label>状态</label>
      <select name="status">
        <option value="1" <?=isset($val['status']) && $val['status'] == 1 ? 'selected' : (isset($tenant['status']) && $tenant['status'] == 1 ? 'selected' : '')?>>启用</option>
        <option value="0" <?=isset($val['status']) && $val['status'] == 0 ? 'selected' : (isset($tenant['status']) && $tenant['status'] == 0 ? 'selected' : '')?>>禁用</option>
      </select>
    </div>
    
    <input type="hidden" name="id" value="<?=isset($tenant['id']) ? $tenant['id'] : ''?>" />

    <div>
      <button type="submit" class="pure-button pure-button-primary">更新</button>
      <a href="<?=BASE?>/tenant/" class="pure-button">返回</a>
    </div>
  </form>
</div>