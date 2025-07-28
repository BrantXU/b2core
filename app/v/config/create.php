<div>
  <h2>创建配置</h2>
  
  <?php if(isset($err['general'])): ?>
    <div class="pure-alert pure-alert-error"><?=$err['general']?></div>
  <?php endif; ?>
  
  <form method="post" class="pure-form pure-form-stacked">
    <div>
      <label>键名</label>
      <input type="text" name="key" 
        value="<?=isset($val['key']) ? htmlspecialchars($val['key']) : ''?>" />
      <span class="help-inline"><?=isset($err['key']) ? $err['key'] : ''?></span>
    </div>

    <div>
      <label>值</label>
      <textarea name="value" rows="10" cols="50"><?=isset($val['value']) ? htmlspecialchars($val['value']) : ''?></textarea>
      <div class="help-block">可以使用YAML格式输入，系统会自动转换为JSON格式保存。例如：
<pre>
name: 示例配置
items:
  - item1
  - item2
settings:
  debug: true
  timeout: 30
</pre>
</div>
      <span class="help-inline"><?=isset($err['value']) ? $err['value'] : ''?></span>
    </div>

    <div>
      <label>描述</label>
      <input type="text" name="description" 
        value="<?=isset($val['description']) ? htmlspecialchars($val['description']) : ''?>" />
      <span class="help-inline"><?=isset($err['description']) ? $err['description'] : ''?></span>
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
      <a href="<?=BASE?>/config/" class="pure-button">返回</a>
    </div>
  </form>
</div>