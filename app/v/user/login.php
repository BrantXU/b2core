<div>
  <h2>用户登录</h2>
  <?php if(isset($info) && $info): ?>
    <div class="pure-alert pure-alert-error"><?=$info?></div>
  <?php endif; ?>
  <form class="pure-form pure-form-stacked" method="post">
    <div>
      <label>用户名</label>
      <input type="text" name="username" 
        value="<?=isset($val['username']) ? htmlspecialchars($val['username']) : ''?>" />
      <span><?=isset($err['username']) ? $err['username'] : ''?></span>
    </div>

    <div>
      <label>密码</label>
      <input type="password" name="password" />
      <span><?=isset($err['password']) ? $err['password'] : ''?></span>
    </div>

    <div>
      <button type="submit" class="pure-button pure-button-primary">登录</button>
      <a href="<?=tenant_url('')?>" class="pure-button">返回</a>
    </div>
  </form>
</div>
