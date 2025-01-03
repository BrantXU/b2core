<div>
  <h2>用户注册</h2>
  <form method="post" class="pure-form pure-form-stacked">
    <div>
      <label>用户名</label>
      <input type="text" name="username" 
        value="<?=isset($val['username']) ? htmlspecialchars($val['username']) : ''?>" />
      <span><?=isset($err['username']) ? $err['username'] : ''?></span>
    </div>

    <div>
      <label>密码</label>
      <input type="password" name="password" 
        value="<?=isset($val['password']) ? htmlspecialchars($val['password']) : ''?>" />
      <span><?=isset($err['password']) ? $err['password'] : ''?></span>
    </div>

    <div>
      <label>确认密码</label>
      <input type="password" name="repassword" 
        value="<?=isset($val['repassword']) ? htmlspecialchars($val['repassword']) : ''?>" />
    </div>

    <div>
      <label>电子邮箱</label>
      <input type="text" name="email" 
        value="<?=isset($val['email']) ? htmlspecialchars($val['email']) : ''?>" />
      <span><?=isset($err['email']) ? $err['email'] : ''?></span>
    </div>

    <div>
      <button type="submit" class="pure-button pure-button-primary">注册</button>
      <a href="<?=BASE?>" class="pure-button">返回</a>
    </div>
  </form>
</div>
