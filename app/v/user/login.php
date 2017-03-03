
<div class="container">
	<form class="form-horizontal" method="post">
  	<h2 class="signin-heading">登录</h2>
    	<div class="control-group">
           <label class="control-label" for="username">用户名</label>
           <div class="controls">
           	  <input type="text" id="username" placeholder="用户名" name="username">
           	  <?=$err['username']?>
           </div>
      </div>
      <div class="control-group">
           <label class="control-label" for="password">密码</label>
           <div class="controls">
           	   <input type="password" id="passowrd" placeholder="密码" name="password">
           	   <?=$err['password'] ?>
           	   <?=$info ?>
           </div>
      </div>
      <div class="control-group">
           <div class="controls">
           	  <button class="btn btn-large btn-primary" type="submit">登录</button>
           </div>
      </div>
		</form>
</div>
