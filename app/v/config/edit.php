<div>
  <h2>编辑配置</h2>
  
  <?php if(isset($err['general'])): ?>
    <div class="pure-alert pure-alert-error"><?=$err['general']?></div>
  <?php endif; ?>
  
  <form method="post" class="pure-form pure-form-stacked">
    <div>
      <label>键名</label>
      <input type="text" name="key" 
        value="<?=isset($val['key']) ? htmlspecialchars($val['key']) : (isset($config['key']) ? htmlspecialchars($config['key']) : '')?>" />
      <span class="help-inline"><?=isset($err['key']) ? $err['key'] : ''?></span>
    </div>

    <div>
      <label>值</label>
      <textarea name="value" rows="5" cols="50"><?=isset($val['value']) ? htmlspecialchars($val['value']) : (isset($config['value']) ? htmlspecialchars($config['value']) : '')?></textarea>
      <span class="help-inline"><?=isset($err['value']) ? $err['value'] : ''?></span>
    </div>

    <div>
      <label>描述</label>
      <input type="text" name="description" 
        value="<?=isset($val['description']) ? htmlspecialchars($val['description']) : (isset($config['description']) ? htmlspecialchars($config['description']) : '')?>" />
      <span class="help-inline"><?=isset($err['description']) ? $err['description'] : ''?></span>
    </div>
    
    <input type="hidden" name="id" value="<?=isset($config['id']) ? $config['id'] : ''?>" />

    <div>
      <button type="submit" class="pure-button pure-button-primary">更新</button>
      <a href="<?=BASE?>/config/" class="pure-button">返回</a>
    </div>
  </form>
</div>