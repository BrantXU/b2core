<div>
  <h2><?=isset($_GET['action']) && $_GET['action'] == 'create' ? '创建配置' : '编辑配置'?></h2>
  
  <?php if(isset($err['general'])): ?>
    <div class="uk-alert uk-alert-danger"><?=$err['general']?></div>
  <?php endif; ?>
  
  <form method="post" class="uk-form uk-form-stacked">
    <div class="uk-margin">
      <label class="uk-form-label">键名</label>
      <div class="uk-form-controls">
        <input type="text" name="key" class="uk-input" 
          value="<?=isset($val['key']) ? htmlspecialchars($val['key']) : (isset($config['key']) ? htmlspecialchars($config['key']) : '')?>" />
        <span class="uk-text-danger"><?=isset($err['key']) ? $err['key'] : ''?></span>
      </div>
    </div>

    <div class="uk-margin">
      <label class="uk-form-label">值</label>
      <div class="uk-form-controls">
        <textarea name="value" rows="10" cols="50" class="uk-textarea"><?=isset($val['value']) ? htmlspecialchars($val['value']) : (isset($config['value']) ? htmlspecialchars($config['value']) : '')?></textarea>
        <div class="uk-text-meta">可以使用YAML格式输入，系统会自动转换为JSON格式保存。例如：
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
        <span class="uk-text-danger"><?=isset($err['value']) ? $err['value'] : ''?></span>
      </div>
    </div>

    <div class="uk-margin">
      <label class="uk-form-label">描述</label>
      <div class="uk-form-controls">
        <input type="text" name="description" class="uk-input" 
          value="<?=isset($val['description']) ? htmlspecialchars($val['description']) : (isset($config['description']) ? htmlspecialchars($config['description']) : '')?>" />
        <span class="uk-text-danger"><?=isset($err['description']) ? $err['description'] : ''?></span>
      </div>
    </div>

    <div class="uk-margin">
      <label class="uk-form-label">类别</label>
      <div class="uk-form-controls">
        <select name="config_type" class="uk-select">
          <option value="mod" <?=isset($val['config_type']) && $val['config_type'] == 'mod' ? 'selected' : (isset($config['config_type']) && $config['config_type'] == 'mod' ? 'selected' : '')?>>模型(mod)</option>
          <option value="flow" <?=isset($val['config_type']) && $val['config_type'] == 'flow' ? 'selected' : (isset($config['config_type']) && $config['config_type'] == 'flow' ? 'selected' : '')?>>流程(flow)</option>
          <option value="men" <?=isset($val['config_type']) && $val['config_type'] == 'men' ? 'selected' : (isset($config['config_type']) && $config['config_type'] == 'men' ? 'selected' : '')?>>菜单(men)</option>
          <option value="layout" <?=isset($val['config_type']) && $val['config_type'] == 'layout' ? 'selected' : (isset($config['config_type']) && $config['config_type'] == 'layout' ? 'selected' : '')?>>排版(layout)</option>
          <option value="doc" <?=isset($val['config_type']) && $val['config_type'] == 'doc' ? 'selected' : (isset($config['config_type']) && $config['config_type'] == 'doc' ? 'selected' : '')?>>文档(doc)</option>
        </select>
      </div>
    </div>

    <div class="uk-margin">
      <label class="uk-form-label">租户</label>
      <div class="uk-form-controls">
        <select name="tenant_id" class="uk-select">
          <?php if(isset($tenants) && !empty($tenants)): ?>
            <?php foreach ($tenants as $tenant): ?>
              <option value="<?=$tenant['id']?>" <?=isset($val['tenant_id']) && $val['tenant_id'] == $tenant['id'] ? 'selected' : (isset($config['tenant_id']) && $config['tenant_id'] == $tenant['id'] ? 'selected' : '')?>><?=$tenant['name']?></option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>
    </div>
    
    <?php if(!isset($_GET['action']) || $_GET['action'] != 'create'): ?>
    <input type="hidden" name="id" value="<?=isset($config['id']) ? $config['id'] : ''?>" />
    <?php endif; ?>

    <div class="uk-margin">
      <button type="submit" class="uk-button uk-button-primary"><?=isset($_GET['action']) && $_GET['action'] == 'create' ? '创建' : '更新'?></button>
      <a href="<?=BASE?>/config/" class="uk-button uk-button-default">返回</a>
    </div>
  </form>
</div>