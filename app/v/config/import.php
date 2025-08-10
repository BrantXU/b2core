<div>
  <h2>lpp配置导入</h2>
  
  <?php if(isset($err['general'])): ?>
    <div class="uk-alert uk-alert-danger"><?=$err['general']?></div>
  <?php endif; ?>
  
  <form method="post" enctype="multipart/form-data" class="uk-form uk-form-stacked">
    <div class="uk-margin">
      <label class="uk-form-label">配置文件</label>
      <div class="uk-form-controls">
        <input type="file" name="config_file" class="uk-input" accept="*/*" />
        <div class="uk-text-meta">支持JSON或YAML格式的配置文件。</div>
        <div class="uk-text-meta">支持两种格式：</div>
        <div class="uk-text-meta">1. 单条配置：包含id和key字段的JSON/YAML对象</div>
        <div class="uk-text-meta">2. 多条配置：包含多个配置对象的JSON/YAML数组，每个对象必须包含id和key字段</div>
        <div class="uk-text-meta uk-text-danger">示例JSON数组格式：</div>
        <pre class="uk-code uk-margin-small-top">[
  {
    "id": "config1",
    "key": "key1",
    "config_type": "mod",
    "description": "配置描述1",
    "其他字段": "值"
  },
  {
    "id": "config2",
    "key": "key2",
    "config_type": "flow",
    "description": "配置描述2",
    "其他字段": "值"
  }
]</pre>
      </div>
    </div>
    
    <div class="uk-margin">
      <button type="submit" class="uk-button uk-button-primary">导入</button>
      <a href="<?=BASE?>/config/" class="uk-button uk-button-default">返回列表</a>
    </div>
  </form>
</div>