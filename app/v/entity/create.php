<div>
  <h2>创建实体</h2>
  
  <?php if(isset($err['general'])): ?>
    <div class="pure-alert pure-alert-error"><?=$err['general']?></div>
  <?php endif; ?>
  
  <form method="post" class="pure-form pure-form-stacked">
    <div>
      <label>实体名称</label>
      <input type="text" name="name" 
        value="<?=isset($val['name']) ? htmlspecialchars($val['name']) : ''?>" />
      <span class="help-inline"><?=isset($err['name']) ? $err['name'] : ''?></span>
    </div>

    <div>
      <label>实体类型</label>
      <input type="text" name="type" 
        value="<?=isset($val['type']) ? htmlspecialchars($val['type']) : ''?>" />
      <span class="help-inline"><?=isset($err['type']) ? $err['type'] : ''?></span>
    </div>

    <div>
      <label>实体数据 (JSON格式)</label>
      <textarea name="data" rows="5" cols="50"><?=isset($val['data']) ? htmlspecialchars($val['data']) : ''?></textarea>
    </div>

    <div>
      <label>描述</label>
      <input type="text" name="description" 
        value="<?=isset($val['description']) ? htmlspecialchars($val['description']) : ''?>" />
    </div>

    <div>
      <label>租户</label>
      <select name="tenant_id">
        <?php if(isset($tenants) && !empty($tenants)): ?>
          <?php foreach ($tenants as $tenant): ?>
            <option value="<?=$tenant['id']?>" <?=isset($val['tenant_id']) && $val['tenant_id'] == $tenant['id'] ? 'selected' : ''?>><?=$tenant['name']?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </div>

    <div>
      <button type="submit" class="pure-button pure-button-primary">创建</button>
      <a href="<?=BASE?>/entity/" class="pure-button">返回</a>
    </div>
  </form>
</div>