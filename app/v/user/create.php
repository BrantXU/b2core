<div class="container">
<h2>创建用户</h2>
<?php if(!empty($err['error'])): ?>
<div class="pure-alert pure-alert-error"><?php echo $err['error']; ?></div>
<?php endif; ?>
<form class="pure-form pure-form-stacked" method="post">
    <fieldset>
        <label for="username">用户名</label>
        <input type="text" id="username" name="username" value="<?php echo isset($val['username']) ? htmlspecialchars($val['username']) : ''; ?>" class="pure-input-1">
        <?php if(!empty($err['username'])): ?><span class="pure-form-message-inline pure-alert pure-alert-error"><?php echo $err['username']; ?></span><?php endif; ?>
        
        <label for="password">密码</label>
        <input type="password" id="password" name="password" class="pure-input-1">
        <?php if(!empty($err['password'])): ?><span class="pure-form-message-inline pure-alert pure-alert-error"><?php echo $err['password']; ?></span><?php endif; ?>
        
        <label for="confirm_password">确认密码</label>
        <input type="password" id="confirm_password" name="confirm_password" class="pure-input-1">
        <?php if(!empty($err['confirm_password'])): ?><span class="pure-form-message-inline pure-alert pure-alert-error"><?php echo $err['confirm_password']; ?></span><?php endif; ?>
        
        <label for="email">电子邮箱</label>
        <input type="email" id="email" name="email" value="<?php echo isset($val['email']) ? htmlspecialchars($val['email']) : ''; ?>" class="pure-input-1">
        <?php if(!empty($err['email'])): ?><span class="pure-form-message-inline pure-alert pure-alert-error"><?php echo $err['email']; ?></span><?php endif; ?>
        
        <button type="submit" class="pure-button pure-button-primary">创建</button>
        <a href="<?=tenant_url('user')?>" class="pure-button">返回</a>
    </fieldset>
</form>
</div>