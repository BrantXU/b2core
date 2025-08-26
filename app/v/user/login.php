<div class="uk-container" style="width:500px;margin-top:40px;" >
  <h2>用户登录</h2>
  <?php if(isset($info) && $info): ?>
    <div class="pure-alert pure-alert-error"><?=$info?></div>
  <?php endif; ?>
  <form class="uk-form-stacked" method="POST" ßß>
  <div class="uk-margin">
      <label class="uk-form-label">用户名</label>
      <input class="uk-input uk-form-control" type="text" name="username" 
        value="<?=isset($val['username']) ? htmlspecialchars($val['username']) : ''?>" />
      <span><?=isset($err['username']) ? $err['username'] : ''?></span>
    </div>

  <div class="uk-margin">
      <label class="uk-form-label">密码</label>
      <input class="uk-input uk-form-control" type="password" name="password" />
      <span><?=isset($err['password']) ? $err['password'] : ''?></span>
    </div>
  <div class="uk-margin">
      <button type="submit" class="uk-button uk-button-primary">登录</button>
      <a href="<?=tenant_url('')?>" class="uk-button">返回</a>
    </div>
  </form>
</div>
