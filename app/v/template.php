<!DOCTYPE html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title><?=$page_title?></title>
  <meta name="keywords" content="<?=$meta_keywords?>"/>
  <meta name="description" content="<?=$meta_description?>"/>
  <link href="<?=BASE ?>/pure-min.css" rel="stylesheet" type="text/css">
  <style>
    body{background: #f9f9ff;line-height: 1.5;}
    .container{padding: 20px;}
    a{color: #000;}
    .help-inline{color: #f00;}
    .nav li{display: inline-block;} 
  </style>
</head>
<body>
  <div class="container">
    <div> 
  <?if($u['id']!=0){?>
   当前登录 <?=isset($u['name']) ? $u['name'] : (isset($u['email']) ? $u['email'] : '用户')?> ，
   <a href="?/user/logout">退出登录</a><?
  }
  else {?>
  <ul class="nav nav-pills">
    <li><a href="/user/reg/">注册</a></li>
    <li><a href="/user/login/">请登录</a></li>
    <li><a href="/tenant/">租户管理</a></li>
    <li><a href="/config/">配置管理</a></li>
    <li><a href="/user/">用户管理</a></li>
    <li><a href="/entity/">实体管理</a></li>
  </ul>
  <?}?>  
</div>


    <?php if(isset($al_content)) echo $al_content; else echo $content ?? '';?>
  </div>
</body>
</html>
