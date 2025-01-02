<div> 
  <?if($u['id']!=0){?>
   当前登录 <?=isset($u['name']) ? $u['name'] : (isset($u['email']) ? $u['email'] : '用户')?> ，
   <a href="?/user/logout">退出登录</a><?
  }
  else {?>
  <ul class="nav nav-pills">
    <li><a href="?/user/reg/">注册</a></li>
    <li><a href="?/user/login/">请登录</a></li>
  </ul>
  <?}?>  
</div>  
