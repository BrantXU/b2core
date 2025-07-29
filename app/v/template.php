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
    
    /* 多级菜单样式 */
    .nav, .submenu {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    
    .nav > li {
      display: inline-block;
      position: relative;
    }
    
    .submenu {
      display: none;
      position: absolute;
      top: 100%;
      left: 0;
      background: #fff;
      border: 1px solid #ccc;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      z-index: 1000;
    }
    
    .nav > li:hover > .submenu {
      display: block;
    }
    
    .submenu-item {
      display: block;
      min-width: 150px;
    }
    
    .submenu .submenu {
      left: 100%;
      top: 0;
    }
    
    .submenu-item > a {
      display: block;
      padding: 8px 15px;
      text-decoration: none;
    }
    
    .submenu-item > a:hover {
      background: #f5f5f5;
    }
    
    .has-children > a::after {
      content: " ▼";
      font-size: 0.8em;
    }
  </style>
</head>
<body>
  <div class="container">
    <div> 
  <?php if(!empty($u) && is_array($u) && array_key_exists('id', $u) && $u['id']!=0){?>
   当前登录 <?=isset($u['name']) ? $u['name'] : (isset($u['email']) ? $u['email'] : '用户')?> ，
   <?php if(!empty($current_tenant) && is_array($current_tenant) && array_key_exists('name', $current_tenant) && isset($current_tenant['name'])){?>
   访问租户 <?=htmlspecialchars($current_tenant['name'] ?? '未知租户')?> ，
   <?php }?>
   <a href="<?=tenant_url('user/logout')?>">退出登录</a>
  <?php }  ?>
  <?php if(!empty($menu_data)): ?>
  <?=render_menu($menu_data)?>
  <?php endif; ?>  
</div>
    <?php if(isset($al_content)) echo $al_content; else echo $content ?? '';?>
  </div>
</body>
</html>
