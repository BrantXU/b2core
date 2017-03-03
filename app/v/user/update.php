
      <div class="container">
      <form class="form-horizontal" method="post">
        <h2 class="signin-heading">更新用户</h2>
         <div class="control-group">
           <label class="control-label" for="username">用户名</label>
           <div class="controls">
           	  <input type="text" id="username" placeholder="用户名" name="username" value=<?=$val['username'] ?>>
           	  <?=$err['username']?>
           </div>
        </div>
        <div class="control-group">
           <label class="control-label" for="password">密码</label>
           <div class="controls">
           	   <input type="password" id="passowrd" placeholder="密码" name="password" value=<?=$val['password'] ?>>
           	   <?=$err['password'] ?>
           </div>
        </div>
         <div class="control-group">
           <label class="control-label" for="repassword">确认密码</label>
           <div class="controls">
           	   <input type="password" id="repassowrd" placeholder="密码" name="repassword" value=<?=$val['password'] ?>>
           </div>
        </div>
        <div class="control-group">
           <label class="control-label" for="email">电子邮箱</label>
           <div class="controls">
           	   <input type="text" id="email" placeholder="电子邮箱" name="email" value=<?=$val['email'] ?>>
           	   <?=$err['email'] ?>
           	   <input type="hidden" value="<?=$user['id']?>" name="id">
           </div>
        </div>
        <div class="control-group">
           <div class="controls">
           	  <button class="btn btn-large btn-primary" type="submit">提交</button>
           </div>
        </div>
      </form>
      </div>
